<?php

declare(strict_types=1);

namespace App\Models\Isp\Network;
use App\Models\Isp\Customer\Customer;
use App\Models\Isp\Network\ServiceNode;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $customer_id
 * @property int $olt_id
 * @property string $onu_index
 * @property float|null $rx_power
 * @property float|null $tx_power
 * @property float|null $olt_rx_power
 * @property \Illuminate\Support\Carbon $collected_at
 * @property-read Customer|null $customer
 * @property-read ServiceNode $olt
 */
class OltSignal extends Model
{
    protected $table = 'isp_olt_signals';

    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'olt_id',
        'onu_index',
        'rx_power',
        'tx_power',
        'olt_rx_power',
        'collected_at',
    ];

    protected $casts = [
        'rx_power' => 'float',
        'tx_power' => 'float',
        'olt_rx_power' => 'float',
        'collected_at' => 'datetime',
        'customer_id' => 'integer',
        'olt_id' => 'integer',
    ];

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<ServiceNode, $this>
     */
    public function olt(): BelongsTo
    {
        return $this->belongsTo(ServiceNode::class);
    }
}
