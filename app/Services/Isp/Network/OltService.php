<?php

declare(strict_types=1);

namespace App\Services\Isp\Network;

use App\Models\Isp\Network\ServiceNode;
use Illuminate\Support\Facades\Log;

class OltService
{
    /**
     * Get a driver instance for a specific OLT.
     */
    public function getDriver(ServiceNode $olt): \App\Services\Isp\Network\Drivers\OltDriver
    {
        return match (strtoupper((string) ($olt->sub_type ?? $olt->type))) {
            'ZTE' => new \App\Services\Isp\Network\Drivers\ZteOltDriver($olt),
            'HUAWEI' => new \App\Services\Isp\Network\Drivers\HuaweiOltDriver($olt),
            'HSGQ' => new \App\Services\Isp\Network\Drivers\HsqgOltDriver($olt),
            'HIOSO' => new \App\Services\Isp\Network\Drivers\HiosoOltDriver($olt),
            'ZYMLINK' => new \App\Services\Isp\Network\Drivers\ZymlinkOltDriver($olt),
            'GLOBAL' => new \App\Services\Isp\Network\Drivers\GlobalOltDriver($olt),
            'VSOL' => new \App\Services\Isp\Network\Drivers\VsolOltDriver($olt),
            'MOCK', '' => new \App\Services\Isp\Network\Drivers\MockOltDriver($olt),
            default => throw new \InvalidArgumentException('Unsupported OLT type: '.($olt->sub_type ?? $olt->type)),
        };
    }

    /**
     * Register a new ONU on the given OLT.
     *
     * @param  array<string, mixed>  $config
     */
    public function registerOnu(ServiceNode $olt, string $sn, array $config): bool
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
    public function getSignal(ServiceNode $olt, string $interface, string $onuIndex): ?float
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
    public function rebootOnu(ServiceNode $olt, string $interface, string $onuIndex): bool
    {
        try {
            return $this->getDriver($olt)->rebootONU($interface, $onuIndex);
        } catch (\Exception $e) {
            Log::error('OLT Reboot Error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * De-register an ONU from the OLT.
     */
    public function deRegisterOnu(ServiceNode $olt, string $interface, string $onuIndex): bool
    {
        try {
            return $this->getDriver($olt)->deRegisterONU($interface, $onuIndex);
        } catch (\Exception $e) {
            Log::error('OLT De-registration Error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Test connection to an OLT.
     */
    public function testConnection(ServiceNode $olt): bool
    {
        try {
            return $this->getDriver($olt)->connect();
        } catch (\Exception $e) {
            Log::error("OLT Connection Test Failed for {$olt->name}: ".$e->getMessage());

            return false;
        }
    }
}
