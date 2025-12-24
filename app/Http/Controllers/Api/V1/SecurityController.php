<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\SecurityLog;
use App\Services\SecurityService;
use App\Services\SecurityAlertService;
use Illuminate\Http\Request;

class SecurityController extends BaseApiController
{
    protected $securityService;
    protected $alertService;

    public function __construct(SecurityService $securityService, SecurityAlertService $alertService)
    {
        $this->securityService = $securityService;
        $this->alertService = $alertService;
    }

    public function index(Request $request)
    {
        $query = SecurityLog::with('user');

        if ($request->has('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        if ($request->has('ip_address')) {
            $query->where('ip_address', $request->ip_address);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage = $request->input('per_page', 50);
        $logs = $query->latest()->paginate($perPage);

        return $this->paginated($logs, 'Security logs retrieved successfully');
    }

    public function show(SecurityLog $securityLog)
    {
        return $this->success($securityLog->load('user'), 'Security log retrieved successfully');
    }

    public function stats(Request $request)
    {
        $days = $request->input('days', 30);
        $stats = $this->securityService->getSecurityStats($days);

        return $this->success($stats, 'Security statistics retrieved successfully');
    }

    /**
     * Get security alerts for suspicious activity
     */
    public function alerts()
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
    
    public function getBlocklist()
    {
        $blocklist = $this->securityService->getBlocklist();
        return $this->success($blocklist, 'Blocklist retrieved successfully');
    }

    public function blockIp(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'reason' => 'nullable|string',
            'permanent' => 'sometimes|boolean',
        ]);

        // Default to permanent blocking so IP appears in blocklist tab
        // Use permanent=false to only block temporarily (cache only)
        if ($request->input('permanent', true)) {
            $result = $this->securityService->blockIpPermanently($request->ip_address, $request->reason);
        } else {
            $seconds = $this->securityService->blockIpTemporarily($request->ip_address, $request->reason);
            $result = $seconds > 0;
        }
        
        if (!$result) {
            return $this->error('Cannot block whitelisted or protected IP address', 400);
        }

        return $this->success(null, 'IP address blocked successfully');
    }

    public function unblockIp(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
        ]);

        $this->securityService->unblockIp($request->ip_address);

        return $this->success(null, 'IP address unblocked successfully');
    }
    
    public function bulkBlock(Request $request)
    {
        $request->validate([
            'ip_addresses' => 'required|array',
            'ip_addresses.*' => 'required|ip',
            'reason' => 'nullable|string',
        ]);
        
        $blocked = 0;
        $skipped = 0;
        
        foreach ($request->ip_addresses as $ip) {
            if ($this->securityService->blockIp($ip, $request->reason)) {
                $blocked++;
            } else {
                $skipped++;
            }
        }
        
        return $this->success([
            'blocked' => $blocked,
            'skipped' => $skipped,
        ], "{$blocked} IP addresses blocked, {$skipped} skipped (whitelisted)");
    }
    
    public function bulkUnblock(Request $request)
    {
        $request->validate([
            'ip_addresses' => 'required|array',
            'ip_addresses.*' => 'required|ip',
        ]);
        
        foreach ($request->ip_addresses as $ip) {
            $this->securityService->unblockIp($ip);
        }
        
        return $this->success(null, count($request->ip_addresses) . ' IP addresses unblocked');
    }

    // =====================
    // Whitelist Management
    // =====================
    
    public function getWhitelist()
    {
        $whitelist = $this->securityService->getWhitelist();
        return $this->success($whitelist, 'Whitelist retrieved successfully');
    }
    
    public function addToWhitelist(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'reason' => 'nullable|string',
        ]);

        $this->securityService->addToWhitelist($request->ip_address, $request->reason);

        return $this->success(null, 'IP address added to whitelist');
    }
    
    public function removeFromWhitelist(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
        ]);

        $this->securityService->removeFromWhitelist($request->ip_address);

        return $this->success(null, 'IP address removed from whitelist');
    }
    
    public function bulkWhitelist(Request $request)
    {
        $request->validate([
            'ip_addresses' => 'required|array',
            'ip_addresses.*' => 'required|ip',
            'reason' => 'nullable|string',
        ]);
        
        foreach ($request->ip_addresses as $ip) {
            $this->securityService->addToWhitelist($ip, $request->reason);
        }
        
        return $this->success(null, count($request->ip_addresses) . ' IP addresses added to whitelist');
    }
    
    public function bulkRemoveWhitelist(Request $request)
    {
        $request->validate([
            'ip_addresses' => 'required|array',
            'ip_addresses.*' => 'required|ip',
        ]);
        
        foreach ($request->ip_addresses as $ip) {
            $this->securityService->removeFromWhitelist($ip);
        }
        
        return $this->success(null, count($request->ip_addresses) . ' IP addresses removed from whitelist');
    }

    // =====================
    // IP Check & Clear
    // =====================

    public function checkIp(Request $request)
    {
        $ipAddress = $request->input('ip_address', \App\Helpers\IpHelper::getClientIp($request));
        $blockInfo = $this->securityService->getBlockInfo($ipAddress);

        return $this->success([
            'ip_address' => $ipAddress,
            'is_blocked' => $blockInfo['is_blocked'],
            'remaining_seconds' => $blockInfo['remaining_seconds'],
            'failed_attempts' => $blockInfo['failed_attempts'],
            'offense_count' => $blockInfo['offense_count'],
        ], 'IP status retrieved successfully');
    }

    public function clearFailedAttempts(Request $request)
    {
        $ipAddress = $request->input('ip_address', \App\Helpers\IpHelper::getClientIp($request));
        
        // Clear all security cache for IP
        $this->securityService->clearSecurityCache($ipAddress);
        $this->securityService->unblockIp($ipAddress);
        
        // Also clear email-based locks if provided
        if ($request->has('email')) {
            $this->securityService->clearSecurityCache($request->email, 'email');
            $this->securityService->unlockAccount($request->email);
        }

        return $this->success(null, 'Security cache cleared for IP: ' . $ipAddress);
    }
}
