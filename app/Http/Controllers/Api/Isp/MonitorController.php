<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\Isp\ServiceNode;
use App\Services\Isp\MikrotikService;
use Illuminate\Http\Request;

class MonitorController extends BaseApiController
{
    protected MikrotikService $mikrotik;

    public function __construct(MikrotikService $mikrotik)
    {
        $this->mikrotik = $mikrotik;
    }

    /**
     * Get real-time monitoring statistics.
     */
    public function stats(Request $request): \Illuminate\Http\JsonResponse
    {
        $global = $this->mikrotik->getGlobalStats();
        $historyResult = $this->mikrotik->getTrafficHistory();

        // Get stats for core nodes
        $nodes = ServiceNode::where('status', 'active')->limit(5)->get();
        $nodeStats = $nodes->map(function ($node) {
            return $this->mikrotik->getNodeStats($node);
        });

        return $this->success([
            'global' => $global,
            'history' => $historyResult['data'],
            'nodes' => $nodeStats,
            'is_simulated' => $historyResult['is_simulated'],
        ], 'Monitoring statistics retrieved successfully');
    }

    public function sessions(Request $request): \Illuminate\Http\JsonResponse
    {
        $routerId = $request->input('router_id');
        if (! $routerId) {
            return $this->error('Router ID required', 400);
        }

        /** @var ServiceNode $router */
        $router = ServiceNode::findOrFail($routerId);

        /** @var \App\Services\Isp\RouterService $routerService */
        $routerService = app(\App\Services\Isp\RouterService::class);
        $sessions = $routerService->getActiveSessions($router);

        return $this->success($sessions, 'Active sessions retrieved successfully');
    }

    public function disconnect(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'router_id' => 'required|integer',
            'type' => 'required|in:pppoe,hotspot',
            'id' => 'required|string',
        ]);

        /** @var ServiceNode $router */
        $router = ServiceNode::findOrFail($request->input('router_id'));

        /** @var \App\Services\Isp\RouterService $routerService */
        $routerService = app(\App\Services\Isp\RouterService::class);

        $typeValue = $request->input('type');
        $idValue = $request->input('id');

        $type = is_string($typeValue) ? $typeValue : '';
        $id = is_string($idValue) ? $idValue : '';

        $success = $routerService->disconnectSession(
            $router,
            $type,
            $id
        );

        if (! $success) {
            return $this->error('Failed to disconnect session', 500);
        }

        return $this->success(null, 'Session disconnected successfully');
    }
}
