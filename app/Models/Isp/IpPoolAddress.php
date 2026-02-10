<?php

declare(strict_types=1);

namespace App\Models\Isp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $pool_id
 * @property string $ip_address
 * @property int|null $customer_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $assigned_at
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Isp\IpPool $pool
 * @property-read \App\Models\Isp\Customer|null $customer
 */
class IpPoolAddress extends Model
{
    /** @use HasFactory<\Database\Factories\Isp\IpPoolAddressFactory> */
    use HasFactory;

    protected $table = 'ip_pool_addresses';

    protected $fillable = [
        'pool_id',
        'ip_address',
        'customer_id',
        'status',
        'assigned_at',
        'notes',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    /**
     * Get the pool for this address.
     *
     * @return BelongsTo<IpPool, $this>
     */
    public function pool(): BelongsTo
    {
        return $this->belongsTo(IpPool::class, 'pool_id');
    }

    /**
     * Get the customer for this address.
     *
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Release this address back to the pool.
     */
    public function release(): bool
    {
        return $this->update([
            'customer_id' => null,
            'status' => 'available',
            'assigned_at' => null,
        ]);
    }

    /**
     * Reserve this address (not available for automatic assignment).
     */
    public function reserve(?string $notes = null): bool
    {
        return $this->update([
            'status' => 'reserved',
            'notes' => $notes,
        ]);
    }
}
