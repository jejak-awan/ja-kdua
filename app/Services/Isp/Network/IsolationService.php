<?php

declare(strict_types=1);

namespace App\Services\Isp\Network;

use App\Models\Isp\Customer\Customer;
use App\Models\Isp\Network\ServiceNode;
use Illuminate\Support\Facades\Log;

class IsolationService
{
    protected \App\Services\Isp\Network\RadiusService $radius;

    protected \App\Services\Isp\Network\RouterService $routerService;

    protected \App\Services\Isp\Network\MikrotikService $mikrotik;

    public function __construct(
        \App\Services\Isp\Network\RadiusService $radius,
        \App\Services\Isp\Network\RouterService $routerService,
        \App\Services\Isp\Network\MikrotikService $mikrotik
    ) {
        $this->radius = $radius;
        $this->routerService = $routerService;
        $this->mikrotik = $mikrotik;
    }

    /**
     * Isolate a customer (Smart Suspension).
     */
    public function isolate(Customer $customer): bool
    {
        if (! $customer->mikrotik_login) {
            return false;
        }

        Log::info("IsolationService: Isolating customer {$customer->mikrotik_login}");

        // 1. Update Radius with Address-List attribute
        // This ensures that when the user reconnects, they are in the SUSPENDED list
        $this->radius->syncUser($customer->mikrotik_login, (string) $customer->mikrotik_password, [
            'Mikrotik-Group' => 'suspended',
            'Filter-Id' => 'suspended',
            'Mikrotik-Address-List' => 'SUSPENDED_LIST',
        ]);

        // 2. Immediate API-based isolation if IP is known
        $router = ServiceNode::find($customer->router_id);
        if ($router) {
            $session = $this->routerService->findActiveSessionByLogin($router, $customer->mikrotik_login);
            if ($session && ! empty($session['address'])) {
                $this->mikrotik->setAddressList($router, $session['address'], 'SUSPENDED_LIST', true, 'Suspended: '.$customer->mikrotik_login);
            }

            // 3. Disconnect to force attribute refresh or apply walled garden
            $this->radius->sendDisconnectRequest($customer);
        }

        return true;
    }

    /**
     * Restore a customer (Unisolate).
     */
    public function restore(Customer $customer): bool
    {
        if (! $customer->mikrotik_login) {
            return false;
        }

        Log::info("IsolationService: Restoring customer {$customer->mikrotik_login}");

        // 1. Restore Radius profile (Remove Address-List)
        $rateLimit = $customer->plan->rate_limit ?? '10M/10M';
        $this->radius->syncUser($customer->mikrotik_login, (string) $customer->mikrotik_password, [
            'Mikrotik-Rate-Limit' => $rateLimit,
        ]);

        // 2. Remove from API Address-List if IP is known
        $router = ServiceNode::find($customer->router_id);
        if ($router) {
            $session = $this->routerService->findActiveSessionByLogin($router, $customer->mikrotik_login);
            if ($session && ! empty($session['address'])) {
                $this->mikrotik->setAddressList($router, $session['address'], 'SUSPENDED_LIST', false);
            }

            // 3. Disconnect to force attribute refresh
            $this->radius->sendDisconnectRequest($customer);
        }

        return true;
    }
}
