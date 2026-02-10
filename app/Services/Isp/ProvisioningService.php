<?php

declare(strict_types=1);

namespace App\Services\Isp;

use App\Models\Isp\CustomerDevice;
use App\Models\Isp\ServiceRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProvisioningService
{
    protected OltService $oltService;

    protected RadiusIntegration $radius;

    public function __construct(OltService $oltService, RadiusIntegration $radius)
    {
        $this->oltService = $oltService;
        $this->radius = $radius;
    }

    /**
     * Execute the provisioning logic based on a service request.
     */
    public function executeRequest(ServiceRequest $serviceRequest): bool
    {
        try {
            $customer = $serviceRequest->customer;
            $user = $customer->user;

            Log::info("Executing provisioning for Request ID: {$serviceRequest->id}", [
                'type' => $serviceRequest->type,
                'customer' => $user->name,
            ]);

            // 1. Update Radius profile
            if ($customer->mikrotik_login && $customer->mikrotik_password) {
                /** @var string $rateLimit */
                $rateLimit = $serviceRequest->details['rate_limit'] ?? '10M/10M';
                $this->radius->syncUser($customer->mikrotik_login, $customer->mikrotik_password, [
                    'Mikrotik-Rate-Limit' => $rateLimit,
                ]);
            }

            // 2. Register ONU on OLT if applicable
            $device = CustomerDevice::where('customer_id', $customer->id)->where('type', 'ONU')->first();
            if ($device && $customer->olt_id) {
                $olt = \App\Models\Isp\Olt::findOrFail($customer->olt_id);
                $this->oltService->registerOnu($olt, $device->serial_number, [
                    'vlan' => $customer->vlan ?? 100, // This might still be missing on model, added to docblock
                    'profile' => $serviceRequest->details['olt_profile'] ?? 'BASIC',
                ]);
            }

            // 3. Update associated device lifecycle if applicable
            if ($device) {
                if ($serviceRequest->type === 'Upgrade' || $serviceRequest->type === 'Activation') {
                    $device->update([
                        'activated_at' => Carbon::now(),
                        'status' => 'active',
                    ]);
                } elseif ($serviceRequest->type === 'Cancellation') {
                    $device->update([
                        'status' => 'inactive',
                        'expiration_date' => Carbon::now(),
                    ]);

                    if ($customer->mikrotik_login) {
                        $this->radius->removeUser($customer->mikrotik_login);
                    }
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Provisioning Error: '.$e->getMessage());

            return false;
        }
    }
}
