<?php

declare(strict_types=1);

namespace App\Services\Isp\Network;

use App\Models\Isp\Network\ServiceNode;
use Illuminate\Support\Facades\Log;

class RouterProvisioningService
{
    protected MikrotikClient $api;

    protected \App\Services\Isp\Network\RadiusSyncService $radiusSync;

    protected \App\Services\Isp\Network\RadiusService $radius;

    public function __construct(
        MikrotikClient $api,
        \App\Services\Isp\Network\RadiusSyncService $radiusSync,
        \App\Services\Isp\Network\RadiusService $radius
    ) {
        $this->api = $api;
        $this->radiusSync = $radiusSync;
        $this->radius = $radius;
    }

    public function syncToRadius(ServiceNode $router): void
    {
        $this->radiusSync->syncNode($router);
    }

    public function disconnectSession(ServiceNode $router, string $type, string $id): bool
    {
        $host = $router->ip_address;
        if (! $host) {
            return false;
        }

        try {
            if ($this->api->connect($host, $router->api_username ?? '', $router->api_password ?? '')) {
                $path = ($type === 'pppoe') ? '/ppp/active/remove' : '/ip/hotspot/active/remove';
                $this->api->comm($path, ['.id' => $id]);
                $this->api->disconnect();

                return true;
            }
        } catch (\Exception $e) {
            Log::error('RouterProvisioningService: disconnectSession Failed: '.$e->getMessage());
        }

        return false;
    }

    public function suspendCustomer(\App\Models\Isp\Customer\Customer $customer): bool
    {
        if (! $customer->router_id) {
            return false;
        }
        $router = ServiceNode::find($customer->router_id);
        if (! $router || ! $customer->mikrotik_login) {
            return false;
        }

        $this->radius->syncUser($customer->mikrotik_login, (string) $customer->mikrotik_password, [
            'Mikrotik-Group' => 'suspended',
            'Filter-Id' => 'suspended',
        ]);

        $host = $router->ip_address;
        if ($host) {
            try {
                if ($this->api->connect($host, $router->api_username ?? '', $router->api_password ?? '')) {
                    $this->api->comm('/ppp/active/remove', ['.id' => $customer->mikrotik_login]);
                    $this->api->comm('/ip/hotspot/active/remove', ['.id' => $customer->mikrotik_login]);
                    $this->api->disconnect();
                }
            } catch (\Exception $e) {
                Log::warning("RouterProvisioningService: Could not disconnect customer {$customer->mikrotik_login} during suspension.");
            }
        }

        return true;
    }

    public function reactivateCustomer(\App\Models\Isp\Customer\Customer $customer): bool
    {
        if (! $customer->router_id) {
            return false;
        }
        $router = ServiceNode::find($customer->router_id);
        if (! $router || ! $customer->mikrotik_login) {
            return false;
        }

        $rateLimit = $customer->plan->rate_limit ?? '10M/10M';
        $this->radius->syncUser($customer->mikrotik_login, (string) $customer->mikrotik_password, [
            'Mikrotik-Rate-Limit' => $rateLimit,
        ]);

        return true;
    }

    public function createCustomer(\App\Models\Isp\Customer\Customer $customer): bool
    {
        return $this->syncCustomer($customer);
    }

    public function updateCustomer(\App\Models\Isp\Customer\Customer $customer): bool
    {
        return $this->syncCustomer($customer);
    }

    public function deleteCustomer(\App\Models\Isp\Customer\Customer $customer): bool
    {
        $this->radiusSync->removeCustomer($customer);

        return true;
    }

    public function syncCustomer(\App\Models\Isp\Customer\Customer $customer): bool
    {
        if ($customer->mikrotik_login) {
            $rateLimit = $customer->plan->rate_limit ?? '10M/10M';
            $this->radius->syncUser($customer->mikrotik_login, (string) $customer->mikrotik_password, [
                'Mikrotik-Rate-Limit' => $rateLimit,
            ]);
        }

        return true;
    }

    /**
     * @return array<int, array{id: string, name: string, service: string, profile: string, remoteaddress: string, routes: string, comment: string}>
     */
    public function getVpnSecrets(ServiceNode $router): array
    {
        $secrets = [];
        $host = $router->ip_address;
        if (! $host) {
            return $secrets;
        }

        try {
            if ($this->api->connect($host, $router->api_username ?? '', $router->api_password ?? '')) {
                /** @var array<int, array<string, string>> $res */
                $res = $this->api->comm('/ppp/secret/print');
                foreach ($res as $item) {
                    $secrets[] = [
                        'id' => (string) ($item['.id'] ?? ''),
                        'name' => (string) ($item['name'] ?? ''),
                        'service' => (string) ($item['service'] ?? 'any'),
                        'profile' => (string) ($item['profile'] ?? 'default'),
                        'remoteaddress' => (string) ($item['remote-address'] ?? ''),
                        'routes' => (string) ($item['routes'] ?? ''),
                        'comment' => (string) ($item['comment'] ?? ''),
                    ];
                }
                $this->api->disconnect();
            }
        } catch (\Exception $e) {
            Log::error('RouterProvisioningService: getVpnSecrets Failed: '.$e->getMessage());
        }

        return $secrets;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createVpnSecret(ServiceNode $router, array $data): bool
    {
        $host = $router->ip_address;
        if (! $host) {
            return false;
        }

        try {
            if ($this->api->connect($host, $router->api_username ?? '', $router->api_password ?? '')) {
                $name = (isset($data['name']) && is_string($data['name'])) ? $data['name'] : '';
                if ($name === '') {
                    return false;
                }

                /** @var array<int, array<string, string>> $existing */
                $existing = $this->api->comm('/ppp/secret/print', ['?name' => $name]);

                $params = [
                    'name' => $name,
                    'password' => (isset($data['password']) && is_string($data['password'])) ? $data['password'] : '',
                    'service' => (isset($data['service']) && is_string($data['service'])) ? $data['service'] : 'any',
                    'profile' => (isset($data['profile']) && is_string($data['profile'])) ? $data['profile'] : 'default',
                ];

                if (isset($data['remote_address']) && is_string($data['remote_address'])) {
                    $params['remote-address'] = $data['remote_address'];
                }
                if (isset($data['comment']) && is_string($data['comment'])) {
                    $params['comment'] = $data['comment'];
                }
                if (isset($data['routes']) && is_string($data['routes'])) {
                    $params['routes'] = $data['routes'];
                }

                if (! empty($existing)) {
                    $this->api->comm('/ppp/secret/set', array_merge(['.id' => (string) ($existing[0]['.id'] ?? '')], $params));
                } else {
                    $this->api->comm('/ppp/secret/add', $params);
                }

                $this->api->disconnect();

                return true;
            }
        } catch (\Exception $e) {
            Log::error('RouterProvisioningService: createVpnSecret Failed: '.$e->getMessage());
        }

        return false;
    }

    public function deleteVpnSecret(ServiceNode $router, string $id): bool
    {
        $host = $router->ip_address;
        if (! $host) {
            return false;
        }

        try {
            if ($this->api->connect($host, $router->api_username ?? '', $router->api_password ?? '')) {
                $this->api->comm('/ppp/secret/remove', ['.id' => $id]);
                $this->api->disconnect();

                return true;
            }
        } catch (\Exception $e) {
            Log::error('RouterProvisioningService: deleteVpnSecret Failed: '.$e->getMessage());
        }

        return false;
    }
}
