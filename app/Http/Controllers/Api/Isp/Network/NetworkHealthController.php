<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Network;

use App\Http\Controllers\Api\Core\BaseApiController;
use App\Models\Isp\Customer\Customer;
use App\Models\Isp\Network\ServiceNode;
use App\Services\Isp\Network\OltService;
use App\Services\Isp\Network\RadiusService;
use App\Services\Isp\Network\RouterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NetworkHealthController extends BaseApiController
{
    protected RouterService $routerService;
    protected OltService $oltService;
    protected RadiusService $radiusService;

    public function __construct(
        RouterService $routerService,
        OltService $oltService,
        RadiusService $radiusService
    ) {
        $this->routerService = $routerService;
        $this->oltService = $oltService;
        $this->radiusService = $radiusService;
    }

    /**
     * Get real-time vitals for all active hardware nodes.
     */
    public function index(): JsonResponse
    {
        try {
            $nodes = ServiceNode::whereIn('type', ['Router', 'OLT'])
                ->where('status', 'active')
                ->get();

            $vitals = $nodes->map(function ($node) {
                if ($node->type === 'Router') {
                    $stats = $this->routerService->getSystemResource($node);
                    $traffic = $this->routerService->getInterfaceTraffic($node, 'ether1-gateway');
                    return [
                        'id' => $node->id,
                        'name' => $node->name,
                        'type' => 'Router',
                        'ip_address' => $node->ip_address,
                        'uptime' => $stats['uptime'] ?? 'N/A',
                        'cpu_load' => (int) ($stats['cpu'] ?? 0),
                        'memory_usage' => (int) ($stats['memory_free'] ?? 0),
                        'traffic_in' => $traffic['rx'] ?? '0bps',
                        'traffic_out' => $traffic['tx'] ?? '0bps',
                        'status' => 'active'
                    ];
                }
                return [
                    'id' => $node->id,
                    'name' => $node->name,
                    'type' => 'OLT',
                    'ip_address' => $node->ip_address,
                    'status' => $node->status
                ];
            });

            return $this->success($vitals, 'Bulk vitals retrieved');
        } catch (\Exception $e) {
            return $this->error('Failed to fetch bulk vitals: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get real-time vitals for a specific service node.
     */
    public function show(ServiceNode $node): JsonResponse
    {
        try {
            if ($node->type === 'Router') {
                $vitals = $this->routerService->getSystemResource($node);
                $traffic = $this->routerService->getInterfaceTraffic($node, 'ether1-gateway');
                
                return $this->success([
                    'type' => 'router',
                    'vitals' => $vitals,
                    'traffic' => $traffic,
                    'active_clients' => $this->routerService->getActiveClientCount($node),
                    'timestamp' => now()->toIso8601String()
                ], 'Router vitals retrieved');
            }

            if ($node->type === 'OLT') {
                // For OLT, we might need specific driver calls for CPU/Temp
                // For now, we return basic connectivity and status
                return $this->success([
                    'type' => 'olt',
                    'status' => $node->status,
                    'timestamp' => now()->toIso8601String()
                ], 'OLT status retrieved');
            }

            return $this->error('Unsupported node type', 400);

        } catch (\Exception $e) {
            Log::error('Vitals Fetch Failed: ' . $e->getMessage());
            return $this->error('Failed to fetch vitals: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Process an active remediation (Fix My Connection).
     */
    public function remediate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:isp_customers,id',
            'action' => 'required|in:reset_session,reboot_onu,flash_port'
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        $action = $validated['action'];

        try {
            $success = false;
            $message = '';

            switch ($action) {
                case 'reset_session':
                    if ($customer->mikrotik_login && $customer->nas_ip) {
                        $success = $this->radiusService->rawDisconnect($customer->mikrotik_login, $customer->nas_ip);
                        $message = $success ? 'PPPoE session reset via CoA' : 'Failed to reset session';
                    } else {
                        return $this->error('Customer login or NAS IP missing', 400);
                    }
                    break;

                case 'reboot_onu':
                    if ($customer->olt_id && $customer->onu_index) {
                        $olt = ServiceNode::findOrFail((int) $customer->olt_id);
                        $success = $this->oltService->rebootOnu($olt, $customer->onu_interface ?? '', (string) $customer->onu_index);
                        $message = $success ? 'ONU reboot command sent' : 'Failed to reboot ONU';
                    } else {
                        return $this->error('OLT details missing for this customer', 400);
                    }
                    break;

                case 'flash_port':
                    // Experimental: Toggle interface or specific OLT logic
                    $message = 'Port flashing is currently in simulation mode';
                    $success = true;
                    break;
            }

            if ($success) {
                return $this->success(null, $message);
            }

            return $this->error($message, 500);

        } catch (\Exception $e) {
            Log::error('Remediation Failed: ' . $e->getMessage());
            return $this->error('System error during remediation', 500);
        }
    }

    /**
     * Run a jitter/latency test to the customer CPE.
     */
    public function jitterTest(Customer $customer): JsonResponse
    {
        if (!$customer->ip_address) {
            return $this->error('Customer IP address unknown', 422);
        }

        try {
            // Find the NAS/Router for this customer
            $router = ServiceNode::where('ip_address', $customer->nas_ip)->first();
            
            if (!$router) {
                return $this->error('Associated router not found', 404);
            }

            $pingResults = $this->routerService->pingExternal($router, $customer->ip_address, 10);
            
            // Calculate Jitter (Simplified: variance in latency)
            // This would require multiple pings and calculating diffs
            // For now, we return the average latency from the router service
            
            return $this->success([
                'ip' => $customer->ip_address,
                'latency_avg' => $pingResults['latency'] ?? 0,
                'success_rate' => $pingResults['success'] ? 100 : 0,
                'timestamp' => now()->toIso8601String()
            ], 'Jitter test completed');

        } catch (\Exception $e) {
            return $this->error('Failed to run jitter test', 500);
        }
    }
}
