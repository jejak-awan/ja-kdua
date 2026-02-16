<?php

declare(strict_types=1);

namespace App\Jobs\Isp;

use App\Events\Isp\ProvisioningUpdate;
use App\Models\Isp\Customer\CustomerDevice;
use App\Models\Isp\Network\ServiceNode;
use App\Models\Isp\Support\ServiceRequest;
use App\Services\Isp\Network\OltService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessProvisioningJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ServiceRequest $serviceRequest;

    /**
     * Create a new job instance.
     */
    public function __construct(ServiceRequest $serviceRequest)
    {
        $this->serviceRequest = $serviceRequest;
    }

    /**
     * Execute the job.
     */
    public function handle(OltService $oltService, \App\Services\Isp\Network\RadiusService $radius): void
    {
        $request = $this->serviceRequest;
        $customer = $request->customer;
        
        try {
            // STEP 1: Radius Synchronization
            event(new ProvisioningUpdate($request, 'RADIUS', 'processing', 'Synchronizing user with RADIUS server...'));
            
            if ($customer->mikrotik_login && $customer->mikrotik_password) {
                $rateLimit = is_string($request->details['rate_limit'] ?? null) ? $request->details['rate_limit'] : '10M/10M';
                $radius->syncUser($customer->mikrotik_login, $customer->mikrotik_password, [
                    'Mikrotik-Rate-Limit' => $rateLimit,
                ]);
            }
            
            event(new ProvisioningUpdate($request, 'RADIUS', 'success', 'RADIUS synchronization complete.'));

            // STEP 2: OLT Registration
            event(new ProvisioningUpdate($request, 'OLT', 'processing', 'Registering ONU on OLT...'));
            
            $device = CustomerDevice::where('customer_id', $customer->id)->where('type', 'ONU')->first();
            if ($device && $customer->olt_id) {
                $olt = ServiceNode::findOrFail($customer->olt_id);
                $oltProfile = is_string($request->details['olt_profile'] ?? null) ? $request->details['olt_profile'] : 'BASIC';
                $regSuccess = $oltService->registerOnu($olt, (string) $device->serial_number, [
                    'vlan' => (int) ($customer->vlan ?? 100),
                    'profile' => $oltProfile,
                ]);
                
                if (!$regSuccess) {
                    throw new \Exception('OLT Registration failed on the device.');
                }
            }
            
            event(new ProvisioningUpdate($request, 'OLT', 'success', 'ONU registration complete.'));

            // STEP 3: Database & Lifecycle
            event(new ProvisioningUpdate($request, 'LIFECYCLE', 'processing', 'Updating device lifecycle status...'));
            
            DB::transaction(function () use ($request, $customer, $device, $radius) {
                if ($device && ($request->type === 'Upgrade' || $request->type === 'Activation')) {
                    $device->update([
                        'activated_at' => Carbon::now(),
                        'status' => 'active',
                    ]);
                } elseif ($request->type === 'Cancellation') {
                    if ($device) {
                        $device->update([
                            'status' => 'inactive',
                            'expiration_date' => Carbon::now(),
                        ]);
                    }
                    if ($customer->mikrotik_login) {
                        $radius->removeUser($customer->mikrotik_login);
                    }
                }
                
                $request->update([
                    'status' => 'Completed',
                    'completed_at' => Carbon::now(),
                ]);
            });

            event(new ProvisioningUpdate($request, 'LIFECYCLE', 'success', 'Provisioning completed successfully.'));

        } catch (\Exception $e) {
            Log::error("Provisioning Job Failed for Request #{$request->id}: {$e->getMessage()}");
            
            $request->update([
                'status' => 'Failed',
                'metadata' => array_merge($request->metadata ?? [], ['error' => $e->getMessage()]),
            ]);

            event(new ProvisioningUpdate($request, 'ERROR', 'failed', $e->getMessage()));
        }
    }
}
