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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BruteforceMonitorJob implements ShouldQueue
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
            Log::info("Bruteforce Monitor: Scanning logs for {$this->router->name}");

            $logs = $mikrotik->getLogs($this->router, 200);
            if (empty($logs)) return;

            $failures = [];
            foreach ($logs as $log) {
                // Focus on login failures
                if (str_contains(strtolower($log['message']), 'login failure')) {
                    // Extract IP address from message (e.g. "... from 1.2.3.4 via ...")
                    if (preg_match('/from\s+([0-9.]+|[0-9a-fA-F:]+)/', $log['message'], $matches)) {
                        $ip = $matches[1];
                        $failures[$ip] = ($failures[$ip] ?? 0) + 1;
                    }
                }
            }

            foreach ($failures as $ip => $count) {
                // Threshold: 5 failures in the recent log window
                if ($count >= 5) {
                    $this->blockIp($mikrotik, $telegram, $ip);
                }
            }

        } catch (\Exception $e) {
            Log::error("Bruteforce Monitor Error for {$this->router->name}: " . $e->getMessage());
        }
    }

    protected function blockIp(MikrotikService $mikrotik, TelegramNotificationService $telegram, string $ip): void
    {
        $cacheKey = "blocked_ip_{$this->router->id}_{$ip}";
        if (Cache::has($cacheKey)) return;

        Log::warning("Bruteforce Monitor: Blocking IP {$ip} on {$this->router->name}");

        // Add to BLACKHOLE address list
        $mikrotik->setAddressList($this->router, $ip, 'BLACKHOLE', true, 'Automated Bruteforce Block');

        // Cache for 24 hours to avoid redundant API calls/alerts
        Cache::put($cacheKey, true, 86400);

        // Notify NOC
        $telegram->sendAdminAlert("🛡️ *Security Hardening* 🛡️\n\nIP *{$ip}* has been automatically blacklisted on *{$this->router->name}* due to repeated login failures.");
    }
}
