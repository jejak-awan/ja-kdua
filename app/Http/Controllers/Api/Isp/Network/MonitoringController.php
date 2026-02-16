<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Network;

use App\Http\Controllers\Api\Core\BaseApiController;
use App\Models\Isp\Customer\Customer;
use App\Models\Isp\Billing\Invoice;
use App\Models\Isp\Network\OltSignal;
use App\Models\Isp\Network\ServiceNode;
use App\Services\Isp\Billing\BillingAnalyticsService;
use App\Services\Isp\Network\MikrotikService;
use App\Services\Isp\Network\RouterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

class MonitoringController extends BaseApiController
{
    protected MikrotikService $mikrotik;

    protected \App\Services\Isp\Network\RadiusService $radius;

    protected BillingAnalyticsService $analytics;

    protected RouterService $routerService;

    public function __construct(
        MikrotikService $mikrotik,
        \App\Services\Isp\Network\RadiusService $radius,
        BillingAnalyticsService $analytics,
        RouterService $routerService
    ) {
        $this->mikrotik = $mikrotik;
        $this->radius = $radius;
        $this->analytics = $analytics;
        $this->routerService = $routerService;
    }

    // --- Network & Dashboard ---

    /**
     * Get consolidated network stats for dashboard.
     */
    public function dashboard(): JsonResponse
    {
        $global = $this->mikrotik->getGlobalStats();
        $historyResult = $this->mikrotik->getTrafficHistory();

        $nodeCount = ServiceNode::count();
        $onlineNodes = ServiceNode::where('status', 'active')->count();
        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('status', 'active')->count();

        // Get stats for major nodes
        $nodes = ServiceNode::where('status', 'active')->limit(5)->get();
        $nodeStats = $nodes->map(function ($node) {
            return $this->mikrotik->getNodeStats($node);
        });

        return $this->success([
            'network' => $global,
            'nodes' => [
                'total' => $nodeCount,
                'online' => $onlineNodes,
                'offline' => $nodeCount - $onlineNodes,
                'details' => $nodeStats,
            ],
            'customers' => [
                'total' => $totalCustomers,
                'active' => $activeCustomers,
            ],
            'traffic_history' => $historyResult['data'],
            'is_simulated' => $historyResult['is_simulated'],
            'timestamp' => now()->toIso8601String(),
        ], 'Dashboard statistics retrieved successfully');
    }

    /**
     * Get real-time traffic for an interface.
     */
    public function liveTraffic(Request $request): JsonResponse
    {
        $interface = $request->input('interface');
        $interfaceStr = is_string($interface) ? $interface : 'ether1-gateway';
        
        $routerId = $request->input('router_id');
        
        if ($routerId) {
            /** @var \App\Models\Isp\Network\ServiceNode $router */
            $router = ServiceNode::findOrFail($routerId);
            $data = $this->routerService->getInterfaceTraffic($router, $interfaceStr);
        } else {
            // Deprecated: fallback to old method (default router via env or mock)
            $data = $this->mikrotik->getInterfaceTraffic($interfaceStr);
        }

        if ($data === null) {
            return $this->error('Failed to fetch traffic data', 500, [
                'rx' => 0,
                'tx' => 0,
            ]);
        }

        return $this->success($data, 'Live traffic data retrieved');
    }

    /**
     * Get interfaces for a specific router.
     */
    public function interfaces(Request $request): JsonResponse
    {
        $routerId = $request->input('router_id');
        if (!$routerId) {
            return $this->error('Router ID required', 400);
        }

        /** @var \App\Models\Isp\Network\ServiceNode $router */
        $router = ServiceNode::findOrFail($routerId);
        $interfaces = $this->routerService->getInterfaces($router);

        return $this->success($interfaces, 'Interfaces retrieved successfully');
    }

    /**
     * Get network-wide usage trends from RADIUS.
     */
    public function usageTrends(): JsonResponse
    {
        $history = $this->radius->getGlobalUsageDaily();

        return $this->success([
            'history' => $history,
            'peak_in' => collect($history)->max('up'),
            'peak_out' => collect($history)->max('down'),
        ], 'Usage trends retrieved successfully');
    }

    // --- Router Sessions (Mikrotik) ---

    public function routerSessions(Request $request): JsonResponse
    {
        $routerId = $request->input('router_id');
        if (! $routerId) {
            return $this->error('Router ID required', 400);
        }

        /** @var \App\Models\Isp\Network\ServiceNode $router */
        $router = ServiceNode::findOrFail($routerId);
        $sessions = $this->routerService->getActiveSessions($router);

        return $this->success($sessions, 'Active router sessions retrieved successfully');
    }

    public function disconnectRouterSession(Request $request): JsonResponse
    {
        $request->validate([
            'router_id' => 'required|integer',
            'type' => 'required|in:pppoe,hotspot',
            'id' => 'required|string',
        ]);

        /** @var \App\Models\Isp\Network\ServiceNode $router */
        $router = ServiceNode::findOrFail($request->input('router_id'));
        /** @var string $type */
        $type = $request->input('type');
        /** @var string $id */
        $id = $request->input('id');

        if ($this->routerService->disconnectSession($router, $type, $id)) {
            return $this->success(null, 'Session disconnected successfully');
        }

        return $this->error('Failed to disconnect session', 500);
    }

    // --- RADIUS Monitoring ---

