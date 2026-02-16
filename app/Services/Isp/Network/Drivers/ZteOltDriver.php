<?php

declare(strict_types=1);

namespace App\Services\Isp\Network\Drivers;

use App\Models\Isp\Network\ServiceNode;
use Illuminate\Support\Facades\Log;

class ZteOltDriver extends BaseSshDriver implements OltDriver
{
    protected ServiceNode $olt;

    public function __construct(ServiceNode $olt)
    {
        $this->olt = $olt;
        parent::__construct(
            (string) ($olt->ip_address ?? '127.0.0.1'),
            (string) ($olt->api_username ?? 'admin'),
            (string) ($olt->api_password ?? ''),
            (int) ($olt->api_port ?? 22),
            10,
            (int) $olt->id
        );
    }

    /**
     * ZTE-Specific session initialization.
     */
    protected function initializeSession(): void
    {
        // 1. Disable paging to get clean output
        $this->writeAndRead('terminal length 0', '#');
    }

    /**
     * Register ONU on ZTE OLT (Gpon-onu mode).
     *
     * @param  array<string, mixed>  $config
     */
    public function registerONU(string $sn, array $config): bool
    {
        $ponInterface = isset($config['interface']) && is_string($config['interface']) ? $config['interface'] : 'gpon-olt_1/1/1';
        $onuId = isset($config['onu_id']) && is_string($config['onu_id']) ? $config['onu_id'] : 'auto';
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
            "vlan port eth_0/1 mode tag vlan {$vlan}",
            'exit',
        ];

        Log::info("ZTE OLT [{$this->olt->name}]: Registering ONU {$sn}");

        return $this->executeOltCommands($commands);
    }

    public function getSignal(string $interface, string $onuIndex): ?float
    {
        if (! $this->connect()) {
            return null;
        }

        // Command: show pon power att gpon-onu_1/1/1:1
        $output = $this->writeAndRead("show pon power att gpon-onu_{$interface}:{$onuIndex}", '#');

        // Regex to extract Rx power, e.g., "Rx power:  -22.45 dBm"
        if (preg_match('/Rx power:\s+(-?\d+\.?\d*)/i', $output, $matches)) {
            return (float) $matches[1];
        }

        return null;
    }

    public function rebootONU(string $interface, string $onuIndex): bool
    {
        $commands = [
            "pon-onu-mng gpon-onu_{$interface}:{$onuIndex}",
            'reboot',
            'yes',
            'exit',
        ];

        return $this->executeOltCommands($commands);
    }

    public function deRegisterONU(string $interface, string $onuIndex): bool
    {
        $commands = [
            'conf t',
            "no interface gpon-onu_{$interface}:{$onuIndex}",
            'exit',
        ];

        return $this->executeOltCommands($commands);
    }

    /**
     * Helper to execute multiple ZTE CLI commands.
     *
     * @param  array<int, string>  $commands
     */
    protected function executeOltCommands(array $commands, string $prompt = '#'): bool
    {
        if (! $this->connect()) {
            return false;
        }

        foreach ($commands as $command) {
            Log::debug("ZTE OLT [{$this->olt->name}]: Executing -> {$command}");
            $this->writeAndRead($command, $prompt);
        }

        return true;
    }

    public function discoverUnconfiguredOnus(): array
    {
        if (! $this->connect()) {
            return [];
        }

        $output = $this->writeAndRead('show gpon onu unconfigured', '#');
        $onus = [];

        // Example output: gpon-onu_1/1/1:1  SN:ZTEGC0000000  Status:unconfigured
        if (preg_match_all('/gpon-onu_(\d+\/\d+\/\d+):\d+\s+SN:([A-Z0-9]+)/i', $output, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $onus[] = [
                    'sn' => $match[2],
                    'interface' => $match[1],
                ];
            }
        }

        return $onus;
    }
}
