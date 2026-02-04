<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $session_id
 * @property int|null $user_id
 * @property string $event_type
 * @property string $event_name
 * @property string|null $event_category
 * @property array|null $event_data
 * @property string|null $url
 * @property int|null $content_id
 * @property string|null $ip_address
 * @property \Illuminate\Support\Carbon|null $occurred_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @property-read \App\Models\Content|null $content
 */
class AnalyticsEvent extends Model
{
    use HasFactory;

    protected $table = 'analytics_events';

    protected $fillable = [
        'session_id',
        'user_id',
        'event_type',
        'event_name',
        'event_category',
        'event_data',
        'url',
        'content_id',
        'ip_address',
        'occurred_at',
    ];

    protected $casts = [
        'event_data' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    public static function track($eventType, $eventName, $data = [], $contentId = null)
    {
        return self::create([
            'session_id' => session()->getId(),
            'user_id' => auth()->id(),
            'event_type' => $eventType,
            'event_name' => $eventName,
            'event_category' => $data['category'] ?? null,
            'event_data' => $data,
            'url' => request()->fullUrl(),
            'content_id' => $contentId,
            'ip_address' => request()->ip(),
            'occurred_at' => now(),
        ]);
    }
}
