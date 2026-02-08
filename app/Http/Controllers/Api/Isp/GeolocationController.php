<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp;

use App\Http\Controllers\Controller;
use App\Models\Isp\Customer;
use Illuminate\Http\JsonResponse;

class GeolocationController extends Controller
{
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
}
