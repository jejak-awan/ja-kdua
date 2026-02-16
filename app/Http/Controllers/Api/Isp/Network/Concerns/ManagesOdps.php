<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Network\Concerns;

use App\Models\Isp\Network\Odp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @property \App\Services\Isp\Network\InfraNodeService $infraNodeService
 * @property \App\Services\Isp\Core\ISPValidationService $validator
 * @mixin \App\Http\Controllers\Api\Isp\Network\InfrastructureController
 */
trait ManagesOdps
{
    public function odpIndex(Request $request): JsonResponse
    {
        $query = Odp::with('olt')->withCount('customers');
        if ($request->has('olt_id')) {
            $query->where('olt_id', $request->olt_id);
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $searchTerm = is_string($search) ? $search : '';
            $query->where('name', 'like', "%{$searchTerm}%")
                ->orWhere('location_address', 'like', "%{$searchTerm}%");
        }

        return $this->success($query->latest()->get(), 'ODPs retrieved successfully');
    }

    public function odpStore(Request $request): JsonResponse
    {
        /** @var array<string, mixed> $validated */
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'olt_id' => 'required|exists:isp_service_nodes,id',
            'total_ports' => 'integer|min:1',
            'location_address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'status' => 'required|in:Active,Full,Maintenance,Inactive',
            'description' => 'nullable|string',
        ]);

        if (isset($validated['total_ports'])) {
            $this->validator->validateOdpPort((int) $validated['total_ports']);
        }

        $odp = Odp::create($validated);

        return $this->success($odp, 'ODP created successfully', 201);
    }

    public function odpShow(Odp $odp): JsonResponse
    {
        return $this->success($odp->load(['olt', 'customers.user']), 'ODP retrieved successfully');
    }

    public function odpUpdate(Request $request, Odp $odp): JsonResponse
    {
        /** @var array<string, mixed> $validated */
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'olt_id' => 'sometimes|required|exists:isp_service_nodes,id',
            'total_ports' => 'sometimes|integer|min:1',
            'location_address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'status' => 'sometimes|required|in:Active,Full,Maintenance,Inactive',
            'description' => 'nullable|string',
        ]);

        if (isset($validated['total_ports'])) {
            $this->validator->validateOdpPort((int) $validated['total_ports']);
        }

        $odp->update($validated);

        return $this->success($odp, 'ODP updated successfully');
    }

    public function odpDestroy(Odp $odp): JsonResponse
    {
        $odp->delete();

        return $this->success(null, 'ODP deleted successfully');
    }
}
