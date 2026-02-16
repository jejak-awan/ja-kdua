<?php

declare(strict_types=1);

namespace App\Services\Isp\Billing;

use App\Models\Isp\Customer\Customer;
use App\Models\Isp\Billing\Invoice;
use App\Models\Isp\Billing\InvoiceItem;
use App\Services\Isp\Network\RouterService;
use App\Services\Isp\ThirdParty\WhatsAppNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BillingService
{
    protected RouterService $routerService;

    protected WhatsAppNotificationService $whatsApp;

    public function __construct(
        RouterService $routerService,
        WhatsAppNotificationService $whatsApp
    ) {
        $this->routerService = $routerService;
        $this->whatsApp = $whatsApp;
    }

    /**
     * Generate invoices for customers whose billing cycle starts today.
     */
    public function generateInvoicesForToday(): int
    {
        $today = Carbon::today();
        $dayOfMonth = $today->day;

        // Find customers whose billing cycle starts today
        /** @var \Illuminate\Database\Eloquent\Collection<int, Customer> $customers */
        $customers = Customer::where('status', 'active')
            ->where('billing_cycle_start', $dayOfMonth)
            ->with(['user', 'plan'])
            ->get();

        $generatedCount = 0;
        $period = $today->format('Y-m');

        foreach ($customers as $customer) {
            if ($this->generateInvoiceForCustomer($customer, $period)) {
                $generatedCount++;
            }
        }

        return $generatedCount;
    }

    /**
     * Generate an invoice for a specific customer and period.
     */
    public function generateInvoiceForCustomer(Customer $customer, string $period): ?Invoice
    {
        // Check if invoice already exists for this period
        $exists = Invoice::where('user_id', $customer->user_id)
            ->where('billing_period', $period)
            ->exists();

        if ($exists) {
            return null;
        }

        return DB::transaction(function () use ($customer, $period) {
            $plan = $customer->plan;
            if (! $plan) {
                Log::warning('BillingService: Customer has no plan', ['customer_id' => $customer->id]);

                return null;
            }

            $subtotal = (float) $plan->price;
            $tax = 0.0;
            $uniqueCode = is_numeric($customer->unique_code) ? (int) $customer->unique_code : 0;

            if ($customer->is_taxed) {
                // Get dynamic tax rates from settings or use hardcoded defaults
                $ppnVal = \App\Models\Core\Setting::get('billing_tax_ppn', 0.11);
                $ppnRate = is_numeric($ppnVal) ? (float) $ppnVal : 0.11;

                $bhpVal = \App\Models\Core\Setting::get('billing_tax_bhp', 0.005);
                $bhpRate = is_numeric($bhpVal) ? (float) $bhpVal : 0.005;

                $usoVal = \App\Models\Core\Setting::get('billing_tax_uso', 0.0125);
                $usoRate = is_numeric($usoVal) ? (float) $usoVal : 0.0125;

                $ppn = round($subtotal * $ppnRate, 2);
                $bhp = round($subtotal * $bhpRate, 2);
                $uso = round($subtotal * $usoRate, 2);
                $tax = $ppn + $bhp + $uso;
            }

            $finalAmount = round($subtotal + $tax + (float) $uniqueCode, 2);
            $dueDaysVal = \App\Models\Core\Setting::get('billing_invoice_due_days', 7);
            $dueDays = is_numeric($dueDaysVal) ? (int) $dueDaysVal : 7;

            $invoice = Invoice::create([
                'user_id' => $customer->user_id,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'unique_code' => $uniqueCode,
                'amount' => $finalAmount,
                'due_date' => Carbon::today()->addDays($dueDays),
                'status' => 'unpaid',
                'billing_period' => $period,
            ]);

            // Add main plan item
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'name' => 'Monthly Fee: '.$plan->name,
                'price' => $subtotal,
                'qty' => 1,
                'total' => $subtotal,
            ]);

            return $invoice;
        });
    }

    /**
     * Suspend customers who have unpaid overdue invoices.
     */
    public function suspendOverdueCustomers(): int
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Invoice> $overdueInvoices */
        $overdueInvoices = Invoice::where('status', 'unpaid')
            ->where('due_date', '<', Carbon::today())
            ->with(['user.customer'])
            ->get();

        $suspendedCount = 0;

        foreach ($overdueInvoices as $invoice) {
            $user = $invoice->user;
            $customer = $user->customer ?? null;
            if ($customer && $customer->status === 'active') {
                $customer->update(['status' => 'suspended']);

                // Dispatch background job for router synchronization
                \App\Jobs\Isp\SuspendCustomerJob::dispatch($customer);

                // Send WhatsApp notification
                if ($user->phone) {
                    $this->whatsApp->sendSuspensionNotice($user->phone, $user->name);
                }

                Log::info('BillingService: Queued suspension job and sent notice for customer', [
                    'customer_id' => $customer->id,
                    'mikrotik_login' => $customer->mikrotik_login,
                ]);

                $suspendedCount++;
            }
        }

        return $suspendedCount;
    }

    /**
     * Process an invoice payment, reactivating the customer if they were suspended.
     */
    public function processPayment(Invoice $invoice, ?string $method = 'manual'): bool
    {
        return DB::transaction(function () use ($invoice, $method) {
            if ($invoice->status === 'paid') {
                return true;
            }

            $customer = $invoice->user->customer ?? null;
            if (! $customer) {
                Log::error('BillingService: Cannot process payment, customer record not found.', ['invoice_id' => $invoice->id]);

                return false;
            }

            // 1. Update Invoice Status
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_method' => $method,
            ]);

            // 2. Add Debit Transaction to Ledger
            $invoiceAmount = (float) $invoice->amount;
            $customer->addDebit(
                $invoiceAmount,
                'invoice_payment',
                "Payment for invoice #{$invoice->id}",
                'invoice',
                $invoice->id
            );

            // 3. Reactivate customer if suspended
            if ($customer->status === 'suspended') {
                $customer->update(['status' => 'active']);

                // Dispatch background job for router reactivation
                \App\Jobs\Isp\ReactivateCustomerJob::dispatch($customer);

                // Optional: Send "Thank you/Reactivated" notice if needed
                if ($invoice->user->phone) {
                    $this->whatsApp->sendMessage($invoice->user->phone, "Terima kasih *{$invoice->user->name}*, pembayaran tagihan #{$invoice->id} telah diterima. Layanan internet Anda sedang diaktifkan kembali.");
                }

                Log::info('BillingService: Queued reactivation job and sent confirmation for customer', [
                    'customer_id' => $customer->id,
                    'invoice_id' => $invoice->id,
                ]);
            }

            return true;
        });
    }

    /**
     * Send WhatsApp reminders for overdue invoices.
     */
    public function sendOverdueReminders(): int
    {
        // Find unpaid invoices that were due 1-3 days ago
        /** @var \Illuminate\Database\Eloquent\Collection<int, Invoice> $overdueInvoices */
        $overdueInvoices = Invoice::where('status', 'unpaid')
            ->whereBetween('due_date', [Carbon::today()->subDays(3), Carbon::today()->subDay()])
            ->with(['user.customer'])
            ->get();

        $sentCount = 0;

        foreach ($overdueInvoices as $invoice) {
            $user = $invoice->user;
            $customer = $user->customer ?? null;

            if ($customer && $user->phone) {
                $this->whatsApp->sendOverdueReminder(
                    (string) $user->phone,
                    (string) $user->name,
                    number_format((float) $invoice->amount, 0, ',', '.'),
                    $invoice->due_date ? $invoice->due_date->format('d M Y') : '---'
                );
                $sentCount++;
            }
        }

        return $sentCount;
    }
}
