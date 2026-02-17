<?php

namespace App\Services\Core;

use App\Models\Core\IpList;
use App\Models\Core\SecurityLog;
use App\Models\Core\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SecurityService
{
    // Cache prefix for all security-related keys
    protected string $cachePrefix = 'security:';

    // Account lockout settings
    protected int $accountLockMinutes = 15;

    // Progressive blocking settings
    protected int $maxFailedAttempts;

    protected int $baseBlockMinutes;

    protected int $maxBlockMinutes = 60; // Maximum block duration in minutes

    protected int $offenseResetHours = 24; // Reset offense count after this many hours

    public function __construct()
    {
        $this->maxFailedAttempts = is_scalar($v1 = \App\Models\Core\Setting::get('login_attempts_limit', 5)) ? (int) $v1 : 5;
        $this->baseBlockMinutes = is_scalar($v2 = \App\Models\Core\Setting::get('block_duration_minutes', 15)) ? (int) $v2 : 15;
        // Ensure baseBlockMinutes is at least 1 to avoid math issues or immediate expiry
        $this->baseBlockMinutes = max(1, $this->baseBlockMinutes);
        $this->accountLockMinutes = $this->baseBlockMinutes;
    }

    /**
     * Record a failed login attempt.
     * Uses progressive blocking - block duration increases with each offense.
     *
     * @return array{ip_attempts: int, email_attempts: int, ip_blocked: bool, account_locked: bool, block_duration: int}
     */
    public function recordFailedLogin(string $email, string $ipAddress): array
    {
        SecurityLog::log('login_failed', null, $ipAddress, "Failed login attempt for email: {$email}", [
            'email' => $email,
        ]);

        // Track failed attempts by IP
        $ipAttempts = $this->incrementFailedAttempts($ipAddress);

        // Track failed attempts by email
        $emailAttempts = $this->incrementFailedAttempts($email, 'email');

        $result = [
            'ip_attempts' => $ipAttempts,
            'email_attempts' => $emailAttempts,
            'ip_blocked' => false,
            'account_locked' => false,
            'block_duration' => 0,
        ];

        // Block IP if too many attempts (progressive blocking)
        if ($ipAttempts >= $this->maxFailedAttempts && ! $this->isProtectedIp($ipAddress)) {
            $blockSeconds = $this->blockIpTemporarily($ipAddress, "Too many failed login attempts ({$ipAttempts})");
            $result['ip_blocked'] = true;
            $result['block_duration'] = $blockSeconds;
        }

        // Lock account if too many attempts
        if ($emailAttempts >= $this->maxFailedAttempts) {
            $this->lockAccount($email, "Too many failed login attempts ({$emailAttempts})");
            $result['account_locked'] = true;
            SecurityLog::log('account_locked', null, $ipAddress, "Account locked due to {$emailAttempts} failed login attempts", [
                'email' => $email,
            ]);
        }

        return $result;
    }

    /**
     * Record a successful login and clear all security counters.
     */
    public function recordSuccessfulLogin(User $user, string $ipAddress): void
    {
        SecurityLog::log('login_success', $user, $ipAddress, "Successful login for user: {$user->email}");

        // Clear all security restrictions for this user
        $this->clearSecurityCache($ipAddress);
        $this->clearSecurityCache($user->email, 'email');
        $this->unlockAccount($user->email);
    }

    /**
     * Increment failed attempts counter with cache TTL for auto-expiry.
     */
    protected function incrementFailedAttempts(string $identifier, string $type = 'ip'): int
    {
        $key = $this->cachePrefix."failed_attempts:{$type}:{$identifier}";
        $current = Cache::get($key, 0);
        $attempts = (is_numeric($current) ? (int) $current : 0) + 1;

        // Store with TTL so it auto-expires (no manual cache:clear needed)
        Cache::put($key, $attempts, now()->addMinutes($this->accountLockMinutes));

        return $attempts;
    }

    /**
     * Get current failed attempts count.
     */
    public function getFailedAttempts(string $identifier, string $type = 'ip'): int
    {
        $key = $this->cachePrefix."failed_attempts:{$type}:{$identifier}";
        $attempts = Cache::get($key, 0);

        return is_numeric($attempts) ? (int) $attempts : 0;
    }

    /**
     * Block IP temporarily with progressive duration.
     * Duration doubles with each offense (1min → 2min → 4min → 8min... max 60min).
     *
     * @return int Block duration in seconds
     */
    public function blockIpTemporarily(string $ipAddress, ?string $reason = null): int
    {
        if ($this->isProtectedIp($ipAddress)) {
            return 0;
        }

        if (IpList::isWhitelisted($ipAddress)) {
            return 0;
        }

        // Get and increment offense count
        $offenseKey = $this->cachePrefix."offense_count:{$ipAddress}";
        $currentOffense = Cache::get($offenseKey, 0);
        $offenseCount = (is_numeric($currentOffense) ? (int) $currentOffense : 0) + 1;
        Cache::put($offenseKey, $offenseCount, now()->addHours($this->offenseResetHours));

        // Calculate progressive block duration (exponential backoff)
        $blockMinutes = min(
            $this->baseBlockMinutes * pow(2, $offenseCount - 1),
            $this->maxBlockMinutes
        );

        // Store block_until timestamp (auto-expires via cache TTL)
        $blockUntil = now()->addMinutes($blockMinutes);
        Cache::put(
            $this->cachePrefix."block_until:{$ipAddress}",
            $blockUntil->toIso8601String(),
            $blockMinutes * 60 // TTL in seconds
        );

        SecurityLog::log('ip_blocked_temp', null, $ipAddress,
            "IP temporarily blocked for {$blockMinutes} minute(s): ".($reason ?? 'Too many failed attempts'),
            [
                'duration_minutes' => $blockMinutes,
                'offense_count' => $offenseCount,
                'block_until' => $blockUntil->toIso8601String(),
            ]
        );

        return $blockMinutes * 60; // Return seconds
    }

    /**
     * Check if IP is currently blocked.
     * Temporary blocks auto-expire via cache TTL (no manual cache:clear needed).
     */
    public function isIpBlocked(string $ipAddress): bool
    {
        // Never block protected IPs
        if ($this->isProtectedIp($ipAddress)) {
            return false;
        }

        // Whitelisted IPs are never blocked
        if (IpList::isWhitelisted($ipAddress)) {
            return false;
        }

        // Check permanent blocklist (database)
        if (IpList::isBlocked($ipAddress)) {
            return true;
        }

        // Check temporary block (auto-expires via cache TTL)
        return Cache::has($this->cachePrefix."block_until:{$ipAddress}");
    }

    /**
     * Get remaining block time in seconds.
     */
    public function getRemainingBlockTime(string $ipAddress): int
    {
        $key = $this->cachePrefix."block_until:{$ipAddress}";
        $blockUntil = Cache::get($key);

        if (! is_string($blockUntil)) {
            return 0;
        }

        $remaining = Carbon::parse($blockUntil)->diffInSeconds(now(), false);

        return (int) max(0, -$remaining);
    }

    /**
     * Get block info for API response.
     *
     * @return array{is_blocked: bool, remaining_seconds: int, offense_count: int, failed_attempts: int}
     */
    public function getBlockInfo(string $ipAddress): array
    {
        return [
            'is_blocked' => $this->isIpBlocked($ipAddress),
            'remaining_seconds' => $this->getRemainingBlockTime($ipAddress),
            'offense_count' => is_numeric($offenseCount = Cache::get($this->cachePrefix."offense_count:{$ipAddress}", 0)) ? (int) $offenseCount : 0,
            'failed_attempts' => $this->getFailedAttempts($ipAddress),
        ];
    }

    /**
     * Permanently block an IP (adds to database).
     */
    public function blockIpPermanently(string $ipAddress, ?string $reason = null): bool
    {
        if ($this->isProtectedIp($ipAddress)) {
            return false;
        }

        if (IpList::isWhitelisted($ipAddress)) {
            return false;
        }

        IpList::updateOrCreate(
            ['ip_address' => $ipAddress, 'type' => 'blocklist'],
            [
                'reason' => $reason ?? 'IP address blocked permanently',
                'created_by' => Auth::id(),
            ]
        );

        SecurityLog::log('ip_blocked_permanent', null, $ipAddress, $reason ?? 'IP address blocked permanently');

        return true;
    }

    /**
     * Unblock an IP (removes from both cache and database).
     */
    public function unblockIp(string $ipAddress): void
    {
        // Remove from database blocklist
        IpList::where('ip_address', $ipAddress)->where('type', 'blocklist')->delete();

        // Clear all cache entries for this IP
        $this->clearSecurityCache($ipAddress);

        SecurityLog::log('ip_unblocked', null, $ipAddress, 'IP address unblocked');
    }

    /**
     * Clear all security-related cache for an identifier.
     */
    public function clearSecurityCache(string $identifier, string $type = 'ip'): void
    {
        $keys = $type === 'ip' ? [
            "block_until:{$identifier}",
            "failed_attempts:ip:{$identifier}",
            "offense_count:{$identifier}",
        ] : [
            "failed_attempts:email:{$identifier}",
            "account_locked:{$identifier}",
        ];

        foreach ($keys as $key) {
            Cache::forget($this->cachePrefix.$key);
        }
    }

    /**
     * Add IP to whitelist.
     */
    public function addToWhitelist(string $ipAddress, ?string $reason = null): bool
    {
        // Remove from blocklist if exists
        IpList::where('ip_address', $ipAddress)->where('type', 'blocklist')->delete();
        $this->clearSecurityCache($ipAddress);

        IpList::updateOrCreate(
            ['ip_address' => $ipAddress, 'type' => 'whitelist'],
            [
                'reason' => $reason ?? 'IP address whitelisted',
                'created_by' => Auth::id(),
            ]
        );

        SecurityLog::log('ip_whitelisted', null, $ipAddress, $reason ?? 'IP address whitelisted');

        return true;
    }

    /**
     * Remove IP from whitelist.
     */
    public function removeFromWhitelist(string $ipAddress): void
    {
        IpList::where('ip_address', $ipAddress)->where('type', 'whitelist')->delete();
        SecurityLog::log('ip_whitelist_removed', null, $ipAddress, 'IP address removed from whitelist');
    }

    /**
     * Get all blocked IPs.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, IpList>
     */
    public function getBlocklist()
    {
        return IpList::blocklist()->with('creator')->latest()->get();
    }

    /**
     * Get all whitelisted IPs.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, IpList>
     */
    public function getWhitelist()
    {
        return IpList::whitelist()->with('creator')->latest()->get();
    }

    /**
     * Check if account is locked.
     */
    public function isAccountLocked(string $email): bool
    {
        return Cache::has($this->cachePrefix."account_locked:{$email}");
    }

    /**
     * Lock an account temporarily.
     */
    public function lockAccount(string $email, ?string $reason = null): void
    {
        Cache::put(
            $this->cachePrefix."account_locked:{$email}",
            now()->addMinutes($this->accountLockMinutes)->toIso8601String(),
            $this->accountLockMinutes * 60
        );
    }

    /**
     * Unlock an account.
     */
    public function unlockAccount(string $email): void
    {
        Cache::forget($this->cachePrefix."account_locked:{$email}");
        Cache::forget($this->cachePrefix."failed_attempts:email:{$email}");
    }

    /**
     * Get remaining account lockout time in seconds.
     */
    public function getAccountLockoutRemaining(string $email): int
    {
        $key = $this->cachePrefix."account_locked:{$email}";
        $lockUntil = Cache::get($key);

        if (! is_string($lockUntil)) {
            return 0;
        }

        $remaining = Carbon::parse($lockUntil)->diffInSeconds(now(), false);

        return (int) max(0, -$remaining);
    }

    /**
     * Get lockout duration in minutes.
     */
    public function getLockoutDuration(): int
    {
        return $this->accountLockMinutes;
    }

    /**
     * Get max failed attempts threshold.
     */
    public function getMaxFailedAttempts(): int
    {
        return $this->maxFailedAttempts;
    }

    /**
     * Record suspicious activity.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function recordSuspiciousActivity(string $description, ?User $user = null, array $metadata = []): void
    {
        SecurityLog::log('suspicious_activity', $user, request()->ip(), $description, $metadata);
    }

    /**
     * Get security statistics.
     *
     * @return array<string, mixed>
     */
    public function getSecurityStats(int $days = 30): array
    {
        $since = now()->subDays($days);

        return [
            'total_events' => SecurityLog::where('created_at', '>=', $since)->count(),
            'failed_logins' => SecurityLog::where('event_type', 'login_failed')
                ->where('created_at', '>=', $since)
                ->count(),
            'successful_logins' => SecurityLog::where('event_type', 'login_success')
                ->where('created_at', '>=', $since)
                ->count(),
            'blocked_ips' => SecurityLog::whereIn('event_type', ['ip_blocked_temp', 'ip_blocked_permanent'])
                ->where('created_at', '>=', $since)
                ->count(),
            'suspicious_activities' => SecurityLog::where('event_type', 'suspicious_activity')
                ->where('created_at', '>=', $since)
                ->count(),
            'recent_events' => SecurityLog::where('created_at', '>=', $since)
                ->latest()
                ->limit(50)
                ->with('user')
                ->get(),
        ];
    }

    /**
     * Check if an IP is protected (should never be blocked).
     * Only protects localhost - private IPs can still be blocked for security.
     */
    public function isProtectedIp(string $ip): bool
    {
        // Only protect localhost IPs (development/testing)
        $localhostIps = ['127.0.0.1', '::1', 'localhost'];

        return in_array($ip, $localhostIps);
    }

    /**
     * Check if the request has already passed the security shield (Trust Cache).
     */
    public function isShieldVerified(string $ipAddress, string $userAgent): bool
    {
        $key = 'shield:verified:'.md5($ipAddress.$userAgent);

        return Cache::has($this->cachePrefix.$key);
    }

    /**
     * Record a successful shield verification.
     */
    public function recordShieldVerification(string $ipAddress, string $userAgent): void
    {
        $key = 'shield:verified:'.md5($ipAddress.$userAgent);
        // Trust for 24 hours
        Cache::put($this->cachePrefix.$key, true, now()->addDay());

        // Only log success if enabled in settings
        if (\App\Models\Core\Setting::get('shield_log_verification_success', false)) {
            SecurityLog::log('shield_verified', null, $ipAddress, 'Security shield challenge passed', [
                'user_agent' => $userAgent,
            ]);
        }
    }

    /**
     * Generate a signed nonce for the PoW challenge.
     */
    public function generateShieldNonce(string $ipAddress): string
    {
        $timestamp = time();
        $salt = bin2hex(random_bytes(16));
        $data = "{$ipAddress}:{$timestamp}:{$salt}";
        $key = config('app.key');
        $signature = hash_hmac('sha256', $data, is_string($key) ? $key : '');

        return base64_encode("{$data}:{$signature}");
    }

    /**
     * Verify the PoW solution.
     */
    public function verifyShieldSolution(string $nonce, string $solution, string $ipAddress): bool
    {
        $decoded = base64_decode($nonce);
        if (! $decoded) {
            return false;
        }

        $parts = explode(':', $decoded);
        if (count($parts) !== 4) {
            return false;
        }

        [$nonceIp, $timestamp, $salt, $signature] = $parts;

        // Verify IP matches
        if ($nonceIp !== $ipAddress) {
            return false;
        }

        // Verify signature
        $extData = "{$nonceIp}:{$timestamp}:{$salt}";
        $key = config('app.key');
        if (! hash_equals(hash_hmac('sha256', $extData, is_string($key) ? $key : ''), $signature)) {
            return false;
        }

        // Verify TTL (nonce valid for 10 minutes)
        if (time() - (int) $timestamp > 600) {
            return false;
        }

        // Verify PoW solution
        // The solution 'n' must make SHA256(nonce + n) start with the required number of zeros
        $difficulty = $this->getShieldDifficulty();
        $hash = hash('sha256', $nonce.$solution);
        $target = str_repeat('0', $difficulty);

        return str_starts_with($hash, $target);
    }

    /**
     * Get dynamic difficulty based on recent traffic.
     */
    public function getShieldDifficulty(): int
    {
        $settingValue = \App\Models\Core\Setting::get('shield_protection_difficulty', 4);
        $baseDifficulty = is_numeric($settingValue) ? (int) $settingValue : 4;

        // Check for traffic spike in last minute
        $spikeKey = $this->cachePrefix.'shield:attempts_per_minute';
        $attemptsValue = Cache::get($spikeKey, 0);
        $attempts = is_numeric($attemptsValue) ? (int) $attemptsValue : 0;

        // Simple scaling: add 1 zero if attempts > 500/min, add 2 if > 2000/min
        if ($attempts > 2000) {
            return $baseDifficulty + 2;
        } elseif ($attempts > 500) {
            return $baseDifficulty + 1;
        }

        return $baseDifficulty;
    }

    /**
     * Track verification attempts for dynamic scaling.
     */
    public function trackShieldAttempt(): void
    {
        $key = $this->cachePrefix.'shield:attempts_per_minute';
        if (! Cache::has($key)) {
            Cache::put($key, 1, 60);
        } else {
            Cache::increment($key);
        }
    }

    /**
     * Get statistics for the Bot Shield protection.
     *
     * @return array<string, mixed>
     */
    public function getShieldStats(): array
    {
        $attemptsKey = $this->cachePrefix.'shield:attempts_per_minute';
        $attemptsValue = Cache::get($attemptsKey, 0);
        $attempts = is_numeric($attemptsValue) ? (int) $attemptsValue : 0;

        return [
            'verifications' => SecurityLog::where('event_type', 'shield_verified')->count(),
            'failures' => SecurityLog::where('event_type', 'shield_failed')->count(),
            'honeypot' => SecurityLog::where('event_type', 'shield_honeypot')->count(),
            'currentDifficulty' => $this->getShieldDifficulty(),
            'isScaling' => $attempts > 500,
        ];
    }
}
