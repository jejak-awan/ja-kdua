<?php

declare(strict_types=1);

namespace App\Models\Isp\Customer;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property string $discount_type
 * @property float $discount_value
 * @property float $min_transaction
 * @property float|null $max_discount
 * @property int|null $max_usage
 * @property int $used_count
 * @property int $max_per_customer
 * @property \Carbon\Carbon|null $valid_from
 * @property \Carbon\Carbon|null $valid_until
 * @property bool $is_active
 * @property int|null $created_by
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, CouponUsage> $usages
 */
class Coupon extends Model
{
    use LogsActivity, SoftDeletes;

    protected $table = 'isp_coupons';

    protected $fillable = [
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'min_transaction',
        'max_discount',
        'max_usage',
        'used_count',
        'max_per_customer',
        'valid_from',
        'valid_until',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'discount_value' => 'float',
        'min_transaction' => 'float',
        'max_discount' => 'float',
        'max_usage' => 'integer',
        'used_count' => 'integer',
        'max_per_customer' => 'integer',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * @return HasMany<CouponUsage, $this>
     */
    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    /**
     * Check if coupon is currently valid.
     */
    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();
        if ($this->valid_from !== null && $now->lt($this->valid_from)) {
            return false;
        }
        if ($this->valid_until !== null && $now->gt($this->valid_until)) {
            return false;
        }

        if ($this->max_usage !== null && $this->used_count >= $this->max_usage) {
            return false;
        }

        return true;
    }

    /**
     * Calculate discount amount for a given transaction amount.
     */
    public function calculateDiscount(float $amount): float
    {
        if ($amount < $this->min_transaction) {
            return 0.0;
        }

        $discount = $this->discount_type === 'percentage'
            ? round($amount * ($this->discount_value / 100), 2)
            : $this->discount_value;

        if ($this->max_discount !== null && $discount > $this->max_discount) {
            $discount = $this->max_discount;
        }

        return min($discount, $amount);
    }

    /**
     * Check if customer has exceeded usage limit.
     */
    public function canBeUsedByCustomer(int $customerId): bool
    {
        $usageCount = $this->usages()->where('customer_id', $customerId)->count();

        return $usageCount < $this->max_per_customer;
    }
}
