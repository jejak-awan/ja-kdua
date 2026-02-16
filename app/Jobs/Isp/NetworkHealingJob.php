<?php

declare(strict_types=1);

namespace App\Jobs\Isp;

use App\Models\Isp\Customer\Customer;
use App\Models\Isp\Customer\CustomerDevice;
use App\Models\Isp\Network\ServiceNode;
use App\Services\Isp\Network\OltService;
use App\Services\Isp\ThirdParty\TelegramNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class NetworkHealingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(OltService $oltService, TelegramNotificationService $telegram): void
    {
        Log::info('NetworkHealing: Starting proactive network scan...');

        // Find all active customers with an ONU device on an OLT
        $customers = Customer::where('status', 'active')
            ->whereNotNull('olt_id')
            ->whereHas('devices', function ($q) {
                $q->where('type', 'ONU')->where('status', 'active');
            })
            ->get();

        foreach ($customers as $customer) {
            $device = $customer->devices()->where('type', 'ONU')->first();
            $olt = ServiceNode::find($customer->olt_id);

            if (!$olt || !$device) continue;

            /** @var array<string, mixed> $metadata */
            $metadata = $device->metadata ?? [];
            $interface = is_string($metadata['interface'] ?? null) ? $metadata['interface'] : 'gpon-olt_1/1/1';
            $onuIndex = is_string($metadata['onu_index'] ?? null) ? $metadata['onu_index'] : '1';

            $signal = $oltService->getSignal($olt, $interface, $onuIndex);

            if ($signal !== null && $signal < -30) {
                $cacheKey = "healing_critical_count_{$customer->id}";
                $count = (int) Cache::get($cacheKey, 0) + 1;
                Cache::put($cacheKey, $count, 3600);

                Log::warning("NetworkHealing: Critical signal detected for Customer #{$customer->id} ({$customer->name}): {$signal} dBm. Count: {$count}");

                // Threshold for reboot attempt (e.g. 2 consecutive detections)
                if ($count >= 2) {
                    Log::info("NetworkHealing: Attempting proactive reboot for Customer #{$customer->id}");
                    $rebootSuccess = $oltService->rebootOnu($olt, $interface, $onuIndex);

                    if ($rebootSuccess) {
                        $telegram->sendAdminAlert("🛠 *Proactive Healing*\n\nCustomer: *{$customer->name}*\nOLT: *{$olt->name}*\nSignal: `{$signal} dBm`\nAction: *ONU Reboot Triggered* (Critical signal sustained)");
                        Cache::forget($cacheKey);
                    }
                }
            } else {
                Cache::forget("healing_critical_count_{$customer->id}");
            }
        }

        Log::info('NetworkHealing: Proactive network scan completed.');
    }
}
