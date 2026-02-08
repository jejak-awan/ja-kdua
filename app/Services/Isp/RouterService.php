<?php

declare(strict_types=1);

namespace App\Services\Isp;

use App\Models\Isp\ServiceNode;
use Exception;
use Illuminate\Support\Facades\Log;

class RouterService
{
    protected RouterOSAPI $api;

    public function __construct(RouterOSAPI $api)
    {
        $this->api = $api;
    }

    public function checkConnectivity(ServiceNode $router): bool
    {
        if ($router->connection_method === 'api') {
            return $this->checkApiConnection($router);
        }

        if ($router->connection_method === 'snmp') {
            return $this->checkSnmpConnection($router);
        }

        return false;
    }

    protected function checkApiConnection(ServiceNode $router): bool
    {
        try {
            $this->api->port = is_numeric($router->api_port) ? (int) $router->api_port : 8728;
            $host = $router->ip_address;
            if ($host === null) {
                return false;
            }

            $connected = $this->api->connect(
                $host,
                $router->api_username ?? '',
                $router->api_password ?? ''
            );
            if ($connected) {
                $this->api->disconnect();

                return true;
            }
        } catch (Exception $e) {
            Log::error('Mikrotik API Connection Failed', [
                'router_id' => $router->id,
                'ip' => $router->ip_address,
                'error' => $e->getMessage(),
            ]);
        }

        return false;
    }

    protected function checkSnmpConnection(ServiceNode $router): bool
    {
        $ip = $router->ip_address;
        if ($ip === null) {
            return false;
        }

        $port = is_numeric($router->snmp_port) ? (int) $router->snmp_port : 161;
        $socket = @fsockopen("udp://{$ip}", $port, $errno, $errstr, 2);
        if ($socket) {
            fclose($socket);

            return true;
        }

        return false;
    }

    public function getActiveClientCount(ServiceNode $router): int
    {
        if ($router->connection_method !== 'api') {
            return 0;
        }

        try {
            $this->api->port = is_numeric($router->api_port) ? (int) $router->api_port : 8728;
            $host = $router->ip_address;
            if ($host === null) {
                return 0;
            }

            if ($this->api->connect($host, $router->api_username ?? '', $router->api_password ?? '')) {
                // Get PPPoE active count
                /** @var array<int, array{ret: string}> $pppoe */
                $pppoe = $this->api->comm('/ppp/active/print', ['count-only' => '']);
                // Get Hotspot active count
                /** @var array<int, array{ret: string}> $hotspot */
                $hotspot = $this->api->comm('/ip/hotspot/active/print', ['count-only' => '']);

                $this->api->disconnect();

                $pCount = isset($pppoe[0]['ret']) ? (int) $pppoe[0]['ret'] : 0;
                $hCount = isset($hotspot[0]['ret']) ? (int) $hotspot[0]['ret'] : 0;

                return $pCount + $hCount;
            }
        } catch (Exception $e) {
            Log::error('RouterService: getActiveClientCount Failed', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);
        }

        return 0;
    }

