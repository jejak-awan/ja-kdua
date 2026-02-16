<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Billing;

use App\Exports\Isp\VoucherExport;
use App\Http\Controllers\Api\Core\BaseApiController;
use App\Imports\Isp\VoucherImport;
use App\Models\Isp\Customer\Partner;
use App\Models\Isp\Billing\Voucher;
use App\Services\Isp\Billing\VoucherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VoucherController extends BaseApiController
{
    protected VoucherService $service;

    public function __construct(VoucherService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of vouchers.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Voucher::with(['partner', 'serviceProfile']);

        if ($request->has('status')) {
            $status = $request->status;
            if (is_string($status) && str_contains($status, ',')) {
                $query->whereIn('status', explode(',', $status));
            } else {
                $query->where('status', $status);
            }
        }

        if ($request->has('batch_id')) {
            $query->where('batch_id', $request->batch_id);
        }

        if ($request->has('partner_id')) {
            $query->where('partner_id', $request->partner_id);
        }

        if ($request->has('search')) {
            /** @var string $search */
            $search = $request->search;
            $query->where('code', 'like', "%{$search}%");
        }

        if ($request->has('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('created_at', '<=', $request->end_date);
        }

        return $this->success($query->latest()->paginate(50), 'Vouchers retrieved successfully');
    }

    /**
     * Generate a batch of vouchers.
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'profile' => 'required|string',
            'price' => 'required|numeric|min:0',
            'count' => 'required|integer|min:1|max:1000',
            'prefix' => 'nullable|string|max:10',
            'pattern' => 'nullable|string|in:mixed,numbers,lowercase,uppercase',
            'duration' => 'nullable|string|max:20',
            'partner_id' => 'nullable|integer|exists:isp_partners,id',
            'profile_id' => 'nullable|integer|exists:isp_plans,id',
        ]);

        $result = $this->service->generateBatch(
            (int) $validated['count'],
            (string) $validated['profile'],
            (float) $validated['price'],
            $validated['prefix'] ?? '',
            $validated['pattern'] ?? 'mixed',
            $validated['duration'] ?? null,
            [
                'partner_id' => $validated['partner_id'] ?? null,
                'profile_id' => $validated['profile_id'] ?? null,
                'created_by' => $request->user()?->id,
            ]
        );

        return $this->success($result, 'Vouchers generated successfully', 201);
    }

    /**
     * Create a single voucher.
     */
    public function createSingle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:isp_vouchers,code',
            'profile' => 'required|string',
            'price' => 'required|numeric|min:0',
            'duration' => 'nullable|string|max:20',
            'partner_id' => 'nullable|integer|exists:isp_partners,id',
            'profile_id' => 'nullable|integer|exists:isp_plans,id',
        ]);

        try {
            $result = $this->service->createSingleVoucher($validated, $request->user());

            return $this->success($result, 'Voucher created successfully', 201);
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to create single voucher', 'VoucherController@createSingle');
        }
    }

    /**
     * Sell voucher(s) to a partner.
     */
    public function sell(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'voucher_ids' => 'required|array|min:1',
                'voucher_ids.*' => 'integer|exists:isp_vouchers,id',
                'partner_id' => 'required|integer|exists:isp_partners,id',
            ]);
            // No more Mitra, it's Partner
            /** @var Partner $partner */
            $partner = Partner::findOrFail((int) $validated['partner_id']);

            $result = $this->service->sellToPartner(
                array_map('intval', $validated['voucher_ids']),
                $partner,
                $request->user()
            );

            return $this->success($result, 'Vouchers sold to partner successfully');
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to sell vouchers', 'VoucherController@sell');
        }
    }

    /**
     * Get stock summary including totals and grouped by profile.
     */
    public function stockSummary(): JsonResponse
    {
        try {
            return $this->success([
                'global' => $this->service->getGlobalStockSummary(),
                'by_profile' => $this->service->getStockSummary(),
            ], 'Stock summary retrieved successfully');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to get stock summary', 'VoucherController@stockSummary');
        }
    }

    /**
     * Delete a batch of vouchers.
     */
    public function destroyBatch(string $batchId): JsonResponse
    {
        Voucher::where('batch_id', $batchId)->where('status', 'Available')->delete();

        return $this->success(null, 'Batch deleted successfully');
    }

    /**
     * Remove the specified voucher.
     */
    public function destroy(Voucher $voucher): JsonResponse
    {
        if ($voucher->status === 'Used') {
            return $this->error('Cannot delete a used voucher', 422);
        }

        $voucher->delete();

        return $this->success(null, 'Voucher deleted successfully');
    }

    /**
     * Get sales report for a specific month.
     */
    public function salesReport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2100',
            'month' => 'required|integer|min:1|max:12',
            'partner_id' => 'nullable|integer|exists:isp_partners,id',
        ]);

        $report = $this->service->getSalesReport(
            (int) $validated['year'],
            (int) $validated['month'],
            isset($validated['partner_id']) ? (int) $validated['partner_id'] : null
        );

        return $this->success($report, 'Sales report retrieved successfully');
    }

    /**
     * Get today's and this month's sales summary.
     */
    public function summary(): JsonResponse
    {
        $summary = $this->service->getTodaysSummary();

        return $this->success($summary, 'Sales summary retrieved successfully');
    }

    /**
     * Export vouchers to RouterOS script format.
     */
    public function exportScript(Request $request): Response
    {
        $filters = $request->only(['status', 'batch_id', 'profile']);

        $script = $this->service->exportToScript($filters);

        return response($script, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="vouchers-'.now()->format('Y-m-d').'.rsc"',
        ]);
    }

    /**
     * Export vouchers to CSV format.
     */
    public function exportCsv(Request $request): Response
    {
        $filters = $request->only(['status', 'batch_id', 'profile']);

        $csv = $this->service->exportToCsv($filters);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="vouchers-'.now()->format('Y-m-d').'.csv"',
        ]);
    }

    /**
     * Delete multiple vouchers by ID.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:isp_vouchers,id',
        ]);

        $this->service->bulkDelete(array_map('intval', (array) $validated['ids']));

        return $this->success(null, 'Vouchers deleted successfully');
    }

    /**
     * Update status for multiple vouchers.
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:isp_vouchers,id',
            'status' => 'required|string|in:Available,Sold,Expired',
        ]);

        $this->service->bulkUpdateStatus(
            array_map('intval', (array) $validated['ids']),
            (string) $validated['status']
        );

        return $this->success(null, 'Voucher statuses updated successfully');
    }

    /**
     * Refund a voucher.
     */
    public function refund(Request $request, Voucher $voucher): JsonResponse
    {
        try {
            $this->service->refundVoucher($voucher, $request->user());

            return $this->success(null, 'Voucher refunded successfully');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Refund failed', 'VoucherController@refund');
        }
    }

    /**
     * Reset usage counter.
     */
    public function resetCounter(Voucher $voucher): JsonResponse
    {
        try {
            $this->service->resetVoucherCounter($voucher);

            return $this->success(null, 'Usage counter reset successfully');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Reset failed', 'VoucherController@resetCounter');
        }
    }

    /**
     * Import vouchers from file.
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt',
        ]);

        try {
            /** @var \App\Services\Isp\Network\RadiusService $radius */
            $radius = app(\App\Services\Isp\Network\RadiusService::class);
            Excel::import(new VoucherImport($radius), $request->file('file'));

            return $this->success(null, 'Vouchers imported successfully');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Import failed', 'VoucherController@import');
        }
    }

    /**
     * Export vouchers to file.
     */
    public function export(Request $request): BinaryFileResponse|JsonResponse
    {
        try {
            $filters = $request->only(['status', 'partner_id']);

            return Excel::download(new VoucherExport($filters), 'vouchers.xlsx');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Bulk cleanup vouchers by criteria.
     */
    public function cleanup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'nullable|string|in:Expired,Available',
            'before_date' => 'nullable|date',
        ]);

        $count = $this->service->deleteByCriteria($validated);

        return $this->success(['deleted_count' => $count], "Deleted {$count} vouchers successfully");
    }

    /**
     * Bulk refund sold vouchers.
     */
    public function bulkRefund(Request $request): JsonResponse
    {
        $request->validate(['ids' => 'required|array|min:1']);

        try {
            $ids = (array) $request->input('ids');
            $vouchers = Voucher::whereIn('id', $ids)->get();
            $count = 0;

            foreach ($vouchers as $voucher) {
                // Only Sold or Available can be refunded logic is in service
                $this->service->refundVoucher($voucher, $request->user());
                $count++;
            }

            return $this->success(null, "Successfully refunded {$count} vouchers");
        } catch (\Exception $e) {
            return $this->handleException($e, 'Bulk refund failed');
        }
    }

    /**
     * Bulk reset voucher counters.
     */
    public function bulkResetCounter(Request $request): JsonResponse
    {
        $request->validate(['ids' => 'required|array|min:1']);

        try {
            $ids = (array) $request->input('ids');
            $vouchers = Voucher::whereIn('id', $ids)->get();
            $count = 0;

            foreach ($vouchers as $voucher) {
                $this->service->resetVoucherCounter($voucher);
                $count++;
            }

            return $this->success(null, "Successfully reset {$count} voucher counters");
        } catch (\Exception $e) {
            return $this->handleException($e, 'Bulk reset failed');
        }
    }

    /**
     * Sync voucher usage with RADIUS.
     */
    public function syncUsage(Request $request): JsonResponse
    {
        try {
            $count = $this->service->syncUsageWithRadius();

            return $this->success(['updated_count' => $count], "Voucher usage sync completed: {$count} vouchers updated.");
        } catch (\Exception $e) {
            return $this->handleException($e, 'Sync failed', 'VoucherController@syncUsage');
        }
    }
}
