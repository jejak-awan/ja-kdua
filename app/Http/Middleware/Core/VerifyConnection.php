<?php

declare(strict_types=1);

namespace App\Http\Middleware\Core;

use App\Helpers\IpHelper;
use App\Models\Core\IpList;
use App\Models\Core\Setting;
use App\Services\Core\SecurityService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerifyConnection
{
    protected SecurityService $securityService;

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $mode = Setting::get('shield_protection_mode', 'off');

        // Bypasses
        if ($mode === 'off') {
            return $next($request);
        }

        // 1. Static assets bypass
        if ($request->is('assets/*', 'storage/*', '*/favicon.ico')) {
            return $next($request);
        }

        // 2. IP Bypasses (Whitelisted or Protected)
        $ip = IpHelper::getClientIp($request);
        if ($this->securityService->isProtectedIp($ip) || IpList::isWhitelisted($ip)) {
            return $next($request);
        }

        // 3. Admin user bypass
        if (Auth::check() && Auth::user() instanceof \App\Models\Core\User && Auth::user()->hasRole('admin')) {
            return $next($request);
        }

        // 4. Verify existing trust session
        if ($this->securityService->isShieldVerified($ip, (string) $request->userAgent())) {
            return $next($request);
        }

        // 5. Verification endpoint bypass (must allow the verification itself)
        if ($request->is('api/v1/security/verify-connection')) {
            return $next($request);
        }

        // 6. Suspicious Only mode check
        if ($mode === 'suspicious') {
            // Logic to determine if "suspicious" - e.g. based on headers or recent failed login patterns
            // For now, if it's not verified, we can assume "suspicious" if it's a new session without auth
            if (Auth::check()) {
                return $next($request);
            }
        }

        // ISSUE CHALLENGE
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->issueAjaxChallenge($request);
        }

        return $this->issueHtmlChallenge($request);
    }

    /**
     * Issue a challenge for AJAX/API requests.
     */
    protected function issueAjaxChallenge(Request $request): Response
    {
        $ip = IpHelper::getClientIp($request);
        $nonce = $this->securityService->generateShieldNonce($ip);

        return response()->json([
            'message' => 'Connection verification required',
            'challenge' => [
                'nonce' => $nonce,
                'difficulty' => $this->securityService->getShieldDifficulty(),
            ],
        ], 429)->withHeaders([
            'X-Shield-Challenge' => $nonce,
            'X-Shield-Difficulty' => $this->securityService->getShieldDifficulty(),
        ]);
    }

    /**
     * Issue a challenge for direct HTML requests.
     */
    protected function issueHtmlChallenge(Request $request): Response
    {
        $ip = IpHelper::getClientIp($request);
        $nonce = $this->securityService->generateShieldNonce($ip);
        $difficulty = $this->securityService->getShieldDifficulty();

        // Pass target URL to the view to allow redirection after verification
        return response()->view('errors.security.challenge', [
            'nonce' => $nonce,
            'difficulty' => $difficulty,
            'redirectTo' => $request->fullUrl(),
        ], 429);
    }
}
