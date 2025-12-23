<?php

namespace App\Services;

use App\Helpers\IpHelper;
use App\Models\IpList;
use App\Models\SecurityLog;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

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

        // Block IP if too many attempts (but not localhost/private IPs)
        if ($ipAttempts >= $this->maxFailedAttempts && !$this->isProtectedIp($ipAddress)) {
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
        // Never block protected IPs (localhost, private ranges)
        if ($this->isProtectedIp($ipAddress)) {
            return false;
        }
        
        // Check whitelist first - whitelisted IPs are never blocked
        if (IpList::isWhitelisted($ipAddress)) {
            return false;
        }
        
        // Check database blocklist
        if (IpList::isBlocked($ipAddress)) {
            return true;
        }
        
        // Check cache (temporary blocks from failed login attempts)
        return Cache::has("blocked_ip_{$ipAddress}");
    }

    public function blockIp($ipAddress, $reason = null)
    {
        // Don't block protected IPs (localhost, private ranges)
        if ($this->isProtectedIp($ipAddress)) {
            return false;
        }
        
        // Don't block whitelisted IPs
        if (IpList::isWhitelisted($ipAddress)) {
            return false;
        }
        
        // Add to database blocklist (permanent)
        IpList::updateOrCreate(
            ['ip_address' => $ipAddress, 'type' => 'blocklist'],
            [
                'reason' => $reason ?? 'IP address blocked',
                'created_by' => Auth::id(),
            ]
        );
        
        // Also add to cache for faster lookup
        Cache::put("blocked_ip_{$ipAddress}", true, now()->addHours(24));

        SecurityLog::log('ip_blocked', null, $ipAddress, $reason ?? 'IP address blocked');
        
        return true;
    }

    public function unblockIp($ipAddress)
    {
        // Remove from database blocklist
        IpList::where('ip_address', $ipAddress)->where('type', 'blocklist')->delete();
        
        // Remove from cache
        Cache::forget("blocked_ip_{$ipAddress}");

        SecurityLog::log('ip_unblocked', null, $ipAddress, 'IP address unblocked');
    }
    
    public function addToWhitelist($ipAddress, $reason = null)
    {
        // Remove from blocklist if exists
        IpList::where('ip_address', $ipAddress)->where('type', 'blocklist')->delete();
        Cache::forget("blocked_ip_{$ipAddress}");
        
        // Add to whitelist
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
    
    public function removeFromWhitelist($ipAddress)
    {
        IpList::where('ip_address', $ipAddress)->where('type', 'whitelist')->delete();
        
        SecurityLog::log('ip_whitelist_removed', null, $ipAddress, 'IP address removed from whitelist');
    }
    
    public function getBlocklist()
    {
        return IpList::blocklist()->with('creator')->latest()->get();
    }
    
    public function getWhitelist()
    {
        return IpList::whitelist()->with('creator')->latest()->get();
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

    /**
     * Check if an IP is protected (should never be blocked).
     * This prevents false positives from blocking server/localhost IPs.
     *
     * @param string $ip
     * @return bool
     */
    public function isProtectedIp(string $ip): bool
    {
        // Localhost IPs
        $localhostIps = ['127.0.0.1', '::1', 'localhost'];
        if (in_array($ip, $localhostIps)) {
            return true;
        }

        // Private IP ranges (shouldn't be blocked as they're typically server IPs)
        // 10.0.0.0 - 10.255.255.255
        // 172.16.0.0 - 172.31.255.255
        // 192.168.0.0 - 192.168.255.255
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $isPrivate = filter_var(
                $ip,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            ) === false;
            
            if ($isPrivate) {
                return true;
            }
        }

        return false;
    }
}
