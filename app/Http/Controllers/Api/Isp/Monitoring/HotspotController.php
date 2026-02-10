<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Monitoring;

use App\Http\Controllers\Api\Core\BaseApiController;
use App\Services\Isp\MikrotikService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HotspotController extends BaseApiController
{
    public function __construct(
        protected MikrotikService $mikrotik
    ) {}

    // ============================================================
    // IP BINDING
    // ============================================================

    /**
     * Get all IP Bindings.
     */
    public function ipBindings(): JsonResponse
    {
        $bindings = $this->mikrotik->getIpBindings();

        return $this->success($bindings, 'IP Bindings retrieved successfully');
    }

    /**
     * Add a new IP Binding.
     */
    public function addIpBinding(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mac' => 'nullable|string|regex:/^([0-9A-Fa-f]{2}:){5}[0-9A-Fa-f]{2}$/',
            'address' => 'nullable|ip',
            'type' => 'nullable|in:regular,bypassed,blocked',
            'comment' => 'nullable|string|max:255',
        ]);

        if (empty($validated['mac']) && empty($validated['address'])) {
            return $this->error('Either MAC address or IP address is required', 422);
        }

        $success = $this->mikrotik->addIpBinding($validated);

        if (! $success) {
            return $this->error('Failed to add IP Binding', 500);
        }

        return $this->success(null, 'IP Binding added successfully');
    }

    /**
     * Remove an IP Binding.
     */
    public function removeIpBinding(string $id): JsonResponse
    {
        $success = $this->mikrotik->removeIpBinding($id);

        if (! $success) {
            return $this->error('Failed to remove IP Binding', 500);
        }

        return $this->success(null, 'IP Binding removed successfully');
    }

    /**
     * Toggle IP Binding enable/disable.
     */
    public function toggleIpBinding(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'disabled' => 'required|boolean',
        ]);

        $success = $this->mikrotik->toggleIpBinding($id, (bool) $validated['disabled']);

        if (! $success) {
            return $this->error('Failed to toggle IP Binding', 500);
        }

        return $this->success(null, 'IP Binding toggled successfully');
    }

    // ============================================================
    // HOTSPOT COOKIES
    // ============================================================

    /**
     * Get all Hotspot Cookies.
     */
    public function cookies(): JsonResponse
    {
        $cookies = $this->mikrotik->getHotspotCookies();

        return $this->success($cookies, 'Hotspot Cookies retrieved successfully');
    }

    /**
     * Remove a Hotspot Cookie.
     */
    public function removeCookie(string $id): JsonResponse
    {
        $success = $this->mikrotik->removeHotspotCookie($id);

        if (! $success) {
            return $this->error('Failed to remove Hotspot Cookie', 500);
        }

        return $this->success(null, 'Hotspot Cookie removed successfully');
    }

    // ============================================================
    // INTERFACES (for Traffic Monitor)
    // ============================================================

    /**
     * Get all router interfaces.
     */
    public function interfaces(): JsonResponse
    {
        $interfaces = $this->mikrotik->getInterfaces();

        return $this->success($interfaces, 'Interfaces retrieved successfully');
    }

    /**
     * Get traffic for a specific interface.
     */
    public function interfaceTraffic(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'interface' => 'required|string',
        ]);

        $traffic = $this->mikrotik->getInterfaceTraffic((string) $validated['interface']);

        if ($traffic === null) {
            return $this->error('Failed to get traffic data', 500);
        }

        return $this->success($traffic, 'Interface traffic retrieved successfully');
    }
}
