<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Network;

use App\Http\Controllers\Api\Core\BaseApiController;
use App\Models\Isp\IpPool;
use App\Models\Isp\IpPoolAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IpPoolController extends BaseApiController
{
    /**
     * List all IP pools with usage stats.
     */
    public function index(): JsonResponse
    {
        $pools = IpPool::with('router')
            ->withCount([
                'addresses',
                'addresses as available_count' => fn ($q) => $q->where('status', 'available'),
                'addresses as assigned_count' => fn ($q) => $q->where('status', 'assigned'),
            ])
            ->orderBy('name')
            ->get();

        return $this->success($pools, 'IP Pools retrieved');
    }

    /**
     * Create a new IP pool.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:ip_pools,name',
            'network' => 'required|string|max:255',
            'gateway' => 'nullable|ip',
            'dns_primary' => 'nullable|ip',
            'dns_secondary' => 'nullable|ip',
            'vlan_id' => 'nullable|integer|min:1|max:4094',
            'router_id' => 'nullable|exists:service_nodes,id',
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'description' => 'nullable|string|max:1000',
            'generate_addresses' => 'nullable|boolean',
        ]);

        $pool = IpPool::create($validated);

        // Optionally generate addresses from CIDR
        if ($request->boolean('generate_addresses', true)) {
            $count = $pool->generateAddressesFromCidr();
        }

        $pool->loadCount('addresses');

        return $this->success($pool, 'IP Pool created with '.($count ?? 0).' addresses', 201);
    }

    /**
     * Get a single IP pool with addresses.
     */
    public function show(IpPool $ipPool): JsonResponse
    {
        $ipPool->load(['router', 'addresses.customer']);

        return $this->success([
            'pool' => $ipPool,
            'stats' => $ipPool->getUsageStats(),
        ], 'IP Pool retrieved');
    }

    /**
     * Update an IP pool.
     */
    public function update(Request $request, IpPool $ipPool): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('ip_pools', 'name')->ignore($ipPool->id)],
            'gateway' => 'nullable|ip',
            'dns_primary' => 'nullable|ip',
            'dns_secondary' => 'nullable|ip',
            'vlan_id' => 'nullable|integer|min:1|max:4094',
            'router_id' => 'nullable|exists:service_nodes,id',
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'description' => 'nullable|string|max:1000',
        ]);

        $ipPool->update($validated);

        return $this->success($ipPool, 'IP Pool updated');
    }

    /**
     * Delete an IP pool.
     */
    public function destroy(IpPool $ipPool): JsonResponse
    {
        // Check if any addresses are assigned
        $assignedCount = $ipPool->addresses()->where('status', 'assigned')->count();
        if ($assignedCount > 0) {
            return $this->error("Cannot delete pool with {$assignedCount} assigned addresses", 422);
        }

        $ipPool->delete();

        return $this->success(null, 'IP Pool deleted');
    }

    /**
     * Get addresses for a pool.
     */
    public function addresses(Request $request, IpPool $ipPool): JsonResponse
    {
        $query = $ipPool->addresses()->with('customer');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $addresses = $query->orderByRaw('INET_ATON(ip_address)')->paginate(50);

        return $this->success($addresses, 'Addresses retrieved');
    }

    /**
     * Update an address status.
     */
    public function updateAddress(Request $request, IpPoolAddress $address): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['available', 'reserved'])],
            'notes' => 'nullable|string|max:500',
        ]);

        // Cannot change status of assigned addresses
        if ($address->status === 'assigned') {
            return $this->error('Cannot modify assigned address. Release it first.', 422);
        }

        $address->update($validated);

        return $this->success($address, 'Address updated');
    }

    /**
     * Regenerate addresses for a pool.
     */
    public function regenerateAddresses(IpPool $ipPool): JsonResponse
    {
        // Only delete available addresses
        $ipPool->addresses()->where('status', 'available')->delete();

        $count = $ipPool->generateAddressesFromCidr();

        return $this->success(['generated' => $count], "Regenerated {$count} addresses");
    }
}