    /**
     * Get system resources (CPU, Memory, Uptime).
     *
     * @return array{cpu: int, memory_free: int, memory_total: int, uptime: string}|null
     */
    public function getSystemResource(ServiceNode $router): ?array
    {
        if ($router->connection_method !== 'api') {
            return null;
        }

        try {
            $this->api->port = is_numeric($router->api_port) ? (int) $router->api_port : 8728;
            $host = $router->ip_address;
            if ($host === null) {
                return null;
            }

            if ($this->api->connect($host, $router->api_username ?? '', $router->api_password ?? '')) {
                $resource = $this->api->comm('/system/resource/print');
                $this->api->disconnect();

                if (isset($resource[0])) {
                    $freeMemory = is_numeric($resource[0]['free-memory'] ?? null) ? (int) $resource[0]['free-memory'] : 0;
                    $totalMemory = is_numeric($resource[0]['total-memory'] ?? null) ? (int) $resource[0]['total-memory'] : 0;

                    return [
                        'cpu' => (int) ($resource[0]['cpu-load'] ?? 0),
                        'memory_free' => (int) ($freeMemory / 1024 / 1024), // MB
                        'memory_total' => (int) ($totalMemory / 1024 / 1024), // MB
                        'uptime' => (string) ($resource[0]['uptime'] ?? '0s'),
                    ];
                }
            }
        } catch (Exception $e) {
            Log::error('RouterService: getSystemResource Failed', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);
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
        if ($router->connection_method !== 'api') {
            return null;
        }

        try {
            $this->api->port = is_numeric($router->api_port) ? (int) $router->api_port : 8728;
            $host = $router->ip_address;
            if ($host === null) {
                return null;
            }

            if ($this->api->connect($host, $router->api_username ?? '', $router->api_password ?? '')) {
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
        } catch (Exception $e) {
            Log::error('RouterService: getInterfaceTraffic Failed', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
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
        if ($router->connection_method !== 'api') {
            return [];
        }

        try {
            $this->api->port = is_numeric($router->api_port) ? (int) $router->api_port : 8728;
            $host = $router->ip_address;
            if ($host === null) {
                return [];
            }

            if ($this->api->connect($host, $router->api_username ?? '', $router->api_password ?? '')) {
                // Get PPPoE active
                $pppoe = $this->api->comm('/ppp/active/print');
                // Get Hotspot active
                $hotspot = $this->api->comm('/ip/hotspot/active/print');

                $this->api->disconnect();

                $sessions = [];

                foreach ($pppoe as $s) {
                    $sessions[] = [
                        'id' => (string) ($s['.id'] ?? ''),
                        'type' => 'pppoe',
                        'name' => (string) ($s['name'] ?? ''),
                        'address' => (string) ($s['address'] ?? ''),
                        'uptime' => (string) ($s['uptime'] ?? ''),
                        'caller_id' => (string) ($s['caller-id'] ?? ''),
                        'service' => (string) ($s['service'] ?? ''),
                    ];
                }

                foreach ($hotspot as $s) {
                    $sessions[] = [
                        'id' => (string) ($s['.id'] ?? ''),
                        'type' => 'hotspot',
                        'name' => (string) ($s['user'] ?? ''),
                        'address' => (string) ($s['address'] ?? ''),
                        'uptime' => (string) ($s['uptime'] ?? ''),
                        'caller_id' => (string) ($s['mac-address'] ?? ''),
                    ];
                }

                return $sessions;
            }
        } catch (Exception $e) {
            Log::error('RouterService: getActiveSessions Failed', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);
        }

        return [];
    }

    public function disconnectSession(ServiceNode $router, string $type, string $id): bool
    {
        if ($router->connection_method !== 'api') {
            return false;
        }

        try {
            $this->api->port = is_numeric($router->api_port) ? (int) $router->api_port : 8728;
            $host = $router->ip_address;
            if ($host === null) {
                return false;
            }

            if ($this->api->connect($host, $router->api_username ?? '', $router->api_password ?? '')) {
                $path = ($type === 'pppoe') ? '/ppp/active/remove' : '/ip/hotspot/active/remove';
                $this->api->comm($path, ['.id' => $id]);
                $this->api->disconnect();

                return true;
            }
        } catch (Exception $e) {
            Log::error('RouterService: disconnectSession Failed', [
                'router_id' => $router->id,
                'session_id' => $id,
                'error' => $e->getMessage(),
            ]);
        }

        return false;
    }

    /**
     * Suspend a customer on the router.
     */
    public function suspendCustomer(\App\Models\Isp\Customer $customer): bool
    {
        /** @var ServiceNode|null $router */
        $router = ServiceNode::find($customer->router_id);
        if (! $router || $router->connection_method !== 'api') {
            return false;
        }

        $login = $customer->mikrotik_login;
        if (! $login) {
            return false;
        }

        try {
            $this->api->port = is_numeric($router->api_port) ? (int) $router->api_port : 8728;
            $host = $router->ip_address;
            if ($host === null) {
                return false;
            }

            if ($this->api->connect($host, $router->api_username ?? '', $router->api_password ?? '')) {
                // 1. Disable PPPoE Secret
                $pppoe = $this->api->comm('/ppp/secret/print', ['?name' => $login]);
                if (! empty($pppoe[0]['.id'])) {
                    $this->api->comm('/ppp/secret/set', [
                        '.id' => $pppoe[0]['.id'],
                        'disabled' => 'yes',
                    ]);
                }

                // 2. Disable Hotspot User
                $hotspot = $this->api->comm('/ip/hotspot/user/print', ['?name' => $login]);
                if (! empty($hotspot[0]['.id'])) {
                    $this->api->comm('/ip/hotspot/user/set', [
                        '.id' => $hotspot[0]['.id'],
                        'disabled' => 'yes',
                    ]);
                }

                // 3. Kick active session
                $activePpp = $this->api->comm('/ppp/active/print', ['?name' => $login]);
                if (! empty($activePpp[0]['.id'])) {
                    $this->api->comm('/ppp/active/remove', ['.id' => $activePpp[0]['.id']]);
                }

                $activeHs = $this->api->comm('/ip/hotspot/active/print', ['?user' => $login]);
                if (! empty($activeHs[0]['.id'])) {
                    $this->api->comm('/ip/hotspot/active/remove', ['.id' => $activeHs[0]['.id']]);
                }

                $this->api->disconnect();

                return true;
            }
        } catch (Exception $e) {
            Log::error('RouterService: suspendCustomer Failed', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);
        }

        return false;
    }

    /**
     * Reactivate a customer on the router.
     */
    public function reactivateCustomer(\App\Models\Isp\Customer $customer): bool
    {
        /** @var ServiceNode|null $router */
        $router = ServiceNode::find($customer->router_id);
        if (! $router || $router->connection_method !== 'api') {
            return false;
        }

        $login = $customer->mikrotik_login;
        if (! $login) {
            return false;
        }

        try {
            $this->api->port = is_numeric($router->api_port) ? (int) $router->api_port : 8728;
            $host = $router->ip_address;
            if ($host === null) {
                return false;
            }

            if ($this->api->connect($host, $router->api_username ?? '', $router->api_password ?? '')) {
                // 1. Enable PPPoE Secret
                $pppoe = $this->api->comm('/ppp/secret/print', ['?name' => $login]);
                if (! empty($pppoe[0]['.id'])) {
                    $this->api->comm('/ppp/secret/set', [
                        '.id' => $pppoe[0]['.id'],
                        'disabled' => 'no',
                    ]);
                }

                // 2. Enable Hotspot User
                $hotspot = $this->api->comm('/ip/hotspot/user/print', ['?name' => $login]);
                if (! empty($hotspot[0]['.id'])) {
                    $this->api->comm('/ip/hotspot/user/set', [
                        '.id' => $hotspot[0]['.id'],
                        'disabled' => 'no',
                    ]);
                }

                $this->api->disconnect();

                return true;
            }
        } catch (Exception $e) {
            Log::error('RouterService: reactivateCustomer Failed', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);
        }

        return false;
    }

    /**
     * Create a new customer on the router (Provisioning).
     */
    public function createCustomer(\App\Models\Isp\Customer $customer): bool
    {
        /** @var ServiceNode|null $router */
        $router = ServiceNode::find($customer->router_id);
        if (! $router || $router->connection_method !== 'api') {
            return false;
        }

        $login = $customer->mikrotik_login;
        $password = $customer->mikrotik_password;
        if (! $login || ! $password) {
            return false;
        }

        // Determine plan profile (defaults to 'default')
        $profile = $customer->plan ? (string) $customer->plan->mikrotik_group : 'default';

        try {
            $this->api->port = is_numeric($router->api_port) ? (int) $router->api_port : 8728;
            $host = $router->ip_address;
            if ($host === null) {
                return false;
            }

            if ($this->api->connect($host, $router->api_username ?? '', $router->api_password ?? '')) {
                // 1. Create PPPoE Secret
                // Check if exists first to avoid error
                $exists = $this->api->comm('/ppp/secret/print', ['?name' => (string) $login]);
                if (empty($exists)) {
                    $this->api->comm('/ppp/secret/add', [
                        'name' => (string) $login,
                        'password' => (string) $password,
                        'profile' => (string) $profile,
                        'service' => 'pppoe',
                        'comment' => "Created by App for Customer #{$customer->id}",
                    ]);
                }

                // 2. Create Hotspot User (Optional, depends on use case, usually one or the other)
                // For this implementation, we'll Create BOTH or just PPPoE depending on plan type?
                // Let's assume PPPoE is primary for Home, Hotspot for Voucher.
                // But safety first: check uniqueness.
                $hsExists = $this->api->comm('/ip/hotspot/user/print', ['?name' => (string) $login]);
                if (empty($hsExists)) {
                    $this->api->comm('/ip/hotspot/user/add', [
                        'name' => (string) $login,
                        'password' => (string) $password,
                        'profile' => (string) $profile, // Ensure this profile exists in Hotspot too!
                        'comment' => "Created by App for Customer #{$customer->id}",
                    ]);
                }

                $this->api->disconnect();

                Log::info('RouterService: Customer provisioned', ['login' => $login, 'router' => $router->name]);

                return true;
            }
        } catch (Exception $e) {
            Log::error('RouterService: createCustomer Failed', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);
        }

        return false;
    }

    /**
     * Update customer on the router (e.g. password or plan change).
     */
    public function updateCustomer(\App\Models\Isp\Customer $customer): bool
    {
        /** @var ServiceNode|null $router */
        $router = ServiceNode::find($customer->router_id);
        if (! $router || $router->connection_method !== 'api') {
            return false;
        }

        $login = $customer->mikrotik_login;
        $password = $customer->mikrotik_password;
        // If login changed, we might need to remove old and add new, but let's assume login is static for now
        // or handled by delete+create logic in controller if critical.
        // Here we just update properties for the *current* login.

        $profile = $customer->plan ? (string) $customer->plan->mikrotik_group : 'default';

        try {
            $this->api->port = is_numeric($router->api_port) ? (int) $router->api_port : 8728;
            $host = $router->ip_address;
            if ($host === null) {
                return false;
            }

            if ($this->api->connect($host, $router->api_username ?? '', $router->api_password ?? '')) {
                // 1. Update PPPoE
                $pppoe = $this->api->comm('/ppp/secret/print', ['?name' => (string) $login]);
                if (! empty($pppoe[0]['.id'])) {
                    $payload = ['.id' => (string) $pppoe[0]['.id']];
                    if ($password) {
                        $payload['password'] = $password;
                    }
                    if ($profile) {
                        $payload['profile'] = $profile;
                    }

                    $this->api->comm('/ppp/secret/set', $payload);
                }

                // 2. Update Hotspot
                $hotspot = $this->api->comm('/ip/hotspot/user/print', ['?name' => (string) $login]);
                if (! empty($hotspot[0]['.id'])) {
                    $payload = ['.id' => (string) $hotspot[0]['.id']];
                    if ($password) {
                        $payload['password'] = $password;
                    }
                    // Hotspot profile might be different from PPP profile, usually.
                    // Assuming same name for simplicity or ignored if not set.
                    if ($profile) {
                        $payload['profile'] = $profile;
                    }

                    $this->api->comm('/ip/hotspot/user/set', $payload);
                }

                // If profile changed, we should probably kick the user to apply new limits
                if ($customer->isDirty('billing_plan_id')) {
                    $activePpp = $this->api->comm('/ppp/active/print', ['?name' => (string) $login]);
                    if (! empty($activePpp[0]['.id'])) {
                        $this->api->comm('/ppp/active/remove', ['.id' => (string) $activePpp[0]['.id']]);
                    }
                }

                $this->api->disconnect();

                return true;
            }
        } catch (Exception $e) {
            Log::error('RouterService: updateCustomer Failed', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);
        }

        return false;
    }

    /**
     * Delete customer from the router.
     */
    public function deleteCustomer(\App\Models\Isp\Customer $customer): bool
    {
        // ... (existing delete logic) ...
        /** @var ServiceNode|null $router */
        $router = ServiceNode::find($customer->router_id);
        if (! $router || $router->connection_method !== 'api') {
            return false;
        }

        $login = $customer->mikrotik_login;
        if (! $login) {
            return false;
        }

        try {
            $this->api->port = is_numeric($router->api_port) ? (int) $router->api_port : 8728;
            $host = $router->ip_address;
            if ($host === null) {
                return false;
            }

            if ($this->api->connect($host, $router->api_username ?? '', $router->api_password ?? '')) {
                // 1. Remove PPPoE Secret
                $pppoe = $this->api->comm('/ppp/secret/print', ['?name' => (string) $login]);
                if (! empty($pppoe[0]['.id'])) {
                    $this->api->comm('/ppp/secret/remove', ['.id' => (string) $pppoe[0]['.id']]);
                }

                // 2. Remove Hotspot User
                $hotspot = $this->api->comm('/ip/hotspot/user/print', ['?name' => (string) $login]);
                if (! empty($hotspot[0]['.id'])) {
                    $this->api->comm('/ip/hotspot/user/remove', ['.id' => (string) $hotspot[0]['.id']]);
                }

                // 3. Remove Active Sessions
                $activePpp = $this->api->comm('/ppp/active/print', ['?name' => (string) $login]);
                if (! empty($activePpp[0]['.id'])) {
                    $this->api->comm('/ppp/active/remove', ['.id' => (string) $activePpp[0]['.id']]);
                }

                $this->api->disconnect();

                Log::info('RouterService: Customer removed from router', ['login' => $login]);

                return true;
            }
        } catch (Exception $e) {
            Log::error('RouterService: deleteCustomer Failed', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);
        }

        return false;
    }

    /**
     * Sync customer state to router (Upsert).
     */
    public function syncCustomer(\App\Models\Isp\Customer $customer): bool
    {
        /** @var ServiceNode|null $router */
        $router = ServiceNode::find($customer->router_id);
        if (! $router || $router->connection_method !== 'api') {
            return false;
        }

        $login = $customer->mikrotik_login;
        $password = $customer->mikrotik_password;
        if (! $login || ! $password) {
            return false;
        }

        $profile = $customer->plan ? (string) $customer->plan->mikrotik_group : 'default';

        try {
            $this->api->port = is_numeric($router->api_port) ? (int) $router->api_port : 8728;
            $host = $router->ip_address;
            if ($host === null) {
                return false;
            }

            if ($this->api->connect($host, $router->api_username ?? '', $router->api_password ?? '')) {
                // PPPoE
                $pppoe = $this->api->comm('/ppp/secret/print', ['?name' => (string) $login]);
                if (empty($pppoe)) {
                    // Create
                    $this->api->comm('/ppp/secret/add', [
                        'name' => (string) $login,
                        'password' => (string) $password,
                        'profile' => (string) $profile,
                        'service' => 'pppoe',
                        'comment' => "Created by App for Customer #{$customer->id}",
                    ]);
                } else {
                    // Update
                    $this->api->comm('/ppp/secret/set', [
                        '.id' => (string) $pppoe[0]['.id'],
                        'password' => (string) $password,
                        'profile' => (string) $profile,
                    ]);
                }

                // Hotspot
                $hotspot = $this->api->comm('/ip/hotspot/user/print', ['?name' => (string) $login]);
                if (empty($hotspot)) {
                    // Create
                    $this->api->comm('/ip/hotspot/user/add', [
                        'name' => (string) $login,
                        'password' => (string) $password,
                        'profile' => (string) $profile,
                        'comment' => "Created by App for Customer #{$customer->id}",
                    ]);
                } else {
                    // Update
                    $this->api->comm('/ip/hotspot/user/set', [
                        '.id' => (string) $hotspot[0]['.id'],
                        'password' => (string) $password,
                        'profile' => (string) $profile,
                    ]);
                }

                $this->api->disconnect();

                return true;
            }
        } catch (Exception $e) {
            Log::error('RouterService: syncCustomer Failed', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);
        }

        return false;
    }
}
