<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\Isp\Customer;
use App\Models\Isp\Invoice;
use App\Models\Isp\ServiceNode;
use App\Services\Isp\MikrotikService;
use App\Services\Isp\RadiusIntegration;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends BaseApiController
{
    protected MikrotikService $mikrotik;
    protected RadiusIntegration $radius;

    public function __construct(MikrotikService $mikrotik, RadiusIntegration $radius)
    {
        $this->mikrotik = $mikrotik;
        $this->radius = $radius;
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
        $history = $this->radius->getGlobalUsageDaily();

        return $this->success([
            'history' => $history,
            'peak_in' => collect($history)->max('up'),
            'peak_out' => collect($history)->max('down'),
            'is_simulated' => false,
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
        $topTalkers = $this->radius->getTopTalkers(10);
        
        // Enrich with customer names
        $enriched = collect($topTalkers)->map(function ($row) {
            $customer = Customer::with('user')->where('mikrotik_login', $row['username'])->first();
            return [
                'username' => $row['username'],
                'name' => $customer ? $customer->user->name : $row['username'],
                'usage_gb' => $row['total_gb'],
                'status' => $customer ? $customer->status : 'Inactive',
            ];
        });

        return $this->success([
            'data' => $enriched,
            'is_simulated' => false,
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
