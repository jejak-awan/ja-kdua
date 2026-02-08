<?php

declare(strict_types=1);

namespace App\Services\Isp\Drivers;

use App\Models\Isp\Olt;
use Illuminate\Support\Facades\Log;

class ZteOltDriver implements OltDriver
{
    protected Olt $olt;

    public function __construct(Olt $olt)
    {
        $this->olt = $olt;
    }

    /**
     * Register ONU on ZTE OLT (Gpon-onu mode).
     * Example commands for ZTE C300/C320.
     *
     * @param  array<string, mixed>  $config
     */
    public function registerONU(string $sn, array $config): bool
    {
        $ponInterface = isset($config['interface']) && is_string($config['interface']) ? $config['interface'] : 'gpon-olt_1/1/1';
        $onuId = isset($config['onu_id']) && is_string($config['onu_id']) ? $config['onu_id'] : 'auto';
        $profile = isset($config['profile']) && is_string($config['profile']) ? $config['profile'] : 'PRO-100M';
        $vlanRaw = $config['vlan'] ?? 100;
        $vlan = is_scalar($vlanRaw) ? (string) $vlanRaw : '100';

        $commands = [
            'conf t',
            "interface {$ponInterface}",
            "onu {$onuId} type ZTE-F660 sn {$sn}",
            'exit',
            "interface gpon-onu_{$ponInterface}:{$onuId}",
            'tcont 1 name TCONT-1 profile UP-1G',
            'gemport 1 tcont 1',
            "service-port 1 vport 1 user-vlan {$vlan} svlan {$vlan}",
            'exit',
            "pon-onu-mng gpon-onu_{$ponInterface}:{$onuId}",
            "service HSI gemport 1 vlan {$vlan}",
            "vlan {$vlan}",
            'exit',
        ];

        Log::info("ZTE OLT [{$this->olt->name}]: Registering ONU {$sn}");

        return $this->executeCommands($commands);
    }

    public function getSignal(string $interface, string $onuIndex): ?float
    {
        // Command: show pon power att gpon-onu_1/1/1:1
        Log::info("ZTE OLT [{$this->olt->name}]: Fetching signal for {$interface}:{$onuIndex}");

        // Mock return for now
        return -22.4;
    }

    public function rebootONU(string $interface, string $onuIndex): bool
    {
        // Command: pon-onu-mng gpon-onu_1/1/1:1 -> reboot
        Log::info("ZTE OLT [{$this->olt->name}]: Rebooting ONU {$interface}:{$onuIndex}");

        return true;
    }

    /**
     * Helper to execute multiple commands.
     *
     * @param  array<int, string>  $commands
     */
    protected function executeCommands(array $commands): bool
    {
        Log::info("ZTE OLT [{$this->olt->name}]: Connecting to {$this->olt->ip_address}...");

        foreach ($commands as $command) {
            // In a real scenario, we would use an SSH or Telnet client here
            // Example: $client->write($command); $client->readUntil('#');
            Log::debug("ZTE OLT [{$this->olt->name}]: Executing -> {$command}");
        }

        return true;
    }
}
