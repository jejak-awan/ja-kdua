<?php

declare(strict_types=1);

namespace App\Services\Isp;

use App\Models\Isp\ServiceNode;
use Exception;
use Illuminate\Support\Facades\Log;
use RouterOS\Client;
use RouterOS\Query;

class MikrotikService
{
    protected ?Client $client = null;

    protected RouterService $routerService;

    public function __construct(RouterService $routerService)
    {
        $this->routerService = $routerService;
    }

    /**
     * Get statistics for a specific node.
     *
     * @return array{node_id: int|null, name: string, status: string, latency: int, uptime: string|int, traffic_in: float, traffic_out: float, cpu_load: int, memory_used: int, memory_total: int, active_clients: int}
     */
    public function getNodeStats(ServiceNode $node): array
    {
        $resources = $this->routerService->getSystemResource($node);
        $traffic = $this->routerService->getInterfaceTraffic($node);
        $activeClients = $this->routerService->getActiveClientCount($node);

        return [
            'node_id' => (int) $node->id,
            'name' => (string) $node->name,
            'status' => (string) $node->status,
            'latency' => rand(5, 20), // Still somewhat simulated without actual ping tool
            'uptime' => $resources['uptime'] ?? '0s',
            'traffic_in' => round(($traffic['rx'] ?? 0) / 1000000, 2), // Mbps
            'traffic_out' => round(($traffic['tx'] ?? 0) / 1000000, 2), // Mbps
            'cpu_load' => $resources['cpu'] ?? 0,
            'memory_used' => ($resources['memory_total'] ?? 0) - ($resources['memory_free'] ?? 0),
            'memory_total' => $resources['memory_total'] ?? 0,
            'active_clients' => $activeClients,
        ];
    }

    /**
     * Get global network statistics.
     *
     * @return array{total_traffic_in: float, total_traffic_out: float, active_customers: int, network_health: string, uptime_percentage: float}
     */
    public function getGlobalStats(): array
    {
        $nodes = ServiceNode::where('status', 'active')->where('type', 'Router')->get();
        $totalIn = 0.0;
        $totalOut = 0.0;
        $totalClients = 0;

        foreach ($nodes as $node) {
            $traffic = $this->routerService->getInterfaceTraffic($node);
            $totalIn += ($traffic['rx'] ?? 0);
            $totalOut += ($traffic['tx'] ?? 0);
            $totalClients += $this->routerService->getActiveClientCount($node);
        }

        return [
            'total_traffic_in' => round($totalIn / 1000000, 2),
            'total_traffic_out' => round($totalOut / 1000000, 2),
            'active_customers' => $totalClients,
            'network_health' => 'Healthy',
            'uptime_percentage' => 99.99,
        ];
    }

    /**
     * Get historical traffic data for charts.
     * NOTE: Returns simulated data until proper SNMP/accounting infrastructure is available.
     *
     * @return array{data: array<int, array{time: string, in: float, out: float}>, is_simulated: bool}
     */
    public function getTrafficHistory(): array
    {
        $history = [];
        $now = time();

        for ($i = 0; $i < 20; $i++) {
            $history[] = [
                'time' => date('H:i:s', $now - ($i * 5)),
                'in' => rand(200, 500) / 10,
                'out' => rand(80, 200) / 10,
            ];
        }

        return [
            'data' => array_reverse($history),
            'is_simulated' => true,
        ];
    }

    /**
     * Get historical bandwidth usage for a specific customer.
     * NOTE: Returns simulated data - implement via PPPoE/Hotspot accounting or local usage table.
     *
     * @return array{daily: array<int, array{time: string, down: float, up: float}>, monthly: array<int, array{date: string, down: float, up: float}>, is_simulated: bool}
     */
    public function getCustomerUsageHistory(int $customerId): array
    {
        // TODO: Query pppoe/hotspot accounting logs or a local usage table
        $daily = [];
        $now = Carbon::now();

        for ($i = 0; $i < 24; $i++) {
            $daily[] = [
                'time' => $now->copy()->subHours($i)->format('H:00'),
                'down' => rand(100, 1500) / 10,
                'up' => rand(20, 400) / 10,
            ];
        }

        $monthly = [];
        for ($i = 0; $i < 30; $i++) {
            $monthly[] = [
                'date' => $now->copy()->subDays($i)->format('M d'),
                'down' => (float) rand(5, 50),
                'up' => (float) rand(1, 10),
            ];
        }

        return [
            'daily' => array_reverse($daily),
            'monthly' => array_reverse($monthly),
            'is_simulated' => true,
        ];
    }

    /**
     * Connect to the Mikrotik router.
     */
    public function connect(string $host, string $user, string $pass, int $port = 8728): bool
    {
        try {
            $this->client = new Client([
                'host' => $host,
                'user' => $user,
                'pass' => $pass,
                'port' => $port,
                'timeout' => 3,
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Mikrotik Connection Failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Get real-time traffic for a specific interface.
     */
    public function getInterfaceTraffic(string $interfaceName): ?array
    {
        // Mock for local dev without creds
        if (config('app.env') === 'local' && ! env('MIKROTIK_HOST')) {
            return [
                'rx' => round(rand(1000000, 50000000) / 1000000, 2),
                'tx' => round(rand(500000, 10000000) / 1000000, 2),
            ];
        }

        if (! $this->client) {
            if (! $this->connect(
                config('services.mikrotik.host', env('MIKROTIK_HOST', '')),
                config('services.mikrotik.user', env('MIKROTIK_USER', '')),
                config('services.mikrotik.pass', env('MIKROTIK_PASS', '')),
                (int) config('services.mikrotik.port', env('MIKROTIK_PORT', 8728))
            )) {
                return null;
            }
        }

        try {
            $query = new Query('/interface/monitor-traffic');
            $query->equal('interface', $interfaceName);
            $query->equal('once');

            $response = $this->client->query($query)->read();

            if (empty($response)) {
                return null;
            }

            $data = $response[0];

            return [
                'rx' => round(($data['rx-bits-per-second'] ?? 0) / 1000000, 2),
                'tx' => round(($data['tx-bits-per-second'] ?? 0) / 1000000, 2),
            ];

        } catch (Exception $e) {
            Log::error('Mikrotik Traffic Query Failed: '.$e->getMessage());

            return null;
        }
    }
}
