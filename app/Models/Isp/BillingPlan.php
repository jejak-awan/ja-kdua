<?php

declare(strict_types=1);

namespace App\Models\Isp;

use Illuminate\Database\Eloquent\Model;

class BillingPlan extends Model
{
    protected $table = 'isp_billing_plans';

    protected $fillable = [
        'name',
        'mikrotik_group',
        'speed_limit',
        'mikrotik_rate_limit',
        'shared_users',
        'active_period',
        'price',
        'cost_price',
        'commission',
        'type', // prepaid, postpaid
        'features',
        'is_active',
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'commission' => 'decimal:2',
        'shared_users' => 'integer',
        'active_period' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the customers using this billing plan.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Customer, $this>
     */
    public function customers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Customer::class, 'billing_plan_id');
    }
}
