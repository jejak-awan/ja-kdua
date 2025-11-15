<?php

use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

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

// SPA Route - All other routes handled by Vue Router
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');

// Redirects are handled by HandleRedirects middleware
