<?php

declare(strict_types=1);

namespace App\Models\Isp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpPoolAddress extends Model
{


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
    public function reserve(string $notes = null): bool
    {
        return $this->update([
            'status' => 'reserved',
            'notes' => $notes,
        ]);
    }
}
