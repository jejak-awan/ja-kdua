<?php

declare(strict_types=1);

namespace App\Models\Isp\Network;
use App\Models\Isp\Network\ServiceNode;
use App\Models\Isp\Customer\Customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $node_id
 * @property string $name
 * @property string $prefix
 * @property string|null $gateway
 * @property int|null $vlan_id
 * @property string $type
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Isp\Network\ServiceNode $node
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Isp\Network\IpAddress> $ipAddresses
 */
class Subnet extends Model
{
    /** @use HasFactory<\Database\Factories\Isp\SubnetFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'isp_subnets';

    protected $fillable = [
        'node_id',
        'name',
        'prefix',
        'gateway',
        'vlan_id',
        'type',
        'status',
    ];

    /**
     * Get the service node this subnet belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<ServiceNode, $this>
     */
    public function node(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ServiceNode::class, 'node_id');
    }

    /**
     * Get the IP addresses in this subnet.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<IpAddress, $this>
     */
    public function ipAddresses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(IpAddress::class, 'subnet_id');
    }

    /**
     * Helper to generate IPs for this subnet (CIDR)
     */
    public function generateIps(): void
    {
        [$network, $mask] = explode('/', $this->prefix);
        $start = ip2long($network);
        $count = pow(2, (32 - (int) $mask));

        // Skip network and broadcast for traditional /24 etc
        for ($i = 1; $i < $count - 1; $i++) {
            IpAddress::firstOrCreate([
                'subnet_id' => $this->id,
                'address' => long2ip($start + $i),
            ]);
        }
    }
}
