<?php

use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

// Sitemap routes (must be before SPA route)
Route::get('/sitemap.xml', [SitemapController::class, 'index']);
Route::get('/sitemap.xml/pages', [SitemapController::class, 'pages']);
Route::get('/sitemap.xml/posts', [SitemapController::class, 'posts']);
Route::get('/sitemap.xml/categories', [SitemapController::class, 'categories']);

// Robots.txt (must be before SPA route)
Route::get('/robots.txt', function () {
    $path = public_path('robots.txt');
    if (file_exists($path)) {
        return response(file_get_contents($path), 200, [
            'Content-Type' => 'text/plain',
        ]);
    }

    return response("User-agent: *\nAllow: /\n\nSitemap: ".url('/sitemap.xml'), 200, [
        'Content-Type' => 'text/plain',
    ]);
});

// Log Viewer (protected, must be before SPA route)
// Access at /logs - requires authentication and admin role or 'view logs' permission
Route::middleware(['auth:sanctum', 'web'])->group(function () {
    Route::get('/logs', function () {
        $user = auth()->user();
        if (! $user || (! $user->hasRole('admin') && ! $user->can('view logs'))) {
            abort(403, 'Unauthorized. You need admin role or "view logs" permission.');
        }
        
        // Use Log Viewer package
        return app(\Rap2hpoutre\LaravelLogViewer\LaravelLogViewer::class)->index();
    })->name('logs.index');
    
    Route::get('/logs/{file}', function ($file) {
        $user = auth()->user();
        if (! $user || (! $user->hasRole('admin') && ! $user->can('view logs'))) {
            abort(403, 'Unauthorized. You need admin role or "view logs" permission.');
        }
        
        return app(\Rap2hpoutre\LaravelLogViewer\LaravelLogViewer::class)->show($file);
    })->name('logs.show');
});

// Frontend Routes - All handled by Vue SPA now
// Vue Router will handle: /, /blog, /blog/:slug, /about, /contact, /search

// Specific login route for Laravel middleware compatibility
Route::get('/login', function () {
    return view('app');
})->name('login');

// SPA Route - All routes handled by Vue Router
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');

// Redirects are handled by HandleRedirects middleware
