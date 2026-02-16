<?php

declare(strict_types=1);

namespace App\Services\Isp\Network;

use App\Models\Isp\Network\ServiceNode;

class RouterService
{
    protected RouterMonitoringService $monitoringService;

    protected RouterProvisioningService $provisioningService;

    public function __construct(
        RouterMonitoringService $monitoringService,
        RouterProvisioningService $provisioningService
    ) {
        $this->monitoringService = $monitoringService;
        $this->provisioningService = $provisioningService;
    }

    // --- Delegation to Provisioning Service ---

    public function syncToRadius(ServiceNode $router): void
    {
        $this->provisioningService->syncToRadius($router);
    }

    public function suspendCustomer(\App\Models\Isp\Customer\Customer $customer): bool
    {
        return $this->provisioningService->suspendCustomer($customer);
    }

    public function reactivateCustomer(\App\Models\Isp\Customer\Customer $customer): bool
    {
        return $this->provisioningService->reactivateCustomer($customer);
    }

    public function createCustomer(\App\Models\Isp\Customer\Customer $customer): bool
    {
        return $this->provisioningService->createCustomer($customer);
    }

    public function updateCustomer(\App\Models\Isp\Customer\Customer $customer): bool
    {
        return $this->provisioningService->updateCustomer($customer);
    }

    public function deleteCustomer(\App\Models\Isp\Customer\Customer $customer): bool
    {
        return $this->provisioningService->deleteCustomer($customer);
    }

    public function syncCustomer(\App\Models\Isp\Customer\Customer $customer): bool
    {
        return $this->provisioningService->syncCustomer($customer);
    }

    /**
     * @return array<int, array{id: string, name: string, service: string, profile: string, remoteaddress: string, routes: string, comment: string}>
     */
    public function getVpnSecrets(ServiceNode $router): array
    {
        return $this->provisioningService->getVpnSecrets($router);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createVpnSecret(ServiceNode $router, array $data): bool
    {
        return $this->provisioningService->createVpnSecret($router, $data);
    }

    public function deleteVpnSecret(ServiceNode $router, string $id): bool
    {
        return $this->provisioningService->deleteVpnSecret($router, $id);
    }

    public function disconnectSession(ServiceNode $router, string $type, string $id): bool
    {
        return $this->provisioningService->disconnectSession($router, $type, $id);
    }

    // --- Delegation to Monitoring Service ---

    /**
     * @return array{is_connected: bool, active_count: int}
     */
    public function getDetailedStatus(ServiceNode $router): array
    {
        return $this->monitoringService->getDetailedStatus($router);
    }

    public function checkPingConnection(ServiceNode $router): bool
    {
        return $this->monitoringService->checkPingConnection($router);
    }

    public function checkSnmpConnection(ServiceNode $router): bool
    {
        return $this->monitoringService->checkSnmpConnection($router);
    }

    public function checkConnectivity(ServiceNode $router): bool
    {
        return $this->monitoringService->getDetailedStatus($router)['is_connected'];
    }

    public function getActiveClientCount(ServiceNode $router): int
    {
        return $this->monitoringService->getMonitoredStats($router)['active_count'];
    }

    /**
     * @return array{cpu: int, memory_free: int, memory_total: int, uptime: string}|null
     */
    public function getSystemResource(ServiceNode $router): ?array
    {
        return $this->monitoringService->getSystemResource($router);
    }

    /**
     * @return array{rx: int, tx: int}|null
     */
    public function getInterfaceTraffic(ServiceNode $router, string $interface = 'ether1'): ?array
    {
        return $this->monitoringService->getInterfaceTraffic($router, $interface);
    }

    /**
     * @return array<int, array{name: string, type: string, running: bool, disabled: bool}>
     */
    public function getInterfaces(ServiceNode $router): array
    {
        return $this->monitoringService->getInterfaces($router);
    }

    /**
     * @return array{name: string, status: string, speed: string, duplex: string, running: bool}|null
     */
    public function getInterfaceStatus(ServiceNode $router, string $interface = 'ether1'): ?array
    {
        return $this->monitoringService->getInterfaceStatus($router, $interface);
    }

    /**
     * @return array{resource?: array{cpu: int, memory_free: int, memory_total: int, uptime: string}, traffic?: array{rx: int, tx: int}, active_count: int}
     */
    public function getMonitoredStats(ServiceNode $router): array
    {
        return $this->monitoringService->getMonitoredStats($router);
    }

    /**
     * @return array{id: string, type: string, name: string, address: string, uptime: string, caller_id: string, service?: string}|null
     */
    public function findActiveSessionByLogin(ServiceNode $router, string $login): ?array
    {
        return $this->monitoringService->findActiveSessionByLogin($router, $login);
    }

    /**
     * @return array<int, array{id: string, type: string, name: string, address: string, uptime: string, caller_id: string, service?: string}>
     */
    public function getActiveSessions(ServiceNode $router): array
    {
        return $this->monitoringService->getActiveSessions($router);
    }

    /**
     * @return array{success: bool, latency: float|null}
     */
    public function pingExternal(ServiceNode $router, string $address = '8.8.8.8', int $count = 3): array
    {
        return $this->monitoringService->pingExternal($router, $address, $count);
    }
}
