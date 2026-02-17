<?php

declare(strict_types=1);

namespace App\Services\Isp\Network;

use App\Models\Isp\Network\ServiceNode;
use Exception;
use Illuminate\Support\Facades\Log;

// Assuming MikrotikClient is a local class in the same namespace

class MikrotikService
{
    protected ?MikrotikClient $client = null;

    protected \App\Services\Isp\Network\RouterService $routerService;

    protected RadiusStatService $radiusStats;

    public function __construct(\App\Services\Isp\Network\RouterService $routerService, RadiusStatService $radiusStats)
    {
        $this->routerService = $routerService;
        $this->radiusStats = $radiusStats;
    }

    /**
     * Get the RouterOS API client instance.
     */
    public function getClient(): ?MikrotikClient
    {
        return $this->client;
    }

    /**
     * Get statistics for a specific node.
     *
     * @return array{node_id: int|null, name: string, status: string, latency: int, uptime: string|int, traffic_in: float, traffic_out: float, cpu_load: int, memory_used: int, memory_total: int, active_clients: int}
     */
    public function getNodeStats(ServiceNode $node): array
    {
        $stats = $this->routerService->getMonitoredStats($node);
        
        $resources = $stats['resource'] ?? [];
        $traffic = $stats['traffic'] ?? [];
        $activeClients = $stats['active_count'] ?? 0;

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
        $nodes = \App\Models\Isp\Network\ServiceNode::where('status', 'active')->where('type', 'Router')->get();
        $totalIn = 0.0;
        $totalOut = 0.0;
        $totalClients = 0;

        foreach ($nodes as $node) {
            // Optimization: Use getMonitoredStats to fetch both traffic and client counts in one connection
            // This method also utilizes cache, reducing real-time overhead
            $stats = $this->routerService->getMonitoredStats($node);

            if (isset($stats['traffic'])) {
                $totalIn += ($stats['traffic']['rx'] ?? 0);
                $totalOut += ($stats['traffic']['tx'] ?? 0);
            }

            $totalClients += ($stats['active_count'] ?? 0);
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
     *
     * @return array{data: array<int, array{time: string, in: float, out: float}>, is_simulated: bool}
     */
    public function getTrafficHistory(): array
    {
        /** @var \Illuminate\Support\Collection<int, \stdClass> $logs */
        $logs = \Illuminate\Support\Facades\DB::table('isp_traffic_logs')
            ->selectRaw("to_char(logged_at, 'HH24:MI') as time_label, SUM(rx_bps) as total_in, SUM(tx_bps) as total_out")
            ->where('logged_at', '>=', now()->subHours(2)) // Last 2 hours
            ->groupBy('time_label')
            ->orderBy('time_label', 'desc')
            ->limit(20)
            ->get();

        if ($logs->isEmpty()) {
            return [
                'data' => [],
                'is_simulated' => false,
            ];
        }

        /** @var array<int, array{time: string, in: float, out: float}> $data */
        $data = $logs->map(fn ($log) => [
            'time' => (string) ($log->time_label ?? ''),
            'in' => round(($log->total_in ?? 0) / 1000000, 2), // Mbps
            'out' => round(($log->total_out ?? 0) / 1000000, 2),
        ])->toArray();

        return [
            'data' => array_reverse($data),
            'is_simulated' => false,
        ];
    }

    /**
     * Get historical bandwidth usage for a specific customer.
     *
     * @return array{daily: array<int, array{time: string, down: float, up: float}>, monthly: array<int, array{date: string, down: float, up: float}>, is_simulated: bool}
     */
    public function getCustomerUsageHistory(int $customerId): array
    {
        $customer = \App\Models\Isp\Customer\Customer::find($customerId);
        if (! $customer || ! $customer->mikrotik_login) {
            return [
                'daily' => [],
                'monthly' => [],
                'is_simulated' => false,
            ];
        }

        $daily = $this->radiusStats->getCustomerUsageDaily($customer->mikrotik_login);
        $monthly = $this->radiusStats->getCustomerUsageMonthly($customer->mikrotik_login);

        return [
            'daily' => $daily,
            'monthly' => $monthly,
            'is_simulated' => false,
        ];
    }

    /**
     * Connect to the Mikrotik router.
     */
    public function connect(string $host, string $user, string $pass, int $port = 8728): bool
    {
        try {
            $this->client = new MikrotikClient;
            $this->client->api_port = $port;
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

    /**
     * Manage an IP in a firewall address list.
     */
    public function setAddressList(ServiceNode $node, string $ip, string $list, bool $active, ?string $comment = null): bool
    {
        if (! $this->connect((string) $node->ip_address, (string) $node->api_username, (string) $node->api_password, (int) ($node->api_port ?? 8728))) {
            return false;
        }

        try {
            /** @var array<int, array<string, string>> $response */
            $response = $this->client?->comm('/ip/firewall/address-list/print', [
                '?address' => $ip,
                '?list' => $list,
            ]) ?? [];

            if ($active) {
                if (empty($response)) {
                    $this->client?->comm('/ip/firewall/address-list/add', [
                        'address' => $ip,
                        'list' => $list,
                        'comment' => $comment ?? 'JA-KDUA Isolation',
                    ]);
                }
            } else {
                foreach ($response as $entry) {
                    $id = $entry['.id'] ?? '';
                    if (! empty($id)) {
                        $this->client?->comm('/ip/firewall/address-list/remove', ['.id' => $id]);
                    }
                }
            }

            $this->client?->disconnect();

            return true;
        } catch (Exception $e) {
            Log::error('Mikrotik Address List Management Failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Test connection to a service node.
     *
     * @return array{success: bool, message: string, version?: string, board?: string}
     */
    public function testConnection(ServiceNode $node): array
    {
        try {
            if ($this->connect((string) $node->ip_address, (string) $node->api_username, (string) $node->api_password, (int) ($node->api_port ?? 8728))) {
                /** @var array<int, array<string, string>> $resources */
                $resources = $this->client?->comm('/system/resource/print') ?? [];
                $this->client?->disconnect();

                return [
                    'success' => true,
                    'message' => 'Connected successfully',
                    'version' => (string) ($resources[0]['version'] ?? 'unknown'),
                    'board' => (string) ($resources[0]['board-name'] ?? 'unknown'),
                ];
            }

            return [
                'success' => false,
                'message' => 'Connection failed',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get recent logs from the router.
     *
     * @return array<int, array{id: string, time: string, topics: string, message: string}>
     */
    public function getLogs(ServiceNode $node, int $limit = 100): array
    {
        if (! $this->connect((string) $node->ip_address, (string) $node->api_username, (string) $node->api_password, (int) ($node->api_port ?? 8728))) {
            return [];
        }

        try {
            /** @var array<int, array<string, string>> $response */
            $response = $this->client?->comm('/log/print', [
                '.proplist' => '.id,time,topics,message',
                'tail' => (string) $limit,
            ]) ?? [];

            $this->client?->disconnect();

            return array_map(function (array $row): array {
                return [
                    'id' => (string) ($row['.id'] ?? ''),
                    'time' => (string) ($row['time'] ?? ''),
                    'topics' => (string) ($row['topics'] ?? ''),
                    'message' => (string) ($row['message'] ?? ''),
                ];
            }, $response);
        } catch (Exception $e) {
            Log::error('Mikrotik Log Query Failed: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Detect potential multi-login fraud (sharing accounts).
     *
     * @return array<int, array{username: string, macs: array<int, string>, count: int}>
     */
    public function getMultiLoginAlerts(ServiceNode $node): array
    {
        if (! $this->connect((string) $node->ip_address, (string) $node->api_username, (string) $node->api_password, (int) ($node->api_port ?? 8728))) {
            return [];
        }

        try {
            /** @var array<int, array<string, string>> $activeSessions */
            $activeSessions = $this->client?->comm('/ip/hotspot/active/print', [
                '.proplist' => 'user,mac-address',
            ]) ?? [];

            $grouped = [];
            foreach ($activeSessions as $session) {
                $user = $session['user'] ?? 'unknown';
                $mac = $session['mac-address'] ?? 'unknown';
                $grouped[$user][] = $mac;
            }

            $alerts = [];
            foreach ($grouped as $user => $macs) {
                $uniqueMacs = array_values(array_unique($macs));
                if (count($uniqueMacs) > 1) {
                    $alerts[] = [
                        'username' => $user,
                        'macs' => $uniqueMacs,
                        'count' => count($uniqueMacs),
                    ];
                }
            }

            return $alerts;
        } catch (Exception $e) {
            Log::error('Mikrotik Multi-login query failed: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Check for potential tethering / router-behind-router anomalies.
     * This looks for firewall mangle "TTL change" hits if configured.
     *
     * @return array<int, array{src_address: string, hits: int, last_seen: string}>
     */
    public function getTtlAnomalies(ServiceNode $node): array
    {
        if (! $this->connect((string) $node->ip_address, (string) $node->api_username, (string) $node->api_password, (int) ($node->api_port ?? 8728))) {
            return [];
        }

        try {
            /** @var array<int, array<string, string>> $mangleRules */
            // Expecting rules with comment "FRAUD_TTL_DETECT"
            $mangleRules = $this->client?->comm('/ip/firewall/mangle/print', [
                '?comment' => 'FRAUD_TTL_DETECT',
            ]) ?? [];

            $anomalies = [];
            foreach ($mangleRules as $rule) {
                $packets = (int) ($rule['packets'] ?? 0);
                if ($packets > 1000) {
                    $anomalies[] = [
                        'src_address' => $rule['src-address'] ?? 'unknown',
                        'hits' => $packets,
                        'last_seen' => now()->toDateTimeString(),
                    ];
                }
            }

            return $anomalies;
        } catch (Exception $e) {
            Log::error('Mikrotik TTL anomaly query failed: '.$e->getMessage());

            return [];
        }
    }
}
