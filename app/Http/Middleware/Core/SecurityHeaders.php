<?php

namespace App\Http\Middleware\Core;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request and add security headers
     */
    public function handle(Request $request, Closure $next): Response
    {
        // JA-CMS CSP STRATEGY: STABILITY OVER STRICT NONCE
        // We do NOT use Nonce because dynamic plugins, themes, and Vue/Vite splitting
        // frequently break with strict nonce enforcement.
        // We rely on 'unsafe-inline' + sanitization for security.

        $response = $next($request);

        // Content Security Policy
        $csp = $this->getContentSecurityPolicy();
        $response->headers->set('Content-Security-Policy', $csp);

        // Standard Security Headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // Remove server signature
        $response->headers->remove('X-Powered-By');

        // Force remove upstream/conflicting Report-Only headers
        // These are often added by Cloudflare or Nginx proxies and can be too strict ('none')
        $response->headers->remove('Content-Security-Policy-Report-Only');
        $response->headers->remove('X-Content-Security-Policy');
        $response->headers->remove('X-WebKit-CSP');

        return $response;
    }

    /**
     * Get Content Security Policy directives
     */
    protected function getContentSecurityPolicy(): string
    {
        $isLocal = app()->environment('local');
        $isDevelopment = app()->environment(['local', 'development']);

        $directives = [
            "default-src 'self'",
        ];

        // Script sources
        // 'unsafe-inline' and 'unsafe-eval' are required for Vue/Vite and many plugins
        $scriptSrc = [
            "'self'",
            "'unsafe-inline'",
            "'unsafe-eval'",
            'https:',
        ];

        // Allow localhost for Vite dev server (including development on production machine)
        $scriptSrc[] = 'http://localhost:5173';
        $scriptSrc[] = 'ws://localhost:5173';

        // Add external script sources
        $scriptSrc = array_merge($scriptSrc, [
            'https://cdn.jsdelivr.net',
            'https://code.jquery.com',
            'https://unpkg.com',
            'https://static.cloudflareinsights.com',
            'https://cloudflareinsights.com',
            // Note: We removed specific hashes ("'sha256-...'") to allow 'unsafe-inline' to work.
            // When hashes or nonces are present, browsers ignore 'unsafe-inline', causing constant blocking
            // of new inline scripts. By removing hashes, we fall back to 'unsafe-inline' which allows
            // all inline scripts (less secure, but "cleaner" and prevents breakage).
        ]);

        // Add same-origin domains
        $host = request()->getHost();
        if ($host) {
            $scriptSrc[] = "https://{$host}";
            $scriptSrc[] = "https://*.{$host}";
        }

        $directives[] = 'script-src '.implode(' ', $scriptSrc);

        // Style sources
        $styleSrc = ["'self'", "'unsafe-inline'"];

        // Allow localhost for Vite dev server (including development on production machine)
        $styleSrc[] = 'http://localhost:5173';

        $styleSrc = array_merge($styleSrc, [
            'https://cdn.jsdelivr.net',
            'https://fonts.googleapis.com',
            'https://fonts.bunny.net',
            'https://unpkg.com',
        ]);

        $directives[] = 'style-src '.implode(' ', $styleSrc);

        // Font sources
        $directives[] = "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net https://fonts.bunny.net data:";

        // Image sources
        $directives[] = "img-src 'self' data: https: blob:";

        // Connect sources (for AJAX, WebSocket, etc.)
        $connectSrc = ["'self'"];

        // Allow localhost WebSocket for Vite HMR (including development on production machine)
        $connectSrc[] = 'http://localhost:5173';
        $connectSrc[] = 'ws://localhost:5173';
        $connectSrc[] = 'wss://localhost:5173';

        $connectSrc = array_merge($connectSrc, [
            'https://nominatim.openstreetmap.org',
            'https://www.emsifa.com',
            'https://static.cloudflareinsights.com',
            'https://cloudflareinsights.com',
            'wss:',
            'ws:',
        ]);

        if ($host) {
            $connectSrc[] = "https://{$host}";
            $connectSrc[] = "https://*.{$host}";
        }

        $directives[] = 'connect-src '.implode(' ', $connectSrc);

        // Frame ancestors
        $directives[] = "frame-ancestors 'self'";

        // Base URI
        $directives[] = "base-uri 'self'";

        // Form action
        $directives[] = "form-action 'self'";

        // Worker sources (for service workers, PDF generation, etc.)
        $directives[] = "worker-src 'self' blob:";

        // Frame sources (for iframes - YouTube, Vimeo, Google Maps, etc.)
        $frameSrc = [
            "'self'",
            'https://www.youtube.com',
            'https://youtube.com',
            'https://www.youtube-nocookie.com',
            'https://player.vimeo.com',
            'https://vimeo.com',
            'https://www.google.com',
            'https://maps.google.com',
            'https://www.google.com/maps',
            'https://open.spotify.com',
            'https://w.soundcloud.com',
            'https://www.dailymotion.com',
            'https://codepen.io',
            'https://jsfiddle.net',
        ];

        if ($host) {
            $frameSrc[] = "https://{$host}";
        }

        $directives[] = 'frame-src '.implode(' ', $frameSrc);

        // CSP Violation Reporting
        $directives[] = 'report-uri /api/v1/security/crep-collect';

        return implode('; ', $directives);
    }
}
