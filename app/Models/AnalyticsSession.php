<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnalyticsSession extends Model
{
    protected $table = 'analytics_sessions';

    protected $fillable = [
        'session_id',
        'user_id',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'os',
        'country',
        'city',
        'page_views',
        'duration',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'page_views' => 'integer',
        'duration' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(AnalyticsVisit::class, 'session_id', 'session_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(AnalyticsEvent::class, 'session_id', 'session_id');
    }

    public static function start($request, $sessionId = null)
    {
        $sessionId = $sessionId ?? session()->getId();
        
        $userAgent = $request->userAgent();
        $deviceInfo = \App\Models\AnalyticsVisit::parseUserAgent($userAgent);
        $location = \App\Models\AnalyticsVisit::getLocation($request->ip());

        return self::firstOrCreate(
            ['session_id' => $sessionId],
            [
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'user_agent' => $userAgent,
                'device_type' => $deviceInfo['device_type'],
                'browser' => $deviceInfo['browser'],
                'os' => $deviceInfo['os'],
                'country' => $location['country'],
                'city' => $location['city'],
                'started_at' => now(),
            ]
        );
    }

    public function end()
    {
        $this->update([
            'ended_at' => now(),
            'duration' => $this->started_at->diffInSeconds(now()),
            'page_views' => $this->visits()->count(),
        ]);
    }

    public function incrementPageViews()
    {
        $this->increment('page_views');
    }
}
