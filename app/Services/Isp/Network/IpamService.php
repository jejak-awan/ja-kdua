<?php

declare(strict_types=1);

namespace App\Services\Isp\Network;

use App\Helpers\CidrCalculator;
use App\Models\Core\Setting;
use App\Models\Isp\Network\ServiceNode;
use Illuminate\Support\Facades\Log;

class IpamService
{
    /**
     * Get the configured local network CIDR.
     */
    public function getLocalNetworkCidr(): string
    {
        $cidr = Setting::get('isp_local_network_cidr', '10.0.0.0/8');

        return is_string($cidr) ? $cidr : '10.0.0.0/8';
    }

    /**
     * Get the configured subnet size for routers.
     */
    public function getSubnetSize(): int
    {
        // e.g., 30 for /30 subnet
        $size = Setting::get('isp_subnet_size', 30);

        return is_numeric($size) ? (int) $size : 30;
    }

    /**
     * Calculate the next available subnet within the local network.
     */
    public function getNextAvailableSubnet(): ?string
    {
        $rootCidr = $this->getLocalNetworkCidr();
        $subnetSize = $this->getSubnetSize();

        $usedIps = ServiceNode::whereNotNull('ip_address')
            ->pluck('ip_address')
            ->toArray();

        /** @var string[] $usedIps */
        $usedIpLongs = array_map(fn ($ip) => (int) ip2long($ip), $usedIps);

        $nextSubnet = CidrCalculator::getNextSubnet($rootCidr, $subnetSize, $usedIpLongs);

        if ($nextSubnet) {
            // CidrCalculator returns Network Address/Size.
            // We usually want the first usable IP for the router interface.
            [$net] = explode('/', $nextSubnet);
            $long = ip2long($net);
            $ip = $long !== false ? long2ip($long + 1) : false;

            return is_string($ip) ? $ip : null;
        }

        Log::warning("IPAM: No space left in root CIDR {$rootCidr} for /{$subnetSize} subnets.");

        return null;
    }

    /**
     * Validate if an IP is valid and available (no conflict).
     */
    public function validateIp(string $ip, ?int $ignoreId = null): bool
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }

        $query = ServiceNode::where('ip_address', $ip);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return ! $query->exists();
    }
}
