<?php

declare(strict_types=1);

namespace App\Models\Isp;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $name
 * @property int|null $template_id
 * @property string $target_filter
 * @property string $frequency
 * @property int $day_offset
 * @property string $time
 * @property bool $is_active
 * @property \Carbon\Carbon|null $last_run_at
 * @property \Carbon\Carbon|null $next_run_at
 * @property int|null $created_by
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read WaTemplate|null $template
 */
class WaSchedule extends Model
{
    protected $table = 'isp_wa_schedules';

    protected $fillable = [
        'name',
        'template_id',
        'target_filter',
        'frequency',
        'day_offset',
        'time',
        'is_active',
        'last_run_at',
        'next_run_at',
        'created_by',
    ];

    protected $casts = [
        'day_offset' => 'integer',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<WaTemplate, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(WaTemplate::class, 'template_id');
    }
}
