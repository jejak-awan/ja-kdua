<?php

declare(strict_types=1);

namespace App\Services\Isp\Customer;

use App\Models\Isp\Customer\Coupon;
use App\Models\Isp\Customer\CouponUsage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CouponService
{
    /**
     * Get all coupons with filtering.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Coupon>
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Coupon::query();

        if (isset($filters['search']) && is_string($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search): void {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        $perPage = isset($filters['per_page']) && is_numeric($filters['per_page']) ? (int) $filters['per_page'] : 15;

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new coupon.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Coupon
    {
        return Coupon::create($data);
    }

    /**
     * Update coupon.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Coupon $coupon, array $data): Coupon
    {
        $coupon->update($data);

        return $coupon->fresh() ?? $coupon;
    }

    /**
     * Validate and apply a coupon for a customer transaction.
     *
     * @return array{valid: bool, message: string, discount: float}
     */
    public function validate(string $code, int $customerId, float $transactionAmount): array
    {
        $coupon = Coupon::where('code', $code)->first();

        if ($coupon === null) {
            return ['valid' => false, 'message' => 'Coupon not found', 'discount' => 0.0];
        }

        if (! $coupon->isValid()) {
            return ['valid' => false, 'message' => 'Coupon is expired or inactive', 'discount' => 0.0];
        }

        if (! $coupon->canBeUsedByCustomer($customerId)) {
            return ['valid' => false, 'message' => 'Coupon usage limit exceeded for this customer', 'discount' => 0.0];
        }

        if ($transactionAmount < $coupon->min_transaction) {
            return ['valid' => false, 'message' => 'Minimum transaction amount not met', 'discount' => 0.0];
        }

        $discount = $coupon->calculateDiscount($transactionAmount);

        return ['valid' => true, 'message' => 'Coupon is valid', 'discount' => $discount];
    }

    /**
     * Apply (redeem) a coupon.
     */
    public function redeem(string $code, int $customerId, float $transactionAmount, ?int $invoiceId = null): CouponUsage
    {
        /** @var Coupon $coupon */
        $coupon = Coupon::where('code', $code)->firstOrFail();

        $discount = $coupon->calculateDiscount($transactionAmount);

        $usage = CouponUsage::create([
            'coupon_id' => $coupon->id,
            'customer_id' => $customerId,
            'invoice_id' => $invoiceId,
            'discount_amount' => $discount,
        ]);

        $coupon->increment('used_count');

        return $usage;
    }
}
