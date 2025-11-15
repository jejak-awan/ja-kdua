<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AnalyticsVisit;
use App\Models\AnalyticsSession;

class TrackAnalytics
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track GET requests and successful responses
        if ($request->method() === 'GET' && $response->getStatusCode() === 200) {
            // Skip tracking for admin/api routes
            if (!$this->shouldTrack($request)) {
                return $response;
            }

            try {
                $sessionId = session()->getId();
                
                // Start or get session
                $session = AnalyticsSession::start($request, $sessionId);
                
                // Track visit
                AnalyticsVisit::trackVisit($request, $sessionId);
                
                // Update session
                $session->incrementPageViews();
            } catch (\Exception $e) {
                // Log error but don't break the request
                \Log::error('Analytics tracking failed: ' . $e->getMessage());
            }
        }

        return $response;
    }

    protected function shouldTrack(Request $request): bool
    {
        $path = $request->path();
        
        // Don't track admin routes
        if (str_starts_with($path, 'admin') || str_starts_with($path, 'api')) {
            return false;
        }

        // Don't track static assets
        if (preg_match('/\.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$/i', $path)) {
            return false;
        }

        return true;
    }
}
