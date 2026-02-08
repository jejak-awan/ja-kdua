<?php

declare(strict_types=1);

namespace App\Models\Isp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
