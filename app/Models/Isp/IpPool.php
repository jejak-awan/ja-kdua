<?php

declare(strict_types=1);

namespace App\Models\Isp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $network
 * @property string|null $gateway
 * @property string|null $dns_primary
 * @property string|null $dns_secondary
 * @property int|null $vlan_id
 * @property int|null $router_id
 * @property string $status
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Isp\ServiceNode|null $router
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Isp\IpPoolAddress> $addresses
 */
class IpPool extends Model
{
    /** @use HasFactory<\Database\Factories\Isp\IpPoolFactory> */
    use HasFactory;

    protected $table = 'ip_pools';

    protected $fillable = [
        'name',
        'network',
        'gateway',
        'dns_primary',
        'dns_secondary',
        'vlan_id',
        'router_id',
        'status',
        'description',
    ];

    protected $casts = [
        'vlan_id' => 'integer',
    ];

    /**
     * Get the router for this pool.
     *
     * @return BelongsTo<ServiceNode, $this>
     */
    public function router(): BelongsTo
    {
        return $this->belongsTo(ServiceNode::class, 'router_id');
    }

    /**
     * Get all addresses in this pool.
     *
     * @return HasMany<IpPoolAddress, $this>
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(IpPoolAddress::class, 'pool_id');
    }

    /**
     * Get available addresses in this pool.
     *
     * @return HasMany<IpPoolAddress, $this>
     */
    public function availableAddresses(): HasMany
    {
        return $this->hasMany(IpPoolAddress::class, 'pool_id')->where('status', 'available');
    }

    /**
     * Get usage statistics.
     *
     * @return array{total: int, available: int, assigned: int, reserved: int, usage_percent: float}
     */
    public function getUsageStats(): array
    {
        $total = $this->addresses()->count();
        $available = $this->addresses()->where('status', 'available')->count();
        $assigned = $this->addresses()->where('status', 'assigned')->count();
        $reserved = $this->addresses()->where('status', 'reserved')->count();

        return [
            'total' => $total,
            'available' => $available,
            'assigned' => $assigned,
            'reserved' => $reserved,
            'usage_percent' => $total > 0 ? round(($assigned / $total) * 100, 1) : 0,
        ];
    }

    /**
     * Generate addresses from CIDR notation.
     */
    public function generateAddressesFromCidr(): int
    {
        $parts = explode('/', $this->network);
        if (count($parts) !== 2) {
            return 0;
        }

        $ip = $parts[0];
        $prefix = (int) $parts[1];

        if ($prefix < 8 || $prefix > 30) {
            return 0; // Invalid or too large range
        }

        $ipLong = ip2long($ip);
        if ($ipLong === false) {
            return 0;
        }

        $netmask = ~((1 << (32 - $prefix)) - 1);
        $network = $ipLong & $netmask;
        $broadcast = $network | ~$netmask;

        // Skip network and broadcast addresses
        $count = 0;
        for ($i = $network + 1; $i < $broadcast; $i++) {
            $address = long2ip($i);
            if ($address === false) {
                continue;
            }

            // Skip gateway if set
            if ($this->gateway && $address === $this->gateway) {
                continue;
            }

            $this->addresses()->firstOrCreate(
                ['ip_address' => $address],
                ['status' => 'available']
            );
            $count++;
        }

        return $count;
    }

    /**
     * Assign an available IP to a customer.
     */
    public function assignToCustomer(Customer $customer): ?IpPoolAddress
    {
        /** @var IpPoolAddress|null $address */
        $address = $this->availableAddresses()->first();
        if (! $address) {
            return null;
        }

        $address->update([
            'customer_id' => $customer->id,
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);

        return $address;
    }
}