    public function radiusSessions(Request $request): JsonResponse
    {
        $limit = $request->integer('limit', 50);

        return $this->success($this->radius->getActiveSessions($limit), 'Active RADIUS sessions retrieved');
    }

    public function radiusLogs(Request $request): JsonResponse
    {
        $limit = $request->integer('limit', 50);

        return $this->success($this->radius->getAuthLogs($limit), 'Authentication logs retrieved');
    }

    public function radiusStatus(): JsonResponse
    {
        return $this->success($this->radius->getRadiusStatus(), 'RADIUS status retrieved');
    }

    public function radiusDisconnect(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'nas_ip' => 'required|string',
        ]);

        /** @var string $user */
        $user = $request->input('username');
        /** @var string $nas */
        $nas = $request->input('nas_ip');

        if ($this->radius->rawDisconnect($user, $nas)) {
            return $this->success(null, 'Disconnect request sent successfully (CoA)');
        }

        return $this->error('Failed to send disconnect request', 500);
    }

    // --- OLT Signal Monitoring ---

    public function oltSignalHistory(Request $request): JsonResponse
    {
        $request->validate([
            'olt_id' => 'required_without:onu_serial|exists:isp_service_nodes,id',
            'onu_serial' => 'nullable|string',
            'limit' => 'nullable|integer|max:500',
        ]);

        $query = OltSignal::query();

        if ($request->has('olt_id')) {
            $query->where('olt_id', $request->olt_id);
        }
        if ($request->has('onu_serial')) {
            $query->where('onu_serial', $request->onu_serial);
        }
        if ($request->has('interface')) {
            $query->where('interface', $request->interface);
        }

        $limit = $request->integer('limit', 100);

        return $this->success($query->latest()->limit($limit)->get(), 'Signal history retrieved successfully');
    }

    public function oltSignalStats(ServiceNode $olt): JsonResponse
    {
        $stats = OltSignal::where('olt_id', $olt->id)
            ->where('created_at', '>=', now()->subDay())
            ->selectRaw('
                MIN(rx_power) as min_rx,
                MAX(rx_power) as max_rx,
                AVG(rx_power) as avg_rx,
                COUNT(*) as sample_count
            ')
            ->first();

        return $this->success($stats, 'OLT signal stats retrieved successfully');
    }

    // --- Analytics & Business Intelligence ---

    public function revenue(): JsonResponse
    {
        $monthlyRevenue = Invoice::select(
            DB::raw("to_char(due_date, 'YYYY-MM') as month"),
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

    public function topTalkers(): JsonResponse
    {
        $topTalkers = $this->radius->getTopTalkers(10);
        $enriched = collect($topTalkers)->map(function ($row) {
            $customer = Customer::with('user')->where('mikrotik_login', $row['username'])->first();

            return [
                'username' => $row['username'],
                'name' => $customer ? $customer->user->name : $row['username'],
                'usage_gb' => $row['total_gb'],
                'status' => $customer ? $customer->status : 'Inactive',
            ];
        });

        return $this->success(['data' => $enriched], 'Top talkers retrieved successfully');
    }

    public function businessIntelligence(): JsonResponse
    {
        return $this->success([
            'arpu' => [
                'current' => $this->analytics->getArpu(),
                'by_node' => $this->analytics->getArpuPerNode(),
            ],
            'churn_risk' => $this->analytics->getChurnRiskCustomers(),
            'growth' => $this->analytics->getGrowthTrends(),
        ], 'Business intelligence metrics retrieved successfully');
    }

    /**
     * Get geospatial heatmap data for coverage and revenue.
     */
    public function heatmap(): JsonResponse
    {
        try {
            $coverage = Customer::whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->select('latitude', 'longitude', DB::raw("CASE WHEN status = 'active' THEN 1.0 ELSE 0.5 END as intensity"))
                ->get()
                ->map(fn($c) => [
                    (float)$c->latitude, 
                    (float)$c->longitude, 
                    (float)$c->intensity
                ]);

            $revenue = Customer::whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->join('isp_invoices', 'isp_customers.user_id', '=', 'isp_invoices.user_id')
                ->where('isp_invoices.status', 'paid')
                ->select('latitude', 'longitude', DB::raw('SUM(isp_invoices.amount) as total_revenue'))
                ->groupBy('latitude', 'longitude', 'isp_customers.id')
                ->get()
                ->map(fn($c) => [
                    (float)$c->latitude, 
                    (float)$c->longitude, 
                    (float)$c->total_revenue
                ]);

            return $this->success([
                'coverage' => $coverage,
                'revenue' => $revenue,
            ], 'Heatmap data retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve heatmap data: ' . $e->getMessage(), 500);
        }
    }

    public function downloadHealthReport(Customer $customer): \Symfony\Component\HttpFoundation\Response
    {
        $customer->load('plan');
        $latestSignal = \App\Models\Isp\Network\OltSignal::where('onu_serial', $customer->onu_sn)
            ->latest()
            ->first();

        $mpdf = new Mpdf([
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
        ]);

        $html = view('isp.pdf.health_report', [
            'customer' => $customer,
            'signal' => $latestSignal,
        ])->render();

        $mpdf->WriteHTML($html);

        $filename = "Network_Health_Report_{$customer->id}_" . now()->format('Ymd') . ".pdf";

        return response($mpdf->Output($filename, 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
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
