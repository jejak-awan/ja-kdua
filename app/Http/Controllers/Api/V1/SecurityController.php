<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\SecurityLog;
use App\Services\SecurityService;
use Illuminate\Http\Request;

class SecurityController extends BaseApiController
{
    protected $securityService;

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
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

        $logs = $query->latest()->paginate(50);

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

    public function blockIp(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'reason' => 'nullable|string',
        ]);

        $this->securityService->blockIp($request->ip_address, $request->reason);

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

    public function checkIp(Request $request)
    {
        $ipAddress = $request->input('ip_address', $request->ip());
        $isBlocked = $this->securityService->isIpBlocked($ipAddress);
        $failedAttempts = $this->securityService->getFailedAttempts($ipAddress);

        return $this->success([
            'ip_address' => $ipAddress,
            'is_blocked' => $isBlocked,
            'failed_attempts' => $failedAttempts,
        ], 'IP status retrieved successfully');
    }

    public function clearFailedAttempts(Request $request)
    {
        $ipAddress = $request->input('ip_address', $request->ip());
        $this->securityService->clearFailedAttempts($ipAddress);
        $this->securityService->unblockIp($ipAddress);

        return $this->success(null, 'Failed attempts and block status cleared for IP: ' . $ipAddress);
    }
}
