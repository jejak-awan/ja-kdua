<?php

declare(strict_types=1);

namespace App\Services\Isp\Network\Drivers;

use App\Models\Isp\Network\ServiceNode;
use Illuminate\Support\Facades\Log;

class MockOltDriver implements OltDriver
{
    protected ServiceNode $olt;

    public function __construct(ServiceNode $olt)
    {
        $this->olt = $olt;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public function registerONU(string $sn, array $config): bool
    {
        Log::info("MOCK OLT: Registering ONU {$sn} on {$this->olt->name}", $config);

        return true;
    }

    public function getSignal(string $interface, string $onuIndex): ?float
    {
        Log::info("MOCK OLT: Getting signal for {$interface} index {$onuIndex}");

        return -19.5; // Dummy signal level
    }

    public function rebootONU(string $interface, string $onuIndex): bool
    {
        Log::info("MOCK OLT: Rebooting ONU on {$interface} index {$onuIndex}");

        return true;
    }

    public function deRegisterONU(string $interface, string $onuIndex): bool
    {
        Log::info("MOCK OLT: De-registering ONU on {$interface} index {$onuIndex}");

        return true;
    }

    public function discoverUnconfiguredOnus(): array
    {
        return [
            ['sn' => 'MOCK'.strtoupper(bin2hex(random_bytes(4))), 'interface' => '1/1/1'],
        ];
    }

    public function connect(): bool
    {
        return true;
    }
}
