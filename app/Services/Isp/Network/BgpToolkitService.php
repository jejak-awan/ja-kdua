<?php

declare(strict_types=1);

namespace App\Services\Isp\Network;

use Illuminate\Support\Facades\Http;

class BgpToolkitService
{
    /**
     * Lookup ASN information from RIPEstat.
     *
     * @return array{asn: string, holder: string, country: string, source: string}|null
     */
    public function lookupAsn(string $asn): ?array
    {
        $asn = ltrim(strtoupper($asn), 'AS');

        $response = Http::timeout(10)->get('https://stat.ripe.net/data/as-overview/data.json', [
            'resource' => 'AS'.$asn,
        ]);

        if (! $response->successful()) {
            return null;
        }

        /** @var mixed $body */
        $body = $response->json();

        $data = is_array($body) && is_array($body['data'] ?? null) ? $body['data'] : [];
        $resource = is_string($data['resource'] ?? null) ? $data['resource'] : 'AS'.$asn;
        $holder = is_string($data['holder'] ?? null) ? $data['holder'] : 'Unknown';
        $country = is_string($data['country'] ?? null) ? $data['country'] : '';

        return [
            'asn' => $resource,
            'holder' => $holder,
            'country' => $country,
            'source' => 'RIPEstat',
        ];
    }

    /**
     * Get announced prefixes for an ASN from RIPEstat.
     *
     * @return array<int, array{prefix: string, timelines: string}>
     */
    public function getAsPrefixes(string $asn): array
    {
        $asn = ltrim(strtoupper($asn), 'AS');

        $response = Http::timeout(15)->get('https://stat.ripe.net/data/announced-prefixes/data.json', [
            'resource' => 'AS'.$asn,
        ]);

        if (! $response->successful()) {
            return [];
        }

        /** @var mixed $body */
        $body = $response->json();

        /** @var list<mixed> $prefixesRaw */
        $prefixesRaw = [];
        if (is_array($body) && is_array($body['data'] ?? null) && is_array($body['data']['prefixes'] ?? null)) {
            $prefixesRaw = $body['data']['prefixes'];
        }

        $result = [];

        foreach ($prefixesRaw as $entry) {
            if (! is_array($entry)) {
                continue;
            }
            $prefix = is_string($entry['prefix'] ?? null) ? $entry['prefix'] : '';
            if ($prefix === '') {
                continue;
            }

            $starttime = '';
            $timelines = $entry['timelines'] ?? null;
            if (is_array($timelines) && isset($timelines[0]) && is_array($timelines[0])) {
                $starttime = is_string($timelines[0]['starttime'] ?? null) ? $timelines[0]['starttime'] : '';
            }

            $result[] = [
                'prefix' => $prefix,
                'timelines' => $starttime,
            ];
        }

        return $result;
    }

    /**
     * Generate a RouterOS address-list script from prefixes.
     *
     * @param  array<int, array{prefix: string, timelines: string}>  $prefixes
     */
    public function generateAddressList(string $listName, array $prefixes): string
    {
        // Sanitize list name for RouterOS (quotes and spaces)
        $safeListName = '"'.addslashes($listName).'"';

        $lines = [
            '# RouterOS Address List',
            '# Generated: '.now()->format('Y-m-d H:i:s'),
            '# List: '.$listName,
            '# Total Prefixes: '.count($prefixes),
            '',
            '/ip firewall address-list',
        ];

        foreach ($prefixes as $entry) {
            $prefix = $entry['prefix'];
            // Basic validation that prefix looks like an IP/CIDR
            if (preg_match('/^[0-9a-f\.:\/]+$/i', $prefix)) {
                $lines[] = 'add list='.$safeListName.' address='.$prefix;
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Get BGP Peer status from a Mikrotik router.
     *
     * @return array<int, array{name: string, remote_address: string, state: string, uptime: string}>
     */
    public function getBgpPeerStatus(\App\Models\Isp\Network\ServiceNode $node): array
    {
        /** @var \App\Services\Isp\Network\MikrotikService $mikrotik */
        $mikrotik = app(\App\Services\Isp\Network\MikrotikService::class);

        if (! $mikrotik->connect((string) $node->ip_address, (string) $node->api_username, (string) $node->api_password, (int) ($node->api_port ?? 8728))) {
            return [];
        }

        try {
            $response = $mikrotik->getClient()?->comm('/routing/bgp/peer/print');
            if (! is_array($response)) {
                return [];
            }

            return array_map(function ($row) {
                /** @var array<string, string> $row */
                $name = $row['name'] ?? 'unknown';
                $remoteAddress = $row['remote-address'] ?? 'unknown';
                $state = $row['state'] ?? 'idle';
                $uptime = $row['uptime'] ?? '0s';

                return [
                    'name' => $name,
                    'remote_address' => $remoteAddress,
                    'state' => $state,
                    'uptime' => $uptime,
                ];
            }, $response);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('BGP Peer Status Query Failed: '.$e->getMessage());

            return [];
        }
    }
}
