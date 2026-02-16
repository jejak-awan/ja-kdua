<?php

declare(strict_types=1);

namespace App\Models\Isp\Network;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Isp\Customer\Customer;
use App\Models\Isp\Customer\CustomerDevice;
use App\Traits\Isp\HasAuditTrail;

/**
 * @property int $id
 * @property string|null $name
 * @property string|null $description
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
 * @property bool $radius_enabled
 * @property string|null $radius_secret
 * @property string|null $sub_type
 * @property bool $is_vpn_server
 * @property int|null $last_active_count
 * @property string|null $location_lat
 * @property string|null $location_lng
 * @property string $status
 * @property array<string, mixed>|null $metadata
 * @property array<mixed> $discovery_pending
 * @property-read string $pretty_id
 * @property-read string $ip_type
 */
class ServiceNode extends Model
{
    /** @use HasFactory<\Database\Factories\Isp\Network\ServiceNodeFactory> */
    use HasFactory, HasAuditTrail, SoftDeletes;

    protected $table = 'isp_service_nodes';

    protected static function booted()
    {
        static::creating(function ($node) {
            if (! $node->radius_secret) {
                $node->radius_secret = bin2hex(random_bytes(10));
            }
        });
    }

    protected $fillable = [
        'name',
        'description',
        'type', // OLT, POP, Router
        'sub_type', // core, gateway, distribution
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
        'radius_enabled',
        'radius_secret',
        'is_vpn_server',
        'last_active_count',
        'location_lat',
        'location_lng',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'radius_enabled' => 'boolean',
        'is_vpn_server' => 'boolean',
        'location_lat' => 'decimal:8',
        'location_lng' => 'decimal:8',
        'api_password' => 'encrypted',
        'radius_secret' => 'encrypted',
        'secret' => 'encrypted',
    ];

    protected $appends = [
        'pretty_id',
        'ip_type',
        'is_connected',
        'active_count',
    ];

    /**
     * Get the connectivity status from cache.
     */
    public function getIsConnectedAttribute(): bool
    {
        if ($this->type !== 'Router') {
            return true; // Non-router nodes are assumed "online" for now or handled elsewhere
        }

        $cache = \Illuminate\Support\Facades\Cache::get("router_status_{$this->id}");
        $cache = is_array($cache) ? $cache : [];
        
        return (bool) ($cache['is_connected'] ?? false);
    }

    /**
     * Get the active client count from cache.
     */
    public function getActiveCountAttribute(): int
    {
        if ($this->type !== 'Router') {
            return 0;
        }

        $cache = \Illuminate\Support\Facades\Cache::get("router_status_{$this->id}");
        $cache = is_array($cache) ? $cache : [];
        
        return (int) ($cache['active_count'] ?? 0);
    }

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

    /**
     * Get the pretty ID.
     */
    public function getPrettyIdAttribute(): string
    {
        $date = $this->created_at ? $this->created_at->format('Ymd') : date('Ymd');

        return 'RO'.$date.str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the IP type.
     */
    public function getIpTypeAttribute(): string
    {
        if ($this->connection_type === 'VPN RADIUS') {
            return 'VPN';
        }

        if (! $this->ip_address) {
            return '-';
        }

        if (\App\Helpers\IpHelper::isPrivateIp($this->ip_address)) {
            return 'Local';
        }

        return 'Public Static';
    }
}
