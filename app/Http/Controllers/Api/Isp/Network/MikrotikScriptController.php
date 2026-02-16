<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Network;

use App\Http\Controllers\Api\Core\BaseApiController;
use App\Models\Isp\Network\ServiceNode;
use App\Services\Isp\Network\MikrotikScriptService;
use Illuminate\Http\Request;

class MikrotikScriptController extends BaseApiController
{
    protected MikrotikScriptService $scriptService;

    public function __construct(MikrotikScriptService $scriptService)
    {
        $this->scriptService = $scriptService;
    }

    public function generate(Request $request, ServiceNode $infra): \Illuminate\Http\JsonResponse
    {
        if ($infra->type !== 'Router') {
            return $this->error('Not a router', 400);
        }

        $validated = $request->validate([
            'version' => 'required|in:v6,v7',
            'tunnel_type' => 'required|in:none,ovpn,sstp,l2tp',
            'service' => 'required|in:vpn_only,pppoe_only,hotspot_only,both',
            'connection_type' => 'nullable|in:IP PUBLIC,VPN RADIUS',
            'vpn_ip' => 'nullable|string|ip',
        ]);

        $script = $this->scriptService->generate($infra, $validated);

        return $this->success([
            'script' => $script,
            'filename' => 'janet_'.str_replace(' ', '_', strtolower((string) $infra->name)).'.rsc',
        ], 'Mikrotik script generated successfully');
    }
}
