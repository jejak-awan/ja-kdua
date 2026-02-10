<?php

declare(strict_types=1);

namespace App\Observers\Isp;

use App\Models\Isp\Customer;
use App\Models\Isp\IpPool;
use App\Models\Isp\IpPoolAddress;
use App\Services\Isp\RadiusIntegration;
use Illuminate\Support\Facades\Log;

class CustomerObserver
{
    protected RadiusIntegration $radius;

    public function __construct(RadiusIntegration $radius)
    {
        $this->radius = $radius;
    }

    /**
     * Handle the Customer "saved" event.
     */
    public function saved(Customer $customer): void
    {
        if (! $customer->mikrotik_login || ! $customer->mikrotik_password) {
            return;
        }

        $attributes = [];

        // Add rate limit if plan exists
        if ($customer->plan && $customer->plan->mikrotik_rate_limit) {
            $attributes['Mikrotik-Rate-Limit'] = $customer->plan->mikrotik_rate_limit;
        }

        // Handle IP assignment for active customers
        if ($customer->status === 'active') {
            $assignedIp = $this->ensureIpAssigned($customer);
            if ($assignedIp) {
                $attributes['Framed-IP-Address'] = $assignedIp;
            }
        } elseif ($customer->status === 'suspended') {
            // Set isolation group for suspended customers
            $attributes['Mikrotik-Group'] = 'ISOLATED';
        } elseif (in_array($customer->status, ['inactive', 'terminated'])) {
            // Release IP when customer is deactivated
            $this->releaseCustomerIp($customer);
        }

        // Handle suspension
        $password = (string) $customer->mikrotik_password;
        $login = (string) $customer->mikrotik_login;

        $this->radius->syncUser($login, $password, $attributes);

        // If critical attributes changed, trigger CoA to force re-auth
        if ($customer->isDirty(['status', 'billing_plan_id', 'mikrotik_password'])) {
            Log::info("CustomerObserver: Triggering CoA for #{$customer->id} due to change in status/plan/password");
            $this->radius->sendDisconnectRequest($customer);
        }
    }

    /**
     * Ensure the customer has an assigned IP address.
     */
    protected function ensureIpAssigned(Customer $customer): ?string
    {
        // Check if customer already has an assigned IP
        $existingAssignment = IpPoolAddress::where('customer_id', $customer->id)
            ->where('status', 'assigned')
            ->first();

        if ($existingAssignment) {
            return $existingAssignment->ip_address;
        }

        // Try to assign from an active pool
        $pool = IpPool::where('status', 'active')
            ->whereHas('availableAddresses')
            ->first();

        if (! $pool) {
            Log::warning("CustomerObserver: No available IP pools for customer #{$customer->id}");

            return null;
        }

        $address = $pool->assignToCustomer($customer);
        if ($address) {
            Log::info("CustomerObserver: Assigned IP {$address->ip_address} to customer #{$customer->id}");

            return $address->ip_address;
        }

        return null;
    }

    /**
     * Release customer's assigned IP back to pool.
     */
    protected function releaseCustomerIp(Customer $customer): void
    {
        $assignments = IpPoolAddress::where('customer_id', $customer->id)
            ->where('status', 'assigned')
            ->get();

        foreach ($assignments as $assignment) {
            $assignment->release();
            Log::info("CustomerObserver: Released IP {$assignment->ip_address} from customer #{$customer->id}");
        }
    }

    /**
     * Handle the Customer "deleted" event.
     */
    public function deleted(Customer $customer): void
    {
        if ($customer->mikrotik_login) {
            // Release any assigned IPs first
            $this->releaseCustomerIp($customer);
            $this->radius->removeUser((string) $customer->mikrotik_login);
        }
    }
}
