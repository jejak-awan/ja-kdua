<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Core;

use App\Helpers\IpHelper;
use App\Models\Core\SecurityLog;
use App\Services\Core\SecurityAlertService;
use App\Services\Core\SecurityService;
use Illuminate\Http\Request;

class SecurityController extends BaseApiController
{
    protected SecurityService $securityService;

    protected SecurityAlertService $alertService;

    public function __construct(SecurityService $securityService, SecurityAlertService $alertService)
    {
        $this->securityService = $securityService;
        $this->alertService = $alertService;
    }

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = SecurityLog::with('user');

        if ($request->has('event_type')) {
            $eventTypeRaw = $request->input('event_type');
            $eventType = is_string($eventTypeRaw) ? $eventTypeRaw : '';
            $query->where('event_type', $eventType);
        }

        if ($request->has('ip_address')) {
            $ipAddressRaw = $request->input('ip_address');
            $ipAddress = is_string($ipAddressRaw) ? $ipAddressRaw : '';
            $query->where('ip_address', $ipAddress);
        }

        if ($request->has('user_id')) {
            $userIdRaw = $request->input('user_id');
            $userId = is_numeric($userIdRaw) ? (int) $userIdRaw : 0;
            $query->where('user_id', $userId);
        }

        if ($request->has('date_from')) {
            $dateFromRaw = $request->input('date_from');
            $dateFrom = is_string($dateFromRaw) ? $dateFromRaw : null;
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($request->has('date_to')) {
            $dateToRaw = $request->input('date_to');
            $dateTo = is_string($dateToRaw) ? $dateToRaw : null;
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $perPageRaw = $request->input('per_page', 50);
        $perPage = is_numeric($perPageRaw) ? (int) $perPageRaw : 50;
        $logs = $query->latest()->paginate($perPage);

        return $this->paginated($logs, 'Security logs retrieved successfully');
    }

    public function show(SecurityLog $securityLog): \Illuminate\Http\JsonResponse
    {
        return $this->success($securityLog->load('user'), 'Security log retrieved successfully');
    }

    public function stats(Request $request): \Illuminate\Http\JsonResponse
    {
        $daysRaw = $request->input('days', 30);
        $days = is_numeric($daysRaw) ? (int) $daysRaw : 30;
        $stats = $this->securityService->getSecurityStats($days);

        return $this->success($stats, 'Security statistics retrieved successfully');
    }

    /**
     * Get security alerts for suspicious activity
     */
    public function alerts(): \Illuminate\Http\JsonResponse
    {
        $alerts = $this->alertService->getAlerts();
        $count = $this->alertService->getAlertCount();

        return $this->success([
            'alerts' => $alerts,
            'count' => $count,
        ], 'Security alerts retrieved successfully');
    }

    // =====================
    // Blocklist Management
    // =====================

    public function getBlocklist(): \Illuminate\Http\JsonResponse
    {
        $blocklist = $this->securityService->getBlocklist();

        return $this->success($blocklist, 'Blocklist retrieved successfully');
    }

    public function blockIp(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'reason' => 'nullable|string',
            'permanent' => 'sometimes|boolean',
        ]);

        $ipAddressRaw = $request->input('ip_address');
        $ipAddress = is_string($ipAddressRaw) ? $ipAddressRaw : '';
        $reasonRaw = $request->input('reason');
        $reason = is_string($reasonRaw) ? $reasonRaw : null;
        $permanent = $request->boolean('permanent', true);

        // Default to permanent blocking so IP appears in blocklist tab
        // Use permanent=false to only block temporarily (cache only)
        if ($permanent) {
            $result = $this->securityService->blockIpPermanently($ipAddress, $reason);
        } else {
            $seconds = $this->securityService->blockIpTemporarily($ipAddress, $reason);
            $result = $seconds > 0;
        }

        if (! $result) {
            return $this->error('Cannot block whitelisted or protected IP address', 400);
        }

