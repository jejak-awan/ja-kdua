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
 * @property string|null $ip_address
 * @property string|null $secret
 * @property string|null $connection_type
 * @property int|null $management_port
 * @property string|null $connection_method
 * @property string|null $api_username
 * @property string|null $api_password
 * @property int|null $api_port
 * @property string|null $snmp_community
 * @property int|null $snmp_port
 * @property int|null $last_active_count
 * @property string|null $location_lat
 * @property string|null $location_lng
 * @property string $status
 * @property array<string, mixed>|null $metadata
 */
class ServiceNode extends Model
{
    /** @use HasFactory<\Database\Factories\Isp\ServiceNodeFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'isp_service_nodes';

    protected $fillable = [
        'name',
        'type', // OLT, POP, Router
        'ip_address',
        'secret',
        'connection_type',
        'management_port',
        'connection_method',
        'api_username',
        'api_password',
        'api_port',
        'snmp_community',
        'snmp_port',
        'last_active_count',
        'location_lat',
        'location_lng',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'location_lat' => 'decimal:8',
        'location_lng' => 'decimal:8',
    ];

    /**
     * Scope for active nodes.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<ServiceNode>  $query
     * @return \Illuminate\Database\Eloquent\Builder<ServiceNode>
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * @return HasMany<CustomerDevice, $this>
     */
    public function devices(): HasMany
    {
        return $this->hasMany(CustomerDevice::class, 'node_id');
    }

    /**
     * @return HasMany<Outage, $this>
     */
    public function outages(): HasMany
    {
        return $this->hasMany(Outage::class, 'node_id');
    }

    /**
     * @return HasMany<Subnet, $this>
     */
    public function subnets(): HasMany
    {
        return $this->hasMany(Subnet::class, 'node_id');
    }

    /**
     * @return HasMany<IpPool, $this>
     */
    public function ipPools(): HasMany
    {
        return $this->hasMany(IpPool::class, 'router_id');
    }
}
