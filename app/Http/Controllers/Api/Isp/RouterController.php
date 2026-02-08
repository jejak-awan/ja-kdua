<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\Isp\ServiceNode;
use App\Services\Isp\RouterService;
use Illuminate\Http\Request;

class RouterController extends BaseApiController
{
    protected RouterService $routerService;

    public function __construct(RouterService $routerService)
    {
        $this->routerService = $routerService;
    }

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = ServiceNode::where('type', 'Router');

        if ($request->has('search')) {
            $searchValue = $request->input('search');
            $search = is_string($searchValue) ? $searchValue : '';
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('ip_address', 'like', "%{$search}%");
        }

        $perPage = $request->input('per_page', 15);
        $perPageInt = is_numeric($perPage) ? (int) $perPage : 15;

        /** @var \Illuminate\Pagination\LengthAwarePaginator<int, ServiceNode> $paginator */
        $paginator = $query->paginate($perPageInt);

        $paginator->getCollection()->transform(function ($router) {
            /** @var bool $isConnected */
            $isConnected = $this->routerService->checkConnectivity($router);

            if ($isConnected && $router->connection_method === 'api') {
                $router->last_active_count = $this->routerService->getActiveClientCount($router);
                $router->save(); // Update cache/last known count
            }

            // Return transformed array to include connection status
            $data = $router->toArray();
            $data['is_connected'] = $isConnected;

            return $data;
        });

        return $this->success($paginator);
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|string|max:255',
            'secret' => 'nullable|string|max:255',
            'connection_type' => 'required|in:IP PUBLIC,VPN RADIUS',
            'management_port' => 'nullable|integer',
            'connection_method' => 'sometimes|required|in:none,snmp,api',
            'api_username' => 'nullable|string|max:255',
            'api_password' => 'nullable|string|max:255',
            'api_port' => 'nullable|integer',
            'snmp_community' => 'nullable|string|max:255',
            'snmp_port' => 'nullable|integer',
            'status' => 'nullable|string',
        ]);

        $validated['type'] = 'Router';
        $router = ServiceNode::create($validated);

        return $this->success($router, 'Router created successfully');
    }

    public function update(Request $request, ServiceNode $router): \Illuminate\Http\JsonResponse
    {
        if ($router->type !== 'Router') {
            return $this->error('Not a router', 400);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'ip_address' => 'sometimes|required|string|max:255',
            'secret' => 'nullable|string|max:255',
            'connection_type' => 'sometimes|required|in:IP PUBLIC,VPN RADIUS',
            'management_port' => 'nullable|integer',
            'connection_method' => 'sometimes|required|in:none,snmp,api',
            'api_username' => 'nullable|string|max:255',
            'api_password' => 'nullable|string|max:255',
            'api_port' => 'nullable|integer',
            'snmp_community' => 'nullable|string|max:255',
            'snmp_port' => 'nullable|integer',
            'status' => 'sometimes|required|string',
        ]);

        $router->update($validated);

        return $this->success($router, 'Router updated successfully');
    }

    public function destroy(ServiceNode $router): \Illuminate\Http\JsonResponse
    {
        if ($router->type !== 'Router') {
            return $this->error('Not a router', 400);
        }

        $router->delete();

        return $this->success(null, 'Router deleted successfully');
    }
}
