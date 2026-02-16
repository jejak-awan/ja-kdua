<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Network;

use App\Http\Controllers\Controller;
use App\Models\Isp\Customer\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeolocationController extends Controller
{
    protected \App\Services\Isp\Network\TopologyService $topologyService;

    public function __construct(\App\Services\Isp\Network\TopologyService $topologyService)
    {
        $this->topologyService = $topologyService;
    }

    /**
     * Get all customer coordinates and status for the map.
     */
    public function index(): JsonResponse
    {
        $customers = Customer::select([
            'id',
            'latitude',
            'longitude',
            'status',
            'user_id',
        ])
            ->with('user:id,name')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->user->name ?? 'Unknown',
                    'lat' => $customer->latitude,
                    'lng' => $customer->longitude,
                    'status' => $customer->status,
                ];
            });

        return response()->json($customers);
    }

    /**
     * Get full network topology (OLT -> ODP links).
     */
    public function topology(): JsonResponse
    {
        return response()->json($this->topologyService->getFullTopology());
    }
    /**
     * Get coverage heatmap data.
     */
    public function heatmap(Request $request): JsonResponse
    {
        $mode = (string) $request->query('mode', 'density');

        return response()->json($this->topologyService->getCoverageHeatmap($mode));
    }
}
