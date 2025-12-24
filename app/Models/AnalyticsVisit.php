<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsVisit extends Model
{
    use HasFactory;

    protected $table = 'analytics_visits';

    protected $fillable = [
        'session_id',
        'user_id',
        'ip_address',
        'user_agent',
        'referer',
        'url',
        'method',
        'status_code',
        'duration',
        'visited_at',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
        'duration' => 'integer',
        'status_code' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the session for this visit
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(AnalyticsSession::class, 'session_id', 'session_id');
    }

    public function content()
    {
        // Try to find content by URL slug
        $slug = basename(parse_url($this->url, PHP_URL_PATH));

        return \App\Models\Content::where('slug', $slug)->first();
    }

    /**
     * Accessor to get device_type from session
     */
    public function getDeviceTypeAttribute()
    {
        return $this->session?->device_type;
    }

    /**
     * Accessor to get browser from session
     */
    public function getBrowserAttribute()
    {
        return $this->session?->browser;
    }

    /**
     * Accessor to get os from session
     */
    public function getOsAttribute()
    {
        return $this->session?->os;
    }

    /**
     * Accessor to get country from session
     */
    public function getCountryAttribute()
    {
        return $this->session?->country;
    }

    /**
     * Accessor to get city from session
     */
    public function getCityAttribute()
    {
        return $this->session?->city;
    }

    public static function trackVisit($request, $sessionId = null)
    {
        $sessionId = $sessionId ?? session()->getId();

        return self::create([
            'session_id' => $sessionId,
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'status_code' => 200,
            'visited_at' => now(),
        ]);
    }
}
