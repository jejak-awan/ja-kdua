<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust proxies for correct client IP detection
        $middleware->prepend(\App\Http\Middleware\TrustProxies::class);

        // Enable Sanctum stateful API for SPA
        $middleware->statefulApi();

        // Ensure guests are redirected to the named 'login' route (SPA)
        $middleware->redirectGuestsTo(fn () => route('login'));

        $middleware->web(append: [
            \App\Http\Middleware\HandleRedirects::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\TrackAnalytics::class,
        ]);

        // Register permission middleware alias
        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
        ]);

        // Exempt analytics from CSRF protection (public tracking endpoint)
        $middleware->validateCsrfTokens(except: [
            'api/v1/analytics/*',
            'api/v1/security/csp-report*',
            'api/v1/security/crep-collect*',
            'v1/security/csp-report*',
            'v1/security/crep-collect*',
            '*/security/crep-collect*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle rate limiting exceptions - add retry_after to response body
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                // Get retry-after from exception headers
                $headers = $e->getHeaders();
                $retryAfter = $headers['Retry-After'] ?? $headers['retry-after'] ?? 60;

                // Ensure it's an integer
                $retryAfter = (int) $retryAfter;

                // Calculate minutes for user-friendly message
                $minutes = ceil($retryAfter / 60);

                return response()->json([
                    'success' => false,
                    'message' => "Too many attempts. Please try again in {$minutes} minute".($minutes > 1 ? 's' : '').'.',
                    'retry_after' => $retryAfter,
                ], 429)->withHeaders([
                    'Retry-After' => $retryAfter,
                    'X-RateLimit-Limit' => 5,
                    'X-RateLimit-Remaining' => 0,
                ]);
            }
        });
    })->create();
