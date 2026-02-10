<?php

declare(strict_types=1);

namespace App\Models\Isp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int|null $node_id
 * @property string $title
 * @property string $description
 * @property string $type
 * @property string $status
 * @property \Illuminate\Support\Carbon $started_at
 * @property \Illuminate\Support\Carbon|null $resolved_at
 * @property array<string, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Isp\ServiceNode|null $node
 */
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
