<?php

declare(strict_types=1);

namespace App\Models\Isp\Customer;

use App\Models\Isp\Billing\Invoice;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $coupon_id
 * @property int $customer_id
 * @property int|null $invoice_id
 * @property float $discount_amount
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read Coupon $coupon
 * @property-read Customer $customer
 * @property-read Invoice|null $invoice
 */
class CouponUsage extends Model
{
    protected $table = 'isp_coupon_usages';

    protected $fillable = [
        'coupon_id',
        'customer_id',
        'invoice_id',
        'discount_amount',
    ];

    protected $casts = [
        'discount_amount' => 'float',
    ];

    /**
     * @return BelongsTo<Coupon, $this>
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<Invoice, $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
