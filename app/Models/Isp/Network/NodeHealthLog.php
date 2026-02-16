<?php

declare(strict_types=1);

namespace App\Models\Isp\Network;

use Illuminate\Database\Eloquent\Model;

/**
 * @property \Carbon\Carbon $time
 * @property int $node_id
 * @property string $status
 * @property int $latency_ms
 * @property float $packet_loss
 * @property float $availability_pct
 * @property int $total_samples
 * @property int $online_samples
 * @property string $node_name
 */
class NodeHealthLog extends Model
{
    protected $connection = 'mrtg';

    protected $table = 'isp_node_health_logs';

    protected $primaryKey = null;

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'time',
        'node_id',
        'status',
        'latency_ms',
        'packet_loss',
    ];

    protected $casts = [
        'time' => 'datetime',
        'latency_ms' => 'integer',
        'packet_loss' => 'float',
    ];
}
