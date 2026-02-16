<?php

declare(strict_types=1);

namespace App\Services\Isp\Support;

use App\Models\Isp\Customer\Customer;
use App\Models\Isp\Network\ServiceNode;
use App\Models\Isp\Olt; // Import Olt as it is used below

class QuickDiagnosisService
{
    protected \App\Services\Isp\Network\RouterService $routerService;

    protected \App\Services\Isp\Network\OltService $oltService;

    public function __construct(\App\Services\Isp\Network\RouterService $routerService, \App\Services\Isp\Network\OltService $oltService)
    {
        $this->routerService = $routerService;
        $this->oltService = $oltService;
    }

    /**
     * Diagnose a router's health.
     *
     * @return array{online: bool, latency: float, cpu: int, memory: float, active_clients: int, uptime: string}
     */
    public function diagnoseRouter(ServiceNode $router): array
    {
        $status = $this->routerService->getDetailedStatus($router);
        $stats = $this->routerService->getMonitoredStats($router);

        return [
            'online' => (bool) $status['is_connected'],
            'latency' => 0.0, // Latency not current available in high-level status
            'cpu' => (int) ($stats['resource']['cpu'] ?? 0),
            'memory' => (float) ($stats['resource']['memory_free'] ?? 0),
            'active_clients' => (int) $stats['active_count'],
            'uptime' => (string) ($stats['resource']['uptime'] ?? '0s'),
        ];
    }

    /**
     * Diagnose a customer's connection status.
     *
     * @return array<string, mixed>
     */
    public function diagnoseCustomer(Customer $customer): array
    {
        $report = [
            'local' => ['status' => 'pending', 'message' => 'Initializing diagnostic suite...'],
            'session' => ['status' => 'pending', 'message' => 'Waiting for session check...'],
            'interface' => ['status' => 'pending', 'message' => 'Waiting for interface link check...'],
            'signal' => ['status' => 'pending', 'message' => 'Waiting for OLT signal check...'],
            'internet' => ['status' => 'pending', 'message' => 'Waiting for global route test...'],
        ];

        $router = $customer->router_id ? ServiceNode::find($customer->router_id) : null;
        $olt = $customer->olt_id ? ServiceNode::find($customer->olt_id) : null;
        $device = $customer->devices()->where('type', 'ONU')->first();

        // 1. Local Network Stage
        if ($router) {
            $isAlive = $this->routerService->checkPingConnection($router);
            $report['local'] = [
                'status' => $isAlive ? 'success' : 'error',
                'message' => $isAlive ? "Gateway router {$router->name} is reachable." : "Gateway router {$router->name} is unreachable.",
            ];
        } else {
            $report['local'] = ['status' => 'error', 'message' => 'No gateway router assigned to your account.'];
        }

        // 2. ISP Session Stage
        if ($router && $customer->mikrotik_login) {
            $session = $this->routerService->findActiveSessionByLogin($router, $customer->mikrotik_login);
            if ($session) {
                $report['session'] = [
                    'status' => 'success',
                    'message' => "Authenticated session active since {$session['uptime']}.",
                ];
            } else {
                $report['session'] = [
                    'status' => 'error',
                    'message' => 'No active authentication session found. Try resetting your connection.',
                ];
            }
        } else {
            $report['session'] = ['status' => 'error', 'message' => 'Missing authentication credentials.'];
        }

        // 3. Interface Link Stage
        if ($router && $customer->mikrotik_login) {
            // For PPPoE, the interface name is often the login
            $ifaceStatus = $this->routerService->getInterfaceStatus($router, $customer->mikrotik_login);
            if ($ifaceStatus) {
                $report['interface'] = [
                    'status' => $ifaceStatus['running'] ? 'success' : 'error',
                    'message' => $ifaceStatus['running'] ? "Link is UP at {$ifaceStatus['speed']}." : 'Physical link is DOWN.',
                    'details' => $ifaceStatus,
                ];
            } else {
                // Fallback for non-ether monitorable interfaces
                $report['interface'] = [
                    'status' => $report['session']['status'] === 'success' ? 'success' : 'pending',
                    'message' => $report['session']['status'] === 'success' ? 'Session link established over tunnel.' : 'Waiting for session...',
                ];
            }
        }

        // 4. Global Signal Stage (OLT)
        if ($olt && $device) {
            /** @var array<string, mixed> $metadata */
            $metadata = $device->metadata ?? [];
            $ifaceName = is_string($metadata['interface'] ?? null) ? $metadata['interface'] : 'gpon-olt_1/1/1';
            $onuIdx = is_string($metadata['onu_index'] ?? null) ? $metadata['onu_index'] : '1';

            $signal = $this->oltService->getSignal($olt, $ifaceName, $onuIdx);
            if ($signal !== null) {
                $status = ($signal > -28) ? 'success' : 'error';
                $report['signal'] = [
                    'status' => $status,
                    'message' => "Optical signal level is optimal at {$signal} dBm.",
                ];
                if ($status === 'error') {
                    $report['signal']['message'] = "Critical optical signal levels detected: {$signal} dBm.";
                }
            } else {
                $report['signal'] = ['status' => 'error', 'message' => 'Could not retrieve OLT telemetry.'];
            }
        } else {
            $report['signal'] = ['status' => 'success', 'message' => 'No OLT infrastructure in path (Direct Fiber/Ethernet).'];
        }

        // 5. Internet Access Stage
        if ($router && $report['local']['status'] === 'success') {
            $ping = $this->routerService->pingExternal($router);
            if ($ping['success']) {
                $report['internet'] = [
                    'status' => 'success',
                    'message' => 'Global route verified via Google DNS.',
                    'latency' => $ping['latency'],
                ];
            } else {
                $report['internet'] = [
                    'status' => 'error',
                    'message' => 'Could not reach global network. Potential uplink issues.',
                ];
            }
        }

        // Calculate overall status
        $errors = array_filter($report, fn($s) => $s['status'] === 'error');
        
        return [
            'report' => $report,
            'overall_status' => empty($errors) ? 'healthy' : 'issue',
        ];
    }
}
