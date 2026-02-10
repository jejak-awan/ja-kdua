<?php

declare(strict_types=1);

namespace App\Models\Isp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $customer_id
 * @property int|null $node_id
 * @property string $type
 * @property string $serial_number
 * @property string|null $mac_address
 * @property string $status
 * @property \Carbon\Carbon|null $activated_at
 * @property \Carbon\Carbon|null $expiration_date
 * @property array<string, mixed>|null $metadata
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read Customer $customer
 * @property-read ServiceNode|null $node
 */
class CustomerDevice extends Model
{
    /** @use HasFactory<\Database\Factories\Isp\CustomerDeviceFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'isp_customer_devices';

    protected $fillable = [
        'customer_id',
        'node_id',
        'type', // ONU, ONT, CPE
        'serial_number',
        'mac_address',
        'status',
        'activated_at',
        'expiration_date',
        'metadata',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'expiration_date' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the customer that owns the device.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Customer, $this>
     */
    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Get the node where this device is connected.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<ServiceNode, $this>
     */
    public function node(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ServiceNode::class, 'node_id');
    }
}
