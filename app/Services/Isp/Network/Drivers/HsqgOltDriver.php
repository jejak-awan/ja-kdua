<?php

declare(strict_types=1);

namespace App\Services\Isp\Network\Drivers;

use App\Models\Isp\Network\ServiceNode;
use Illuminate\Support\Facades\Log;

class HsqgOltDriver extends BaseSshDriver implements OltDriver
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

    protected function initializeSession(): void
    {
        // 1. Enter enable mode
        $this->writeAndRead('enable', '#');

        // 2. Disable paging
        $this->writeAndRead('no terminal monitor', '#'); // HSGQ paging disable variant
    }

    /**
     * Register ONU on HSGQ OLT.
     *
     * @param  array<string, mixed>  $config  Expects port, onu_id, vlan
     */
    public function registerONU(string $sn, array $config): bool
    {
        $port = isset($config['port']) && is_scalar($config['port']) ? (string) $config['port'] : '1';
        $onuId = isset($config['onu_id']) && is_scalar($config['onu_id']) ? (string) $config['onu_id'] : '1';
        $vlan = isset($config['vlan']) && is_scalar($config['vlan']) ? (string) $config['vlan'] : '100';

        $commands = [
            'config',
            "interface gpon 0/{$port}",
            "ont add {$onuId} sn-auth {$sn} type auto",
            "ont native-vlan {$onuId} vlan {$vlan}",
            'exit',
            "service-port add vlan {$vlan} gpon 0/{$port} ont {$onuId}",
            'write',
        ];

        Log::info("HSGQ OLT [{$this->olt->name}]: Registering ONU {$sn} on 0/{$port}:{$onuId}");

        return $this->executeOltCommands($commands);
    }

    public function getSignal(string $interface, string $onuIndex): ?float
    {
        if (! $this->connect()) {
            return null;
        }

        // HSGQ format: show ont optical-info gpon 0/1 1
        $cmd = "show ont optical-info gpon {$interface} {$onuIndex}";
        $output = $this->writeAndRead($cmd, '#');

        // Regex for Rx Power
        if (preg_match('/Rx power\(dBm\)\s+:\s+(-?\d+\.?\d*)/i', $output, $matches)) {
            return (float) $matches[1];
        }

        return null;
    }

    public function rebootONU(string $interface, string $onuIndex): bool
    {
        $commands = [
            'config',
            "interface gpon {$interface}",
            "ont reboot {$onuIndex}",
            'exit',
        ];

        return $this->executeOltCommands($commands);
    }

    public function deRegisterONU(string $interface, string $onuIndex): bool
    {
        $commands = [
            'config',
            "interface gpon {$interface}",
            "ont delete {$onuIndex}",
            'exit',
            'write',
        ];

        return $this->executeOltCommands($commands);
    }

    /**
     * @param  array<int, string>  $commands
     */
    protected function executeOltCommands(array $commands, string $prompt = '#'): bool
    {
        if (! $this->connect()) {
            return false;
        }

        foreach ($commands as $command) {
            Log::debug("HSGQ OLT [{$this->olt->name}]: Executing -> {$command}");
            $this->writeAndRead($command, $prompt);
        }

        return true;
    }

    public function discoverUnconfiguredOnus(): array
    {
        if (! $this->connect()) {
            return [];
        }

        // HSGQ: show ont autofind
        $output = $this->writeAndRead('show ont autofind', '#');
        $onus = [];

        // Example output: 0/1   SN:HSGQ00000000
        if (preg_match_all('/(\d+\/\d+)\s+SN:([A-Z0-9]+)/i', $output, $matches, PREG_SET_ORDER)) {
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
