<?php

declare(strict_types=1);

namespace App\Services\Isp\Network;

use App\Models\Isp\Network\ServiceNode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RouterMonitoringService
{
    protected MikrotikClient $api;

    public function __construct(MikrotikClient $api)
    {
        $this->api = $api;
    }

    /**
     * Get detailed status (connectivity and active count).
     *
     * @return array{is_connected: bool, active_count: int}
     */
    public function getDetailedStatus(ServiceNode $router): array
    {
        $status = [
            'is_connected' => false,
            'active_count' => 0,
        ];

        if ($router->connection_method === 'none') {
            return $status;
        }

        if ($router->connection_method === 'api') {
            try {
                $this->api->api_port = is_numeric($router->api_port) ? (int) $router->api_port : 8728;
                $host = $router->ip_address;
                if ($host === null) {
                    return $status;
                }

                if ($this->api->connect($host, $router->api_username ?? '', $router->api_password ?? '')) {
                    $status['is_connected'] = true;

                    // Get counts in the same session
                    /** @var array<int, array{ret: string}> $pppoe */
                    $pppoe = $this->api->comm('/ppp/active/print', ['count-only' => '']);
                    /** @var array<int, array{ret: string}> $hotspot */
                    $hotspot = $this->api->comm('/ip/hotspot/active/print', ['count-only' => '']);

                    $pCount = isset($pppoe[0]['ret']) ? (int) $pppoe[0]['ret'] : 0;
                    $hCount = isset($hotspot[0]['ret']) ? (int) $hotspot[0]['ret'] : 0;

                    $status['active_count'] = $pCount + $hCount;

                    $this->api->disconnect();
                }
            } catch (\Exception $e) {
                Log::error('RouterMonitoringService: getDetailedStatus Failed', [
                    'router_id' => $router->id,
                    'error' => $e->getMessage(),
                ]);
            }
        } elseif ($router->connection_method === 'snmp') {
            $status['is_connected'] = $this->checkSnmpConnection($router);
        } else {
            $status['is_connected'] = $this->checkPingConnection($router);
        }

        return $status;
    }

    public function checkPingConnection(ServiceNode $router): bool
    {
        $host = $router->ip_address;
        if (! $host) {
            return false;
        }

        $wait = 2; // seconds
        exec("ping -c 1 -W $wait ".escapeshellarg($host), $output, $result);

        return $result === 0;
    }

    public function checkSnmpConnection(ServiceNode $router): bool
    {
        $host = $router->ip_address;
        if (! $host || ! function_exists('snmp2_get')) {
            return false;
        }

        try {
            $community = 'public';
            $uptime = @snmp2_get($host, $community, '1.3.6.1.2.1.1.3.0', 1000000, 1);

            return $uptime !== false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get system resource from the router.
     *
     * @return array{cpu: int, memory_free: int, memory_total: int, uptime: string}|null
     */
    public function getSystemResource(ServiceNode $router): ?array
    {
        $host = $router->ip_address;
        if (! $host) {
            return null;
        }

        try {
            if ($this->api->connect($host, $router->api_username ?? '', $router->api_password ?? '')) {
                /** @var array<int, array<string, string>> $resource */
                $resource = $this->api->comm('/system/resource/print');
                $this->api->disconnect();

                if (isset($resource[0])) {
                    return [
                        'cpu' => (int) ($resource[0]['cpu-load'] ?? 0),
                        'memory_free' => (int) ($resource[0]['free-memory'] ?? 0),
                        'memory_total' => (int) ($resource[0]['total-memory'] ?? 0),
                        'uptime' => (string) ($resource[0]['uptime'] ?? '0s'),
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('RouterMonitoringService: getSystemResource Failed: '.$e->getMessage());
        }

        return null;
    }

    /**
     * Get real-time traffic for an interface.
     *
     * @return array{rx: int, tx: int}|null
     */
    public function getInterfaceTraffic(ServiceNode $router, string $interface = 'ether1'): ?array
    {
        $host = $router->ip_address;
        if (! $host) {
            return null;
        }

        try {
            if ($this->api->connect($host, $router->api_username ?? '', $router->api_password ?? '')) {
                /** @var array<int, array<string, string>> $traffic */
                $traffic = $this->api->comm('/interface/monitor-traffic', [
                    'interface' => $interface,
                    'once' => '',
                ]);
                $this->api->disconnect();

                if (isset($traffic[0])) {
                    return [
                        'rx' => (int) ($traffic[0]['rx-bits-per-second'] ?? 0),
                        'tx' => (int) ($traffic[0]['tx-bits-per-second'] ?? 0),
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('RouterMonitoringService: getInterfaceTraffic Failed: '.$e->getMessage());
        }

        return null;
    }

    /**
     * @return array{name: string, status: string, speed: string, duplex: string, running: bool}|null
     */
    public function getInterfaceStatus(ServiceNode $router, string $interface = 'ether1'): ?array
    {
        $host = $router->ip_address;
        if (! $host) {
            return null;
        }

        try {
            if ($this->api->connect($host, $router->api_username ?? '', $router->api_password ?? '')) {
                /** @var array<int, array<string, string>> $resp */
                $resp = $this->api->comm('/interface/ethernet/monitor', [
                    'numbers' => $interface,
                    'once' => '',
                ]);
                $this->api->disconnect();

                if (isset($resp[0])) {
                    return [
                        'name' => $interface,
                        'status' => (string) ($resp[0]['status'] ?? 'unknown'),
                        'speed' => (string) ($resp[0]['rate'] ?? 'unknown'),
                        'duplex' => (string) ($resp[0]['full-duplex'] ?? 'false') === 'true' ? 'full' : 'half',
                        'running' => (string) ($resp[0]['status'] ?? '') === 'link-ok',
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('RouterMonitoringService: getInterfaceStatus Failed: '.$e->getMessage());
        }

        return null;
    }

    /**
     * Get all interfaces from the router.
     *
     * @return array<int, array{name: string, type: string, running: bool, disabled: bool}>
     */
    public function getInterfaces(ServiceNode $router): array
    {
        $host = $router->ip_address;
        if (! $host) {
            return [];
        }

        try {
            if ($this->api->connect($host, $router->api_username ?? '', $router->api_password ?? '')) {
                /** @var array<int, array<string, string>> $response */
                $response = $this->api->comm('/interface/print');
                $this->api->disconnect();

                return array_map(function (array $row): array {
                    return [
                        'name' => (string) ($row['name'] ?? ''),
                        'type' => (string) ($row['type'] ?? ''),
                        'running' => ($row['running'] ?? 'false') === 'true',
                        'disabled' => ($row['disabled'] ?? 'false') === 'true',
                    ];
                }, $response);
            }
        } catch (\Exception $e) {
            Log::error('RouterMonitoringService: getInterfaces Failed: '.$e->getMessage());
        }

        return [];
    }

    /**
     * Get consolidated monitoring stats (Resource, Traffic, Clients) in a single session.
     *
     * @return array{resource?: array{cpu: int, memory_free: int, memory_total: int, uptime: string}, traffic?: array{rx: int, tx: int}, active_count: int}
     */
    public function getMonitoredStats(ServiceNode $router): array
    {
        $cacheKey = "router_stats_{$router->id}";

        /** @var array{resource?: array{cpu: int, memory_free: int, memory_total: int, uptime: string}, traffic?: array{rx: int, tx: int}, active_count: int}|null $cached */
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return $cached;
        }

        $stats = ['active_count' => 0];

        $host = $router->ip_address;
        if (! $host) {
            return $stats;
        }

        try {
            if ($this->api->connect($host, $router->api_username ?? '', $router->api_password ?? '')) {
                /** @var array<int, array<string, string>> $res */
                $res = $this->api->comm('/system/resource/print');
                if (isset($res[0])) {
                    $stats['resource'] = [
                        'cpu' => (int) ($res[0]['cpu-load'] ?? 0),
                        'memory_free' => (int) ($res[0]['free-memory'] ?? 0),
                        'memory_total' => (int) ($res[0]['total-memory'] ?? 0),
                        'uptime' => (string) ($res[0]['uptime'] ?? '0s'),
                    ];
                }

                $wanInterface = is_string($router->metadata['wan_interface'] ?? null) ? $router->metadata['wan_interface'] : 'ether1';
                /** @var array<int, array<string, string>> $traff */
                $traff = $this->api->comm('/interface/monitor-traffic', ['interface' => $wanInterface, 'once' => '']);
                if (isset($traff[0])) {
                    $stats['traffic'] = [
                        'rx' => (int) ($traff[0]['rx-bits-per-second'] ?? 0),
                        'tx' => (int) ($traff[0]['tx-bits-per-second'] ?? 0),
                    ];
                }

                /** @var array<int, array{ret: string}> $pppoe */
                $pppoe = $this->api->comm('/ppp/active/print', ['count-only' => '']);
                /** @var array<int, array{ret: string}> $hotspot */
                $hotspot = $this->api->comm('/ip/hotspot/active/print', ['count-only' => '']);

                $stats['active_count'] = (isset($pppoe[0]['ret']) ? (int) $pppoe[0]['ret'] : 0) +
                                       (isset($hotspot[0]['ret']) ? (int) $hotspot[0]['ret'] : 0);

                $this->api->disconnect();

                Cache::put($cacheKey, $stats, 30);
            }
        } catch (\Exception $e) {
            Log::error('RouterMonitoringService: getMonitoredStats Failed: '.$e->getMessage());
        }

        return $stats;
    }

    /**
     * Find a specific active session by login/name.
     *
     * @return array{id: string, type: string, name: string, address: string, uptime: string, caller_id: string, service?: string}|null
     */
    public function findActiveSessionByLogin(ServiceNode $router, string $login): ?array
    {
        $sessions = $this->getActiveSessions($router);

        foreach ($sessions as $session) {
            if ($session['name'] === $login) {
                return $session;
            }
        }

        return null;
    }

    /**
     * @return array<int, array{id: string, type: string, name: string, address: string, uptime: string, caller_id: string, service?: string}>
     */
    public function getActiveSessions(ServiceNode $router): array
    {
        $sessions = [];
        $host = $router->ip_address;
        if (! $host) {
            return $sessions;
        }

        try {
            if ($this->api->connect($host, $router->api_username ?? '', $router->api_password ?? '')) {
                /** @var array<int, array<string, string>> $pppoe */
                $pppoe = $this->api->comm('/ppp/active/print');
                foreach ($pppoe as $item) {
                    $sessions[] = [
                        'id' => (string) ($item['.id'] ?? ''),
                        'type' => 'pppoe',
                        'name' => (string) ($item['name'] ?? ''),
                        'address' => (string) ($item['address'] ?? ''),
                        'uptime' => (string) ($item['uptime'] ?? ''),
                        'caller_id' => (string) ($item['caller-id'] ?? ''),
                        'service' => (string) ($item['service'] ?? 'pppoe'),
                    ];
                }

                /** @var array<int, array<string, string>> $hotspot */
                $hotspot = $this->api->comm('/ip/hotspot/active/print');
                foreach ($hotspot as $item) {
                    $sessions[] = [
                        'id' => (string) ($item['.id'] ?? ''),
                        'type' => 'hotspot',
                        'name' => (string) ($item['user'] ?? ''),
                        'address' => (string) ($item['address'] ?? ''),
                        'uptime' => (string) ($item['uptime'] ?? ''),
                        'caller_id' => (string) ($item['mac-address'] ?? ''),
                    ];
                }

                $this->api->disconnect();
            }
        } catch (\Exception $e) {
            Log::error('RouterMonitoringService: getActiveSessions Failed: '.$e->getMessage());
        }

        return $sessions;
    }

    /**
     * Run a ping test from the router to an external address.
     *
     * @return array{success: bool, latency: float|null}
     */
    public function pingExternal(ServiceNode $router, string $address = '8.8.8.8', int $count = 3): array
    {
        $host = $router->ip_address;
        if (! $host) {
            return ['success' => false, 'latency' => null];
        }

        try {
            if ($this->api->connect($host, $router->api_username ?? '', $router->api_password ?? '')) {
                /** @var array<int, array<string, string>> $resp */
                $resp = $this->api->comm('/ping', [
                    'address' => $address,
                    'count' => (string) $count,
                ]);
                $this->api->disconnect();

                if (empty($resp)) {
                    return ['success' => false, 'latency' => null];
                }

                $received = 0;
                $totalLatency = 0.0;

                foreach ($resp as $ping) {
                    if (isset($ping['avg-at']) || isset($ping['time'])) {
                        $latencyStr = $ping['avg-at'] ?? $ping['time'];
                        // Mikrotik might return "15ms" or similar
                        $latency = (float) preg_replace('/[^0-9.]/', '', $latencyStr);
                        if ($latency > 0) {
                            $totalLatency += $latency;
                            $received++;
                        }
                    }
                }

                if ($received > 0) {
                    return [
                        'success' => true,
                        'latency' => round($totalLatency / $received, 2),
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('RouterMonitoringService: pingExternal Failed: '.$e->getMessage());
        }

        return ['success' => false, 'latency' => null];
    }
}
