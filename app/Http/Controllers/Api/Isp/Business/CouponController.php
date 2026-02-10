<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Business;

use App\Http\Controllers\Api\Core\BaseApiController;
use App\Models\Isp\Coupon;
use App\Services\Isp\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends BaseApiController
{
    public function __construct(
        protected CouponService $couponService
    ) {}

    /**
     * List all coupons.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['search', 'is_active', 'per_page']);

            $coupons = $this->couponService->list($filters);

            return $this->success($coupons, 'Coupons retrieved');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to get coupons', 'CouponController@index');
        }
    }

    /**
     * Create a new coupon.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|max:50|unique:isp_coupons,code',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:500',
                'discount_type' => ['required', Rule::in(['percentage', 'fixed'])],
                'discount_value' => 'required|numeric|min:0',
                'min_transaction' => 'nullable|numeric|min:0',
                'max_discount' => 'nullable|numeric|min:0',
                'max_usage' => 'nullable|integer|min:1',
                'max_per_customer' => 'nullable|integer|min:1',
                'valid_from' => 'nullable|date',
                'valid_until' => 'nullable|date|after:valid_from',
                'is_active' => 'nullable|boolean',
            ]);

            $validated['created_by'] = $request->user()?->id;

            $coupon = $this->couponService->create($validated);

            return $this->success($coupon, 'Coupon created', 201);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to create coupon', 'CouponController@store');
        }
    }

    /**
     * Show coupon details.
     */
    public function show(int $id): JsonResponse
    {
        try {
            /** @var Coupon $coupon */
            $coupon = Coupon::with('usages')->findOrFail($id);

            return $this->success($coupon, 'Coupon retrieved');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to get coupon', 'CouponController@show');
        }
    }

    /**
     * Update coupon.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            /** @var Coupon $coupon */
            $coupon = Coupon::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string|max:500',
                'discount_type' => ['sometimes', Rule::in(['percentage', 'fixed'])],
                'discount_value' => 'sometimes|numeric|min:0',
                'min_transaction' => 'nullable|numeric|min:0',
                'max_discount' => 'nullable|numeric|min:0',
                'max_usage' => 'nullable|integer|min:1',
                'max_per_customer' => 'nullable|integer|min:1',
                'valid_from' => 'nullable|date',
                'valid_until' => 'nullable|date',
                'is_active' => 'nullable|boolean',
            ]);

            $coupon = $this->couponService->update($coupon, $validated);

            return $this->success($coupon, 'Coupon updated');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to update coupon', 'CouponController@update');
        }
    }

    /**
     * Delete coupon.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            /** @var Coupon $coupon */
            $coupon = Coupon::findOrFail($id);
            $coupon->delete();

            return $this->success(null, 'Coupon deleted');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to delete coupon', 'CouponController@destroy');
        }
    }

    /**
     * Validate a coupon for a customer.
     */
    public function validateCoupon(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string',
                'customer_id' => 'required|integer|exists:isp_customers,id',
                'amount' => 'required|numeric|min:0',
            ]);

            $result = $this->couponService->validate(
                (string) $validated['code'],
                (int) $validated['customer_id'],
                (float) $validated['amount']
            );

            if (! $result['valid']) {
                return $this->error($result['message'], 422);
            }

            return $this->success($result, 'Coupon validated');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to validate coupon', 'CouponController@validateCoupon');
        }
    }

    /**
     * Redeem (apply) a coupon.
     */
    public function redeem(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string',
                'customer_id' => 'required|integer|exists:isp_customers,id',
                'amount' => 'required|numeric|min:0',
                'invoice_id' => 'nullable|integer|exists:isp_invoices,id',
            ]);

            // Validate first
            $validation = $this->couponService->validate(
                (string) $validated['code'],
                (int) $validated['customer_id'],
                (float) $validated['amount']
            );

            if (! $validation['valid']) {
                return $this->error($validation['message'], 422);
            }

            $usage = $this->couponService->redeem(
                (string) $validated['code'],
                (int) $validated['customer_id'],
                (float) $validated['amount'],
                isset($validated['invoice_id']) ? (int) $validated['invoice_id'] : null
            );

            return $this->success($usage, 'Coupon redeemed successfully');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to redeem coupon', 'CouponController@redeem');
        }
    }
}
