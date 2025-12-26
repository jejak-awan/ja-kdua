<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request and add security headers
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Prevent clickjacking attacks
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        
        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        
        // Enable XSS protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        
        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // Permissions Policy
        $response->headers->set('Permissions-Policy', 
            'geolocation=(), microphone=(), camera=(), payment=(), usb=(), magnetometer=(), gyroscope=()'
        );
        
        // Strict Transport Security (HSTS) - only for HTTPS
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 
                'max-age=31536000; includeSubDomains; preload'
            );
        }
        
        // Content Security Policy
        $csp = $this->getContentSecurityPolicy();
        
        // Remove any existing CSP headers first
        $response->headers->remove('Content-Security-Policy');
        $response->headers->remove('Content-Security-Policy-Report-Only');
        
        // Set our CSP
        $response->headers->set('Content-Security-Policy', $csp, false);
        
        // Remove server signature
        $response->headers->remove('X-Powered-By');
        
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
        $scriptSrc = ["'self'", "'unsafe-inline'", "'unsafe-eval'"];
        
        // Allow localhost for Vite dev server in development
        if ($isDevelopment) {
            $scriptSrc[] = 'http://localhost:5173';
            $scriptSrc[] = 'ws://localhost:5173';
        }
        
        // Add external script sources
        $scriptSrc = array_merge($scriptSrc, [
            'https://cdn.jsdelivr.net',
            'https://code.jquery.com',
            'https://static.cloudflareinsights.com',
        ]);
        
        // Add same-origin domains
        $host = request()->getHost();
        if ($host) {
            $scriptSrc[] = "https://{$host}";
            $scriptSrc[] = "https://*.{$host}";
        }
        
        $directives[] = "script-src " . implode(' ', $scriptSrc);
        
        // Style sources
        $styleSrc = ["'self'", "'unsafe-inline'"];
        
        // Allow localhost for Vite dev server in development
        if ($isDevelopment) {
            $styleSrc[] = 'http://localhost:5173';
        }
        
        $styleSrc = array_merge($styleSrc, [
            'https://cdn.jsdelivr.net',
            'https://fonts.googleapis.com',
            'https://fonts.bunny.net',
        ]);
        
        $directives[] = "style-src " . implode(' ', $styleSrc);
        
        // Font sources
        $directives[] = "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net https://fonts.bunny.net data:";
        
        // Image sources
        $directives[] = "img-src 'self' data: https: blob:";
        
        // Connect sources (for AJAX, WebSocket, etc.)
        $connectSrc = ["'self'"];
        
        // Allow localhost WebSocket for Vite HMR in development
        if ($isDevelopment) {
            $connectSrc[] = 'http://localhost:5173';
            $connectSrc[] = 'ws://localhost:5173';
            $connectSrc[] = 'wss://localhost:5173';
        }
        
        $connectSrc = array_merge($connectSrc, [
            'https://static.cloudflareinsights.com',
            'wss:',
            'ws:',
        ]);
        
        if ($host) {
            $connectSrc[] = "https://{$host}";
            $connectSrc[] = "https://*.{$host}";
        }
        
        $directives[] = "connect-src " . implode(' ', $connectSrc);
        
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
        
        $directives[] = "frame-src " . implode(' ', $frameSrc);
        
        return implode('; ', $directives);
    }
}

