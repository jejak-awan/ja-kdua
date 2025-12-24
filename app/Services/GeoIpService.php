<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoIpService
{
    /**
     * Cache TTL in seconds (24 hours)
     */
    protected int $cacheTtl = 86400;

    /**
     * Get location data for an IP address
     *
     * @param string $ipAddress
     * @return array{country: string|null, city: string|null, country_code: string|null}
     */
    public function getLocation(string $ipAddress): array
    {
        // Skip for local/private IPs
        if ($this->isLocalIp($ipAddress)) {
            return $this->getDefaultLocation();
        }

        // Check cache first
        $cacheKey = "geoip:{$ipAddress}";
        $cached = Cache::get($cacheKey);
        
        if ($cached !== null) {
            return $cached;
        }

        // Fetch from API
        $location = $this->fetchFromApi($ipAddress);
        
        // Cache the result
        Cache::put($cacheKey, $location, $this->cacheTtl);
        
        return $location;
    }

    /**
     * Fetch location data from ip-api.com
     *
     * @param string $ipAddress
     * @return array
     */
    protected function fetchFromApi(string $ipAddress): array
    {
        try {
            // Using ip-api.com (free tier: 45 requests per minute)
            $response = Http::timeout(5)->get("http://ip-api.com/json/{$ipAddress}", [
                'fields' => 'status,country,countryCode,city',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (($data['status'] ?? '') === 'success') {
                    return [
                        'country' => $data['country'] ?? null,
                        'country_code' => $data['countryCode'] ?? null,
                        'city' => $data['city'] ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning('GeoIP lookup failed', [
                'ip' => $ipAddress,
                'error' => $e->getMessage(),
            ]);
        }

        return $this->getDefaultLocation();
    }

    /**
     * Check if IP is local/private
     *
     * @param string $ipAddress
     * @return bool
     */
    protected function isLocalIp(string $ipAddress): bool
    {
        // Localhost
        if (in_array($ipAddress, ['127.0.0.1', '::1', 'localhost'])) {
            return true;
        }

        // Private IP ranges
        $privateRanges = [
            '10.0.0.0/8',
            '172.16.0.0/12',
            '192.168.0.0/16',
        ];

        $ip = ip2long($ipAddress);
        if ($ip === false) {
            return true; // Invalid IP, treat as local
        }

        foreach ($privateRanges as $range) {
            [$subnet, $mask] = explode('/', $range);
            $subnetLong = ip2long($subnet);
            $maskLong = ~((1 << (32 - $mask)) - 1);
            
            if (($ip & $maskLong) === ($subnetLong & $maskLong)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get default location (when lookup fails or IP is local)
     *
     * @return array
     */
    protected function getDefaultLocation(): array
    {
        return [
            'country' => null,
            'country_code' => null,
            'city' => null,
        ];
    }

    /**
     * Clear GeoIP cache for a specific IP
     *
     * @param string $ipAddress
     * @return void
     */
    public function clearCache(string $ipAddress): void
    {
        Cache::forget("geoip:{$ipAddress}");
    }
}
