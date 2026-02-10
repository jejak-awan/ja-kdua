<?php

declare(strict_types=1);

namespace App\Models\Isp;

use App\Models\Core\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $code
 * @property string|null $profile
 * @property int|null $profile_id
 * @property int|null $partner_id
 * @property int|null $outlet_id
 * @property string|null $batch_id
 * @property string|null $batch_code
 * @property float $price
 * @property float $commission
 * @property string $status
 * @property \Carbon\Carbon|null $used_at
 * @property int|null $used_by
 * @property \Carbon\Carbon|null $sold_at
 * @property int|null $sold_by
 * @property string|null $duration
 */
class Voucher extends Model
{
    /** @use HasFactory<\Database\Factories\Isp\VoucherFactory> */
    use HasFactory;

    protected $table = 'isp_vouchers';

    protected $fillable = [
        'code',
        'profile',
        'profile_id',
        'partner_id',
        'outlet_id',
        'batch_id',
        'batch_code',
        'price',
        'commission',
        'status',
        'used_at',
        'used_by',
        'sold_at',
        'sold_by',
        'duration',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'float',
        'commission' => 'float',
        'used_at' => 'datetime',
        'sold_at' => 'datetime',
    ];

    /**
     * Get the user who used this voucher.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by');
    }

    /**
     * Get the service profile for this voucher.
     *
     * @return BelongsTo<IspPlan, $this>
     */
    public function serviceProfile(): BelongsTo
    {
        return $this->belongsTo(IspPlan::class, 'profile_id');
    }

    /**
     * Get the partner who owns this voucher.
     *
     * @return BelongsTo<Partner, $this>
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * Get the user who sold this voucher.
     *
     * @return BelongsTo<User, $this>
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sold_by');
    }

    /**
     * Check if voucher is available for sale.
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available' && $this->used_at === null;
    }

    /**
     * Check if voucher has been used.
     */
    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    /**
     * Check if voucher has been sold.
     */
    public function isSold(): bool
    {
        return $this->sold_at !== null;
    }
}
