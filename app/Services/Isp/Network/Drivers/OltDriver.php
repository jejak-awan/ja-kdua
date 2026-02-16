<?php

namespace App\Services\Isp\Network\Drivers;

interface OltDriver
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function registerONU(string $sn, array $config): bool;

    public function getSignal(string $interface, string $onuIndex): ?float;

    public function rebootONU(string $interface, string $onuIndex): bool;

    public function deRegisterONU(string $interface, string $onuIndex): bool;

    /**
     * Discover ONU's that are connected but not yet registered.
     *
     * @return array<int, array{sn: string, interface: string}>
     */
    public function discoverUnconfiguredOnus(): array;

    public function connect(): bool;
}
