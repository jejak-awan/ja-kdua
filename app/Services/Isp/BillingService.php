<?php

declare(strict_types=1);

namespace App\Services\Isp;

use App\Models\Isp\Customer;
use App\Models\Isp\Invoice;
use App\Models\Isp\InvoiceItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BillingService
{
    protected RouterService $routerService;

    public function __construct(RouterService $routerService)
    {
        $this->routerService = $routerService;
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
                $ppn = round($subtotal * 0.11, 2); // 11% PPN
                $bhp = round($subtotal * 0.005, 2); // 0.5% BHP
                $uso = round($subtotal * 0.0125, 2); // 1.25% USO
                $tax = $ppn + $bhp + $uso;
            }

            $finalAmount = $subtotal + $tax + (float) $uniqueCode;

            $invoice = Invoice::create([
                'user_id' => $customer->user_id,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'unique_code' => $uniqueCode,
                'amount' => $finalAmount,
                'due_date' => Carbon::today()->addDays(7), // Standard 7 days due date
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
            ->with(['user.customer', 'user.customer.node']) // Assuming relation exists
            ->get();

        $suspendedCount = 0;

        foreach ($overdueInvoices as $invoice) {
            $user = $invoice->user;
            $customer = $user->customer ?? null;
            if ($customer && $customer->status === 'active') {
                $customer->update(['status' => 'suspended']);

                // --- Router Synchronization ---
                $syncSuccess = $this->routerService->suspendCustomer($customer);

                if ($syncSuccess) {
                    Log::info('BillingService: Customer suspended on router', [
                        'customer_id' => $customer->id,
                        'mikrotik_login' => $customer->mikrotik_login,
                    ]);
                } else {
                    Log::error('BillingService: Failed to suspend customer on router', [
                        'customer_id' => $customer->id,
                        'mikrotik_login' => $customer->mikrotik_login,
                    ]);
                }

                $suspendedCount++;
            }
        }

        return $suspendedCount;
    }
}
