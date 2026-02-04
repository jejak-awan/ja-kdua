<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $session_id
 * @property int|null $user_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $referer
 * @property string|null $url
 * @property string|null $method
 * @property int $status_code
 * @property int $duration
 * @property int|null $visits_count
 * @property \Illuminate\Support\Carbon|null $visited_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string|null $device_type
 * @property-read string|null $browser
 * @property-read string|null $os
 * @property-read string|null $country
 * @property-read string|null $city
 * @property-read \App\Models\User|null $user
 * @property-read \App\Models\AnalyticsSession|null $session
 */
class AnalyticsVisit extends Model
{
    /** @use HasFactory<\Database\Factories\AnalyticsVisitFactory> */
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

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the session for this visit
     *
     * @return BelongsTo<AnalyticsSession, $this>
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(AnalyticsSession::class, 'session_id', 'session_id');
    }

    public function content(): ?\App\Models\Content
    {
        // Try to find content by URL slug
        $path = parse_url((string) $this->url, PHP_URL_PATH);
        if (! is_string($path)) {
            return null;
        }

        $slug = basename($path);

        /** @var \App\Models\Content|null */
        return \App\Models\Content::where('slug', $slug)->first();
    }

    /**
     * Accessor to get device_type from session
     */
    public function getDeviceTypeAttribute(): ?string
    {
        return $this->session?->device_type;
    }

    /**
     * Accessor to get browser from session
     */
    public function getBrowserAttribute(): ?string
    {
        return $this->session?->browser;
    }

    /**
     * Accessor to get os from session
     */
    public function getOsAttribute(): ?string
    {
        return $this->session?->os;
    }

    /**
     * Accessor to get country from session
     */
    public function getCountryAttribute(): ?string
    {
        return $this->session?->country;
    }

    /**
     * Accessor to get city from session
     */
    public function getCityAttribute(): ?string
    {
        return $this->session?->city;
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $sessionId
     */
    public static function trackVisit($request, $sessionId = null): self
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
