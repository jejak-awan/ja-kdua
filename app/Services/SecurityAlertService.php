<?php

namespace App\Services;

use App\Models\LoginHistory;
use App\Models\SecurityLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SecurityAlertService
{
    /**
     * Alert thresholds
     */
    protected int $failedLoginThreshold = 5;

    protected int $blockedIpThreshold = 3;

    protected int $suspiciousIpThreshold = 10;

    protected int $checkWindowMinutes = 60;

    /**
     * Get all security alerts
     */
    public function getAlerts(): array
    {
        return Cache::remember('security_alerts', 60, function () {
            $alerts = [];
            $since = Carbon::now()->subMinutes($this->checkWindowMinutes);

            // Check for multiple failed logins
            $failedLogins = $this->getFailedLoginAlerts($since);
            $alerts = array_merge($alerts, $failedLogins);

            // Check for blocked IPs
            $blockedIps = $this->getBlockedIpAlerts($since);
            $alerts = array_merge($alerts, $blockedIps);

            // Check for suspicious activity patterns
            $suspicious = $this->getSuspiciousActivityAlerts($since);
            $alerts = array_merge($alerts, $suspicious);

            // Sort by severity and time
            usort($alerts, function ($a, $b) {
                $severityOrder = ['critical' => 0, 'warning' => 1, 'info' => 2];
                $severityDiff = ($severityOrder[$a['severity']] ?? 3) - ($severityOrder[$b['severity']] ?? 3);
                if ($severityDiff !== 0) {
                    return $severityDiff;
                }

                return strtotime($b['timestamp']) - strtotime($a['timestamp']);
            });

            return array_slice($alerts, 0, 20); // Max 20 alerts
        });
    }

    /**
     * Get count of unread/active alerts
     */
    public function getAlertCount(): int
    {
        $alerts = $this->getAlerts();

        return count(array_filter($alerts, fn ($a) => $a['severity'] !== 'info'));
    }

    /**
     * Check for failed login attempts
     */
    protected function getFailedLoginAlerts(Carbon $since): array
    {
        $alerts = [];

        try {
            // Group failed logins by IP
            $failedByIp = SecurityLog::where('created_at', '>=', $since)
                ->where('event_type', 'login_failed')
                ->selectRaw('ip_address, COUNT(*) as count')
                ->groupBy('ip_address')
                ->having('count', '>=', $this->failedLoginThreshold)
                ->get();

            foreach ($failedByIp as $record) {
                $alerts[] = [
                    'id' => 'failed_login_'.md5($record->ip_address),
                    'type' => 'failed_login',
                    'severity' => $record->count >= 10 ? 'critical' : 'warning',
                    'title' => 'Multiple Failed Logins',
                    'message' => "IP {$record->ip_address} has {$record->count} failed login attempts in the last hour",
                    'ip_address' => $record->ip_address,
                    'count' => $record->count,
                    'timestamp' => now()->toISOString(),
                ];
            }
        } catch (\Exception $e) {
            Log::error('Failed to get failed login alerts: '.$e->getMessage());
        }

        return $alerts;
    }

    /**
     * Check for recently blocked IPs
     */
    protected function getBlockedIpAlerts(Carbon $since): array
    {
        $alerts = [];

        try {
            $blockedIps = SecurityLog::where('created_at', '>=', $since)
                ->where('event_type', 'ip_blocked')
                ->latest()
                ->limit(10)
                ->get();

            foreach ($blockedIps as $log) {
                $alerts[] = [
                    'id' => 'blocked_ip_'.$log->id,
                    'type' => 'ip_blocked',
                    'severity' => 'warning',
                    'title' => 'IP Blocked',
                    'message' => "IP {$log->ip_address} was blocked due to suspicious activity",
                    'ip_address' => $log->ip_address,
                    'timestamp' => $log->created_at->toISOString(),
                ];
            }
        } catch (\Exception $e) {
            Log::error('Failed to get blocked IP alerts: '.$e->getMessage());
        }

        return $alerts;
    }

    /**
     * Check for suspicious activity patterns
     */
    protected function getSuspiciousActivityAlerts(Carbon $since): array
    {
        $alerts = [];

        try {
            // Check for unusual login patterns (same user from multiple IPs)
            $multipleIpLogins = LoginHistory::where('created_at', '>=', $since)
                ->where('status', 'success')
                ->selectRaw('user_id, COUNT(DISTINCT ip_address) as ip_count')
                ->groupBy('user_id')
                ->having('ip_count', '>=', 3)
                ->with('user:id,name,email')
                ->get();

            foreach ($multipleIpLogins as $record) {
                if ($record->user) {
                    $alerts[] = [
                        'id' => 'multiple_ip_'.$record->user_id,
                        'type' => 'multiple_ip_login',
                        'severity' => 'info',
                        'title' => 'Multiple IP Login',
                        'message' => "User {$record->user->name} logged in from {$record->ip_count} different IPs",
                        'user_id' => $record->user_id,
                        'user_name' => $record->user->name,
                        'timestamp' => now()->toISOString(),
                    ];
                }
            }

            // Check for brute force attempts (many failed logins to same user)
            $bruteForce = SecurityLog::where('created_at', '>=', $since)
                ->where('event_type', 'login_failed')
                ->whereNotNull('user_id')
                ->selectRaw('user_id, COUNT(*) as count')
                ->groupBy('user_id')
                ->having('count', '>=', 5)
                ->get();

            foreach ($bruteForce as $record) {
                $alerts[] = [
                    'id' => 'brute_force_'.$record->user_id,
                    'type' => 'brute_force',
                    'severity' => 'critical',
                    'title' => 'Possible Brute Force Attack',
                    'message' => "User ID {$record->user_id} received {$record->count} failed login attempts",
                    'user_id' => $record->user_id,
                    'count' => $record->count,
                    'timestamp' => now()->toISOString(),
                ];
            }
        } catch (\Exception $e) {
            Log::error('Failed to get suspicious activity alerts: '.$e->getMessage());
        }

        return $alerts;
    }

    /**
     * Clear cached alerts
     */
    public function clearCache(): void
    {
        Cache::forget('security_alerts');
    }
}
