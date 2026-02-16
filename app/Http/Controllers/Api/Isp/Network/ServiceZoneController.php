<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Network;

use App\Http\Controllers\Api\Core\BaseApiController;
use App\Models\Isp\Network\ServiceZone;
use Illuminate\Http\Request;

class ServiceZoneController extends BaseApiController
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        return $this->success(ServiceZone::all());
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $name = $request->input('name');
        $request->merge([
            'name' => is_string($name) ? trim($name) : '',
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:isp_service_zones,name',
            'ip_address' => 'nullable|string|max:255',
            'role' => 'nullable|in:main,backup',
            'is_active' => 'nullable|boolean',
            'status' => 'nullable|string',
        ]);

        $service_zone = ServiceZone::create($validated);

        return $this->success($service_zone, 'Service Zone created successfully');
    }

    public function update(Request $request, ServiceZone $service_zone): \Illuminate\Http\JsonResponse
    {
        if ($request->has('name')) {
            $name = $request->input('name');
            $request->merge([
                'name' => is_string($name) ? trim($name) : '',
            ]);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:isp_service_zones,name,'.$service_zone->id,
            'ip_address' => 'nullable|string|max:255',
            'role' => 'sometimes|nullable|in:main,backup',
            'is_active' => 'sometimes|boolean',
            'status' => 'sometimes|required|string',
        ]);

        $service_zone->update($validated);

        return $this->success($service_zone, 'Service Zone updated successfully');
    }

    public function destroy(ServiceZone $service_zone): \Illuminate\Http\JsonResponse
    {
        $service_zone->delete();

        return $this->success(null, 'Service Zone deleted successfully');
    }

    public function toggleActive(Request $request, ServiceZone $service_zone): \Illuminate\Http\JsonResponse
    {
        $isActive = $request->input('is_active', ! $service_zone->is_active);

        // If we are activating this one, we should probably deactivate others
        // IF the requirement is "only 1 active at a time".
        if ($isActive) {
            ServiceZone::where('id', '!=', $service_zone->id)->update(['is_active' => false]);
        }

        $service_zone->update(['is_active' => $isActive]);

        return $this->success($service_zone, 'Service Zone active status toggled');
    }
}
