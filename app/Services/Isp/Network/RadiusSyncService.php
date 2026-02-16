<?php

declare(strict_types=1);

namespace App\Services\Isp\Network;

use App\Models\Isp\Customer\Customer;
use App\Models\Isp\Network\ServiceNode;
use Illuminate\Support\Facades\Log;

/**
 * Handles high-level logic for Panel <-> Radius synchronization.
 * Uses \App\Services\Isp\Network\RadiusService for low-level DB operations.
 */
class RadiusSyncService
{
    protected \App\Services\Isp\Network\RadiusService $radius;

    public function __construct(\App\Services\Isp\Network\RadiusService $radius)
    {
        $this->radius = $radius;
    }

    /**
     * Synchronize a ServiceNode (Router) as a NAS in Radius.
     */
    public function syncNode(ServiceNode $node): void
    {
        if ($node->type !== 'Router') {
            return;
        }

        Log::debug("RadiusSyncService: Triggering sync for node {$node->name}");
        $this->radius->syncNas($node);
    }

    /**
     * Synchronize a Customer and their plan attributes to Radius.
     */
    public function syncCustomer(Customer $customer): void
    {
        if (! $customer->mikrotik_login || ! $customer->mikrotik_password) {
            Log::warning("RadiusSyncService: Skipping customer #{$customer->id} due to missing credentials");

            return;
        }

        $plan = $customer->plan;
        $replyAttributes = [];
        $checkAttributes = [];

        if ($plan) {
            // Standard Mikrotik Rate Limit (or customized per customer if implemented)
            $rateLimit = $customer->is_fup_active && $plan->fup_speed
                ? $plan->fup_speed
                : ($plan->mikrotik_rate_limit ?: '10M/10M');

            $replyAttributes['Mikrotik-Rate-Limit'] = $rateLimit;
        }

        // If customer is suspended, we can either remove them or point to a suspended profile/address-list
        if ($customer->status === 'suspended') {
            $replyAttributes['Mikrotik-Address-List'] = 'SUSPENDED';
        }

        $this->radius->syncUser(
            $customer->mikrotik_login,
            $customer->mikrotik_password,
            $replyAttributes,
            $checkAttributes
        );
    }

    /**
     * Remove a node from Radius.
     */
    public function removeNode(ServiceNode $node): void
    {
        $this->radius->removeNas($node);
    }

    /**
     * Remove a customer from Radius.
     */
    public function removeCustomer(Customer $customer): void
    {
        if ($customer->mikrotik_login) {
            $this->radius->removeUser($customer->mikrotik_login);
        }
    }

    /**
     * Perform a full reconstruction of all active NAS entries.
     */
    public function reconstructNas(): int
    {
        $nodes = ServiceNode::where('type', 'Router')->where('radius_enabled', true)->get();
        $count = 0;

        foreach ($nodes as $node) {
            $this->syncNode($node);
            $count++;
        }

        return $count;
    }
}
