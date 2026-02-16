<?php

declare(strict_types=1);

namespace App\Jobs\Isp;

use App\Models\Isp\Customer\Customer;
use App\Models\Isp\Network\ServiceNode;
use App\Services\Isp\Network\RouterProvisioningService;
use App\Services\Isp\ThirdParty\TelegramNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DriftDetectionJob implements ShouldQueue
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
    public function handle(RouterProvisioningService $provisioning, TelegramNotificationService $telegram): void
    {
        try {
            Log::info("Drift Detection: Auditing router {$this->router->name}");

            // 1. Fetch live secrets from router
            $routerSecrets = $provisioning->getVpnSecrets($this->router);
            $routerSecretNames = collect($routerSecrets)->pluck('name')->toArray();

            // 2. Fetch local customers for this router
            $localCustomers = Customer::where('router_id', $this->router->id)->get();
            $localCustomerLogins = $localCustomers->pluck('mikrotik_login')->toArray();

            // 3. Identify Inconsistencies
            $ghostSecrets = array_diff($routerSecretNames, $localCustomerLogins);
            $missingOnRouter = array_diff($localCustomerLogins, $routerSecretNames);

            // 4. Detailed Audit Results
            $stats = [
                'total_local' => count($localCustomerLogins),
                'total_router' => count($routerSecretNames),
                'ghost_count' => count($ghostSecrets),
                'missing_count' => count($missingOnRouter),
                'ghost_samples' => array_slice($ghostSecrets, 0, 10),
                'timestamp' => now()->toDateTimeString(),
            ];

            // 5. Store audit results in router metadata
            $this->router->update([
                'metadata' => array_merge($this->router->metadata ?? [], [
                    'last_audit' => $stats,
                ]),
            ]);

            // 6. Alert if drift is significant
            if (count($ghostSecrets) > 0 || count($missingOnRouter) > 5) {
                $msg = "📢 *Network Drift Detected* 📢\n\n" .
                       "Router: *{$this->router->name}*\n" .
                       "Ghost Secrets: " . count($ghostSecrets) . "\n" .
                       "Missing on Router: " . count($missingOnRouter) . "\n\n" .
                       "Please review the NOC dashboard for details.";
                
                $telegram->sendAdminAlert($msg);
            }

            Log::info("Drift Detection finished for {$this->router->name}. Ghosts: " . count($ghostSecrets));
        } catch (\Exception $e) {
            Log::error("Drift Detection Error for {$this->router->name}: " . $e->getMessage());
        }
    }
}
