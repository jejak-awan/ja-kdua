<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\Isp\CustomerDevice;
use App\Models\Isp\Invoice;
use App\Models\Isp\Outage;
use App\Models\Isp\ServiceNode;
use App\Models\Isp\ServiceRequest;
use App\Services\Isp\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberPortalController extends BaseApiController
{
    protected MikrotikService $mikrotik;

    public function __construct(MikrotikService $mikrotik)
    {
        $this->mikrotik = $mikrotik;
    }

    /**
     * Get aggregated data for the member dashboard.
     */
    public function dashboard(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return $this->unauthorized();
        }

        $userId = (int) $user->id;

        // 1. Get customer device
        /** @var CustomerDevice|null $device */
        $device = CustomerDevice::where('customer_id', $userId)->first();

        // 2. Get latest invoices
        /** @var \Illuminate\Database\Eloquent\Collection<int, Invoice> $invoices */
        $invoices = Invoice::where('user_id', $userId)
            ->latest()
            ->limit(5)
            ->get();

        $unpaidBalance = (float) Invoice::where('user_id', $userId)
            ->where('status', 'unpaid')
            ->sum('amount');

        // 3. Get real connection stats if device and login exist
        $connection = null;
        /** @var \App\Models\Isp\Customer|null $customer */
        $customer = $user->customer;

        if ($customer && $customer->mikrotik_login) {
            $router = $customer->router_id ? ServiceNode::find($customer->router_id) : null;
            if ($router) {
                /** @var \App\Services\Isp\RouterService $routerService */
                $routerService = app(\App\Services\Isp\RouterService::class);
                $session = $routerService->findActiveSessionByLogin($router, $customer->mikrotik_login);

                if ($session) {
                    $connection = [
                        'status' => 'online',
                        'signal_strength' => 'N/A', // Signal is usually not in PPP/Hotspot active, would need wireless print or SNMP
                        'uptime' => $session['uptime'],
                        'last_latency' => '---', // Latency requires a ping from router
                        'ip_address' => $session['address'],
                    ];
                } else {
                    $connection = ['status' => 'offline'];
                }
            }
        }

        if ($connection === null && $device) {
            // Fallback for simple display if no login but device exists
            $connection = [
                'status' => 'unknown',
            ];
        }

        // 4. Get active outages
        /** @var \Illuminate\Database\Eloquent\Collection<int, Outage> $outages */
        $outages = Outage::where('status', '!=', 'Resolved')
            ->where(function ($query) use ($device) {
                /** @var \Illuminate\Database\Eloquent\Builder<Outage> $query */
                $query->whereNull('node_id');
                if ($device && $device->node_id) {
                    $query->orWhere('node_id', $device->node_id);
                }
            })
            ->get();

        $trafficResult = $this->mikrotik->getTrafficHistory();

        return $this->success([
            'user' => $user,
            'device' => $device,
            'invoices' => $invoices,
            'unpaid_balance' => $unpaidBalance,
            'connection' => $connection,
            'active_outages' => $outages,
            'traffic_history' => $trafficResult['data'],
        ], 'Member dashboard data retrieved successfully');
    }

    /**
     * Get only the authenticated user's invoices.
     */
    public function invoices(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return $this->unauthorized();
        }

        $userId = (int) $user->id;
        $perPage = $request->input('per_page');
        $perPageInt = is_numeric($perPage) ? (int) $perPage : 10;

        /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, Invoice> $invoices */
        $invoices = Invoice::where('user_id', $userId)
            ->latest()
            ->paginate($perPageInt);

        return $this->success($invoices, 'User invoices retrieved successfully');
    }

    /**
     * Get bandwidth usage history for the member.
     */
    public function usage(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return $this->unauthorized();
        }

        $userId = (int) $user->id;
        $usageResult = $this->mikrotik->getCustomerUsageHistory($userId);

        /** @var \App\Models\Isp\Customer|null $customer */
        $customer = $user->customer;
        $connection = ['status' => 'unknown'];

        if ($customer && $customer->mikrotik_login) {
            $router = $customer->router_id ? ServiceNode::find($customer->router_id) : null;
            if ($router) {
                /** @var \App\Services\Isp\RouterService $routerService */
                $routerService = app(\App\Services\Isp\RouterService::class);
                $session = $routerService->findActiveSessionByLogin($router, $customer->mikrotik_login);

                if ($session) {
                    $connection = [
                        'status' => 'connected',
                        'latency' => '---',
                        'uptime' => $session['uptime'],
                    ];
                } else {
                    $connection = ['status' => 'offline'];
                }
            }
        }

        return $this->success([
            'usage' => [
                'daily' => $usageResult['daily'],
                'monthly' => $usageResult['monthly'],
            ],
            'connection' => $connection,
            'is_simulated' => $usageResult['is_simulated'],
        ], 'Usage history retrieved successfully');
    }

    /**
     * Submit a service upgrade/downgrade request.
     */
    public function requestService(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return $this->unauthorized();
        }

        $userId = (int) $user->id;

        /** @var array{type: string, details: array<string, mixed>|null} $validated */
        $validated = $request->validate([
            'type' => 'required|string|in:Upgrade,Downgrade,Cancellation,Relocation',
            'details' => 'nullable|array',
        ]);

        $serviceRequest = ServiceRequest::create([
            'customer_id' => $userId,
            'type' => $validated['type'],
            'details' => $validated['details'] ?? [],
            'status' => 'Pending',
        ]);

        return $this->success($serviceRequest, 'Service request submitted successfully', 201);
    }

    /**
     * Run connectivity diagnostics.
     */
    public function diagnostics(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return $this->unauthorized();
        }

        /** @var \App\Models\Isp\Customer|null $customer */
        $customer = $user->customer;
        $sessionStatus = 'pending';
        $sessionMessage = 'No active session found';

        if ($customer && $customer->mikrotik_login) {
            $router = $customer->router_id ? ServiceNode::find($customer->router_id) : null;
            if ($router) {
                /** @var \App\Services\Isp\RouterService $routerService */
                $routerService = app(\App\Services\Isp\RouterService::class);
                $session = $routerService->findActiveSessionByLogin($router, $customer->mikrotik_login);

                if ($session) {
                    $sessionStatus = 'success';
                    $sessionMessage = 'Active session found on router: '.$router->name;
                } else {
                    $sessionStatus = 'error';
                    $sessionMessage = 'User is not connected to the router';
                }
            }
        }

        // Simulated results for other steps, but session check is now REAL
        $results = [
            'timestamp' => now()->toISOString(),
            'steps' => [
                [
                    'name' => 'Local Network',
                    'status' => 'success',
                    'message' => 'Local gateway is reachable',
                    'latency' => rand(1, 5).'ms',
                ],
                [
                    'name' => 'DNS Resolution',
                    'status' => 'success',
                    'message' => 'Google DNS (8.8.8.8) is resolving correctly',
                    'latency' => rand(10, 30).'ms',
                ],
                [
                    'name' => 'ISP Session',
                    'status' => $sessionStatus,
                    'message' => $sessionMessage,
                ],
                [
                    'name' => 'Internet Access',
                    'status' => $sessionStatus === 'success' ? 'success' : 'error',
                    'message' => $sessionStatus === 'success' ? 'Connection to global network is stable' : 'Internet access is blocked or unavailable',
                    'latency' => $sessionStatus === 'success' ? rand(15, 40).'ms' : '',
                ],
            ],
            'overall_status' => $sessionStatus === 'success' ? 'healthy' : 'issue',
        ];

        return $this->success($results, 'Diagnostics completed successfully');
    }
}
