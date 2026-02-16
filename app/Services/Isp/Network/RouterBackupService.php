<?php

declare(strict_types=1);

namespace App\Services\Isp\Network;

use App\Models\Isp\Network\ServiceNode;
use Illuminate\Support\Facades\Log;

class RouterBackupService
{
    protected MikrotikService $mikrotik;

    public function __construct(MikrotikService $mikrotik)
    {
        $this->mikrotik = $mikrotik;
    }

    /**
     * Trigger a new backup on the router.
     * 
     * @param ServiceNode $router
     * @return array{success: bool, message: string}
     */
    public function triggerBackup(ServiceNode $router): array
    {
        if ($router->type !== 'Router') {
            return ['success' => false, 'message' => 'Node is not a router'];
        }

        $host = (string) $router->ip_address;
        $user = (string) $router->api_username;
        $pass = (string) $router->api_password;
        $port = (int) ($router->api_port ?? 8728);

        if (!$this->mikrotik->connect($host, $user, $pass, $port)) {
            return ['success' => false, 'message' => 'Could not connect to router API'];
        }

        try {
            $client = $this->mikrotik->getClient();
            if (!$client) {
                return ['success' => false, 'message' => 'API client not initialized'];
            }

            $date = now()->format('Ymd_His');
            $backupName = "JA_BACKUP_{$date}";
            $exportName = "JA_EXPORT_{$date}";

            // 1. Save binary backup
            $client->comm('/system/backup/save', [
                'name' => $backupName
            ]);

            // 2. Export configuration script
            $client->comm('/export', [
                'file' => $exportName
            ]);

            Log::info("Router Backup triggered for {$router->name} ({$host}): {$backupName}.backup and {$exportName}.rsc created.");

            return [
                'success' => true, 
                'message' => "Backup & Export triggered successfully on router. Files: {$backupName}.backup, {$exportName}.rsc"
            ];

        } catch (\Exception $e) {
            Log::error("Router Backup failed for {$router->name}: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get list of backups on the router.
     * 
     * @return array<int, array<string, string>>
     */
    public function listBackups(ServiceNode $router): array
    {
         if (!$this->mikrotik->connect((string)$router->ip_address, (string)$router->api_username, (string)$router->api_password)) {
            return [];
        }

        $client = $this->mikrotik->getClient();
        if (!$client) {
            return [];
        }

        $files = $client->comm('/file/print');
        
        return array_filter($files, function($file) {
            return str_contains($file['name'] ?? '', 'JA_');
        });
    }
}
