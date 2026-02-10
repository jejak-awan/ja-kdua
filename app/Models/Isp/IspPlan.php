<?php

declare(strict_types=1);

namespace App\Models\Isp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string|null $download_limit
 * @property string|null $upload_limit
 * @property string|null $burst_limit
 * @property string|null $mikrotik_group
 * @property string|null $mikrotik_rate_limit
 * @property float $price
 * @property float $cost_price
 * @property float $commission
 * @property float|null $partner_price
 * @property int $shared_users
 * @property int $active_period
 * @property int|null $quota_limit_mb
 * @property float|null $fup_limit_gb
 * @property string|null $fup_speed
 * @property bool $fup_enabled
 * @property string|null $color
 * @property string|null $description
 * @property string $status
 * @property array<string, mixed>|null $features
 */
class IspPlan extends Model
{
    /** @use HasFactory<\Database\Factories\Isp\BillingPlanFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'isp_plans';

    protected $fillable = [
        'name',
        'type',
        'download_limit',
        'upload_limit',
        'burst_limit',
        'mikrotik_group',
        'mikrotik_rate_limit',
        'price',
        'cost_price',
        'commission',
        'partner_price',
        'shared_users',
        'active_period',
        'quota_limit_mb',
        'fup_limit_gb',
        'fup_speed',
        'fup_enabled',
        'color',
        'description',
        'status',
        'features',
    ];

    protected $casts = [
        'price' => 'float',
        'cost_price' => 'float',
        'commission' => 'float',
        'partner_price' => 'float',
        'shared_users' => 'integer',
        'active_period' => 'integer',
        'quota_limit_mb' => 'integer',
        'fup_limit_gb' => 'float',
        'fup_enabled' => 'boolean',
        'features' => 'array',
    ];

    /**
     * @return HasMany<Customer, $this>
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'billing_plan_id'); // We'll keep the column name or rename it later
    }

    /**
     * @return HasMany<Voucher, $this>
     */
    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class, 'profile_id'); // We'll keep the column name or rename it later
    }

    public function isHotspot(): bool
    {
        return $this->type === 'hotspot';
    }

    public function isFiber(): bool
    {
        return $this->type === 'fiber';
    }
}
