<?php

declare(strict_types=1);

namespace App\Services\Isp\Operations;

use App\Models\Core\User;
use App\Models\Isp\Support\ServiceRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class DispatchService
{
    /**
     * Get technicians ranked by proximity to a service request.
     *
     * @return Collection<int, User>
     */
    public function getRecommendedTechnicians(ServiceRequest $request, int $limit = 5): Collection
    {
        $customer = $request->customer;
        if (!$customer || !$customer->latitude || !$customer->longitude) {
            return User::role('technician')->limit($limit)->get();
        }

        $lat = (float) $customer->latitude;
        $lng = (float) $customer->longitude;

        return User::role('technician')
            ->whereNotNull('current_lat')
            ->whereNotNull('current_lng')
            ->get()
            ->map(function ($tech) use ($lat, $lng) {
                $tech->distance_km = $this->calculateDistance(
                    $lat,
                    $lng,
                    (float) $tech->current_lat,
                    (float) $tech->current_lng
                );
                return $tech;
            })
            ->sortBy('distance_km')
            ->take($limit)
            ->values();
    }

    /**
     * Calculate Haversine distance between two points.
     */
    public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // Kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Auto-assign a service request to the closest technician.
     */
    public function autoAssign(ServiceRequest $request): bool
    {
        $technicians = $this->getRecommendedTechnicians($request, 1);
        if ($technicians->isEmpty()) {
            return false;
        }

        $tech = $technicians->first();
        
        Log::info("Dispatch: Auto-assigning SR #{$request->id} to technician {$tech->name} (Distance: {$tech->distance_km} km)");

        return (bool) \App\Models\Isp\Operations\TechnicianDeployment::create([
            'technician_id' => $tech->id,
            'customer_id' => $request->customer_id,
            'service_request_id' => $request->id,
            'type' => $request->type,
            'status' => 'assigned',
            'scheduled_at' => now(),
            'notes' => 'Auto-assigned by distance-aware dispatch system.',
        ]);
    }
}
