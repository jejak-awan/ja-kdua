<?php

declare(strict_types=1);

namespace App\Services\Isp\Network;

use App\Models\Isp\Network\ServiceNode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * The unified authority for infrastructure node lifecycle management.
 * Orchestrates IPAM, Radius Sync, and Network Driver interactions.
 */
class InfraNodeService
{
    protected \App\Services\Isp\Network\IpamService $ipam;

    protected \App\Services\Isp\Network\RadiusSyncService $radiusSync;

    protected NetworkDriverService $networkDriver;

    public function __construct(
        \App\Services\Isp\Network\IpamService $ipam,
        \App\Services\Isp\Network\RadiusSyncService $radiusSync,
        NetworkDriverService $networkDriver
    ) {
        $this->ipam = $ipam;
        $this->radiusSync = $radiusSync;
        $this->networkDriver = $networkDriver;
    }

    /**
     * Create a new infrastructure node with integrated IPAM and Radius sync.
     *
     * @param  array<string, mixed>  $data
     */
    public function createNode(array $data): ServiceNode
    {
        return DB::transaction(function () use ($data) {
            // 1. IPAM Auto-allocation
            if (empty($data['ip_address'])) {
                $ip = $this->ipam->getNextAvailableSubnet();
                if (! $ip) {
                    throw new \RuntimeException('No available IP addresses in the local network pool.');
                }
                $data['ip_address'] = $ip;
            }

            // 2. Persist Node
            $node = ServiceNode::create($data);

            // 3. Radius Sync (if applicable)
            if ($node->type === 'Router' && $node->radius_enabled) {
                $this->radiusSync->syncNode($node);
            }

            Log::info("InfraNodeService: Created {$node->type} node '{$node->name}' at {$node->ip_address}");

            return $node;
        });
    }

    /**
     * Update an existing infrastructure node.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateNode(ServiceNode $node, array $data): ServiceNode
    {
        return DB::transaction(function () use ($node, $data) {
            $oldIp = $node->ip_address;
            $oldRadiusEnabled = $node->radius_enabled;

            // 1. IPAM Update (if IP is being cleared)
            if (array_key_exists('ip_address', $data) && empty($data['ip_address'])) {
                $ip = $this->ipam->getNextAvailableSubnet();
                if (! $ip) {
                    throw new \RuntimeException('No available IP addresses in the local network pool.');
                }
                $data['ip_address'] = $ip;
            }

            // 2. Update Node
            $node->update($data);

            // 3. Radius Sync Management
            if ($node->type === 'Router') {
                // If IP changed or radius was toggled, we might need to remove old/add new NAS
                if ($oldIp !== $node->ip_address || $oldRadiusEnabled !== $node->radius_enabled) {
                    if ($oldIp && $oldRadiusEnabled) {
                        // Create a temporary mock node for removal if IP changed
                        $mockNode = clone $node;
                        $mockNode->ip_address = $oldIp;
                        $this->radiusSync->removeNode($mockNode);
                    }

                    if ($node->radius_enabled) {
                        $this->radiusSync->syncNode($node);
                    }
                } elseif ($node->radius_enabled) {
                    // Just update existing NAS info
                    $this->radiusSync->syncNode($node);
                }
            }

            Log::info("InfraNodeService: Updated {$node->type} node '{$node->name}'");

            return $node;
        });
    }

    /**
     * Delete an infrastructure node.
     */
    public function deleteNode(ServiceNode $node): bool
    {
        return DB::transaction(function () use ($node) {
            // 1. Radius Cleanup
            if ($node->type === 'Router' && $node->radius_enabled) {
                $this->radiusSync->removeNode($node);
            }

            // 2. Soft Delete
            Log::info("InfraNodeService: Deleting {$node->type} node '{$node->name}'");

            return (bool) $node->delete();
        });
    }

    /**
     * Test connection to the node.
     */
    public function testConnection(ServiceNode $node): bool
    {
        return $this->networkDriver->testConnection($node);
    }
}
