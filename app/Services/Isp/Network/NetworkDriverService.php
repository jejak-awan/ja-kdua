<?php

declare(strict_types=1);

namespace App\Services\Isp\Network;

use App\Models\Isp\Network\ServiceNode;
use Illuminate\Support\Facades\Log;

/**
 * The Master Factory for all Network Hardware communication.
 * Provides a unified interface to resolve MikroTik, OLT, and other vendor drivers.
 */
class NetworkDriverService
{
    protected \App\Services\Isp\Network\OltService $oltService;

    protected \App\Services\Isp\Network\MikrotikService $mikrotikService;

    public function __construct(\App\Services\Isp\Network\OltService $oltService, \App\Services\Isp\Network\MikrotikService $mikrotikService)
    {
        $this->oltService = $oltService;
        $this->mikrotikService = $mikrotikService;
    }

    /**
     * Resolve the appropriate driver/service for a ServiceNode.
     *
     * @return \App\Services\Isp\Network\Drivers\OltDriver|MikrotikService
     */
    public function getDriver(ServiceNode $node)
    {
        if ($node->type === 'Router') {
            return $this->mikrotikService;
        }

        if ($node->type === 'OLT') {
            return $this->oltService->getDriver($node);
        }

        throw new \InvalidArgumentException("No driver found for node type: {$node->type}");
    }

    /**
     * Unified connection test for any network node.
     */
    public function testConnection(ServiceNode $node): bool
    {
        try {
            if ($node->type === 'Router') {
                $result = $this->mikrotikService->testConnection($node);

                return $result['success'];
            }

            if ($node->type === 'OLT') {
                return $this->oltService->testConnection($node);
            }
        } catch (\Exception $e) {
            Log::error("NetworkDriverService: Connection test failed for {$node->name} ({$node->type}): ".$e->getMessage());
        }

        return false;
    }
}
