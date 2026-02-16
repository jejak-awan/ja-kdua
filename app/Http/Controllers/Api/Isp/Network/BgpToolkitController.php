<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Network;

use App\Http\Controllers\Api\Core\BaseApiController;
use App\Services\Isp\Network\BgpToolkitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BgpToolkitController extends BaseApiController
{
    public function __construct(
        protected BgpToolkitService $bgpService
    ) {}

    /**
     * Lookup ASN information.
     */
    public function lookup(Request $request): JsonResponse
    {
        $asn = $request->input('asn');
        if (! is_string($asn) || ! preg_match('/^(AS)?\d+$/i', $asn)) {
            return $this->error('Valid ASN is required (e.g. AS123 or 123)', 422);
        }

        $result = $this->bgpService->lookupAsn($asn);

        if ($result === null) {
            return $this->error('ASN not found or lookup failed', 404);
        }

        return $this->success($result);
    }

    /**
     * Get announced prefixes for an ASN.
     */
    public function prefixes(Request $request): JsonResponse
    {
        $asn = $request->input('asn');
        if (! is_string($asn) || ! preg_match('/^(AS)?\d+$/i', $asn)) {
            return $this->error('Valid ASN is required', 422);
        }

        $prefixes = $this->bgpService->getAsPrefixes($asn);

        return $this->success([
            'asn' => strtoupper(ltrim($asn, 'asAS')),
            'total' => count($prefixes),
            'prefixes' => $prefixes,
        ]);
    }

    /**
     * Download prefixes as RouterOS address-list script.
     */
    public function downloadAddressList(Request $request): StreamedResponse|JsonResponse
    {
        $asn = $request->input('asn');
        if (! is_string($asn) || ! preg_match('/^(AS)?\d+$/i', $asn)) {
            return $this->error('Valid ASN is required', 422);
        }

        $listName = $request->input('list_name');
        if (! is_string($listName) || $listName === '') {
            $listName = 'AS'.ltrim(strtoupper($asn), 'AS');
        }

        // Basic sanitization for list name
        $listName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $listName) ?? $listName;

        $prefixes = $this->bgpService->getAsPrefixes($asn);

        if ($prefixes === []) {
            return $this->error('No prefixes found for this ASN', 404);
        }

        $script = $this->bgpService->generateAddressList($listName, $prefixes);

        return response()->streamDownload(function () use ($script): void {
            echo $script;
        }, $listName.'.rsc', [
            'Content-Type' => 'text/plain',
        ]);
    }
}
