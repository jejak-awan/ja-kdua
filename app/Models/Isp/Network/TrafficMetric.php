<?php

declare(strict_types=1);

namespace App\Models\Isp\Network;

use Illuminate\Database\Eloquent\Model;

class TrafficMetric extends Model
{
    protected $connection = 'mrtg';

    protected $table = 'isp_traffic_metrics';

    protected $primaryKey = null;

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'time',
        'router_id',
        'interface',
        'rx_bps',
        'tx_bps',
    ];

    protected $casts = [
        'time' => 'datetime',
        'rx_bps' => 'integer',
        'tx_bps' => 'integer',
    ];
}
