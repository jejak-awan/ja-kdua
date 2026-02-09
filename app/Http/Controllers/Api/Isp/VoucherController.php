<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\Isp\Voucher;
use App\Services\Isp\VoucherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
        $query = Voucher::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('batch_id')) {
            $query->where('batch_id', $request->batch_id);
        }

        if ($request->has('search')) {
            /** @var string $search */
            $search = $request->search;
            $query->where('code', 'like', "%{$search}%");
        }

        return $this->success($query->latest()->paginate(20), 'Vouchers retrieved successfully');
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
            'duration' => 'nullable|string|max:20', // e.g. 1h, 1d, 3600
        ]);

        $result = $this->service->generateBatch(
            (int) $validated['count'],
            (string) $validated['profile'],
            (float) $validated['price'],
            $validated['prefix'] ?? '',
            $validated['pattern'] ?? 'mixed',
            $validated['duration'] ?? null
        );

        return $this->success($result, 'Vouchers generated successfully', 201);
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
        ]);

        $report = $this->service->getSalesReport(
            (int) $validated['year'],
            (int) $validated['month']
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
            'Content-Disposition' => 'attachment; filename="vouchers-' . now()->format('Y-m-d') . '.rsc"',
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
            'Content-Disposition' => 'attachment; filename="vouchers-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}
