<?php

namespace App\Services;

use App\Models\SecurityLog;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class SecurityService
{
    protected $maxFailedAttempts = 5;

    protected $lockoutDuration = 15; // minutes

    public function recordFailedLogin($email, $ipAddress)
    {
        SecurityLog::log('login_failed', null, $ipAddress, "Failed login attempt for email: {$email}", [
            'email' => $email,
        ]);

        // Track failed attempts by IP
        $ipKey = "failed_login_attempts_{$ipAddress}";
        $ipAttempts = Cache::get($ipKey, 0) + 1;
        Cache::put($ipKey, $ipAttempts, now()->addMinutes($this->lockoutDuration));

        // Track failed attempts by email (for account lockout)
        $emailKey = "failed_login_attempts_email_{$email}";
        $emailAttempts = Cache::get($emailKey, 0) + 1;
        Cache::put($emailKey, $emailAttempts, now()->addMinutes($this->lockoutDuration));

        // Block IP if too many attempts
        if ($ipAttempts >= $this->maxFailedAttempts) {
            $this->blockIp($ipAddress, "Too many failed login attempts ({$ipAttempts})");
            SecurityLog::log('login_blocked', null, $ipAddress, "IP blocked due to {$ipAttempts} failed login attempts");
        }

        // Lock account if too many attempts
        if ($emailAttempts >= $this->maxFailedAttempts) {
            $this->lockAccount($email, "Too many failed login attempts ({$emailAttempts})");
            SecurityLog::log('account_locked', null, $ipAddress, "Account locked due to {$emailAttempts} failed login attempts", [
                'email' => $email,
            ]);
        }
    }

    public function recordSuccessfulLogin(User $user, $ipAddress)
    {
        SecurityLog::log('login_success', $user, $ipAddress, "Successful login for user: {$user->email}");

        // Clear failed attempts for IP
        $ipKey = "failed_login_attempts_{$ipAddress}";
        Cache::forget($ipKey);

        // Clear failed attempts for email
        $emailKey = "failed_login_attempts_email_{$user->email}";
        Cache::forget($emailKey);

        // Unlock account if locked
        $this->unlockAccount($user->email);
    }

    public function isIpBlocked($ipAddress)
    {
        return Cache::has("blocked_ip_{$ipAddress}");
    }

    public function blockIp($ipAddress, $reason = null)
    {
        Cache::put("blocked_ip_{$ipAddress}", true, now()->addHours(24));

        SecurityLog::log('ip_blocked', null, $ipAddress, $reason ?? 'IP address blocked');
    }

    public function unblockIp($ipAddress)
    {
        Cache::forget("blocked_ip_{$ipAddress}");

        SecurityLog::log('ip_unblocked', null, $ipAddress, 'IP address unblocked');
    }

    public function getFailedAttempts($ipAddress)
    {
        return Cache::get("failed_login_attempts_{$ipAddress}", 0);
    }

    public function clearFailedAttempts($ipAddress)
    {
        Cache::forget("failed_login_attempts_{$ipAddress}");
    }

    public function isAccountLocked($email)
    {
        return Cache::has("account_locked_{$email}");
    }

    public function lockAccount($email, $reason = null)
    {
        Cache::put("account_locked_{$email}", true, now()->addMinutes($this->lockoutDuration));
    }

    public function unlockAccount($email)
    {
        Cache::forget("account_locked_{$email}");
        Cache::forget("failed_login_attempts_email_{$email}");
    }

    public function getAccountLockoutTime($email)
    {
        $lockoutKey = "account_locked_{$email}";
        if (!Cache::has($lockoutKey)) {
            return null;
        }

        // Get remaining time from cache TTL
        // Note: This is approximate, as Cache doesn't expose TTL directly
        return now()->addMinutes($this->lockoutDuration);
    }

    public function getLockoutDuration()
    {
        return $this->lockoutDuration;
    }

    public function getMaxFailedAttempts()
    {
        return $this->maxFailedAttempts;
    }

    public function clearAllBlockedIPs()
    {
        // This is a simple approach - in production you might want to track blocked IPs in database
        // For now, we'll just clear the cache patterns
        Cache::flush();
    }

    public function recordSuspiciousActivity($description, $user = null, $metadata = [])
    {
        SecurityLog::log('suspicious_activity', $user, request()->ip(), $description, $metadata);
    }

    public function getSecurityStats($days = 30)
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
            'blocked_ips' => SecurityLog::where('event_type', 'ip_blocked')
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
}
