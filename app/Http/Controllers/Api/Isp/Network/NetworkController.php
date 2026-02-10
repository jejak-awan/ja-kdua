<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Network;

use App\Http\Controllers\Api\Core\BaseApiController;
use App\Models\Isp\IpAddress;
use App\Models\Isp\IspPlan;
use App\Models\Isp\Subnet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NetworkController extends BaseApiController
{
    /**
     * List all subnets with node info
     */
    public function indexSubnets(): \Illuminate\Http\JsonResponse
    {
        $subnets = Subnet::with(['node'])->latest()->get();

        return $this->success($subnets);
    }

    /**
     * Create a new subnet and generate IPs
     */
    public function storeSubnet(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'node_id' => 'required|exists:isp_service_nodes,id',
            'name' => 'required|string',
            'prefix' => 'required|string', // CIDR validation could be added
            'gateway' => 'nullable|string',
            'vlan_id' => 'nullable|integer',
            'type' => 'required|in:WAN,LAN,CGNAT',
        ]);

        return DB::transaction(function () use ($validated) {
            $subnet = Subnet::create($validated);
            $subnet->generateIps();

            return $this->success($subnet, 'Subnet created and IP pool generated');
        });
    }

    /**
     * List IPs for a subnet with pagination and search
     */
    public function indexIps(Request $request, mixed $id): \Illuminate\Http\JsonResponse
    {
        $id = is_numeric($id) ? (int) $id : 0;
        $query = IpAddress::where('subnet_id', $id)->with('device.customer');

        if ($request->has('search')) {
            $search = $request->input('search');
            $searchTerm = is_string($search) ? $search : '';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('address', 'like', '%'.$searchTerm.'%')
                    ->orWhereHas('device', function ($dq) use ($searchTerm) {
                        $dq->where('serial_number', 'like', '%'.$searchTerm.'%')
                            ->orWhereHas('customer', function ($cq) use ($searchTerm) {
                                $cq->where('name', 'like', '%'.$searchTerm.'%');
                            });
                    });
            });
        }

        $perPage = $request->input('per_page');
        $perPageInt = is_numeric($perPage) ? (int) $perPage : 15;
        $ips = $query->paginate($perPageInt);

        return $this->success($ips);
    }

    /**
     * List all service profiles
     */
    public function indexProfiles(): \Illuminate\Http\JsonResponse
    {
        $profiles = IspPlan::latest()->get();

        return $this->success($profiles);
    }

    /**
     * Store or update a service profile
     */
    public function storeProfile(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'id' => 'nullable|exists:isp_service_profiles,id',
            'name' => 'required|string',
            'download_limit' => 'required|integer',
            'upload_limit' => 'required|integer',
            'burst_limit' => 'nullable|string',
        ]);

        $profile = IspPlan::updateOrCreate(['id' => $validated['id'] ?? null], $validated);

        return $this->success($profile, 'Service profile saved');
    }
}
