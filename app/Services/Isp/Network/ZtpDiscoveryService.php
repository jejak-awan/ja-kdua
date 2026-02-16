<?php

declare(strict_types=1);

namespace App\Services\Isp\Network;

use App\Models\Isp\Network\ServiceNode;
use App\Models\Isp\Customer\Customer;
use App\Models\Isp\Support\ServiceRequest;
use App\Jobs\Isp\ProcessProvisioningJob;
use Illuminate\Support\Facades\Log;

class ZtpDiscoveryService
{
    protected \App\Services\Isp\Network\OltService $oltService;

    protected \App\Services\Isp\ThirdParty\TelegramNotificationService $telegram;

    public function __construct(\App\Services\Isp\Network\OltService $oltService, \App\Services\Isp\ThirdParty\TelegramNotificationService $telegram)
    {
        $this->oltService = $oltService;
        $this->telegram = $telegram;
    }

    /**
     * Scan all OLTs for unconfigured ONUs and notify NOC.
     */
    public function scanAllOlts(): void
    {
        Log::info('ZtpDiscoveryService: Starting network-wide ONU discovery.');

        $olts = ServiceNode::where('type', 'OLT')->where('status', 'active')->get();

        foreach ($olts as $olt) {
            try {
                $driver = $this->oltService->getDriver($olt);
                $unconfiguredOnus = $driver->discoverUnconfiguredOnus();

                // Cache results for 1 hour for frontend consumption
                $cacheKey = "ztp_discovery_results_{$olt->id}";
                if (! empty($unconfiguredOnus)) {
                    \Illuminate\Support\Facades\Cache::put($cacheKey, $unconfiguredOnus, 3600);
                    
                    // Attempt Auto-Provisioning
                    foreach ($unconfiguredOnus as $onu) {
                        $this->attemptAutoProvision($olt, $onu);
                    }

                    $this->notifyNoc($olt, $unconfiguredOnus);
                } else {
                    \Illuminate\Support\Facades\Cache::forget($cacheKey);
                }
            } catch (\InvalidArgumentException $e) {
                // Skip unsupported OLT types without error spam
                Log::warning("ZtpDiscoveryService: Skipping OLT [{$olt->name}]: ".$e->getMessage());
            } catch (\Exception $e) {
                Log::error("ZtpDiscoveryService: Error scanning OLT [{$olt->name}]: ".$e->getMessage());
            }
        }

        Log::info('ZtpDiscoveryService: Network-wide discovery completed.');
    }

    /**
     * Attempt to automatically provision a discovered ONU.
     *
     * @param  array{sn: string, interface: string}  $onu
     */
    protected function attemptAutoProvision(ServiceNode $olt, array $onu): void
    {
        $sn = $onu['sn'];

        // Find customer with matching ONU serial on this OLT who is not yet active/provisioned
        /** @var Customer|null $customer */
        $customer = Customer::where('olt_id', $olt->id)
            ->where('onu_serial', $sn)
            ->where('status', 'inactive')
            ->first();

        if ($customer) {
            Log::info("ZtpDiscoveryService: Auto-matching ONU {$sn} with Customer #{$customer->id}");

            // Create a Service Request if one doesn't exist for activation
            $request = ServiceRequest::updateOrCreate(
                [
                    'customer_id' => $customer->id,
                    'type' => 'Activation',
                    'status' => 'Pending',
                ],
                [
                    'details' => [
                        'olt_profile' => $customer->onu_model ?? 'BASIC',
                        'rate_limit' => $customer->plan->mikrotik_rate_limit ?? '10M/10M',
                        'auto_ztp' => true,
                        'discovered_port' => $onu['interface'],
                    ],
                ]
            );

            // Update customer with the discovered port
            $customer->update(['olt_port' => $onu['interface']]);

            // Dispatch provisioning job
            ProcessProvisioningJob::dispatch($request);
            
            Log::info("ZtpDiscoveryService: Dispatched auto-provisioning job for Customer #{$customer->id}");
        }
    }

    /**
     * Send Telegram notification to NOC about discovered ONUs.
     *
     * @param  array<int, array{sn: string, interface: string}>  $onus
     */
    protected function notifyNoc(ServiceNode $olt, array $onus): void
    {
        $message = "🚨 *ZTP Discovery Alert*\n\n";
        $message .= "OLT: *{$olt->name}* ({$olt->ip_address})\n";
        $message .= "New equipment detected:\n\n";

        foreach ($onus as $onu) {
            $message .= "📍 Port: `{$onu['interface']}`\n";
            $message .= "🆔 SN: `{$onu['sn']}`\n";
            $message .= "-------------------\n";
        }

        $message .= "\nAction required: provision these devices via the dashboard.";

        $this->telegram->sendAdminAlert($message);

        Log::info('ZtpDiscoveryService: Notified NOC about '.count($onus)." discovered ONUs on OLT [{$olt->name}].");
    }
}
