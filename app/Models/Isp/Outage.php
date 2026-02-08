<?php

declare(strict_types=1);

namespace App\Models\Isp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Outage extends Model
{
    /** @use HasFactory<\Database\Factories\Isp\OutageFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'isp_outages';

    protected $fillable = [
        'node_id',
        'title',
        'description',
        'type',
        'status',
        'started_at',
        'resolved_at',
        'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'resolved_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the service node affected by the outage.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<ServiceNode, $this>
     */
    public function node(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ServiceNode::class, 'node_id');
    }
}
