<?php

namespace App\Http\Middleware\Core;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockMaliciousBots
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();

        // Block common scanner paths
        $blockedPaths = [
            'wp-login.php',
            'wp-admin',
            'xmlrpc.php',
            '.env',
            'actuator/health',
            '_profiler',
            'phpinfo',
            'config.json',
            'composer.json',
            'composer.lock',
            'package.json',
            'yarn.lock',
            'telescope',
            'debugbar',
            'storage/logs',
            '/.git',
        ];

        foreach ($blockedPaths as $blockedPath) {
            if (str_contains($path, $blockedPath)) {
                \Illuminate\Support\Facades\Log::channel('security')->warning('Blocked malicious scanner', [
                    'ip' => $request->ip(),
                    'path' => $path,
                    'user_agent' => $request->userAgent(),
                    'type' => 'blocked_path'
                ]);
                return response('Forbidden', 403);
            }
        }

        // Block specific file extensions often probed
        // Exempt the system journal API which legitimately serves .log files
        if (preg_match('/\.(sql|bak|old|swp|zip|tar|gz|rar|env|git|ini|log|sh)$/i', $path)) {
            // Allow legitimate log viewing via API
            if (str_starts_with($path, 'api/v1/admin/janet/system-journal/')) {
                return $next($request);
            }

            \Illuminate\Support\Facades\Log::channel('security')->warning('Blocked malicious extension', [
                'ip' => $request->ip(),
                'path' => $path,
                'user_agent' => $request->userAgent(),
                'type' => 'blocked_extension'
            ]);
            // Allow legitimate zip downloads if authorized (this is a crude check, refine if needed)
            // But usually public zips are handled via specific routes or storage URLs
            return response('Forbidden', 403);
        }

        return $next($request);
    }
}
