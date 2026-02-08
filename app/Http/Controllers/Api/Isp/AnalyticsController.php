<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\Isp\Customer;
use App\Models\Isp\Invoice;
use App\Models\Isp\ServiceNode;
use App\Services\Isp\MikrotikService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends BaseApiController
{
    protected MikrotikService $mikrotik;

    public function __construct(MikrotikService $mikrotik)
    {
        $this->mikrotik = $mikrotik;
    }

    /**
     * Get aggregated network stats for the admin dashboard.
     */
    public function index(): JsonResponse
    {
        $stats = $this->mikrotik->getGlobalStats();

        $nodeCount = ServiceNode::count();
        $onlineNodes = ServiceNode::where('status', 'Active')->count();
        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('status', 'Active')->count();

        return $this->success([
            'network' => $stats,
            'nodes' => [
                'total' => $nodeCount,
                'online' => $onlineNodes,
                'offline' => $nodeCount - $onlineNodes,
            ],
            'customers' => [
                'total' => $totalCustomers,
                'active' => $activeCustomers,
            ],
            'timestamp' => now()->toIso8601String(),
        ], 'Network analytics retrieved successfully');
    }

    /**
     * Get network-wide usage trends.
     */
    public function usage(): JsonResponse
    {
        // Aggregated from MikrotikService (simulated for now)
        $historyResult = $this->mikrotik->getTrafficHistory();
        $history = $historyResult['data'];

        return $this->success([
            'history' => $history,
            'peak_in' => collect($history)->max('in'),
            'peak_out' => collect($history)->max('out'),
            'is_simulated' => $historyResult['is_simulated'],
        ], 'Usage trends retrieved successfully');
    }

    /**
     * Get revenue and billing analytics.
     */
    public function revenue(): JsonResponse
    {
        $monthlyRevenue = Invoice::select(
            DB::raw("DATE_FORMAT(due_date, '%Y-%m') as month"),
            DB::raw('SUM(amount) as total'),
            DB::raw("SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as collected"),
            DB::raw("SUM(CASE WHEN status != 'paid' THEN amount ELSE 0 END) as outstanding")
        )
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get();

        return $this->success([
            'monthly' => $monthlyRevenue,
            'total_outstanding' => Invoice::where('status', 'unpaid')->sum('amount'),
            'collection_rate' => $this->calculateCollectionRate(),
        ], 'Revenue analytics retrieved successfully');
    }

    /**
     * Get customers with highest bandwidth consumption.
     * NOTE: Returns simulated data - requires accounting table for real usage.
     */
    public function topTalkers(): JsonResponse
    {
        // TODO: Implement real usage tracking via RADIUS accounting or traffic table
        $customers = Customer::with('user')
            ->limit(10)
            ->get()
            ->map(function (Customer $customer): array {
                return [
                    'id' => $customer->id,
                    'name' => $customer->user->name,
                    'usage_gb' => rand(50, 500), // Simulated - no accounting data available
                    'status' => $customer->status,
                ];
            })
            ->sortByDesc('usage_gb')
            ->values();

        return $this->success([
            'data' => $customers,
            'is_simulated' => true,
            'notice' => 'Usage data is simulated. Real accounting infrastructure required.',
        ], 'Top talkers retrieved successfully');
    }

    private function calculateCollectionRate(): float
    {
        $total = (float) Invoice::sum('amount');
        if ($total <= 0) {
            return 100.0;
        }

        $paid = (float) Invoice::where('status', 'paid')->sum('amount');

        return round(($paid / $total) * 100, 2);
    }
}
