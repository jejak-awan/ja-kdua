<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
