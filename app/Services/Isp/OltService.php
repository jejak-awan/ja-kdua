<?php

declare(strict_types=1);

namespace App\Services\Isp;

use App\Models\Isp\Olt;
use App\Services\Isp\Drivers\MockOltDriver;
use App\Services\Isp\Drivers\OltDriver;
use App\Services\Isp\Drivers\ZteOltDriver;
use Illuminate\Support\Facades\Log;

class OltService
{
    /**
     * Get a driver instance for a specific OLT.
     */
    public function getDriver(Olt $olt): OltDriver
    {
        return match (strtoupper($olt->type)) {
            'ZTE' => new ZteOltDriver($olt),
            'MOCK' => new MockOltDriver($olt),
            default => throw new \InvalidArgumentException("Unsupported OLT type: {$olt->type}"),
        };
    }

    /**
     * Register a new ONU on the given OLT.
     *
     * @param  array<string, mixed>  $config
     */
    public function registerOnu(Olt $olt, string $sn, array $config): bool
    {
        try {
            $driver = $this->getDriver($olt);
            Log::info("Registering ONU {$sn} on OLT: {$olt->name}");

            return $driver->registerONU($sn, $config);
        } catch (\Exception $e) {
            Log::error('OLT Registration Error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Get optical signal level.
     */
    public function getSignal(Olt $olt, string $interface, string $onuIndex): ?float
    {
        try {
            return $this->getDriver($olt)->getSignal($interface, $onuIndex);
        } catch (\Exception $e) {
            Log::error('OLT Signal Error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Reboot an ONU.
     */
    public function rebootOnu(Olt $olt, string $interface, string $onuIndex): bool
    {
        try {
            return $this->getDriver($olt)->rebootONU($interface, $onuIndex);
        } catch (\Exception $e) {
            Log::error('OLT Reboot Error: '.$e->getMessage());

            return false;
        }
    }
}
