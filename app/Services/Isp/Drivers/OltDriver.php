<?php

namespace App\Services\Isp\Drivers;

interface OltDriver
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function registerONU(string $sn, array $config): bool;

    public function getSignal(string $interface, string $onuIndex): ?float;

    public function rebootONU(string $interface, string $onuIndex): bool;
}
