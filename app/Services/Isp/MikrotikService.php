<?php

declare(strict_types=1);

namespace App\Services\Isp;

use App\Models\Isp\ServiceNode;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

// Assuming RouterOSAPI is a local class in the same namespace

class MikrotikService
{
    protected ?RouterOSAPI $client = null;

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
        $nodes = \App\Models\Isp\ServiceNode::where('status', 'active')->where('type', 'Router')->get();
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
            $this->client = new RouterOSAPI;
            $this->client->port = $port;
            $this->client->timeout = 3;

            return $this->client->connect($host, $user, $pass);
        } catch (Exception $e) {
            Log::error('Mikrotik Connection Failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Get real-time traffic for a specific interface.
     *
     * @return array{rx: float, tx: float}|null
     */
    public function getInterfaceTraffic(string $interfaceName): ?array
    {
        // Mock for local dev without creds
        if (config('app.env') === 'local' && ! config('services.mikrotik.host')) {
            return [
                'rx' => round(rand(1000000, 50000000) / 1000000, 2),
                'tx' => round(rand(500000, 10000000) / 1000000, 2),
            ];
        }

        if (! $this->client || ! $this->client->connected) {
            /** @var string $host */
            $host = config('services.mikrotik.host', '');
            /** @var string $user */
            $user = config('services.mikrotik.user', '');
            /** @var string $pass */
            $pass = config('services.mikrotik.pass', '');
            /** @var int $port */
            $port = config('services.mikrotik.port', 8728);

            if (! $this->connect($host, $user, $pass, $port)) {
                return null;
            }
        }

        try {
            if ($this->client === null) {
                return null;
            }

            $response = $this->client->comm('/interface/monitor-traffic', [
                'interface' => $interfaceName,
                'once' => '',
            ]);

            if (empty($response)) {
                return null;
            }

            $data = $response[0];

            return [
                'rx' => round(((float) ($data['rx-bits-per-second'] ?? 0)) / 1000000, 2),
                'tx' => round(((float) ($data['tx-bits-per-second'] ?? 0)) / 1000000, 2),
            ];

        } catch (Exception $e) {
            Log::error('Mikrotik Traffic Query Failed: '.$e->getMessage());

            return null;
        }
    }

    // ============================================================
    // IP BINDING METHODS (Phase 9)
    // ============================================================

    /**
     * Get all IP Bindings from the hotspot.
     *
     * @return array<int, array{id: string, mac: string, address: string, type: string, disabled: bool, comment: string|null}>
     */
    public function getIpBindings(): array
    {
        if (! $this->ensureConnected()) {
            return [];
        }

        try {
            /** @var array<int, array<string, string>> $response */
            $response = $this->client?->comm('/ip/hotspot/ip-binding/print') ?? [];

            return array_map(function (array $row): array {
                return [
                    'id' => (string) ($row['.id'] ?? ''),
                    'mac' => (string) ($row['mac-address'] ?? ''),
                    'address' => (string) ($row['address'] ?? ''),
                    'type' => (string) ($row['type'] ?? 'regular'),
                    'disabled' => ($row['disabled'] ?? 'false') === 'true',
                    'comment' => $row['comment'] ?? null,
                ];
            }, $response);

        } catch (Exception $e) {
            Log::error('Mikrotik IP Binding Query Failed: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Add a new IP Binding (bypass hotspot login for specific MAC/IP).
     *
     * @param  array{mac?: string, address?: string, type?: string, comment?: string}  $data
     */
    public function addIpBinding(array $data): bool
    {
        if (! $this->ensureConnected()) {
            return false;
        }

        try {
            $params = [];
            if (! empty($data['mac'])) {
                $params['mac-address'] = $data['mac'];
            }
            if (! empty($data['address'])) {
                $params['address'] = $data['address'];
            }
            $params['type'] = $data['type'] ?? 'bypassed';
            if (! empty($data['comment'])) {
                $params['comment'] = $data['comment'];
            }

            $this->client?->comm('/ip/hotspot/ip-binding/add', $params);

            return true;

        } catch (Exception $e) {
            Log::error('Mikrotik IP Binding Add Failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Remove an IP Binding by ID.
     */
    public function removeIpBinding(string $id): bool
    {
        if (! $this->ensureConnected()) {
            return false;
        }

        try {
            $this->client?->comm('/ip/hotspot/ip-binding/remove', ['.id' => $id]);

            return true;

        } catch (Exception $e) {
            Log::error('Mikrotik IP Binding Remove Failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Toggle IP Binding enable/disable.
     */
    public function toggleIpBinding(string $id, bool $disabled): bool
    {
        if (! $this->ensureConnected()) {
            return false;
        }

        try {
            $action = $disabled ? 'disable' : 'enable';
            $this->client?->comm('/ip/hotspot/ip-binding/'.$action, ['.id' => $id]);

            return true;

        } catch (Exception $e) {
            Log::error('Mikrotik IP Binding Toggle Failed: '.$e->getMessage());

            return false;
        }
    }

    // ============================================================
    // HOTSPOT COOKIES METHODS (Phase 9)
    // ============================================================

    /**
     * Get all Hotspot Cookies (active "remember me" sessions).
     *
     * @return array<int, array{id: string, user: string, mac: string, domain: string, expires_in: string}>
     */
    public function getHotspotCookies(): array
    {
        if (! $this->ensureConnected()) {
            return [];
        }

        try {
            /** @var array<int, array<string, string>> $response */
            $response = $this->client?->comm('/ip/hotspot/cookie/print') ?? [];

            return array_map(function (array $row): array {
                return [
                    'id' => (string) ($row['.id'] ?? ''),
                    'user' => (string) ($row['user'] ?? ''),
                    'mac' => (string) ($row['mac-address'] ?? ''),
                    'domain' => (string) ($row['domain'] ?? ''),
                    'expires_in' => (string) ($row['expires-in'] ?? '0s'),
                ];
            }, $response);

        } catch (Exception $e) {
            Log::error('Mikrotik Hotspot Cookies Query Failed: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Remove a Hotspot Cookie by ID.
     */
    public function removeHotspotCookie(string $id): bool
    {
        if (! $this->ensureConnected()) {
            return false;
        }

        try {
            $this->client?->comm('/ip/hotspot/cookie/remove', ['.id' => $id]);

            return true;

        } catch (Exception $e) {
            Log::error('Mikrotik Hotspot Cookie Remove Failed: '.$e->getMessage());

            return false;
        }
    }

    // ============================================================
    // ROUTER INTERFACES (Phase 11)
    // ============================================================

    /**
     * Get all interfaces from the router.
     *
     * @return array<int, array{name: string, type: string, running: bool, disabled: bool}>
     */
    public function getInterfaces(): array
    {
        if (! $this->ensureConnected()) {
            return [];
        }

        try {
            /** @var array<int, array<string, string>> $response */
            $response = $this->client?->comm('/interface/print') ?? [];

            return array_map(function (array $row): array {
                return [
                    'name' => (string) ($row['name'] ?? ''),
                    'type' => (string) ($row['type'] ?? ''),
                    'running' => ($row['running'] ?? 'false') === 'true',
                    'disabled' => ($row['disabled'] ?? 'false') === 'true',
                ];
            }, $response);

        } catch (Exception $e) {
            Log::error('Mikrotik Interfaces Query Failed: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Ensure connected to Mikrotik before making API calls.
     */
    protected function ensureConnected(): bool
    {
        if ($this->client && $this->client->connected) {
            return true;
        }

        /** @var string $host */
        $host = config('services.mikrotik.host', '');
        /** @var string $user */
        $user = config('services.mikrotik.user', '');
        /** @var string $pass */
        $pass = config('services.mikrotik.pass', '');
        $portVal = config('services.mikrotik.port', 8728);
        $port = is_numeric($portVal) ? (int) $portVal : 8728;

        if (empty($host) || empty($user)) {
            Log::warning('Mikrotik credentials not configured');

            return false;
        }

        return $this->connect($host, $user, $pass, $port);
    }

    // ============================================================
    // USER ACTIVITY & QUICK ACTIONS (Mikhmon-inspired)
    // ============================================================

    /**
     * Get hotspot user statistics.
     *
     * @return array<string, mixed>
     */
    public function getUserStats(string $username): array
    {
        if (! $this->ensureConnected()) {
            return [];
        }

        try {
            /** @var array<int, array<string, string>> $response */
            $response = $this->client?->comm('/ip/hotspot/user/print', [
                '?name' => $username,
            ]) ?? [];

            if (empty($response)) {
                return [];
            }

            $user = $response[0];

            return [
                'username' => (string) ($user['name'] ?? ''),
                'profile' => (string) ($user['profile'] ?? ''),
                'limit_uptime' => (string) ($user['limit-uptime'] ?? '0s'),
                'limit_bytes_in' => (int) ($user['limit-bytes-in'] ?? 0),
                'limit_bytes_out' => (int) ($user['limit-bytes-out'] ?? 0),
                'uptime' => (string) ($user['uptime'] ?? '0s'),
                'bytes_in' => (int) ($user['bytes-in'] ?? 0),
                'bytes_out' => (int) ($user['bytes-out'] ?? 0),
                'disabled' => ($user['disabled'] ?? 'false') === 'true',
                'comment' => $user['comment'] ?? null,
            ];

        } catch (Exception $e) {
            Log::error('Mikrotik User Stats Query Failed: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Disconnect active hotspot user by username.
     */
    public function disconnectUser(string $username): bool
    {
        if (! $this->ensureConnected()) {
            return false;
        }

        try {
            /** @var array<int, array<string, string>> $active */
            $active = $this->client?->comm('/ip/hotspot/active/print', [
                '?user' => $username,
            ]) ?? [];

            if (empty($active)) {
                return true; // User not active
            }

            foreach ($active as $session) {
                $id = $session['.id'] ?? '';
                if (! empty($id)) {
                    $this->client?->comm('/ip/hotspot/active/remove', ['.id' => $id]);
                }
            }

            return true;

        } catch (Exception $e) {
            Log::error('Mikrotik Disconnect User Failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Reset user counters (usage, uptime).
     */
    public function resetUserCounters(string $username): bool
    {
        if (! $this->ensureConnected()) {
            return false;
        }

        try {
            /** @var array<int, array<string, string>> $users */
            $users = $this->client?->comm('/ip/hotspot/user/print', [
                '?name' => $username,
            ]) ?? [];

            if (empty($users)) {
                return false;
            }

            $id = $users[0]['.id'] ?? '';
            if (empty($id)) {
                return false;
            }

            $this->client?->comm('/ip/hotspot/user/reset-counters', ['.id' => $id]);

            return true;

        } catch (Exception $e) {
            Log::error('Mikrotik Reset Counters Failed: '.$e->getMessage());

            return false;
        }
    }
}
