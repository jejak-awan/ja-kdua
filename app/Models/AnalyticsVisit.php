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
        'device_type',
        'browser',
        'os',
        'country',
        'city',
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

    public function content()
    {
        // Try to find content by URL slug
        $slug = basename(parse_url($this->url, PHP_URL_PATH));

        return \App\Models\Content::where('slug', $slug)->first();
    }

    public static function trackVisit($request, $sessionId = null)
    {
        $sessionId = $sessionId ?? session()->getId();

        // Parse user agent
        $userAgent = $request->userAgent();
        $deviceInfo = self::parseUserAgent($userAgent);

        // Get location (simplified - in production use GeoIP service)
        $location = self::getLocation($request->ip());

        return self::create([
            'session_id' => $sessionId,
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $userAgent,
            'referer' => $request->header('referer'),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'status_code' => 200,
            'device_type' => $deviceInfo['device_type'],
            'browser' => $deviceInfo['browser'],
            'os' => $deviceInfo['os'],
            'country' => $location['country'],
            'city' => $location['city'],
            'visited_at' => now(),
        ]);
    }

    protected static function parseUserAgent($userAgent)
    {
        $deviceType = 'desktop';
        $browser = 'unknown';
        $os = 'unknown';

        // Simple detection (in production use a library like jenssegers/agent)
        if (preg_match('/mobile|android|iphone|ipad/i', $userAgent)) {
            $deviceType = 'mobile';
        } elseif (preg_match('/tablet|ipad/i', $userAgent)) {
            $deviceType = 'tablet';
        }

        if (preg_match('/chrome/i', $userAgent)) {
            $browser = 'chrome';
        } elseif (preg_match('/firefox/i', $userAgent)) {
            $browser = 'firefox';
        } elseif (preg_match('/safari/i', $userAgent)) {
            $browser = 'safari';
        } elseif (preg_match('/edge/i', $userAgent)) {
            $browser = 'edge';
        }

        if (preg_match('/windows/i', $userAgent)) {
            $os = 'windows';
        } elseif (preg_match('/mac|os x/i', $userAgent)) {
            $os = 'macos';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $os = 'linux';
        } elseif (preg_match('/android/i', $userAgent)) {
            $os = 'android';
        } elseif (preg_match('/ios|iphone|ipad/i', $userAgent)) {
            $os = 'ios';
        }

        return [
            'device_type' => $deviceType,
            'browser' => $browser,
            'os' => $os,
        ];
    }

    protected static function getLocation($ipAddress)
    {
        // Simplified - in production use GeoIP service like MaxMind or ipapi.co
        return [
            'country' => null,
            'city' => null,
        ];
    }
}
