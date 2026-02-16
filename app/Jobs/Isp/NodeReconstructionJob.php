<?php

declare(strict_types=1);

namespace App\Jobs\Isp;

use App\Models\Isp\Customer\Customer;
use App\Models\Isp\Network\ServiceNode;
use App\Services\Isp\Network\RouterProvisioningService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NodeReconstructionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ServiceNode $router;

    /**
     * Create a new job instance.
     */
    public function __construct(ServiceNode $router)
    {
        $this->router = $router;
    }

    /**
     * Execute the job.
     */
    public function handle(RouterProvisioningService $provisioning): void
    {
        try {
            Log::info("Node Reconstruction: Starting sync for {$this->router->name}");

            // 1. Fetch all local customers for this router
            $customers = Customer::where('router_id', $this->router->id)->get();
            $total = count($customers);
            
            Log::info("Node Reconstruction: Found {$total} customers to sync.");

            foreach ($customers as $index => $customer) {
                // Sync to RADIUS and apply any node-specific logic
                $provisioning->syncCustomer($customer);
                
                // Log progress for large nodes
                if (($index + 1) % 50 === 0) {
                    Log::info("Node Reconstruction: Synced " . ($index + 1) . "/{$total}");
                }
            }

            // 2. Mark audit as clean
            $this->router->update([
                'metadata' => array_merge($this->router->metadata ?? [], [
                    'last_reconstruction' => now()->toDateTimeString(),
                    'last_audit' => [
                        'ghost_count' => 0,
                        'missing_count' => 0,
                        'timestamp' => now()->toDateTimeString(),
                    ],
                ]),
            ]);

            Log::info("Node Reconstruction finished for {$this->router->name}");
        } catch (\Exception $e) {
            Log::error("Node Reconstruction Error for {$this->router->name}: " . $e->getMessage());
        }
    }
}
