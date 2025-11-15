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

        // Track failed attempts
        $key = "failed_login_attempts_{$ipAddress}";
        $attempts = Cache::get($key, 0) + 1;
        Cache::put($key, $attempts, now()->addMinutes($this->lockoutDuration));

        // Block IP if too many attempts
        if ($attempts >= $this->maxFailedAttempts) {
            $this->blockIp($ipAddress, "Too many failed login attempts ({$attempts})");
            SecurityLog::log('login_blocked', null, $ipAddress, "IP blocked due to {$attempts} failed login attempts");
        }
    }

    public function recordSuccessfulLogin(User $user, $ipAddress)
    {
        SecurityLog::log('login_success', $user, $ipAddress, "Successful login for user: {$user->email}");

        // Clear failed attempts
        $key = "failed_login_attempts_{$ipAddress}";
        Cache::forget($key);
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
