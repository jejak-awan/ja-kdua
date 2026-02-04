<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Response;

class TrustProxies
{
    /**
     * The trusted proxies for this application.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = '*'; // Trust all proxies - configure specific IPs in production if needed

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Emergency kill-switch to revert to "trust all" behavior
        if (config('app.trust_all_proxies', false)) {
            $request::setTrustedProxies(
                [$request->server->get('REMOTE_ADDR')],
                $this->headers
            );
            $this->setRealIpFromHeaders($request, true);

            return $next($request);
        }

        // Load trusted IPs from cache (Cloudflare) + Private Networks
        $trustedIps = $this->getTrustedProxies();

        if (count($trustedIps) > 0) {
            $request::setTrustedProxies($trustedIps, $this->headers);
        }

        // Additional headers check (guarded by trust check)
        $this->setRealIpFromHeaders($request, false);

        return $next($request);
    }

    /**
     * Get trusted proxies list.
     */
    protected function getTrustedProxies(): array
    {
        $proxies = [];

        // 1. Load Cloudflare IPs from cache
        $cachePath = storage_path('framework/cache/cloudflare_ips.php');
        if (file_exists($cachePath)) {
            $proxies = include $cachePath;
        }

        // 2. Add Private Networks (RFC 1918)
        // This is safe because if an attacker is in our private network,
        // we have bigger problems. Valid upstream LB/Nginx usually sit here.
        $proxies = array_merge($proxies, [
            '127.0.0.1',
            '::1',
            '10.0.0.0/8',
            '172.16.0.0/12',
            '192.168.0.0/16',
            'fc00::/7', // Unique Local Address
        ]);

        return $proxies;
    }

    /**
     * Try to get real IP from various proxy headers.
     */
    protected function setRealIpFromHeaders(Request $request, bool $forceTrust): void
    {
        // Security: Only parse headers if the remote address is trusted
        if (! $forceTrust) {
            $remoteAddr = $request->server->get('REMOTE_ADDR');
            $trustedProxies = $this->getTrustedProxies();

            if (! IpUtils::checkIp($remoteAddr, $trustedProxies)) {
                // Current proxy is NOT trusted. Do not spoof IP based on headers.
                return;
            }
        }

        $realIp = null;

        // Priority 1: Cloudflare
        if ($request->hasHeader('CF-Connecting-IP')) {
            $realIp = $request->header('CF-Connecting-IP');
        }
        // Priority 2: Nginx X-Real-IP
        elseif ($request->hasHeader('X-Real-IP')) {
            $realIp = $request->header('X-Real-IP');
        }
        // Priority 3: Akamai / Cloudflare Enterprise
        elseif ($request->hasHeader('True-Client-IP')) {
            $realIp = $request->header('True-Client-IP');
        }
        // Priority 4: Standard X-Forwarded-For (get first IP in chain)
        elseif ($request->hasHeader('X-Forwarded-For')) {
            $forwardedFor = $request->header('X-Forwarded-For');
            $ips = array_map('trim', explode(',', $forwardedFor));
            $realIp = $ips[0];
        }

        // Validate and store the real IP
        if ($realIp && filter_var($realIp, FILTER_VALIDATE_IP)) {
            // Store in request attributes for later use
            $request->attributes->set('real_client_ip', $realIp);

            // Override server REMOTE_ADDR if we have a valid IP
            // Note: This relies on setTrustedProxies correctly processing the headers first
            $request->server->set('REMOTE_ADDR', $realIp);
        }
    }
}
