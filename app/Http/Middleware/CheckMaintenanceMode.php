<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check if maintenance mode is enabled
        $maintenanceEnabled = Setting::get('maintenance_mode', false);

        if (! $maintenanceEnabled) {
            return $next($request);
        }

        // 2. Check for scheduling (End Time)
        $endTime = Setting::get('maintenance_end_time');
        if ($endTime) {
            try {
                $endDateTime = \Illuminate\Support\Carbon::parse($endTime);
                if ($endDateTime->isPast()) {
                    Setting::set('maintenance_mode', false, 'boolean', 'general');

                    return $next($request);
                }
            } catch (\Exception $e) {
                // Ignore parse errors
            }
        }

        // 3. Allow access if it's an Admin
        $user = $request->user();
        if ($user && ($user->hasRole('admin') || $user->can('view settings'))) {
            return $next($request);
        }

        // 4. Allow access to specific bypass routes (Admin Panel, Auth, APIs)
        if ($this->shouldBypass($request)) {
            return $next($request);
        }

        // 5. Return maintenance view for everything else
        return response()->view('maintenance', [
            'title' => Setting::get('maintenance_title', 'Coming Soon'),
            'message' => Setting::get('maintenance_message', 'We are currently working on something awesome. Please check back later.'),
            'countdownEnabled' => Setting::get('maintenance_countdown_enabled', false),
            'endTime' => $endTime,
            'siteName' => Setting::get('site_name', 'JA-CMS'),
            'siteLogo' => Setting::get('site_logo', ''),
        ], 503);
    }

    /**
     * Determine if the request should bypass maintenance mode.
     */
    protected function shouldBypass(Request $request): bool
    {
        $path = $request->path();

        $bypassPaths = [
            'admin*',
            'api/admin*',
            'api/v1/admin*',
            'login',
            'logout',
            'sanctum/csrf-cookie',
            'up', // Health check
        ];

        foreach ($bypassPaths as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }
}
