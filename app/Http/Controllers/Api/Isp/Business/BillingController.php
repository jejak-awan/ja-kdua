<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Business;

use App\Http\Controllers\Api\Core\BaseApiController;
use App\Models\Isp\Invoice;
use App\Models\Isp\IspPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingController extends BaseApiController
{
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
     * Get billing statistics.
     */
    public function stats(): \Illuminate\Http\JsonResponse
    {
        $revenueHistory = Invoice::select(
            DB::raw("DATE_FORMAT(due_date, '%b') as month"),
            DB::raw("SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as amount")
        )
            ->where('due_date', '>=', now()->subMonths(6))
            ->groupBy(DB::raw("DATE_FORMAT(due_date, '%Y-%m')"), DB::raw("DATE_FORMAT(due_date, '%b')"))
            ->orderBy(DB::raw('MIN(due_date)'))
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
        $invoice->update(['status' => 'paid']);

        return $this->success($invoice, 'Invoice marked as paid successfully');
    }

    /**
     * Manually trigger invoice generation for today.
     */
    public function generate(Request $request): \Illuminate\Http\JsonResponse
    {
        $billingService = app(\App\Services\Isp\BillingService::class);
        $count = $billingService->generateInvoicesForToday();

        return $this->success(['count' => $count], "Successfully generated {$count} invoices.");
    }

    /**
     * Manually trigger overdue suspension check.
     */
    public function runOverdueCheck(Request $request): \Illuminate\Http\JsonResponse
    {
        $billingService = app(\App\Services\Isp\BillingService::class);
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
}
