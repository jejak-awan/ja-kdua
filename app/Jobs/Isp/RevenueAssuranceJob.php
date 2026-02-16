<?php

declare(strict_types=1);

namespace App\Jobs\Isp;

use App\Models\Isp\Network\ServiceNode;
use App\Services\Isp\Network\MikrotikService;
use App\Services\Isp\ThirdParty\TelegramNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RevenueAssuranceJob implements ShouldQueue
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
    public function handle(MikrotikService $mikrotik, TelegramNotificationService $telegram): void
    {
        try {
            Log::info("Revenue Assurance: Auditing fraud on {$this->router->name}");

            // 1. Detect Multi-login
            $multiLoginAlerts = $mikrotik->getMultiLoginAlerts($this->router);
            if (!empty($multiLoginAlerts)) {
                $msg = "🚨 *Fraud Alert: Multi-Login Detected* 🚨\n\n" .
                       "Router: *{$this->router->name}*\n";
                
                foreach ($multiLoginAlerts as $alert) {
                    $msg .= "👤 User: `{$alert['username']}` ({$alert['count']} MACs)\n";
                }
                
                $telegram->sendAdminAlert($msg);
            }

            // 2. Detect TTL Anomalies (Tethering)
            $ttlAnomalies = $mikrotik->getTtlAnomalies($this->router);
            if (!empty($ttlAnomalies)) {
                $msg = "📡 *Fraud Alert: Tethering Detected* 📡\n\n" .
                       "Router: *{$this->router->name}*\n";
                
                foreach ($ttlAnomalies as $anomaly) {
                    $msg .= "🌐 IP: `{$anomaly['src_address']}` ({$anomaly['hits']} packets)\n";
                }
                
                $telegram->sendAdminAlert($msg);
            }

            Log::info("Revenue Assurance finished for {$this->router->name}");
        } catch (\Exception $e) {
            Log::error("Revenue Assurance Error for {$this->router->name}: " . $e->getMessage());
        }
    }
}
