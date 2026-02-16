<?php

declare(strict_types=1);

namespace App\Models\Isp\Network;

use App\Models\Isp\Customer\CustomerDevice;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $subnet_id
 * @property int|null $device_id
 * @property string $address
 * @property string $status
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Isp\Network\Subnet $subnet
 * @property-read \App\Models\Isp\Customer\CustomerDevice|null $device
 */
class IpAddress extends Model
{
    /** @use HasFactory<\Database\Factories\Isp\IpAddressFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'isp_ip_addresses';

    protected $fillable = [
        'subnet_id',
        'device_id',
        'address',
        'status',
        'notes',
    ];

    /**
     * Get the subnet this IP belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Subnet, $this>
     */
    public function subnet(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Subnet::class);
    }

    /**
     * Get the device assigned to this IP.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<CustomerDevice, $this>
     */
    public function device(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CustomerDevice::class, 'device_id');
    }
}
