<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Monitoring;

use App\Http\Controllers\Api\Core\BaseApiController;
use App\Services\Isp\RadiusIntegration;
use Illuminate\Http\Request;

class RadiusMonitorController extends BaseApiController
{
    protected RadiusIntegration $radius;

    public function __construct(RadiusIntegration $radius)
    {
        $this->radius = $radius;
    }

    /**
     * Get active sessions.
     */
    public function sessions(Request $request): \Illuminate\Http\JsonResponse
    {
        $limitVal = $request->input('limit', 50);
        $limit = is_numeric($limitVal) ? (int) $limitVal : 50;

        return $this->success($this->radius->getActiveSessions($limit), 'Active sessions retrieved');
    }

    /**
     * Get authentication logs.
     */
    public function logs(Request $request): \Illuminate\Http\JsonResponse
    {
        $limitVal = $request->input('limit', 50);
        $limit = is_numeric($limitVal) ? (int) $limitVal : 50;

        return $this->success($this->radius->getAuthLogs($limit), 'Authentication logs retrieved');
    }

    /**
     * Get RADIUS server status.
     */
    public function status(): \Illuminate\Http\JsonResponse
    {
        return $this->success($this->radius->getRadiusStatus(), 'RADIUS status retrieved');
    }
}
