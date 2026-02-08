<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\Isp\Voucher;
use App\Services\Isp\VoucherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        ]);

        $result = $this->service->generateBatch(
            $validated['profile'],
            $validated['price'],
            $validated['count'],
            $validated['prefix'] ?? ''
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
}
