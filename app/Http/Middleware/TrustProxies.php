<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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
        // Set trusted proxies
        if ($this->proxies === '*') {
            // Trust all proxies - useful when behind dynamic proxies
            $request::setTrustedProxies(
                [$request->server->get('REMOTE_ADDR')],
                $this->headers
            );
        } elseif (is_array($this->proxies) && count($this->proxies) > 0) {
            $request::setTrustedProxies($this->proxies, $this->headers);
        }

        // Additional headers check for various proxy/CDN providers
        $this->setRealIpFromHeaders($request);

        return $next($request);
    }

    /**
     * Try to get real IP from various proxy headers.
     * Priority order:
     * 1. CF-Connecting-IP (Cloudflare)
     * 2. X-Real-IP (Nginx)
     * 3. True-Client-IP (Akamai, Cloudflare Enterprise)
     * 4. X-Forwarded-For (Standard)
     */
    protected function setRealIpFromHeaders(Request $request): void
    {
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
            $realIp = $ips[0] ?? null;
        }

        // Validate and store the real IP
        if ($realIp && filter_var($realIp, FILTER_VALIDATE_IP)) {
            // Store in request attributes for later use
            $request->attributes->set('real_client_ip', $realIp);
            
            // Override server REMOTE_ADDR if we have a valid IP
            $request->server->set('REMOTE_ADDR', $realIp);
        }
    }
}
