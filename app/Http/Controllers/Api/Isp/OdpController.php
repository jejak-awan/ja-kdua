<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\Isp\Odp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OdpController extends BaseApiController
{
    /**
     * Display a listing of the ODPs.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Odp::with('olt')->withCount('customers');

        if ($request->has('olt_id')) {
            $query->where('olt_id', $request->olt_id);
        }

        if ($request->has('search')) {
            /** @var string $search */
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('location_address', 'like', "%{$search}%");
        }

        return $this->success($query->latest()->get(), 'ODPs retrieved successfully');
    }

    /**
     * Store a newly created ODP.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'olt_id' => 'required|exists:olts,id',
            'total_ports' => 'integer|min:1',
            'location_address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'status' => 'required|in:Active,Full,Maintenance,Inactive',
            'description' => 'nullable|string',
        ]);

        $odp = Odp::create($validated);

        return $this->success($odp, 'ODP created successfully', 201);
    }

    /**
     * Display the specified ODP.
     */
    public function show(Odp $odp): JsonResponse
    {
        return $this->success($odp->load(['olt', 'customers.user']), 'ODP retrieved successfully');
    }

    /**
     * Update the specified ODP.
     */
    public function update(Request $request, Odp $odp): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'olt_id' => 'sometimes|required|exists:olts,id',
            'total_ports' => 'sometimes|integer|min:1',
            'location_address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'status' => 'sometimes|required|in:Active,Full,Maintenance,Inactive',
            'description' => 'nullable|string',
        ]);

        $odp->update($validated);

        return $this->success($odp, 'ODP updated successfully');
    }

    /**
     * Remove the specified ODP.
     */
    public function destroy(Odp $odp): JsonResponse
    {
        $odp->delete();

        return $this->success(null, 'ODP deleted successfully');
    }
}