        return $this->success(null, 'IP address blocked successfully');
    }

    public function unblockIp(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'ip_address' => 'required|ip',
        ]);

        $ipAddressRaw = $request->input('ip_address');
        $ipAddress = is_string($ipAddressRaw) ? $ipAddressRaw : '';

        $this->securityService->unblockIp($ipAddress);

        return $this->success(null, 'IP address unblocked successfully');
    }

    public function bulkBlock(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'ip_addresses' => 'required|array',
            'ip_addresses.*' => 'required|ip',
            'reason' => 'nullable|string',
        ]);

        $blocked = 0;
        $skipped = 0;

        $ipAddressesRaw = $request->input('ip_addresses');
        $ipAddresses = is_array($ipAddressesRaw) ? $ipAddressesRaw : [];
        $reasonRaw = $request->input('reason');
        $reason = is_string($reasonRaw) ? $reasonRaw : null;

        foreach ($ipAddresses as $ip) {
            if (is_string($ip) && $this->securityService->blockIpPermanently($ip, $reason)) {
                $blocked++;
            } else {
                $skipped++;
            }
        }

        $blockedStr = (string) $blocked;
        $skippedStr = (string) $skipped;

        return $this->success([
            'blocked' => $blocked,
            'skipped' => $skipped,
        ], "{$blockedStr} IP addresses blocked, {$skippedStr} skipped (whitelisted)");
    }

    public function bulkUnblock(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'ip_addresses' => 'required|array',
            'ip_addresses.*' => 'required|ip',
        ]);

        $ipAddressesRaw = $request->input('ip_addresses');
        $ipAddresses = is_array($ipAddressesRaw) ? $ipAddressesRaw : [];

        foreach ($ipAddresses as $ip) {
            if (is_string($ip)) {
                $this->securityService->unblockIp($ip);
            }
        }

        $count = count($ipAddresses);
        $countStr = (string) $count;

        return $this->success(null, "{$countStr} IP addresses unblocked");
    }

    // =====================
    // Whitelist Management
    // =====================

    public function getWhitelist(): \Illuminate\Http\JsonResponse
    {
        $whitelist = $this->securityService->getWhitelist();

        return $this->success($whitelist, 'Whitelist retrieved successfully');
    }

    public function addToWhitelist(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'reason' => 'nullable|string',
        ]);

        $ipAddressRaw = $request->input('ip_address');
        $ipAddress = is_string($ipAddressRaw) ? $ipAddressRaw : '';
        $reasonRaw = $request->input('reason');
        $reason = is_string($reasonRaw) ? $reasonRaw : null;

        $this->securityService->addToWhitelist($ipAddress, $reason);

        return $this->success(null, 'IP address added to whitelist');
    }

    public function removeFromWhitelist(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'ip_address' => 'required|ip',
        ]);

        $ipAddressRaw = $request->input('ip_address');
        $ipAddress = is_string($ipAddressRaw) ? $ipAddressRaw : '';

        $this->securityService->removeFromWhitelist($ipAddress);

        return $this->success(null, 'IP address removed from whitelist');
    }

    public function bulkWhitelist(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'ip_addresses' => 'required|array',
            'ip_addresses.*' => 'required|ip',
            'reason' => 'nullable|string',
        ]);

        $ipAddressesRaw = $request->input('ip_addresses');
        $ipAddresses = is_array($ipAddressesRaw) ? $ipAddressesRaw : [];
        $reasonRaw = $request->input('reason');
        $reason = is_string($reasonRaw) ? $reasonRaw : null;

        foreach ($ipAddresses as $ip) {
            if (is_string($ip)) {
                $this->securityService->addToWhitelist($ip, $reason);
            }
        }

        $count = count($ipAddresses);
        $countStr = (string) $count;

        return $this->success(null, "{$countStr} IP addresses added to whitelist");
    }

    public function bulkRemoveWhitelist(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'ip_addresses' => 'required|array',
            'ip_addresses.*' => 'required|ip',
        ]);

        $ipAddressesRaw = $request->input('ip_addresses');
        $ipAddresses = is_array($ipAddressesRaw) ? $ipAddressesRaw : [];

        foreach ($ipAddresses as $ip) {
            if (is_string($ip)) {
                $this->securityService->removeFromWhitelist($ip);
            }
        }

        $count = count($ipAddresses);
        $countStr = (string) $count;

        return $this->success(null, "{$countStr} IP addresses removed from whitelist");
    }

    // =====================
    // IP Check & Clear
    // =====================

    public function checkIp(Request $request): \Illuminate\Http\JsonResponse
    {
        $ipAddressRaw = $request->input('ip_address', \App\Helpers\IpHelper::getClientIp($request));
        $ipAddress = is_string($ipAddressRaw) ? $ipAddressRaw : '';
        $blockInfo = $this->securityService->getBlockInfo($ipAddress);

        return $this->success([
            'ip_address' => $ipAddress,
            'is_blocked' => $blockInfo['is_blocked'],
            'remaining_seconds' => $blockInfo['remaining_seconds'],
            'failed_attempts' => $blockInfo['failed_attempts'],
            'offense_count' => $blockInfo['offense_count'],
        ], 'IP status retrieved successfully');
    }

    public function clearFailedAttempts(Request $request): \Illuminate\Http\JsonResponse
    {
        $ipAddressRaw = $request->input('ip_address', \App\Helpers\IpHelper::getClientIp($request));
        $ipAddress = is_string($ipAddressRaw) ? $ipAddressRaw : '';

        // Clear all security cache for IP
        $this->securityService->clearSecurityCache($ipAddress);
        $this->securityService->unblockIp($ipAddress);

        // Also clear email-based locks if provided
        if ($request->has('email')) {
            $emailRaw = $request->input('email');
            $email = is_string($emailRaw) ? $emailRaw : '';
            $this->securityService->clearSecurityCache($email, 'email');
            $this->securityService->unlockAccount($email);
        }

        return $this->success(null, 'Security cache cleared for IP: '.$ipAddress);
    }

    public function clear(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $retainDaysRaw = $request->input('retain_days');

            if ($retainDaysRaw) {
                $retainDays = is_numeric($retainDaysRaw) ? (int) $retainDaysRaw : 0;
                $countRaw = \App\Models\Core\SecurityLog::where('created_at', '<', now()->subDays($retainDays))->delete();
                $count = is_numeric($countRaw) ? (int) $countRaw : 0;

                $countStr = (string) $count;
                $retainDaysStr = (string) $retainDays;

                return $this->success(null, "Cleared {$countStr} security logs older than {$retainDaysStr} days");
            }

            \App\Models\Core\SecurityLog::truncate();

            return $this->success(null, 'All security logs cleared successfully');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Security logs clear error: '.$e->getMessage());

            return $this->error('Failed to clear security logs', 500);
        }
    }

    /**
     * Verify the Proof-of-Work solution for the security shield.
     */
    public function verifyConnection(Request $request): \Illuminate\Http\JsonResponse
    {
        // 1. Honeypot check: If any honeypot field is filled, it's a bot.
        $ip = IpHelper::getClientIp($request);
        if ($request->filled('_hp_email') || $request->filled('_hp_subject')) {
            $this->securityService->blockIpPermanently($ip, 'Security Shield: Honeypot trap triggered');

            return $this->error('Access Denied', 403);
        }

        $nonceRaw = $request->input('nonce');
        $nonce = is_string($nonceRaw) ? $nonceRaw : '';

        $solutionRaw = $request->input('solution');
        $solution = is_string($solutionRaw) ? $solutionRaw : '';

        if (! $nonce || ! $solution) {
            return $this->error('Missing challenge details', 400);
        }

        // Track attempt for dynamic scaling
        $this->securityService->trackShieldAttempt();

        if ($this->securityService->verifyShieldSolution($nonce, $solution, $ip)) {
            // Record verification in Trust Cache
            $this->securityService->recordShieldVerification($ip, (string) $request->userAgent());

            return $this->success([
                'verified' => true,
                'redirect_to' => $request->input('redirect_to') ?? session()->pull('shield_redirect_to', '/'),
            ], 'Connection verified successfully');
        }

        return $this->error('Challenge verification failed', 422);
    }

    /**
     * Get the Bot Shield journal of security events.
     */
    public function shieldJournal(Request $request): \Illuminate\Http\JsonResponse
    {
        $perPageRaw = $request->input('per_page', 50);
        $perPage = is_numeric($perPageRaw) ? (int) $perPageRaw : 50;

        $logs = SecurityLog::whereIn('event_type', ['shield_verified', 'shield_failed', 'shield_honeypot'])
            ->latest()
            ->paginate($perPage);

        return $this->paginated($logs, 'Shield journal retrieved successfully');
    }

    /**
     * Clear Bot Shield logs.
     */
    public function clearShieldLogs(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $retainDaysRaw = $request->input('retain_days');
            
            $query = SecurityLog::whereIn('event_type', ['shield_verified', 'shield_failed', 'shield_honeypot']);

            if ($retainDaysRaw) {
                $retainDays = is_numeric($retainDaysRaw) ? (int) $retainDaysRaw : 0;
                $count = $query->where('created_at', '<', now()->subDays($retainDays))->delete();
                $message = "Cleared {$count} shield logs older than {$retainDays} days";
            } else {
                $count = $query->delete();
                $message = "All shield logs cleared successfully";
            }

            return $this->success(null, $message);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Shield logs clear error: '.$e->getMessage());

            return $this->error('Failed to clear shield logs', 500);
        }
    }

    /**
     * Get statistics for the Bot Shield.
     */
    public function shieldStats(): \Illuminate\Http\JsonResponse
    {
        $stats = $this->securityService->getShieldStats();

        return $this->success($stats, 'Shield statistics retrieved successfully');
    }
}
