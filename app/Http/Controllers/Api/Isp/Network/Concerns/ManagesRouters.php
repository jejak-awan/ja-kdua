<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Network\Concerns;

use App\Models\Isp\Network\ServiceNode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @property \App\Services\Isp\Network\RouterService $routerService
 * @mixin \App\Http\Controllers\Api\Isp\Network\InfrastructureController
 */
trait ManagesRouters
{
    public function routerIndex(Request $request): JsonResponse
    {
        $query = ServiceNode::where('type', 'Router');

        if ($request->has('search')) {
            $search = $request->input('search');
            $searchTerm = is_string($search) ? $search : '';
            $query->where('name', 'like', '%'.$searchTerm.'%')
                ->orWhere('ip_address', 'like', '%'.$searchTerm.'%');
        }

        $perPage = $request->input('per_page', 10);
        $perPageInt = is_numeric($perPage) ? (int) $perPage : 10;

        $routers = $query->latest()->paginate($perPageInt);

        return $this->success($routers, 'Router list retrieved successfully');
    }

    public function testRouterConnection(ServiceNode $infra): JsonResponse
    {
        $status = $this->routerService->getDetailedStatus($infra);
        if ($status['is_connected']) {
            return $this->success($status, 'Router connection successful');
        }

        return $this->error('Failed to connect to router. Check credentials and accessibility.', 500);
    }

    public function reconstructRouter(ServiceNode $infra): JsonResponse
    {
        try {
            // Trigger background job or direct sync
            $this->routerService->syncToRadius($infra);

            return $this->success(null, 'Router reconstruction/sync started');
        } catch (\Exception $e) {
            return $this->error('Failed to trigger reconstruction: '.$e->getMessage(), 500);
        }
    }

    public function routerVpnSecrets(ServiceNode $infra): JsonResponse
    {
        $secrets = $this->routerService->getVpnSecrets($infra);

        return $this->success($secrets, 'VPN secrets retrieved successfully');
    }

    public function storeRouterVpnSecret(Request $request, ServiceNode $infra): JsonResponse
    {
        /** @var array<string, mixed> $validated */
        $validated = $request->validate([
            'name' => 'required|string',
            'password' => 'required|string',
            'service' => 'nullable|string',
            'profile' => 'nullable|string',
            'remote_address' => 'nullable|string',
            'comment' => 'nullable|string',
        ]);

        if ($this->routerService->createVpnSecret($infra, $validated)) {
            return $this->success(null, 'VPN secret created/updated successfully');
        }

        return $this->error('Failed to create VPN secret on router', 500);
    }

    public function destroyRouterVpnSecret(ServiceNode $infra, string $id): JsonResponse
    {
        if ($this->routerService->deleteVpnSecret($infra, $id)) {
            return $this->success(null, 'VPN secret deleted successfully');
        }

        return $this->error('Failed to delete VPN secret from router', 500);
    }
}
