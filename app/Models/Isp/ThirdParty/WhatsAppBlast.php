<?php

declare(strict_types=1);

namespace App\Models\Isp\ThirdParty;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property int|null $template_id
 * @property string $message
 * @property string $target_filter
 * @property array<int, string>|null $target_numbers
 * @property int $total_targets
 * @property int $sent_count
 * @property int $failed_count
 * @property string $status
 * @property \Carbon\Carbon|null $scheduled_at
 * @property \Carbon\Carbon|null $started_at
 * @property \Carbon\Carbon|null $completed_at
 * @property int|null $created_by
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read WhatsAppTemplate|null $template
 * @property-read \Illuminate\Database\Eloquent\Collection<int, WhatsAppBlastLog> $logs
 */
class WhatsAppBlast extends Model
{
    protected $table = 'isp_whatsapp_blasts';

    protected $fillable = [
        'name',
        'template_id',
        'message',
        'target_filter',
        'target_numbers',
        'total_targets',
        'sent_count',
        'failed_count',
        'status',
        'scheduled_at',
        'started_at',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'target_numbers' => 'array',
        'total_targets' => 'integer',
        'sent_count' => 'integer',
        'failed_count' => 'integer',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<WhatsAppTemplate, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(WhatsAppTemplate::class, 'template_id');
    }

    /**
     * @return HasMany<WhatsAppBlastLog, $this>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(WhatsAppBlastLog::class, 'blast_id');
    }
}
