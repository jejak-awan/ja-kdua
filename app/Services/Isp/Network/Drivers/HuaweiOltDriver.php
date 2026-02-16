<?php

declare(strict_types=1);

namespace App\Services\Isp\Network\Drivers;

use App\Models\Isp\Network\ServiceNode;
use Illuminate\Support\Facades\Log;

class HuaweiOltDriver extends BaseSshDriver implements OltDriver
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
        $this->writeAndRead('enable', '>');
        $this->writeAndRead('enable', '#'); // Sometimes requires twice or different prompt

        // 2. Disable paging
        $this->writeAndRead('undo smart-paging', '#');
    }

    /**
     * Register ONU on Huawei OLT.
     *
     * @param  array<string, mixed>  $config  Expects frame, slot, port, ont_id, vlan, lineprofile, srvprofile
     */
    public function registerONU(string $sn, array $config): bool
    {
        $frame = isset($config['frame']) && is_scalar($config['frame']) ? (string) $config['frame'] : '0';
        $slot = isset($config['slot']) && is_scalar($config['slot']) ? (string) $config['slot'] : '1';
        $port = isset($config['port']) && is_scalar($config['port']) ? (string) $config['port'] : '0';
        $ontId = isset($config['ont_id']) && is_scalar($config['ont_id']) ? (string) $config['ont_id'] : 'auto';
        $lineprofile = isset($config['lineprofile']) && is_scalar($config['lineprofile']) ? (string) $config['lineprofile'] : 'default';
        $srvprofile = isset($config['srvprofile']) && is_scalar($config['srvprofile']) ? (string) $config['srvprofile'] : 'default';
        $vlan = isset($config['vlan']) && is_scalar($config['vlan']) ? (string) $config['vlan'] : '100';

        $description = $config['description'] ?? "ONU_{$sn}";

        $commands = [
            'config',
            "interface gpon {$frame}/{$slot}",
            "ont add {$port} {$ontId} sn-auth {$sn} omci ont-lineprofile-name {$lineprofile} ont-srvprofile-name {$srvprofile} desc \"{$description}\"",
            "ont port native-vlan {$port} {$ontId} eth 1 vlan {$vlan} priority 0",
            'quit',
            "service-port next-free vlan {$vlan} gpon {$frame}/{$slot}/{$port} ont {$ontId} gemport 1 multi-service user-vlan {$vlan} tag-transform translate",
            'save',
        ];

        Log::info("Huawei OLT [{$this->olt->name}]: Registering ONU {$sn} on {$frame}/{$slot}/{$port}:{$ontId}");

        return $this->executeOltCommands($commands);
    }

    public function getSignal(string $interface, string $onuIndex): ?float
    {
        if (! $this->connect()) {
            return null;
        }

        // Huawei format: display ont optical-info 0 1 0 1
        // $interface should be frame/slot/port
        // $onuIndex should be the ont-id
        $parts = explode('/', $interface);
        if (count($parts) !== 3) {
            Log::warning("Huawei OLT: Invalid interface format: {$interface}");

            return null;
        }

        $cmd = "display ont optical-info {$parts[0]} {$parts[1]} {$parts[2]} {$onuIndex}";
        $output = $this->writeAndRead($cmd, '#');

        // Extract Rx power, e.g., "ONU Rx optical power(dBm)               : -19.45"
        if (preg_match('/ONU Rx optical power\(dBm\)\s+:\s+(-?\d+\.?\d*)/i', $output, $matches)) {
            return (float) $matches[1];
        }

        return null;
    }

    public function rebootONU(string $interface, string $onuIndex): bool
    {
        $parts = explode('/', $interface);
        if (count($parts) !== 3) {
            return false;
        }

        $commands = [
            'config',
            "interface gpon {$parts[0]}/{$parts[1]}",
            "ont reboot {$parts[2]} {$onuIndex}",
            'quit',
        ];

        return $this->executeOltCommands($commands);
    }

    public function deRegisterONU(string $interface, string $onuIndex): bool
    {
        $parts = explode('/', $interface);
        if (count($parts) !== 3) {
            return false;
        }

        $commands = [
            'config',
            "interface gpon {$parts[0]}/{$parts[1]}",
            "ont delete {$parts[2]} {$onuIndex}",
            'quit',
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
            Log::debug("Huawei OLT [{$this->olt->name}]: Executing -> {$command}");
            $this->writeAndRead($command, $prompt);
        }

        return true;
    }

    public function discoverUnconfiguredOnus(): array
    {
        if (! $this->connect()) {
            return [];
        }

        // Huawei: display ont autofind all
        $output = $this->writeAndRead('display ont autofind all', '#');
        $onus = [];

        // Example output:
        // F/S/P   : 0/1/0
        // Ont-ID  : -
        // SN      : 48575443B1A2C3D4 (HWTC...)
        if (preg_match_all('/F\/S\/P\s+:\s+(\d+\/\d+\/\d+).*?SN\s+:\s+([A-Z0-9]+)/s', $output, $matches, PREG_SET_ORDER)) {
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
