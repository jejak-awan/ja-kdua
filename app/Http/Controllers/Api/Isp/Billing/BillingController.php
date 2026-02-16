<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Billing;

use App\Http\Controllers\Api\Core\BaseApiController;
use App\Models\Isp\Customer\Customer;
use App\Models\Isp\Billing\Invoice;
use App\Models\Isp\Billing\IspPlan;
use App\Services\Isp\Billing\BillingService;
use App\Services\Isp\Billing\ProrataService;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Isp\Billing\InvoiceExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BillingController extends BaseApiController
{
    /**
     * @var BillingService
     */
    protected BillingService $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    /**
     * Display a listing of billing plans.
     */
    public function plans(): \Illuminate\Http\JsonResponse
    {
        $plans = IspPlan::all();

        return $this->success($plans, 'Billing plans retrieved successfully');
    }

    /**
     * Display a listing of invoices.
     */
    public function invoices(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Invoice::with('user');

        if ($request->has('status')) {
            $status = $request->input('status');
            if (is_string($status) && $status !== 'all') {
                $query->where('status', $status);
            }
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $searchTerm = is_string($search) ? $search : '';
                $q->where('name', 'like', '%'.$searchTerm.'%')
                    ->orWhere('email', 'like', '%'.$searchTerm.'%');
            });
        }

        $perPage = $request->input('per_page');
        $perPageInt = is_numeric($perPage) ? (int) $perPage : 15;
        $invoices = $query->latest()->paginate($perPageInt);

        return $this->success($invoices, 'Invoices retrieved successfully');
    }

    /**
     * Export invoices to Excel.
     */
    public function exportExcel(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(new InvoiceExport, 'invoices_'.now()->format('Y-m-d').'.xlsx');
    }

    /**
     * Get billing statistics.
     */
    public function stats(): \Illuminate\Http\JsonResponse
    {
        $revenueHistory = Invoice::select(
            DB::raw("to_char(due_date, 'YYYY-MM') as month_key"),
            DB::raw("to_char(due_date, 'Mon YYYY') as month"),
            DB::raw("SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as amount")
        )
            ->where('due_date', '>=', now()->subMonths(6))
            ->groupBy('month_key', 'month')
            ->orderBy('month_key')
            ->get();

        $stats = [
            'total_revenue' => Invoice::where('status', 'paid')->sum('amount'),
            'pending_amount' => Invoice::where('status', 'unpaid')->sum('amount'),
            'paid_count' => Invoice::where('status', 'paid')->count(),
            'unpaid_count' => Invoice::where('status', 'unpaid')->count(),
            'revenue_history' => $revenueHistory,
        ];

        return $this->success($stats, 'Billing statistics retrieved successfully');
    }

    /**
     * Mark invoice as paid (Simulated).
     */
    public function pay(Request $request, Invoice $invoice): \Illuminate\Http\JsonResponse
    {
        $billingService = app(\App\Services\Isp\Billing\BillingService::class);
        $method = $request->input('method', 'manual');
        $success = $billingService->processPayment($invoice, is_string($method) ? $method : 'manual');

        if ($success) {
            return $this->success($invoice->fresh(), 'Invoice processed and paid successfully');
        }

        return $this->error('Failed to process invoice payment', 500);
    }

    /**
     * Manually trigger invoice generation for today.
     */
    public function generate(Request $request): \Illuminate\Http\JsonResponse
    {
        $billingService = app(\App\Services\Isp\Billing\BillingService::class);
        $count = $billingService->generateInvoicesForToday();

        return $this->success(['count' => $count], "Successfully generated {$count} invoices.");
    }

    /**
     * Manually trigger overdue suspension check.
     */
    public function runOverdueCheck(Request $request): \Illuminate\Http\JsonResponse
    {
        $billingService = app(\App\Services\Isp\Billing\BillingService::class);
        $count = $billingService->suspendOverdueCustomers();

        return $this->success(['count' => $count], "Successfully suspended {$count} overdue customers.");
    }

    /**
     * Download a professional PDF invoice.
     */
    public function downloadPdf(Invoice $invoice): \Symfony\Component\HttpFoundation\Response
    {
        $invoice->load('user');

        $mpdf = new \Mpdf\Mpdf([
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
        ]);

        $html = view('isp.pdf.invoice', [
            'invoice' => $invoice,
            'user' => $invoice->user,
        ])->render();

        $mpdf->WriteHTML($html);

        return response($mpdf->Output($invoice->invoice_number.'.pdf', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="'.$invoice->invoice_number.'.pdf"');
    }

    /**
     * Preview the pro-rated adjustment for a plan upgrade.
     */
    public function previewUpgrade(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'customer_id' => 'required|exists:isp_customers,id',
            'new_plan_id' => 'required|exists:isp_plans,id',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors()->toArray());
        }

        /** @var \App\Models\Isp\Customer\Customer|null $customer */
        $customer = \App\Models\Isp\Customer\Customer::find($request->customer_id);

        /** @var \App\Models\Isp\Billing\IspPlan|null $newPlan */
        $newPlan = \App\Models\Isp\Billing\IspPlan::find($request->new_plan_id);

        if (! $customer || ! $newPlan || ! $customer->plan) {
            return $this->error('Incomplete data for adjustment', 400);
        }

        /** @var \App\Services\Isp\Billing\ProrataService $prorata */
        $prorata = app(\App\Services\Isp\Billing\ProrataService::class);

        $nextBilling = $customer->billing_due_date ? Carbon::parse($customer->billing_due_date) : now()->addMonth()->startOfMonth();

        $adjustment = $prorata->calculateAdjustment(
            (float) $customer->plan->price,
            (float) $newPlan->price,
            now(),
            $nextBilling
        );

        return $this->success([
            'current_plan' => $customer->plan->name,
            'new_plan' => $newPlan->name,
            'days_remaining' => now()->diffInDays($nextBilling),
            'adjustment_amount' => $adjustment,
            'next_billing_date' => $nextBilling->toDateString(),
        ], 'Plan upgrade adjustment calculated');
    }
}
