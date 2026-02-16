<?php

declare(strict_types=1);

namespace App\Services\Isp\Network\Drivers;

use App\Models\Isp\Network\ServiceNode;
use Illuminate\Support\Facades\Log;

class VsolOltDriver extends BaseSshDriver implements OltDriver
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
        $this->writeAndRead('enable', '#');
        $this->writeAndRead('configure terminal', '#');
        $this->writeAndRead('no cli-paging', '#'); // VSOL specific paging disable
    }

    public function registerONU(string $sn, array $config): bool
    {
        $port = isset($config['port']) && is_scalar($config['port']) ? (string) $config['port'] : '1';
        $onuId = isset($config['onu_id']) && is_scalar($config['onu_id']) ? (string) $config['onu_id'] : 'auto';
        $vlan = isset($config['vlan']) && is_scalar($config['vlan']) ? (string) $config['vlan'] : '100';

        $commands = [
            "interface gpon 0/{$port}",
            "ont-registration sn-auth {$sn} ont-id {$onuId}",
            "ont-port-config 0/{$port} {$onuId} eth 1 vlan {$vlan}",
            'exit',
            'write',
        ];

        Log::info("VSOL OLT [{$this->olt->name}]: Registering ONU {$sn} on 0/{$port}:{$onuId}");

        return $this->executeOltCommands($commands);
    }

    public function getSignal(string $interface, string $onuIndex): ?float
    {
        if (! $this->connect()) {
            return null;
        }

        $cmd = "show ont optical-info 0/{$interface} {$onuIndex}";
        $output = $this->writeAndRead($cmd, '#');

        if (preg_match('/ONU Rx Power\s+:\s+(-?\d+\.?\d*)/i', $output, $matches)) {
            return (float) $matches[1];
        }

        return null;
    }

    public function rebootONU(string $interface, string $onuIndex): bool
    {
        $commands = [
            "interface gpon {$interface}",
            "ont-reboot {$onuIndex}",
            'exit',
        ];

        return $this->executeOltCommands($commands);
    }

    public function deRegisterONU(string $interface, string $onuIndex): bool
    {
        $commands = [
            "interface gpon {$interface}",
            "ont-delete {$onuIndex}",
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
            Log::debug("VSOL OLT [{$this->olt->name}]: Executing -> {$command}");
            $this->writeAndRead($command, $prompt);
        }

        return true;
    }

    public function discoverUnconfiguredOnus(): array
    {
        if (! $this->connect()) {
            return [];
        }

        // VSOL: show ont-registration sn-auth
        $output = $this->writeAndRead('show ont-registration sn-auth', '#');
        $onus = [];

        // Example: Port: 1 SN: VSOL00000000
        if (preg_match_all('/Port:\s+(\d+)\s+SN:\s+([A-Z0-9]+)/i', $output, $matches, PREG_SET_ORDER)) {
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
