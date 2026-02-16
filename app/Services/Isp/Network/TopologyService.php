<?php

declare(strict_types=1);

namespace App\Services\Isp\Network;

use App\Models\Isp\Customer\Customer;
use App\Models\Isp\Network\Odp;
use App\Models\Isp\Network\ServiceNode;
use Illuminate\Support\Collection;

class TopologyService
{
    /**
     * Get the full hierarchy for a specific customer.
     *
     * @return array{olt: ServiceNode|null, odp: Odp|null, customer: Customer}
     */
    public function getCustomerHierarchy(Customer $customer): array
    {
        return [
            'olt' => $customer->olt_id ? ServiceNode::find($customer->olt_id) : null,
            'odp' => $customer->odp_id ? Odp::with('olt')->find($customer->odp_id) : null,
            'customer' => $customer,
        ];
    }

    /**
     * Get all map topology markers and links.
     *
     * @return array{nodes: Collection, odps: Collection, links: array}
     */
    /**
     * Get data for coverage heatmap (density of active customers or revenue).
     *
     * @return array<int, array{lat: float, lng: float, weight: float}>
     */
    public function getCoverageHeatmap(string $mode = 'density'): array
    {
        if ($mode === 'potential') {
            return Odp::whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get()
                ->map(function ($odp) {
                    /** @var Odp $odp */
                    $available = $odp->available_ports;
                    
                    return [
                        'lat' => (float) $odp->latitude,
                        'lng' => (float) $odp->longitude,
                        'weight' => $available > 0 ? min($available / 4, 5.0) : 0.1,
                    ];
                })
                ->toArray();
        }

        $query = Customer::where('status', 'active')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        if ($mode === 'revenue') {
            $query->with('plan:id,price');
        }

        return $query->get()
            ->map(function ($customer) use ($mode) {
                $weight = 1.0;
                if ($mode === 'revenue') {
                    // Refined normalization: Avg base is 200k-300k.
                    $price = (float) ($customer->plan->price ?? 0);
                    $weight = $price > 0 ? min($price / 150000, 5.0) : 0.2;
                }

                return [
                    'lat' => (float) $customer->latitude,
                    'lng' => (float) $customer->longitude,
                    'weight' => $weight,
                ];
            })
            ->toArray();
    }

    public function getFullTopology(): array
    {
        $nodes = ServiceNode::whereIn('type', ['OLT', 'POP', 'Router'])->get();
        $odps = Odp::with('olt')->get();
        
        $links = [];

        // Link OLT to ODP
        foreach ($odps as $odp) {
            if ($odp->olt_id && $odp->latitude && $odp->longitude) {
                $olt = $nodes->where('id', $odp->olt_id)->first();
                if ($olt && $olt->location_lat && $olt->location_lng) {
                    $links[] = [
                        'from' => ['lat' => $olt->location_lat, 'lng' => $olt->location_lng, 'type' => 'OLT', 'id' => $olt->id],
                        'to' => ['lat' => $odp->latitude, 'lng' => $odp->longitude, 'type' => 'ODP', 'id' => $odp->id],
                        'status' => $odp->status,
                    ];
                }
            }
        }

        return [
            'nodes' => $nodes,
            'odps' => $odps,
            'links' => $links,
        ];
    }
}
