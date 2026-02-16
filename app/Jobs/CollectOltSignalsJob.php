<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Isp\Customer\Customer;
use App\Models\Isp\Network\OltSignal;
use App\Services\Isp\Network\OltService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CollectOltSignalsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(OltService $oltService): void
    {
        Log::info('CollectOltSignalsJob: Starting optical signal collection.');

        Customer::whereNotNull('olt_id')
            ->whereNotNull('olt_port')
            ->whereNotNull('olt_onu_index')
            ->chunk(100, function ($customers) use ($oltService) {
                /** @var \Illuminate\Database\Eloquent\Collection<int, Customer> $customers */
                foreach ($customers as $customer) {
                    try {
                        $olt = $customer->olt;
                        if (!$olt) {
                            continue;
                        }

                        $signal = $oltService->getSignal(
                            $olt, 
                            (string) $customer->olt_port, 
                            (string) $customer->olt_onu_index
                        );

                        if ($signal !== null) {
                            OltSignal::create([
                                'customer_id' => $customer->id,
                                'olt_id' => $olt->id,
                                'onu_index' => (string) $customer->olt_onu_index,
                                'rx_power' => $signal,
                                'collected_at' => now(),
                            ]);
                            
                            Log::debug("CollectOltSignalsJob: Collected signal for customer #{$customer->id}: {$signal} dBm");
                        }
                    } catch (\Exception $e) {
                        Log::error("CollectOltSignalsJob: Error for customer #{$customer->id}: " . $e->getMessage());
                    }
                }
            });

        Log::info('CollectOltSignalsJob: Finished optical signal collection.');
    }
}
