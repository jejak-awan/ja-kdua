<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Network;

use App\Http\Controllers\Api\Core\BaseApiController;
use App\Models\Isp\Network\Odp;
use App\Models\Isp\Network\OltCommandLog;
use App\Models\Isp\Network\ServiceNode;
use App\Services\Isp\Network\InfraNodeService;
use App\Services\Isp\Network\IpamService;
use App\Services\Isp\Network\OltService;
use App\Services\Isp\Network\RouterService;
use App\Services\Isp\Network\ZtpDiscoveryService;
use App\Services\Isp\Core\ISPValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class InfrastructureController extends BaseApiController
{
    use Concerns\ManagesRouters;
    use Concerns\ManagesOlts;
    use Concerns\ManagesOdps;

    protected InfraNodeService $infraNodeService;

    protected RouterService $routerService;

    protected IpamService $ipamService;

    protected OltService $oltService;

    protected ZtpDiscoveryService $ztpService;

    protected ISPValidationService $validator;

    public function __construct(
        InfraNodeService $infraNodeService,
        RouterService $routerService,
        IpamService $ipamService,
        OltService $oltService,
        ZtpDiscoveryService $ztpService,
        ISPValidationService $validator
    ) {
        $this->infraNodeService = $infraNodeService;
        $this->routerService = $routerService;
        $this->ipamService = $ipamService;
        $this->oltService = $oltService;
        $this->ztpService = $ztpService;
        $this->validator = $validator;
    }

    // --- Nodes (General) ---

    public function index(Request $request): JsonResponse
    {
        $query = ServiceNode::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $searchTerm = is_string($search) ? $search : '';
            $query->where('name', 'like', '%'.$searchTerm.'%')
                ->orWhere('ip_address', 'like', '%'.$searchTerm.'%');
        }

        if ($request->has('type')) {
            $type = $request->input('type');
            if (is_string($type)) {
                $query->where('type', $type);
            }
        }

        if ($request->has('status')) {
            $status = $request->input('status');
            if (is_string($status)) {
                $query->where('status', $status);
            }
        }

        $perPage = $request->input('per_page');
        $perPageInt = is_numeric($perPage) ? (int) $perPage : 15;
        $nodes = $query->latest()->paginate($perPageInt);

        return $this->success($nodes, 'Infrastructure nodes retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:isp_service_nodes,name',
            'type' => 'required|string|in:OLT,POP,Router',
            'ip_address' => 'nullable|string|max:255|unique:isp_service_nodes,ip_address,NULL,id,deleted_at,NULL',
            'location_lat' => 'nullable|numeric',
            'location_lng' => 'nullable|numeric',
            'status' => 'required|string|in:active,inactive,maintenance',
            'description' => 'nullable|string|max:255',
            'connection_type' => 'nullable|string|in:IP PUBLIC,VPN RADIUS',
            'management_port' => 'nullable|integer',
            'connection_method' => 'nullable|string|in:none,snmp,api',
            'api_username' => 'nullable|string|max:255',
            'api_password' => 'nullable|string|max:255',
            'api_port' => 'nullable|integer',
            'snmp_community' => 'nullable|string|max:255',
            'snmp_port' => 'nullable|integer',
            'radius_enabled' => 'nullable|boolean',
            'radius_secret' => 'nullable|string|max:255',
            'is_vpn_server' => 'nullable|boolean',
            'sub_type' => 'nullable|string|max:255',
        ]);

        try {
            $node = $this->infraNodeService->createNode($validated);

            return $this->success($node, 'Infrastructure node created successfully', 201);
        } catch (\Exception $e) {
            Log::error('Infrastructure Creation Failed: '.$e->getMessage());

            return $this->error('Failed to create infra node: '.$e->getMessage(), 500);
        }
    }

    public function show(ServiceNode $infra): JsonResponse
    {
        return $this->success($infra, 'Infrastructure node retrieved successfully');
    }

    public function update(Request $request, ServiceNode $infra): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:isp_service_nodes,name,'.$infra->id,
            'type' => 'sometimes|string|in:OLT,POP,Router',
            'ip_address' => 'sometimes|nullable|string|max:255|unique:isp_service_nodes,ip_address,'.$infra->id.',id,deleted_at,NULL',
            'location_lat' => 'nullable|numeric',
            'location_lng' => 'nullable|numeric',
            'status' => 'sometimes|string|in:active,inactive,maintenance',
            'description' => 'nullable|string|max:255',
            'connection_type' => 'nullable|string|in:IP PUBLIC,VPN RADIUS',
            'management_port' => 'nullable|integer',
            'connection_method' => 'nullable|string|in:none,snmp,api',
            'api_username' => 'nullable|string|max:255',
            'api_password' => 'nullable|string|max:255',
            'api_port' => 'nullable|integer',
            'snmp_community' => 'nullable|string|max:255',
            'snmp_port' => 'nullable|integer',
            'radius_enabled' => 'nullable|boolean',
            'radius_secret' => 'nullable|string|max:255',
            'is_vpn_server' => 'nullable|boolean',
            'sub_type' => 'nullable|string|max:255',
        ]);

        try {
            $infra = $this->infraNodeService->updateNode($infra, $validated);

            return $this->success($infra, 'Infrastructure node updated successfully');
        } catch (\Exception $e) {
            Log::error('Infrastructure Update Failed: '.$e->getMessage());

            return $this->error('Failed to update infra node: '.$e->getMessage(), 500);
        }
    }

    public function destroy(ServiceNode $infra): JsonResponse
    {
        try {
            $this->infraNodeService->deleteNode($infra);

            return $this->success(null, 'Infrastructure node deleted successfully');
        } catch (\Exception $e) {
            Log::error('Infrastructure Deletion Failed: '.$e->getMessage());

            return $this->error('Failed to delete infra node: '.$e->getMessage(), 500);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $node = ServiceNode::withTrashed()->findOrFail($id);
            $node->restore();

            return $this->success($node, 'Infrastructure node restored successfully');
        } catch (\Exception $e) {
            Log::error('Infrastructure Restore Failed: '.$e->getMessage());

            return $this->error('Failed to restore infra node: '.$e->getMessage(), 500);
        }
    }

    public function bulkStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:isp_service_nodes,id',
            'status' => 'required|string|in:active,inactive,maintenance',
        ]);

        try {
            $nodes = ServiceNode::whereIn('id', $validated['ids'])->get();
            foreach ($nodes as $node) {
                $this->infraNodeService->updateNode($node, ['status' => $validated['status']]);
            }

            return $this->success(null, 'Bulk status update successful');
        } catch (\Exception $e) {
            Log::error('Bulk Status Update Failed: '.$e->getMessage());

            return $this->error('Failed to update status for some nodes: '.$e->getMessage(), 500);
        }
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:isp_service_nodes,id',
        ]);

        try {
            $nodes = ServiceNode::whereIn('id', $validated['ids'])->get();
            foreach ($nodes as $node) {
                $this->infraNodeService->deleteNode($node);
            }

            return $this->success(null, 'Bulk deletion successful');
        } catch (\Exception $e) {
            Log::error('Bulk Deletion Failed: '.$e->getMessage());

            return $this->error('Failed to delete some nodes: '.$e->getMessage(), 500);
        }
    }

    public function bulkForceDestroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:isp_service_nodes,id',
        ]);

        try {
            $nodes = ServiceNode::withTrashed()->whereIn('id', $validated['ids'])->get();
            foreach ($nodes as $node) {
                if ($node->type === 'Router' && $node->radius_enabled) {
                    $this->radiusSync->removeNode($node);
                }
                $node->forceDelete();
            }

            return $this->success(null, 'Bulk permanent deletion successful');
        } catch (\Exception $e) {
            Log::error('Bulk Force Deletion Failed: '.$e->getMessage());

            return $this->error('Failed to permanently delete some nodes: '.$e->getMessage(), 500);
        }
    }

    public function bulkRestore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:isp_service_nodes,id',
        ]);

        try {
            ServiceNode::withTrashed()->whereIn('id', $validated['ids'])->restore();

            return $this->success(null, 'Bulk restoration successful');
        } catch (\Exception $e) {
            Log::error('Bulk Restoration Failed: '.$e->getMessage());

            return $this->error('Failed to restore some nodes: '.$e->getMessage(), 500);
        }
    }

    public function stats(): JsonResponse
    {
        $stats = [
            'total' => ServiceNode::count(),
            'active' => ServiceNode::where('status', 'active')->count(),
            'maintenance' => ServiceNode::where('status', 'maintenance')->count(),
            'inactive' => ServiceNode::where('status', 'inactive')->count(),
            'by_type' => [
                'OLT' => ServiceNode::where('type', 'OLT')->count(),
                'POP' => ServiceNode::where('type', 'POP')->count(),
                'Router' => ServiceNode::where('type', 'Router')->count(),
            ],
        ];

        return $this->success($stats, 'Infrastructure statistics retrieved successfully');
    }
}
