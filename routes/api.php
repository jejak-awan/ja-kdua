<?php

use Illuminate\Support\Facades\Route;

// API Core
Route::prefix('v1')->group(function () {
    // Public Settings
    Route::get('/public/settings', [App\Http\Controllers\Api\Core\PublicSettingsController::class, 'index']);
    Route::get('/public-settings', [App\Http\Controllers\Api\Core\PublicSettingsController::class, 'index']); // Legacy alias

    // Captcha endpoints (no auth required)
    Route::get('/captcha/generate', [App\Http\Controllers\Api\Core\CaptchaController::class, 'generate']);
    Route::post('/captcha/verify', [App\Http\Controllers\Api\Core\CaptchaController::class, 'verify']);
    Route::get('/captcha/settings', [App\Http\Controllers\Api\Core\CaptchaController::class, 'settings']);

    // Public ISP Status & Payments
    Route::get('/public/isp/status', [App\Http\Controllers\Api\Isp\Network\OutageController::class, 'publicStatus']);
    Route::post('/public/isp/payments/callback', [App\Http\Controllers\Api\Isp\Billing\PaymentController::class, 'callback']);

    // Public endpoint to clear rate limit (no auth required for emergency)
    Route::post('/clear-rate-limit', [App\Http\Controllers\Api\Core\SystemController::class, 'clearRateLimit']);

    // CSP Violation Reporting (public, no auth, rate-limited)
    Route::post('/security/csp-report', [App\Http\Controllers\Api\Core\CspReportController::class, 'store'])
        ->middleware('throttle:100,1');
    Route::post('/security/crep-collect', [App\Http\Controllers\Api\Core\CspReportController::class, 'store'])
        ->middleware('throttle:100,1');

    // Authentication Routes (rate limiting handled by SecurityService with progressive blocking)
    Route::post('/login', [App\Http\Controllers\Api\Core\AuthController::class, 'login'])
        ->middleware('throttle:60,1'); // Increase Laravel throttle to avoid conflict with SecurityService
    Route::post('/register', [App\Http\Controllers\Api\Core\AuthController::class, 'register'])
        ->middleware('throttle:3,1'); // 3 attempts per minute
    Route::post('/logout', [App\Http\Controllers\Api\Core\AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/user', [App\Http\Controllers\Api\Core\AuthController::class, 'user'])->middleware('auth:sanctum');

    // Email Verification
    Route::get('/email/verify/{id}/{hash}', [App\Http\Controllers\Api\Core\AuthController::class, 'verifyEmail'])->name('verification.verify');
    Route::post('/verify-email', [App\Http\Controllers\Api\Core\AuthController::class, 'verifyEmailApi'])->middleware('throttle:5,1');
    Route::post('/resend-verification', [App\Http\Controllers\Api\Core\AuthController::class, 'resendVerificationEmailApi'])->middleware('throttle:3,1');
    Route::post('/email/verification-notification', [App\Http\Controllers\Api\Core\AuthController::class, 'resendVerificationEmail'])->middleware('auth:sanctum');

    // Password Reset (with rate limiting)
    Route::post('/forgot-password', [App\Http\Controllers\Api\Core\AuthController::class, 'forgotPassword'])
        ->middleware('throttle:3,1'); // 3 attempts per minute
    Route::post('/reset-password', [App\Http\Controllers\Api\Core\AuthController::class, 'resetPassword'])
        ->middleware('throttle:3,1'); // 3 attempts per minute

    // User Profile Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [App\Http\Controllers\Api\Core\UserController::class, 'profile']);
        Route::put('/profile', [App\Http\Controllers\Api\Core\UserController::class, 'updateProfile']);
        Route::post('/profile/avatar', [App\Http\Controllers\Api\Core\UserController::class, 'uploadAvatar']);
        Route::put('/profile/password', [App\Http\Controllers\Api\Core\UserController::class, 'updatePassword']);
        Route::get('/profile/access-journal', [App\Http\Controllers\Api\Core\UserController::class, 'loginHistory']);
        Route::get('/profile/preferences', [App\Http\Controllers\Api\Core\UserController::class, 'getPreferences']);
        Route::put('/profile/preferences', [App\Http\Controllers\Api\Core\UserController::class, 'updatePreferences']);

        // Dashboard routes
        Route::get('/dashboard/admin', [App\Http\Controllers\Api\Core\DashboardController::class, 'admin'])->middleware('permission:manage users|manage settings');
        Route::get('/dashboard/creator', [App\Http\Controllers\Api\Core\DashboardController::class, 'creator'])->middleware('permission:create content|edit content');
        Route::get('/dashboard/viewer', [App\Http\Controllers\Api\Core\DashboardController::class, 'viewer']);

        // Two-Factor Authentication Routes
        Route::prefix('two-factor')->group(function () {
            Route::get('/status', [App\Http\Controllers\Api\Core\TwoFactorController::class, 'status']);
            Route::post('/generate', [App\Http\Controllers\Api\Core\TwoFactorController::class, 'generate']);
            Route::post('/verify', [App\Http\Controllers\Api\Core\TwoFactorController::class, 'verify']);
            Route::post('/disable', [App\Http\Controllers\Api\Core\TwoFactorController::class, 'disable']);
            Route::post('/regenerate-backup-codes', [App\Http\Controllers\Api\Core\TwoFactorController::class, 'regenerateBackupCodes']);
        });
    });

    // Two-Factor Authentication Verification (for login, no auth required)
    Route::post('/two-factor/verify-code', [App\Http\Controllers\Api\Core\TwoFactorController::class, 'verifyCode'])->middleware('throttle:5,1');

    // Public CMS API (with rate limiting: 300 requests per minute)
    Route::prefix('ja')->middleware('throttle:300,1')->group(function () {
        Route::get('/contents', [App\Http\Controllers\Api\Core\ContentController::class, 'index']);
        Route::get('/contents/{slug}', [App\Http\Controllers\Api\Core\ContentController::class, 'show']);
        Route::get('/contents/{slug}/related', [App\Http\Controllers\Api\Core\ContentController::class, 'related']);
        Route::get('/categories', [App\Http\Controllers\Api\Core\CategoryController::class, 'index']);
        Route::get('/tags', [App\Http\Controllers\Api\Core\TagController::class, 'index']);

        // Public theme endpoint (no auth required)
        Route::get('/themes/active', [App\Http\Controllers\Api\Core\ThemeController::class, 'getActive']);

        // Comments (public)
        Route::get('/contents/{content}/comments', [App\Http\Controllers\Api\Core\CommentController::class, 'index']);
        Route::post('/contents/{content}/comments', [App\Http\Controllers\Api\Core\CommentController::class, 'store'])->middleware('throttle:10,1');

        // Newsletter (public subscription)
        Route::post('/newsletter/subscribe', [App\Http\Controllers\Api\Core\NewsletterController::class, 'subscribe'])->middleware('throttle:10,1');
        Route::post('/newsletter/unsubscribe', [App\Http\Controllers\Api\Core\NewsletterController::class, 'unsubscribe'])->middleware('throttle:10,1');

        // Forms (public submission - with stricter rate limiting: 10 requests per minute)
        Route::get('/forms/{slug}', function ($slug) {
            $form = \App\Models\Core\Form::where('slug', $slug)->where('is_active', true)->firstOrFail();

            return response()->json($form);
        })->middleware('throttle:10,1'); // 10 form views per minute

        Route::post('/forms/{form:slug}/submit', [App\Http\Controllers\Api\Core\FormController::class, 'submit'])->middleware('throttle:10,1');
        Route::post('/forms/{form:slug}/track', [App\Http\Controllers\Api\Core\FormController::class, 'track'])->middleware('throttle:30,1');

        // Search (public - with rate limiting: 30 requests per minute)
        Route::get('/search', [App\Http\Controllers\Api\Core\SearchController::class, 'search'])->middleware('throttle:30,1');
        Route::get('/search/suggestions', [App\Http\Controllers\Api\Core\SearchController::class, 'suggestions'])->middleware('throttle:30,1');

        // Languages (public)
        Route::get('/languages', [App\Http\Controllers\Api\Core\LanguageController::class, 'index']);

        // Menus (public)
        Route::get('/menus/location/{location}', [App\Http\Controllers\Api\Core\MenuController::class, 'getByLocation']);
        Route::get('/menus/{menu}', [App\Http\Controllers\Api\Core\MenuController::class, 'show']);

        // Widgets (public)
        Route::get('/widgets/location/{location}', [App\Http\Controllers\Api\Core\WidgetController::class, 'getByLocation']);
    });

    // Analytics Event Tracking (public - with rate limiting: 120 requests per minute)
    Route::prefix('analytics')->middleware('throttle:120,1')->group(function () {
        Route::post('/track-visit', [App\Http\Controllers\Api\Core\AnalyticsController::class, 'trackVisit']);
        Route::post('/track', [App\Http\Controllers\Api\Core\AnalyticsController::class, 'trackEvent']);
        Route::post('/track/batch', [App\Http\Controllers\Api\Core\AnalyticsController::class, 'trackBatch']);
    });

    // Frontend Logging (Public/Protected depends on strategy. Better protected by Sanctum if possible, but app crash might lose auth state. Let's keep it under v1 prefix which usually implies auth, but we might want it open for error reporting?
    // Actually, if we use it from logged-in app, auth:sanctum is fine. If we want to catch login page errors, it needs to be public.
    // Let's place it outside auth:sanctum group if possible OR keep it inside.
    // Given complexity, let's put it inside auth group for now to prevent spam, or use throttle.
    Route::post('journal/frontend', [App\Http\Controllers\Api\Core\FrontendLogController::class, 'store'])->middleware('throttle:300,1');

    // Admin Management API (aliased to bypass WAF 403 blocks on /admin/cms/)
    Route::prefix('admin/janet')->middleware(['auth:sanctum', 'throttle:300,1'])->group(function () {
        // Contents
        Route::get('contents/stats', [App\Http\Controllers\Api\Core\ContentController::class, 'stats']);
        Route::get('contents', [App\Http\Controllers\Api\Core\ContentController::class, 'adminIndex']);
        Route::get('contents/{content}', [App\Http\Controllers\Api\Core\ContentController::class, 'adminShow']);
        Route::post('contents', [App\Http\Controllers\Api\Core\ContentController::class, 'store'])->middleware('permission:create content');
        Route::post('contents/autosave', [App\Http\Controllers\Api\Core\ContentController::class, 'autosave'])->middleware('permission:create content');
        Route::put('contents/{content}', [App\Http\Controllers\Api\Core\ContentController::class, 'update'])->middleware('permission:edit content');
        Route::patch('contents/{content}/autosave', [App\Http\Controllers\Api\Core\ContentController::class, 'autosave'])->middleware('permission:edit content');
        Route::delete('contents/{content}', [App\Http\Controllers\Api\Core\ContentController::class, 'destroy'])->middleware('permission:delete content');
        Route::delete('contents/trash/empty', [App\Http\Controllers\Api\Core\ContentController::class, 'emptyTrash'])->middleware('permission:delete content');
        Route::post('contents/{content}/duplicate', [App\Http\Controllers\Api\Core\ContentController::class, 'duplicate'])->middleware('permission:create content');
        Route::post('contents/bulk-action', [App\Http\Controllers\Api\Core\ContentController::class, 'bulkAction'])->middleware('permission:edit content');
        Route::put('contents/{content}/approve', [App\Http\Controllers\Api\Core\ContentController::class, 'approve'])->middleware('permission:approve content');
        Route::put('contents/{content}/reject', [App\Http\Controllers\Api\Core\ContentController::class, 'reject'])->middleware('permission:approve content');
        Route::put('contents/{id}/restore', [App\Http\Controllers\Api\Core\ContentController::class, 'restore'])->middleware('permission:delete content');
        Route::patch('contents/{content}/toggle-featured', [App\Http\Controllers\Api\Core\ContentController::class, 'toggleFeatured'])->middleware('permission:edit content');
        Route::delete('contents/{id}/force-delete', [App\Http\Controllers\Api\Core\ContentController::class, 'forceDelete'])->middleware('permission:delete content');

        // AI Assistance
        Route::get('ai/providers', [App\Http\Controllers\Api\Core\AiController::class, 'getProviders'])->middleware('permission:create content');
        Route::get('ai/models/{provider}', [App\Http\Controllers\Api\Core\AiController::class, 'getModels'])->middleware('permission:create content');
        Route::post('ai/test', [App\Http\Controllers\Api\Core\AiController::class, 'testConnection'])->middleware('permission:create content');
        Route::post('ai/generate', [App\Http\Controllers\Api\Core\AiController::class, 'generate'])->middleware('permission:create content');

        // Newsletter
        Route::post('newsletter/subscribers/bulk-action', [App\Http\Controllers\Api\Core\NewsletterController::class, 'bulkAction'])->middleware('permission:manage users');
        Route::get('newsletter/subscribers', [App\Http\Controllers\Api\Core\NewsletterController::class, 'index'])->middleware('permission:manage users');
        Route::delete('newsletter/subscribers/{id}', [App\Http\Controllers\Api\Core\NewsletterController::class, 'destroy'])->middleware('permission:manage users');
        Route::post('newsletter/subscribers/{id}/restore', [App\Http\Controllers\Api\Core\NewsletterController::class, 'restore'])->middleware('permission:manage users');
        Route::delete('newsletter/subscribers/{id}/force-delete', [App\Http\Controllers\Api\Core\NewsletterController::class, 'forceDelete'])->middleware('permission:manage users');
        Route::get('newsletter/export', [App\Http\Controllers\Api\Core\NewsletterController::class, 'export'])->middleware('permission:manage users');

        // Content Revisions
        Route::get('contents/{content}/revisions', [App\Http\Controllers\Api\Core\ContentRevisionController::class, 'index']);
        Route::get('contents/{content}/revisions/{revision}', [App\Http\Controllers\Api\Core\ContentRevisionController::class, 'show']);
        Route::post('contents/{content}/revisions', [App\Http\Controllers\Api\Core\ContentRevisionController::class, 'store']);
        Route::post('contents/{content}/revisions/{revision}/restore', [App\Http\Controllers\Api\Core\ContentRevisionController::class, 'restore'])->middleware('permission:edit content');
        Route::delete('contents/{content}/revisions/{revision}', [App\Http\Controllers\Api\Core\ContentRevisionController::class, 'destroy']);

        // Content Locking
        Route::post('contents/{content}/lock', [App\Http\Controllers\Api\Core\ContentController::class, 'lock']);
        Route::post('contents/{content}/unlock', [App\Http\Controllers\Api\Core\ContentController::class, 'unlock']);

        // Categories
        Route::post('categories/bulk-destroy', [App\Http\Controllers\Api\Core\CategoryController::class, 'bulkDestroy'])->middleware('permission:edit categories');
        Route::apiResource('categories', App\Http\Controllers\Api\Core\CategoryController::class)->middleware('permission:view categories');
        Route::post('categories/{category}/move', [App\Http\Controllers\Api\Core\CategoryController::class, 'move'])->middleware('permission:edit categories');

        // Content Templates
        Route::post('content-templates/bulk-action', [App\Http\Controllers\Api\Core\ContentTemplateController::class, 'bulkAction'])->middleware('permission:edit content templates');
        Route::put('content-templates/{id}/restore', [App\Http\Controllers\Api\Core\ContentTemplateController::class, 'restore'])->middleware('permission:edit content templates');
        Route::delete('content-templates/{id}/force-delete', [App\Http\Controllers\Api\Core\ContentTemplateController::class, 'forceDelete'])->middleware('permission:delete content templates');
        Route::post('content-templates/{content_template}/create-content', [App\Http\Controllers\Api\Core\ContentTemplateController::class, 'createContent'])->middleware('permission:create content');
        Route::apiResource('content-templates', App\Http\Controllers\Api\Core\ContentTemplateController::class)->middleware('permission:view content templates');

        // Tags
        // Statistics and bulk-delete routes must be defined before apiResource to avoid route conflict
        Route::get('tags/statistics', [App\Http\Controllers\Api\Core\TagController::class, 'statistics'])->middleware('permission:view tags');
        Route::post('tags/bulk-delete', [App\Http\Controllers\Api\Core\TagController::class, 'bulkDelete'])->middleware('permission:delete tags');
        Route::apiResource('tags', App\Http\Controllers\Api\Core\TagController::class)->middleware('permission:view tags');

        // Media (with rate limiting for uploads - increased for better UX)
        Route::get('media/filters', [App\Http\Controllers\Api\Core\MediaController::class, 'filters'])->middleware('permission:view media');
        Route::get('media/statistics', [App\Http\Controllers\Api\Core\MediaController::class, 'statistics'])->middleware('permission:view media');
        Route::post('media/upload', [App\Http\Controllers\Api\Core\MediaController::class, 'upload'])
            ->middleware('throttle:30,1'); // 30 uploads per minute (increased from 10)
        Route::post('media/upload-multiple', [App\Http\Controllers\Api\Core\MediaController::class, 'uploadMultiple'])
            ->middleware('throttle:10,1'); // 10 batch uploads per minute (increased from 5)
        Route::post('media/bulk-action', [App\Http\Controllers\Api\Core\MediaController::class, 'bulkAction'])->middleware('permission:edit media');
        Route::delete('media/empty-trash', [App\Http\Controllers\Api\Core\MediaController::class, 'emptyTrash'])->middleware('permission:delete media');
        Route::post('media/empty-trash', [App\Http\Controllers\Api\Core\MediaController::class, 'emptyTrash'])->middleware('permission:delete media'); // Frontend compat
        Route::post('media/download-zip', [App\Http\Controllers\Api\Core\MediaController::class, 'downloadZip'])->middleware('permission:view media');
        Route::post('media/scan', [App\Http\Controllers\Api\Core\MediaController::class, 'scan'])->middleware('permission:manage media');
        Route::get('media', [App\Http\Controllers\Api\Core\MediaController::class, 'index'])->middleware('permission:view media');
        Route::get('media/{media}', [App\Http\Controllers\Api\Core\MediaController::class, 'show'])->middleware('permission:view media');
        Route::put('media/{media}', [App\Http\Controllers\Api\Core\MediaController::class, 'update']);
        Route::delete('media/{media}', [App\Http\Controllers\Api\Core\MediaController::class, 'destroy']);
        Route::post('media/{media}/delete', [App\Http\Controllers\Api\Core\MediaController::class, 'destroy']); // Frontend compat
        Route::post('media/{id}/restore', [App\Http\Controllers\Api\Core\MediaController::class, 'restore']);
        Route::delete('media/{id}/force-delete', [App\Http\Controllers\Api\Core\MediaController::class, 'forceDelete']);
        Route::post('media/{id}/force-delete', [App\Http\Controllers\Api\Core\MediaController::class, 'forceDelete']); // Frontend compat
        Route::post('media/{media}/thumbnail', [App\Http\Controllers\Api\Core\MediaController::class, 'generateThumbnail'])->middleware('permission:edit media');
        Route::post('media/{media}/resize', [App\Http\Controllers\Api\Core\MediaController::class, 'resize'])->middleware('permission:edit media');
        Route::post('media/{media}/edit', [App\Http\Controllers\Api\Core\MediaController::class, 'edit']);
        Route::get('media/{media}/usage', [App\Http\Controllers\Api\Core\MediaController::class, 'usage'])->middleware('permission:view media');

        // Media Folders
        Route::post('media-folders/{id}/restore', [App\Http\Controllers\Api\Core\MediaFolderController::class, 'restore'])->middleware('permission:edit media');
        Route::delete('media-folders/{id}/force-delete', [App\Http\Controllers\Api\Core\MediaFolderController::class, 'forceDelete'])->middleware('permission:delete media');
        Route::post('media-folders/{id}/force-delete', [App\Http\Controllers\Api\Core\MediaFolderController::class, 'forceDelete'])->middleware('permission:delete media'); // Frontend compat
        Route::apiResource('media-folders', App\Http\Controllers\Api\Core\MediaFolderController::class)->middleware('permission:view media');
        Route::post('media-folders/{mediaFolder}/delete', [App\Http\Controllers\Api\Core\MediaFolderController::class, 'destroy'])->middleware('permission:delete media'); // Frontend compat
        Route::post('media-folders/{mediaFolder}/move', [App\Http\Controllers\Api\Core\MediaFolderController::class, 'move'])->middleware('permission:edit media');

        // Comments (admin)
        Route::get('comments', [App\Http\Controllers\Api\Core\CommentController::class, 'adminIndex']);
        Route::get('comments/statistics', [App\Http\Controllers\Api\Core\CommentController::class, 'statistics']);
        Route::post('comments/bulk', [App\Http\Controllers\Api\Core\CommentController::class, 'bulkAction'])->middleware('permission:manage comments');
        Route::put('comments/{comment}/approve', [App\Http\Controllers\Api\Core\CommentController::class, 'approve'])->middleware('permission:manage comments');
        Route::put('comments/{comment}/reject', [App\Http\Controllers\Api\Core\CommentController::class, 'reject'])->middleware('permission:manage comments');
        Route::put('comments/{comment}/spam', [App\Http\Controllers\Api\Core\CommentController::class, 'markAsSpam'])->middleware('permission:manage comments');
        Route::delete('comments/{comment}', [App\Http\Controllers\Api\Core\CommentController::class, 'destroy'])->middleware('permission:manage comments');

        // Users Management
        Route::post('users/bulk-action', [App\Http\Controllers\Api\Core\UserController::class, 'bulkAction'])->middleware('permission:manage users');
        Route::get('users/stats', [App\Http\Controllers\Api\Core\UserController::class, 'stats'])->middleware('permission:manage users');
        Route::apiResource('users', App\Http\Controllers\Api\Core\UserController::class)->middleware('permission:manage users');
        Route::post('users/{user}/force-logout', [App\Http\Controllers\Api\Core\UserController::class, 'forceLogout'])->middleware('permission:manage users');
        Route::post('users/{user}/verify', [App\Http\Controllers\Api\Core\UserController::class, 'verify'])->middleware('permission:manage users');
        Route::post('users/{user}/restore', [App\Http\Controllers\Api\Core\UserController::class, 'restore'])->middleware('permission:manage users');
        Route::delete('users/{user}/force-delete', [App\Http\Controllers\Api\Core\UserController::class, 'forceDelete'])->middleware('permission:manage users');

        // Roles (for user management)
        Route::post('roles/bulk-action', [App\Http\Controllers\Api\Core\RoleController::class, 'bulkAction'])->middleware('permission:manage users');
        Route::get('roles', [App\Http\Controllers\Api\Core\RoleController::class, 'index'])->middleware('permission:manage users');
        Route::get('roles/permissions', [App\Http\Controllers\Api\Core\RoleController::class, 'permissions'])->middleware('permission:manage users');
        Route::post('roles', [App\Http\Controllers\Api\Core\RoleController::class, 'store'])->middleware('permission:manage users');
        Route::get('roles/{role}', [App\Http\Controllers\Api\Core\RoleController::class, 'show'])->middleware('permission:manage users');
        Route::put('roles/{role}', [App\Http\Controllers\Api\Core\RoleController::class, 'update'])->middleware('permission:manage users');
        Route::delete('roles/{role}', [App\Http\Controllers\Api\Core\RoleController::class, 'destroy'])->middleware('permission:manage users');
        Route::post('roles/{role}/permissions', [App\Http\Controllers\Api\Core\RoleController::class, 'syncPermissions'])->middleware('permission:manage users');
        Route::post('roles/{role}/duplicate', [App\Http\Controllers\Api\Core\RoleController::class, 'duplicate'])->middleware('permission:manage users');

        // Activity Logs
        Route::get('activity-journal', [App\Http\Controllers\Api\Core\ActivityLogController::class, 'index'])->middleware('permission:manage users');
        Route::post('activity-journal/clear', [App\Http\Controllers\Api\Core\ActivityLogController::class, 'clear'])->middleware('permission:manage settings');
        Route::get('activity-journal/recent', [App\Http\Controllers\Api\Core\ActivityLogController::class, 'recent'])->middleware('permission:manage users');
        Route::get('activity-journal/statistics', [App\Http\Controllers\Api\Core\ActivityLogController::class, 'statistics'])->middleware('permission:manage users');
        Route::get('activity-journal/export', [App\Http\Controllers\Api\Core\ActivityLogController::class, 'export'])->middleware('permission:manage users');
        Route::get('activity-journal/user/{userId}', [App\Http\Controllers\Api\Core\ActivityLogController::class, 'userActivity'])->middleware('permission:manage users');
        Route::get('activity-journal/{activityLog}', [App\Http\Controllers\Api\Core\ActivityLogController::class, 'show'])->middleware('permission:manage users');

        // Login History (Admin)
        Route::get('access-journal', [App\Http\Controllers\Api\Core\LoginHistoryController::class, 'index'])->middleware('permission:manage users');
        Route::post('access-journal/clear', [App\Http\Controllers\Api\Core\LoginHistoryController::class, 'clear'])->middleware('permission:manage settings');
        Route::get('access-journal/statistics', [App\Http\Controllers\Api\Core\LoginHistoryController::class, 'statistics'])->middleware('permission:manage users');
        Route::get('access-journal/export', [App\Http\Controllers\Api\Core\LoginHistoryController::class, 'export'])->middleware('permission:manage users');

        // SEO Tools
        Route::get('seo/sitemap', [App\Http\Controllers\Api\Core\SeoController::class, 'generateSitemap']);
        Route::get('seo/robots-txt', [App\Http\Controllers\Api\Core\SeoController::class, 'getRobotsTxt'])->middleware('permission:manage settings');
        Route::put('seo/robots-txt', [App\Http\Controllers\Api\Core\SeoController::class, 'updateRobotsTxt'])->middleware('permission:manage settings');
        Route::get('contents/{content}/seo-analysis', [App\Http\Controllers\Api\Core\SeoController::class, 'analyzeContent'])->middleware('permission:edit content');
        Route::get('contents/{content}/schema', [App\Http\Controllers\Api\Core\SeoController::class, 'generateSchema']);

        // Redirects
        // Statistics route must be defined before apiResource to avoid route conflict
        Route::get('redirects/statistics', [App\Http\Controllers\Api\Core\RedirectController::class, 'statistics'])->middleware('permission:manage settings');
        Route::apiResource('redirects', App\Http\Controllers\Api\Core\RedirectController::class)->middleware('permission:manage settings');

        // Cache Management
        Route::post('cache/clear', function () {
            $cacheService = new \App\Services\Core\CacheService;
            $cacheService->clearAll();

            return response()->json(['message' => 'All caches cleared successfully']);
        })->middleware('permission:manage settings');

        Route::post('cache/clear-content', function () {
            $cacheService = new \App\Services\Core\CacheService;
            $cacheService->clearContentCaches();

            return response()->json(['message' => 'Content caches cleared successfully']);
        })->middleware('permission:manage settings');

        Route::post('cache/warm-up', function () {
            $cacheService = new \App\Services\Core\CacheWarmingService;
            $cacheService->warmUp();

            return response()->json(['message' => 'Cache warmed up successfully']);
        })->middleware('permission:manage settings');

        // Backups
        // Statistics route must be defined before apiResource to avoid route conflict
        Route::get('backups/statistics', [App\Http\Controllers\Api\Core\BackupController::class, 'stats'])->middleware('permission:manage backups');
        Route::get('backups/stats', [App\Http\Controllers\Api\Core\BackupController::class, 'stats'])->middleware('permission:manage backups');
        Route::match(['GET', 'POST'], 'backups/schedule', [App\Http\Controllers\Api\Core\BackupController::class, 'schedule'])->middleware('permission:manage backups');
        Route::post('backups/cleanup', [App\Http\Controllers\Api\Core\BackupController::class, 'cleanup'])->middleware('permission:manage backups');
        Route::apiResource('backups', App\Http\Controllers\Api\Core\BackupController::class)->middleware('permission:manage backups');
        Route::post('backups/{backup}/restore', [App\Http\Controllers\Api\Core\BackupController::class, 'restore'])->middleware('permission:manage backups');
        Route::get('backups/{backup}/download', [App\Http\Controllers\Api\Core\BackupController::class, 'download'])->middleware('permission:manage backups');

        // Security
        Route::get('security/journal', [App\Http\Controllers\Api\Core\SecurityController::class, 'index'])->middleware('permission:manage settings');
        Route::delete('security/journal', [App\Http\Controllers\Api\Core\SecurityController::class, 'clear'])->middleware('permission:manage settings');
        Route::post('security/journal/clear', [App\Http\Controllers\Api\Core\SecurityController::class, 'clear'])->middleware('permission:manage settings');
        Route::get('security/journal/{securityLog}', [App\Http\Controllers\Api\Core\SecurityController::class, 'show'])->middleware('permission:manage settings');
        Route::get('security/stats', [App\Http\Controllers\Api\Core\SecurityController::class, 'stats'])->middleware('permission:manage settings');
        Route::get('security/alerts', [App\Http\Controllers\Api\Core\SecurityController::class, 'alerts'])->middleware('permission:manage settings');

        // IP Blocklist
        Route::get('security/blocklist', [App\Http\Controllers\Api\Core\SecurityController::class, 'getBlocklist'])->middleware('permission:manage settings');
        Route::post('security/block-ip', [App\Http\Controllers\Api\Core\SecurityController::class, 'blockIp'])->middleware('permission:manage settings');
        Route::post('security/unblock-ip', [App\Http\Controllers\Api\Core\SecurityController::class, 'unblockIp'])->middleware('permission:manage settings');
        Route::post('security/bulk-block', [App\Http\Controllers\Api\Core\SecurityController::class, 'bulkBlock'])->middleware('permission:manage settings');
        Route::post('security/bulk-unblock', [App\Http\Controllers\Api\Core\SecurityController::class, 'bulkUnblock'])->middleware('permission:manage settings');

        // IP Whitelist
        Route::get('security/whitelist', [App\Http\Controllers\Api\Core\SecurityController::class, 'getWhitelist'])->middleware('permission:manage settings');
        Route::post('security/whitelist', [App\Http\Controllers\Api\Core\SecurityController::class, 'addToWhitelist'])->middleware('permission:manage settings');
        Route::delete('security/whitelist', [App\Http\Controllers\Api\Core\SecurityController::class, 'removeFromWhitelist'])->middleware('permission:manage settings');
        Route::post('security/bulk-whitelist', [App\Http\Controllers\Api\Core\SecurityController::class, 'bulkWhitelist'])->middleware('permission:manage settings');
        Route::post('security/bulk-remove-whitelist', [App\Http\Controllers\Api\Core\SecurityController::class, 'bulkRemoveWhitelist'])->middleware('permission:manage settings');

        // IP Check & Clear
        Route::get('security/check-ip', [App\Http\Controllers\Api\Core\SecurityController::class, 'checkIp'])->middleware('permission:manage settings');
        Route::post('security/clear-failed-attempts', [App\Http\Controllers\Api\Core\SecurityController::class, 'clearFailedAttempts'])->middleware('permission:manage settings');

        // Themes
        Route::get('themes/active', [App\Http\Controllers\Api\Core\ThemeController::class, 'getActive']);
        Route::get('themes/active/locations', [App\Http\Controllers\Api\Core\ThemeController::class, 'locations']);
        Route::apiResource('themes', App\Http\Controllers\Api\Core\ThemeController::class)->middleware('permission:manage themes');
        Route::post('themes/{theme}/activate', [App\Http\Controllers\Api\Core\ThemeController::class, 'activate'])->middleware('permission:manage themes');
        Route::post('themes/{theme}/deactivate', [App\Http\Controllers\Api\Core\ThemeController::class, 'deactivate'])->middleware('permission:manage themes');
        Route::post('themes/scan', [App\Http\Controllers\Api\Core\ThemeController::class, 'scan'])->middleware('permission:manage themes');
        Route::post('themes/{theme}/validate', [App\Http\Controllers\Api\Core\ThemeController::class, 'validate'])->middleware('permission:manage themes');
        Route::get('themes/{theme}/assets', [App\Http\Controllers\Api\Core\ThemeController::class, 'getAssets'])->middleware('permission:manage themes');
        Route::post('themes/{theme}/compile', [App\Http\Controllers\Api\Core\ThemeController::class, 'compileAssets'])->middleware('permission:manage themes');
        Route::get('themes/{theme}/setting', [App\Http\Controllers\Api\Core\ThemeController::class, 'getSetting'])->middleware('permission:manage themes');
        Route::put('themes/{theme}/settings', [App\Http\Controllers\Api\Core\ThemeController::class, 'updateSettings'])->middleware('permission:manage themes');
        Route::put('themes/{theme}/custom-css', [App\Http\Controllers\Api\Core\ThemeController::class, 'updateCustomCss'])->middleware('permission:manage themes');
        Route::get('themes/{theme}/export', [App\Http\Controllers\Api\Core\ThemeController::class, 'export'])->middleware('permission:manage themes');
        Route::post('themes/import', [App\Http\Controllers\Api\Core\ThemeController::class, 'import'])->middleware('permission:manage themes');
        Route::get('themes/{theme}/partials', [App\Http\Controllers\Api\Core\ThemeController::class, 'getPartials'])->middleware('permission:manage themes');
        Route::get('themes/{theme}/layouts', [App\Http\Controllers\Api\Core\ThemeController::class, 'getLayouts'])->middleware('permission:manage themes');
        Route::post('themes/{theme}/partials/render', [App\Http\Controllers\Api\Core\ThemeController::class, 'renderPartial'])->middleware('permission:manage themes');
        Route::post('themes/{theme}/layouts/render', [App\Http\Controllers\Api\Core\ThemeController::class, 'renderLayout'])->middleware('permission:manage themes');

        // Menus
        Route::post('menus/bulk-action', [App\Http\Controllers\Api\Core\MenuController::class, 'bulkAction'])->middleware('permission:manage menus');
        Route::post('menus/{menu}/restore', [App\Http\Controllers\Api\Core\MenuController::class, 'restore'])->middleware('permission:manage menus');
        Route::delete('menus/{menu}/force-delete', [App\Http\Controllers\Api\Core\MenuController::class, 'forceDelete'])->middleware('permission:manage menus');
        Route::apiResource('menus', App\Http\Controllers\Api\Core\MenuController::class)->middleware('permission:manage menus');
        Route::get('menus/{menu}/items', [App\Http\Controllers\Api\Core\MenuController::class, 'items'])->middleware('permission:manage menus');
        Route::post('menus/{menu}/items', [App\Http\Controllers\Api\Core\MenuController::class, 'addItem'])->middleware('permission:manage menus');
        Route::put('menus/{menu}/items/{menuItem}', [App\Http\Controllers\Api\Core\MenuController::class, 'updateItem'])->middleware('permission:manage menus');
        Route::delete('menus/{menu}/items/{menuItem}', [App\Http\Controllers\Api\Core\MenuController::class, 'deleteItem'])->middleware('permission:manage menus');
        Route::post('menus/{menu}/reorder', [App\Http\Controllers\Api\Core\MenuController::class, 'reorderItems'])->middleware('permission:manage menus');
        Route::get('menus/location/{location}', [App\Http\Controllers\Api\Core\MenuController::class, 'getByLocation']);

        // Widgets
        Route::get('widgets/locations', [App\Http\Controllers\Api\Core\WidgetController::class, 'locations']);
        Route::apiResource('widgets', App\Http\Controllers\Api\Core\WidgetController::class)->middleware('permission:manage widgets');
        Route::get('widgets/location/{location}', [App\Http\Controllers\Api\Core\WidgetController::class, 'getByLocation']);
        Route::post('widgets/reorder', [App\Http\Controllers\Api\Core\WidgetController::class, 'reorder'])->middleware('permission:manage widgets');

        // Plugins
        Route::apiResource('plugins', App\Http\Controllers\Api\Core\PluginController::class)->middleware('permission:manage plugins');
        Route::post('plugins/{plugin}/activate', [App\Http\Controllers\Api\Core\PluginController::class, 'activate'])->middleware('permission:manage plugins');
        Route::post('plugins/{plugin}/deactivate', [App\Http\Controllers\Api\Core\PluginController::class, 'deactivate'])->middleware('permission:manage plugins');
        Route::put('plugins/{plugin}/settings', [App\Http\Controllers\Api\Core\PluginController::class, 'updateSettings'])->middleware('permission:manage plugins');
        Route::get('plugins/active', [App\Http\Controllers\Api\Core\PluginController::class, 'getActive']);

        // Webhooks
        // Statistics route must be defined before apiResource to avoid route conflict
        Route::get('webhooks/statistics', [App\Http\Controllers\Api\Core\WebhookController::class, 'statistics'])->middleware('permission:manage settings');
        Route::apiResource('webhooks', App\Http\Controllers\Api\Core\WebhookController::class)->middleware('permission:manage settings');
        Route::post('webhooks/{webhook}/test', [App\Http\Controllers\Api\Core\WebhookController::class, 'test'])->middleware('permission:manage settings');

        // Custom Fields
        Route::apiResource('field-groups', App\Http\Controllers\Api\Core\FieldGroupController::class)->middleware('permission:manage content');
        Route::apiResource('custom-fields', App\Http\Controllers\Api\Core\CustomFieldController::class)->middleware('permission:manage content');
        Route::get('custom-fields/types', [App\Http\Controllers\Api\Core\CustomFieldController::class, 'getFieldTypes']);

        // Forms
        Route::post('forms/bulk-action', [App\Http\Controllers\Api\Core\FormController::class, 'bulkAction'])->middleware('permission:manage forms');
        Route::post('forms/{form}/duplicate', [App\Http\Controllers\Api\Core\FormController::class, 'duplicate'])->middleware('permission:manage forms');
        Route::post('forms/{form}/restore', [App\Http\Controllers\Api\Core\FormController::class, 'restore'])->middleware('permission:manage forms');
        Route::delete('forms/{form}/force-delete', [App\Http\Controllers\Api\Core\FormController::class, 'forceDelete'])->middleware('permission:manage forms');
        Route::apiResource('forms', App\Http\Controllers\Api\Core\FormController::class)->middleware('permission:manage forms');
        Route::post('forms/{form}/fields', [App\Http\Controllers\Api\Core\FormController::class, 'addField'])->middleware('permission:manage forms');
        Route::put('forms/{form}/fields/{formField}', [App\Http\Controllers\Api\Core\FormController::class, 'updateField'])->middleware('permission:manage forms');
        Route::delete('forms/{form}/fields/{formField}', [App\Http\Controllers\Api\Core\FormController::class, 'deleteField'])->middleware('permission:manage forms');
        Route::post('forms/{form}/reorder-fields', [App\Http\Controllers\Api\Core\FormController::class, 'reorderFields'])->middleware('permission:manage forms');
        // Form submission (public - with rate limiting: 10 requests per minute to prevent spam)
        Route::post('forms/{form}/submit', [App\Http\Controllers\Api\Core\FormController::class, 'submit'])->middleware('throttle:10,1');

        // Form Submissions
        Route::get('forms/{form}/submissions', [App\Http\Controllers\Api\Core\FormSubmissionController::class, 'index'])->middleware('permission:manage forms');
        Route::get('form-submissions', [App\Http\Controllers\Api\Core\FormSubmissionController::class, 'index'])->middleware('permission:manage forms');
        Route::get('/form-submissions/{formSubmission}/export-pdf', [App\Http\Controllers\Api\Core\FormSubmissionController::class, 'exportPdf'])->middleware('permission:manage forms');
        Route::get('form-submissions/{formSubmission}', [App\Http\Controllers\Api\Core\FormSubmissionController::class, 'show'])->middleware('permission:manage forms');
        Route::put('form-submissions/{formSubmission}/read', [App\Http\Controllers\Api\Core\FormSubmissionController::class, 'markAsRead'])->middleware('permission:manage forms');
        Route::put('form-submissions/{formSubmission}/archive', [App\Http\Controllers\Api\Core\FormSubmissionController::class, 'archive'])->middleware('permission:manage forms');
        Route::delete('form-submissions/{formSubmission}', [App\Http\Controllers\Api\Core\FormSubmissionController::class, 'destroy'])->middleware('permission:manage forms');
        Route::post('form-submissions/{formSubmission}/restore', [App\Http\Controllers\Api\Core\FormSubmissionController::class, 'restore'])->middleware('permission:manage forms');
        Route::delete('form-submissions/{formSubmission}/force-delete', [App\Http\Controllers\Api\Core\FormSubmissionController::class, 'forceDelete'])->middleware('permission:manage forms');
        Route::get('forms/{form}/submissions/export', [App\Http\Controllers\Api\Core\FormSubmissionController::class, 'export'])->middleware('permission:manage forms');
        Route::get('forms/{form}/submissions/statistics', [App\Http\Controllers\Api\Core\FormSubmissionController::class, 'statistics'])->middleware('permission:manage forms');

        // Analytics
        Route::prefix('analytics')->middleware('permission:view analytics')->group(function () {
            Route::get('overview', [App\Http\Controllers\Api\Core\AnalyticsController::class, 'overview']);
            Route::get('visits', [App\Http\Controllers\Api\Core\AnalyticsController::class, 'visits']);
            Route::get('top-pages', [App\Http\Controllers\Api\Core\AnalyticsController::class, 'topPages']);
            Route::get('top-content', [App\Http\Controllers\Api\Core\AnalyticsController::class, 'topContent']);
            Route::get('devices', [App\Http\Controllers\Api\Core\AnalyticsController::class, 'devices']);
            Route::get('browsers', [App\Http\Controllers\Api\Core\AnalyticsController::class, 'browsers']);
            Route::get('countries', [App\Http\Controllers\Api\Core\AnalyticsController::class, 'countries']);
            Route::get('referrers', [App\Http\Controllers\Api\Core\AnalyticsController::class, 'referrers']);
            Route::get('events', [App\Http\Controllers\Api\Core\AnalyticsController::class, 'events']);
            Route::get('event-stats', [App\Http\Controllers\Api\Core\AnalyticsController::class, 'eventStats']);
            // Realtime endpoint needs higher rate limit for polling
            Route::get('realtime', [App\Http\Controllers\Api\Core\AnalyticsController::class, 'realTime'])
                ->middleware('throttle:120,1'); // 120 requests per minute for real-time polling
            // Export analytics data to CSV
            Route::get('export', [App\Http\Controllers\Api\Core\AnalyticsController::class, 'export']);
        });

        // Search
        Route::prefix('search')->group(function () {
            Route::get('', [App\Http\Controllers\Api\Core\SearchController::class, 'search']);
            Route::get('suggestions', [App\Http\Controllers\Api\Core\SearchController::class, 'suggestions']);
            Route::get('popular-queries', [App\Http\Controllers\Api\Core\SearchController::class, 'popularQueries'])->middleware('permission:view analytics');
            Route::get('no-results-queries', [App\Http\Controllers\Api\Core\SearchController::class, 'noResultsQueries'])->middleware('permission:view analytics');
            Route::get('stats', [App\Http\Controllers\Api\Core\SearchController::class, 'searchStats'])->middleware('permission:view analytics');
            Route::post('reindex', [App\Http\Controllers\Api\Core\SearchController::class, 'reindex'])->middleware('permission:manage content');
        });

        // Settings
        Route::apiResource('settings', App\Http\Controllers\Api\Core\SettingController::class)->middleware('permission:view settings');
        Route::get('settings/group/{group}', [App\Http\Controllers\Api\Core\SettingController::class, 'getGroup']);
        Route::post('settings/bulk-update', [App\Http\Controllers\Api\Core\SettingController::class, 'bulkUpdate'])->middleware('permission:manage settings');
        Route::post('settings/test-storage', [App\Http\Controllers\Api\Core\SettingController::class, 'testStorage'])->middleware('permission:manage settings');

        // Storage Migration
        Route::get('storage/migration/files', [App\Http\Controllers\Api\Core\StorageMigrationController::class, 'index'])->middleware('permission:manage settings');
        Route::post('storage/migration/batch', [App\Http\Controllers\Api\Core\StorageMigrationController::class, 'migrate'])->middleware('permission:manage settings');

        // Email Templates
        Route::apiResource('email-templates', App\Http\Controllers\Api\Core\EmailTemplateController::class)->middleware('permission:manage settings');
        Route::post('email-templates/{emailTemplate}/preview', [App\Http\Controllers\Api\Core\EmailTemplateController::class, 'preview'])->middleware('permission:manage settings');
        Route::post('email-templates/{emailTemplate}/send-test', [App\Http\Controllers\Api\Core\EmailTemplateController::class, 'sendTest'])->middleware('permission:manage settings');

        // Email Testing & Verification
        Route::prefix('email-test')->middleware('permission:manage settings')->group(function () {
            Route::post('test-connection', [App\Http\Controllers\Api\Core\EmailTestController::class, 'testConnection']);
            Route::post('send-test', [App\Http\Controllers\Api\Core\EmailTestController::class, 'sendTest']);
            Route::get('queue-status', [App\Http\Controllers\Api\Core\EmailTestController::class, 'getQueueStatus']);
            Route::get('recent-journal', [App\Http\Controllers\Api\Core\EmailTestController::class, 'getRecentLogs']);
            Route::get('validate-config', [App\Http\Controllers\Api\Core\EmailTestController::class, 'validateConfig']);
        });

        // Notifications
        Route::get('notifications', [App\Http\Controllers\Api\Core\NotificationController::class, 'index']);
        Route::get('notifications/unread-count', [App\Http\Controllers\Api\Core\NotificationController::class, 'unreadCount']);
        Route::put('notifications/{notification}/read', [App\Http\Controllers\Api\Core\NotificationController::class, 'markAsRead']);
        Route::put('notifications/read-all', [App\Http\Controllers\Api\Core\NotificationController::class, 'markAllAsRead']);
        Route::get('notifications/system', [App\Http\Controllers\Api\Core\NotificationController::class, 'indexSystem']);
        Route::delete('notifications/system/revoke', [App\Http\Controllers\Api\Core\NotificationController::class, 'revokeSystem']);
        Route::post('notifications/system/bulk-revoke', [App\Http\Controllers\Api\Core\NotificationController::class, 'bulkRevokeSystem']);
        Route::post('notifications/broadcast', [App\Http\Controllers\Api\Core\NotificationController::class, 'broadcast']);
        Route::delete('notifications/{notification}', [App\Http\Controllers\Api\Core\NotificationController::class, 'destroy']);

        // Content Preview
        Route::get('contents/{content}/preview', [App\Http\Controllers\Api\Core\ContentController::class, 'preview'])->middleware('permission:edit content');

        // Security Monitoring
        Route::prefix('security')->group(function () {
            Route::get('journal', [App\Http\Controllers\Api\Core\SecurityController::class, 'index'])->middleware('permission:manage settings');
            Route::get('csp-reports', [App\Http\Controllers\Api\Core\CspReportController::class, 'index'])->middleware('permission:manage settings');
            Route::post('csp-reports/bulk-action', [App\Http\Controllers\Api\Core\CspReportController::class, 'bulkAction'])->middleware('permission:manage settings');
            Route::get('csp-reports/statistics', [App\Http\Controllers\Api\Core\CspReportController::class, 'statistics'])->middleware('permission:manage settings');

            // Slow Queries
            Route::get('slow-queries', [App\Http\Controllers\Api\Core\SlowQueryController::class, 'index'])->middleware('permission:manage settings');
            Route::get('slow-queries/statistics', [App\Http\Controllers\Api\Core\SlowQueryController::class, 'statistics'])->middleware('permission:manage settings');

            // Dependency Vulnerabilities
            Route::get('dependency-vulnerabilities', [App\Http\Controllers\Api\Core\DependencyVulnerabilityController::class, 'index'])->middleware('permission:manage settings');
            Route::get('dependency-vulnerabilities/statistics', [App\Http\Controllers\Api\Core\DependencyVulnerabilityController::class, 'statistics'])->middleware('permission:manage settings');
            Route::put('dependency-vulnerabilities/{id}', [App\Http\Controllers\Api\Core\DependencyVulnerabilityController::class, 'update'])->middleware('permission:manage settings');
            Route::post('run-dependency-audit', [App\Http\Controllers\Api\Core\DependencyVulnerabilityController::class, 'runAudit'])->middleware('permission:manage settings');

            // All Dependency Packages (composer + npm)
            Route::get('dependency-packages', [App\Http\Controllers\Api\Core\DependencyPackageController::class, 'index'])->middleware('permission:manage settings');
            Route::get('dependency-packages/statistics', [App\Http\Controllers\Api\Core\DependencyPackageController::class, 'statistics'])->middleware('permission:manage settings');
        });

        // Scheduled Tasks - Custom routes MUST come BEFORE apiResource
        Route::get('scheduled-tasks/allowed-commands', [App\Http\Controllers\Api\Core\ScheduledTaskController::class, 'allowedCommands'])->middleware('permission:manage scheduled tasks');
        Route::post('scheduled-tasks/run-adhoc', [App\Http\Controllers\Api\Core\ScheduledTaskController::class, 'runAdhoc'])->middleware('permission:manage scheduled tasks');
        Route::post('scheduled-tasks/{id}/run', [App\Http\Controllers\Api\Core\ScheduledTaskController::class, 'run'])->middleware('permission:manage scheduled tasks');
        Route::apiResource('scheduled-tasks', App\Http\Controllers\Api\Core\ScheduledTaskController::class)->middleware('permission:manage scheduled tasks');

        // File Manager (Admin-level file system access)
        Route::get('file-manager', [App\Http\Controllers\Api\Core\FileManagerController::class, 'index'])->middleware('permission:manage files');
        Route::post('file-manager/upload', [App\Http\Controllers\Api\Core\FileManagerController::class, 'upload'])->middleware('permission:manage files');
        Route::delete('file-manager', [App\Http\Controllers\Api\Core\FileManagerController::class, 'delete'])->middleware('permission:manage files');
        Route::post('file-manager/delete', [App\Http\Controllers\Api\Core\FileManagerController::class, 'delete'])->middleware('permission:manage files'); // Frontend compat
        Route::post('file-manager/folder', [App\Http\Controllers\Api\Core\FileManagerController::class, 'createFolder'])->middleware('permission:manage files');
        Route::delete('file-manager/folder', [App\Http\Controllers\Api\Core\FileManagerController::class, 'deleteFolder'])->middleware('permission:manage files');
        Route::post('file-manager/folder/delete', [App\Http\Controllers\Api\Core\FileManagerController::class, 'deleteFolder'])->middleware('permission:manage files'); // Frontend compat
        Route::post('file-manager/move', [App\Http\Controllers\Api\Core\FileManagerController::class, 'move'])->middleware('permission:manage files');
        Route::post('file-manager/rename', [App\Http\Controllers\Api\Core\FileManagerController::class, 'rename'])->middleware('permission:manage files');
        Route::get('file-manager/trash', [App\Http\Controllers\Api\Core\FileManagerController::class, 'trash'])->middleware('permission:manage files');
        Route::post('file-manager/trash/empty', [App\Http\Controllers\Api\Core\FileManagerController::class, 'emptyTrash'])->middleware('permission:manage files'); // Frontend compat
        Route::post('file-manager/restore', [App\Http\Controllers\Api\Core\FileManagerController::class, 'restore'])->middleware('permission:manage files');
        Route::delete('file-manager/trash', [App\Http\Controllers\Api\Core\FileManagerController::class, 'emptyTrash'])->middleware('permission:manage files');
        Route::delete('file-manager/trash/permanent', [App\Http\Controllers\Api\Core\FileManagerController::class, 'deletePermanently'])->middleware('permission:manage files');
        Route::post('file-manager/trash/permanent', [App\Http\Controllers\Api\Core\FileManagerController::class, 'deletePermanently'])->middleware('permission:manage files'); // Frontend compat
        Route::post('file-manager/extract', [App\Http\Controllers\Api\Core\FileManagerController::class, 'extract'])->middleware('permission:manage files');
        Route::post('file-manager/compress', [App\Http\Controllers\Api\Core\FileManagerController::class, 'compress'])->middleware('permission:manage files');
        Route::post('file-manager/copy', [App\Http\Controllers\Api\Core\FileManagerController::class, 'copy'])->middleware('permission:manage files');

        // Logs
        Route::get('system-journal', [App\Http\Controllers\Api\Core\LogController::class, 'index'])->middleware('permission:manage settings');
        Route::delete('system-journal', [App\Http\Controllers\Api\Core\LogController::class, 'clear'])->middleware('permission:manage settings');
        Route::post('system-journal/clear', [App\Http\Controllers\Api\Core\LogController::class, 'clear'])->middleware('permission:manage settings');
        Route::get('system-journal/{filename}', [App\Http\Controllers\Api\Core\LogController::class, 'show'])->middleware('permission:manage settings');
        Route::get('system-journal/{filename}/download', [App\Http\Controllers\Api\Core\LogController::class, 'download'])->middleware('permission:manage settings');

        // System Information
        Route::get('system/info', [App\Http\Controllers\Api\Core\SystemController::class, 'info'])->middleware('permission:manage system');
        Route::get('system/health', [App\Http\Controllers\Api\Core\SystemController::class, 'health'])->middleware('permission:manage system');
        Route::get('system/health/detailed', [App\Http\Controllers\Api\Core\SystemController::class, 'systemHealth'])->middleware('permission:manage system');
        Route::get('system/statistics', [App\Http\Controllers\Api\Core\SystemController::class, 'statistics'])->middleware('permission:manage system');
        Route::get('system/cache', [App\Http\Controllers\Api\Core\SystemController::class, 'cache'])->middleware('permission:manage system');
        Route::get('system/cache-status', [App\Http\Controllers\Api\Core\SystemController::class, 'cacheStatus'])->middleware('permission:manage system');
        Route::post('system/cache/clear', [App\Http\Controllers\Api\Core\SystemController::class, 'clearCache'])->middleware('permission:manage system');
        Route::post('system/cache/warm', [App\Http\Controllers\Api\Core\SystemController::class, 'warmCache'])->middleware('permission:manage system');
        Route::get('system/cache/warming-stats', [App\Http\Controllers\Api\Core\SystemController::class, 'cacheWarmingStats'])->middleware('permission:manage system');
        Route::get('system/query-performance', [App\Http\Controllers\Api\Core\SystemController::class, 'queryPerformance'])->middleware('permission:manage system');
        Route::post('system/rate-limit/clear', [App\Http\Controllers\Api\Core\SystemController::class, 'clearRateLimit'])->middleware('permission:manage system');

        // Redis Management
        Route::get('redis/settings', [App\Http\Controllers\Api\Core\RedisController::class, 'index'])->middleware('permission:manage settings');
        Route::put('redis/settings', [App\Http\Controllers\Api\Core\RedisController::class, 'update'])->middleware('permission:manage settings');
        Route::get('redis/test-connection', [App\Http\Controllers\Api\Core\RedisController::class, 'testConnection'])->middleware('permission:manage settings');
        Route::get('redis/info', [App\Http\Controllers\Api\Core\RedisController::class, 'info'])->middleware('permission:manage settings');
        Route::get('redis/cache-stats', [App\Http\Controllers\Api\Core\RedisController::class, 'cacheStats'])->middleware('permission:manage settings');
        Route::post('redis/flush-cache', [App\Http\Controllers\Api\Core\RedisController::class, 'flushCache'])->middleware('permission:manage settings');
        Route::post('redis/warm-cache', [App\Http\Controllers\Api\Core\RedisController::class, 'warmCache'])->middleware('permission:manage settings');

        // Multi-language Support
        // These routes must be defined BEFORE apiResource to avoid route conflicts
        Route::get('languages/ui-stats', [App\Http\Controllers\Api\Core\LanguageController::class, 'uiStats'])->middleware('permission:manage settings');
        Route::get('languages/{language}/export-pack', [App\Http\Controllers\Api\Core\LanguageController::class, 'exportPack'])->middleware('permission:manage settings');
        Route::post('languages/import-pack', [App\Http\Controllers\Api\Core\LanguageController::class, 'importPack'])->middleware('permission:manage settings');
        Route::post('languages/{language}/set-default', [App\Http\Controllers\Api\Core\LanguageController::class, 'setDefault'])->middleware('permission:manage settings');
        Route::apiResource('languages', App\Http\Controllers\Api\Core\LanguageController::class)->middleware('permission:manage settings');

        // Builder API (for visual builder dynamic data)
        Route::prefix('builder')->group(function () {
            Route::get('dynamic-sources', [App\Http\Controllers\Api\Core\BuilderController::class, 'dynamicSources']);
            Route::post('resolve-dynamic', [App\Http\Controllers\Api\Core\BuilderController::class, 'resolveDynamic']);
        });

        // Builder Presets
        Route::apiResource('builder-presets', App\Http\Controllers\Api\Core\BuilderPresetController::class);

        // K2NET ISP Modules
        Route::prefix('isp')->group(function () {
            // Member Portal (Customer Self-Service)
            Route::get('member/dashboard', [App\Http\Controllers\Api\Isp\Customer\MemberPortalController::class, 'dashboard']);
            Route::get('member/invoices', [App\Http\Controllers\Api\Isp\Customer\MemberPortalController::class, 'invoices']);
            Route::get('member/usage', [App\Http\Controllers\Api\Isp\Customer\MemberPortalController::class, 'usage']);
            Route::post('member/service-request', [App\Http\Controllers\Api\Isp\Customer\MemberPortalController::class, 'requestService']);
            Route::post('member/diagnostics', [App\Http\Controllers\Api\Isp\Customer\MemberPortalController::class, 'diagnostics']);
            Route::post('member/reset-connection', [App\Http\Controllers\Api\Isp\Customer\MemberPortalController::class, 'resetConnection']);
            Route::put('member/profile', [App\Http\Controllers\Api\Isp\Customer\MemberPortalController::class, 'updateProfile']);

            // ISP Support & Tickets (Unified for Admins & Members)
            Route::prefix('support/tickets')->group(function () {
                Route::get('/', [App\Http\Controllers\Api\Isp\Support\SupportController::class, 'index']);
                Route::post('/', [App\Http\Controllers\Api\Isp\Support\SupportController::class, 'store']);
                Route::get('/{id}', [App\Http\Controllers\Api\Isp\Support\SupportController::class, 'show']);
                Route::patch('/{id}/status', [App\Http\Controllers\Api\Isp\Support\SupportController::class, 'updateTicketStatus']);
            });

            // Diagnostics
            Route::prefix('diagnosis')->group(function () {
                Route::get('router/{id}', [App\Http\Controllers\Api\Isp\Support\SupportController::class, 'diagnoseRouter']);
                Route::get('customer/{id}', [App\Http\Controllers\Api\Isp\Support\SupportController::class, 'diagnoseCustomer']);
            });

            // Partners (Biller accessible / stock check)
            Route::get('partners', [App\Http\Controllers\Api\Isp\Customer\PartnerController::class, 'index']);

            // Customer Payments
            Route::post('payments/invoice/{invoice}/create', [App\Http\Controllers\Api\Isp\Billing\PaymentController::class, 'createTransaction']);

            // Admin & Management (Management Permission Required)
            Route::middleware('permission:manage isp')->group(function () {
                // Infrastructure
                Route::get('infra/stats', [App\Http\Controllers\Api\Isp\Network\InfrastructureController::class, 'stats']);
                Route::apiResource('infra', App\Http\Controllers\Api\Isp\Network\InfrastructureController::class);

                // Network Health & Remediation
                Route::prefix('health')->group(function () {
                    Route::get('vitals', [App\Http\Controllers\Api\Isp\Network\NetworkHealthController::class, 'index']);
                    Route::get('vitals/{node}', [App\Http\Controllers\Api\Isp\Network\NetworkHealthController::class, 'show']);
                    Route::post('remediate', [App\Http\Controllers\Api\Isp\Network\NetworkHealthController::class, 'remediate']);
                    Route::get('jitter/{customer}', [App\Http\Controllers\Api\Isp\Network\NetworkHealthController::class, 'jitterTest']);
                });

                // Monitoring
                Route::get('monitor/stats', [App\Http\Controllers\Api\Isp\Network\MonitoringController::class, 'dashboard']);
                Route::get('monitor/traffic', [App\Http\Controllers\Api\Isp\Network\MonitoringController::class, 'liveTraffic']);
                Route::get('monitor/heatmap', [App\Http\Controllers\Api\Isp\Network\MonitoringController::class, 'heatmap']);
                Route::get('monitor/sessions', [App\Http\Controllers\Api\Isp\Network\MonitoringController::class, 'routerSessions']);
                Route::post('monitor/disconnect', [App\Http\Controllers\Api\Isp\Network\MonitoringController::class, 'disconnectRouterSession']);

                // RADIUS Monitoring
                Route::prefix('radius')->group(function () {
                    Route::get('sessions', [App\Http\Controllers\Api\Isp\Network\MonitoringController::class, 'radiusSessions']);
                    Route::get('logs', [App\Http\Controllers\Api\Isp\Network\MonitoringController::class, 'radiusLogs']);
                    Route::get('status', [App\Http\Controllers\Api\Isp\Network\MonitoringController::class, 'radiusStatus']);
                    Route::post('disconnect', [App\Http\Controllers\Api\Isp\Network\MonitoringController::class, 'radiusDisconnect']);
                });

                // IP Pool Management
                Route::apiResource('ip-pools', App\Http\Controllers\Api\Isp\Network\IpPoolController::class);
                Route::get('ip-pools/{ip_pool}/addresses', [App\Http\Controllers\Api\Isp\Network\IpPoolController::class, 'addresses']);
                Route::patch('ip-pool-addresses/{address}', [App\Http\Controllers\Api\Isp\Network\IpPoolController::class, 'updateAddress']);
                Route::post('ip-pools/{ip_pool}/regenerate', [App\Http\Controllers\Api\Isp\Network\IpPoolController::class, 'regenerateAddresses']);

                // Sync
                Route::post('sync/{id}', [App\Http\Controllers\Api\Isp\Network\SyncController::class, 'syncCustomer']);
                Route::post('sync-all', [App\Http\Controllers\Api\Isp\Network\SyncController::class, 'syncAll']);

                // Billing
                Route::apiResource('subscription-profiles', App\Http\Controllers\Api\Isp\Billing\IspPlanController::class);
                Route::get('billing-plans', [App\Http\Controllers\Api\Isp\Billing\IspPlanController::class, 'index']); // Alias for frontend
                Route::get('billing/plans', [App\Http\Controllers\Api\Isp\Billing\BillingController::class, 'plans']);
                Route::get('billing/invoices', [App\Http\Controllers\Api\Isp\Billing\BillingController::class, 'invoices']);
                Route::get('billing/invoices/export', [App\Http\Controllers\Api\Isp\Billing\BillingController::class, 'exportExcel']);
                Route::get('billing/invoices/{invoice}/download-pdf', [App\Http\Controllers\Api\Isp\Billing\BillingController::class, 'downloadPdf']);
                // ... analytics/revenue routes were merged into MonitoringController ...
                Route::post('billing/generate', [App\Http\Controllers\Api\Isp\Billing\BillingController::class, 'generate']);
                Route::post('billing/suspend-check', [App\Http\Controllers\Api\Isp\Billing\BillingController::class, 'runOverdueCheck']);
                Route::post('billing/preview-upgrade', [App\Http\Controllers\Api\Isp\Billing\BillingController::class, 'previewUpgrade']);
                Route::post('billing/invoices/{invoice}/pay', [App\Http\Controllers\Api\Isp\Billing\BillingController::class, 'pay']);

                // BI & Analytics
                Route::get('billing/analytics/bi', [App\Http\Controllers\Api\Isp\Billing\AnalyticsController::class, 'index']);
                Route::get('billing/analytics/projections', [App\Http\Controllers\Api\Isp\Billing\AnalyticsController::class, 'projections']);
                Route::get('billing/analytics/churn-risk', [App\Http\Controllers\Api\Isp\Billing\AnalyticsController::class, 'churnRisk']);

                // Payments (Admin)
                Route::get('payments/gateways', [App\Http\Controllers\Api\Isp\Billing\PaymentController::class, 'indexGateways']);
                Route::put('payments/gateways/{gateway}', [App\Http\Controllers\Api\Isp\Billing\PaymentController::class, 'updateGateway']);
                Route::post('payments/callback', [App\Http\Controllers\Api\Isp\Billing\PaymentController::class, 'callback']);

                // Contracts
                Route::apiResource('contracts', App\Http\Controllers\Api\Isp\Customer\ContractController::class);

                // Service Requests (Admin)
                Route::prefix('service-requests')->group(function () {
                    Route::get('/', [App\Http\Controllers\Api\Isp\Support\SupportController::class, 'requestIndex']);
                    Route::patch('/{serviceRequest}/status', [App\Http\Controllers\Api\Isp\Support\SupportController::class, 'updateRequestStatus']);
                    Route::post('/{serviceRequest}/execute', [App\Http\Controllers\Api\Isp\Support\SupportController::class, 'executeRequest']);
                });


                // Network Management (IPAM)
                Route::prefix('network')->group(function () {
                    Route::get('subnets', [App\Http\Controllers\Api\Isp\Network\NetworkController::class, 'indexSubnets']);
                    Route::post('subnets', [App\Http\Controllers\Api\Isp\Network\NetworkController::class, 'storeSubnet']);
                    Route::get('subnets/{id}/ips', [App\Http\Controllers\Api\Isp\Network\NetworkController::class, 'indexIps']);
                    Route::get('profiles', [App\Http\Controllers\Api\Isp\Network\NetworkController::class, 'indexProfiles']);
                    Route::post('profiles', [App\Http\Controllers\Api\Isp\Network\NetworkController::class, 'storeProfile']);
                });

                // Outage Management
                Route::prefix('outages')->group(function () {
                    Route::get('/', [App\Http\Controllers\Api\Isp\Network\OutageController::class, 'index']);
                    Route::post('/', [App\Http\Controllers\Api\Isp\Network\OutageController::class, 'store']);
                    Route::patch('/{id}', [App\Http\Controllers\Api\Isp\Network\OutageController::class, 'update']);
                });

                // Router Management (Merged into InfrastructureController)
                Route::get('routers', [App\Http\Controllers\Api\Isp\Network\InfrastructureController::class, 'routerIndex']);
                Route::post('routers', [App\Http\Controllers\Api\Isp\Network\InfrastructureController::class, 'store']);
                Route::get('routers/{infra}', [App\Http\Controllers\Api\Isp\Network\InfrastructureController::class, 'show']);
                Route::put('routers/{infra}', [App\Http\Controllers\Api\Isp\Network\InfrastructureController::class, 'update']);
                Route::delete('routers/{infra}', [App\Http\Controllers\Api\Isp\Network\InfrastructureController::class, 'destroy']);
                
                Route::post('routers/bulk-status', [App\Http\Controllers\Api\Isp\Network\InfrastructureController::class, 'bulkStatus']);
                Route::post('routers/bulk-destroy', [App\Http\Controllers\Api\Isp\Network\InfrastructureController::class, 'bulkDestroy']);
                Route::post('routers/bulk-force-destroy', [App\Http\Controllers\Api\Isp\Network\InfrastructureController::class, 'bulkForceDestroy']);
                Route::post('routers/bulk-restore', [App\Http\Controllers\Api\Isp\Network\InfrastructureController::class, 'bulkRestore']);
                Route::post('routers/{infra}/restore', [App\Http\Controllers\Api\Isp\Network\InfrastructureController::class, 'restore']);

                Route::get('routers/{infra}/test-connection', [App\Http\Controllers\Api\Isp\Network\InfrastructureController::class, 'testRouterConnection']);
                Route::post('routers/{infra}/reconstruct', [App\Http\Controllers\Api\Isp\Network\InfrastructureController::class, 'reconstructRouter']);
                Route::get('routers/{infra}/vpn-secrets', [App\Http\Controllers\Api\Isp\Network\InfrastructureController::class, 'routerVpnSecrets']);
                Route::post('routers/{infra}/vpn-secrets', [App\Http\Controllers\Api\Isp\Network\InfrastructureController::class, 'storeRouterVpnSecret']);
                Route::delete('routers/{infra}/vpn-secrets/{id}', [App\Http\Controllers\Api\Isp\Network\InfrastructureController::class, 'destroyRouterVpnSecret']);
                Route::post('routers/{infra}/script', [App\Http\Controllers\Api\Isp\Network\MikrotikScriptController::class, 'generate']);

                // Service Zones (Data Servers)
                Route::patch('service-zones/{service_zone}/toggle-active', [App\Http\Controllers\Api\Isp\Network\ServiceZoneController::class, 'toggleActive']);
                Route::apiResource('service-zones', App\Http\Controllers\Api\Isp\Network\ServiceZoneController::class);

                Route::get('customers/stats', [App\Http\Controllers\Api\Isp\Customer\CustomerController::class, 'stats']);
                Route::post('customers/import', [App\Http\Controllers\Api\Isp\Customer\CustomerController::class, 'import']);
                Route::get('customers/export', [App\Http\Controllers\Api\Isp\Customer\CustomerController::class, 'export']);
                Route::get('customers/geolocation', [App\Http\Controllers\Api\Isp\Network\GeolocationController::class, 'index']);
                Route::get('customers/topology', [App\Http\Controllers\Api\Isp\Network\GeolocationController::class, 'topology']);
                Route::get('customers/heatmap', [App\Http\Controllers\Api\Isp\Network\GeolocationController::class, 'heatmap']);
                Route::apiResource('customers', App\Http\Controllers\Api\Isp\Customer\CustomerController::class);

                // Analytics & Reporting (Merged into MonitoringController)
                Route::prefix('analytics')->group(function () {
                    Route::get('/', [App\Http\Controllers\Api\Isp\Network\MonitoringController::class, 'dashboard']);
                    Route::get('/usage', [App\Http\Controllers\Api\Isp\Network\MonitoringController::class, 'usageTrends']);
                    Route::get('/revenue', [App\Http\Controllers\Api\Isp\Network\MonitoringController::class, 'revenue']);
                    Route::get('/bi', [App\Http\Controllers\Api\Isp\Network\MonitoringController::class, 'businessIntelligence']);
                    Route::get('/top-talkers', [App\Http\Controllers\Api\Isp\Network\MonitoringController::class, 'topTalkers']);
                });

                Route::get('olts/discover', [App\Http\Controllers\Api\Isp\Network\InfrastructureController::class, 'oltDiscover']);
                Route::get('olts/logs', [App\Http\Controllers\Api\Isp\Network\InfrastructureController::class, 'oltLogs']);
                Route::get('olts', [App\Http\Controllers\Api\Isp\Network\InfrastructureController::class, 'oltIndex']);
                Route::apiResource('olts', App\Http\Controllers\Api\Isp\Network\InfrastructureController::class, ['parameters' => ['olts' => 'infra']])->except(['index']);
                Route::get('olts/{olt}/test-connection', [App\Http\Controllers\Api\Isp\Network\InfrastructureController::class, 'testOltConnection']);
                Route::get('olts/{olt}/logs', [App\Http\Controllers\Api\Isp\Network\InfrastructureController::class, 'oltLogs']);
                Route::get('olts/{olt}/discover', [App\Http\Controllers\Api\Isp\Network\InfrastructureController::class, 'oltDiscover']);

                // OLT Monitoring (Merged into MonitoringController)
                Route::prefix('monitor/olt')->group(function () {
                    Route::get('signals', [App\Http\Controllers\Api\Isp\Network\MonitoringController::class, 'oltSignalHistory']);
                    Route::get('health-report/{customer}', [App\Http\Controllers\Api\Isp\Network\MonitoringController::class, 'downloadHealthReport']);
                    Route::get('{olt}/signal-stats', [App\Http\Controllers\Api\Isp\Network\MonitoringController::class, 'oltSignalStats']);
                });

                // Network Monitoring (NOC & Traffic)
                Route::prefix('monitor')->group(function () {
                    Route::get('stats', [App\Http\Controllers\Api\Isp\Network\MonitoringController::class, 'dashboard']);
                    Route::get('interfaces', [App\Http\Controllers\Api\Isp\Network\MonitoringController::class, 'interfaces']);
                    Route::get('traffic', [App\Http\Controllers\Api\Isp\Network\MonitoringController::class, 'liveTraffic']);

                    // Reporting
                    Route::get('reports/traffic', [App\Http\Controllers\Api\Isp\Network\ReportingController::class, 'trafficReport']);
                    Route::get('reports/sla', [App\Http\Controllers\Api\Isp\Network\ReportingController::class, 'slaReport']);
                });

                // ODPs (Part of Infrastructure)
                Route::get('odps', [App\Http\Controllers\Api\Isp\Network\InfrastructureController::class, 'odpIndex']);
                Route::apiResource('odps', App\Http\Controllers\Api\Isp\Network\InfrastructureController::class, ['parameters' => ['odps' => 'infra']])->except(['index']);

                // Inventory
                Route::prefix('inventory')->group(function () {
                    Route::get('/', [App\Http\Controllers\Api\Isp\Hardware\InventoryController::class, 'index']);
                    Route::post('/', [App\Http\Controllers\Api\Isp\Hardware\InventoryController::class, 'store']);
                    Route::get('/{inventory}/qr', [App\Http\Controllers\Api\Isp\Hardware\InventoryController::class, 'generateQr']);
                    Route::post('/{inventory}/adjust', [App\Http\Controllers\Api\Isp\Hardware\InventoryController::class, 'adjustStock']);
                    Route::get('/{inventory}/transactions', [App\Http\Controllers\Api\Isp\Hardware\InventoryController::class, 'transactions']);
                    Route::delete('/{inventory}', [App\Http\Controllers\Api\Isp\Hardware\InventoryController::class, 'destroy']);
                });

                // Operations & Technicians
                Route::prefix('operations')->group(function () {
                    Route::get('technicians', [App\Http\Controllers\Api\Isp\Operations\TechnicianController::class, 'technicians']);
                    Route::get('recommendations/{request}', [App\Http\Controllers\Api\Isp\Operations\TechnicianController::class, 'recommendations']);
                    Route::get('deployments', [App\Http\Controllers\Api\Isp\Operations\TechnicianController::class, 'deployments']);
                    Route::post('deploy', [App\Http\Controllers\Api\Isp\Operations\TechnicianController::class, 'deploy']);
                    Route::patch('deployments/{deployment}/status', [App\Http\Controllers\Api\Isp\Operations\TechnicianController::class, 'updateStatus']);
                });

                // Vouchers
                Route::prefix('vouchers')->group(function () {
                    Route::get('/', [App\Http\Controllers\Api\Isp\Billing\VoucherController::class, 'index']);
                    Route::post('/generate', [App\Http\Controllers\Api\Isp\Billing\VoucherController::class, 'generate']);
                    Route::post('/create-single', [App\Http\Controllers\Api\Isp\Billing\VoucherController::class, 'createSingle']);
                    Route::post('/sell', [App\Http\Controllers\Api\Isp\Billing\VoucherController::class, 'sell']);
                    Route::get('/stock-summary', [App\Http\Controllers\Api\Isp\Billing\VoucherController::class, 'stockSummary']);
                    Route::get('/sales-report', [App\Http\Controllers\Api\Isp\Billing\VoucherController::class, 'salesReport']);
                    Route::get('/summary', [App\Http\Controllers\Api\Isp\Billing\VoucherController::class, 'summary']);
                    Route::post('/cleanup', [App\Http\Controllers\Api\Isp\Billing\VoucherController::class, 'cleanup']);
                    Route::post('/sync-usage', [App\Http\Controllers\Api\Isp\Billing\VoucherController::class, 'syncUsage']);
                    Route::post('/import', [App\Http\Controllers\Api\Isp\Billing\VoucherController::class, 'import']);
                    Route::get('/export', [App\Http\Controllers\Api\Isp\Billing\VoucherController::class, 'export']);
                    Route::get('/export/script', [App\Http\Controllers\Api\Isp\Billing\VoucherController::class, 'exportScript']);
                    Route::get('/export/csv', [App\Http\Controllers\Api\Isp\Billing\VoucherController::class, 'exportCsv']);
                    Route::post('/bulk-delete', [App\Http\Controllers\Api\Isp\Billing\VoucherController::class, 'bulkDelete']);
                    Route::post('/bulk-refund', [App\Http\Controllers\Api\Isp\Billing\VoucherController::class, 'bulkRefund']);
                    Route::post('/bulk-reset-counter', [App\Http\Controllers\Api\Isp\Billing\VoucherController::class, 'bulkResetCounter']);
                    Route::post('/bulk-update-status', [App\Http\Controllers\Api\Isp\Billing\VoucherController::class, 'bulkUpdateStatus']);
                    Route::post('/{voucher}/refund', [App\Http\Controllers\Api\Isp\Billing\VoucherController::class, 'refund']);
                    Route::post('/{voucher}/reset-counter', [App\Http\Controllers\Api\Isp\Billing\VoucherController::class, 'resetCounter']);
                    Route::delete('/batch/{batchId}', [App\Http\Controllers\Api\Isp\Billing\VoucherController::class, 'destroyBatch']);
                    Route::delete('/{voucher}', [App\Http\Controllers\Api\Isp\Billing\VoucherController::class, 'destroy']);
                });

                // Partner (Reseller/Biller) Management
                Route::prefix('partners')->group(function () {
                    Route::get('/', [App\Http\Controllers\Api\Isp\Customer\PartnerController::class, 'index']);
                    Route::post('/', [App\Http\Controllers\Api\Isp\Customer\PartnerController::class, 'store']);
                    Route::get('/{id}', [App\Http\Controllers\Api\Isp\Customer\PartnerController::class, 'show']);
                    Route::put('/{id}', [App\Http\Controllers\Api\Isp\Customer\PartnerController::class, 'update']);
                    Route::delete('/{id}', [App\Http\Controllers\Api\Isp\Customer\PartnerController::class, 'destroy']);
                    Route::post('/{id}/credit', [App\Http\Controllers\Api\Isp\Customer\PartnerController::class, 'addCredit']);
                    Route::post('/{id}/debit', [App\Http\Controllers\Api\Isp\Customer\PartnerController::class, 'addDebit']);
                    Route::get('/{id}/transactions', [App\Http\Controllers\Api\Isp\Customer\PartnerController::class, 'transactions']);
                });

                // Customer Saldo Management
                Route::prefix('customers/{customerId}/saldo')->group(function () {
                    Route::get('/', [App\Http\Controllers\Api\Isp\Billing\SaldoController::class, 'show']);
                    Route::post('/credit', [App\Http\Controllers\Api\Isp\Billing\SaldoController::class, 'addCredit']);
                    Route::post('/debit', [App\Http\Controllers\Api\Isp\Billing\SaldoController::class, 'addDebit']);
                    Route::post('/pay-invoice', [App\Http\Controllers\Api\Isp\Billing\SaldoController::class, 'payInvoice']);
                    Route::get('/transactions', [App\Http\Controllers\Api\Isp\Billing\SaldoController::class, 'transactions']);
                });

                // Discount Coupons
                Route::prefix('coupons')->group(function () {
                    Route::get('/', [App\Http\Controllers\Api\Isp\Customer\CouponController::class, 'index']);
                    Route::post('/', [App\Http\Controllers\Api\Isp\Customer\CouponController::class, 'store']);
                    Route::get('/{id}', [App\Http\Controllers\Api\Isp\Customer\CouponController::class, 'show']);
                    Route::put('/{id}', [App\Http\Controllers\Api\Isp\Customer\CouponController::class, 'update']);
                    Route::delete('/{id}', [App\Http\Controllers\Api\Isp\Customer\CouponController::class, 'destroy']);
                    Route::post('/validate', [App\Http\Controllers\Api\Isp\Customer\CouponController::class, 'validateCoupon']);
                    Route::post('/redeem', [App\Http\Controllers\Api\Isp\Customer\CouponController::class, 'redeem']);
                });

                // Print Templates
                Route::prefix('print-templates')->group(function () {
                    Route::get('/', [App\Http\Controllers\Api\Isp\Billing\PrintTemplateController::class, 'index']);
                    Route::post('/', [App\Http\Controllers\Api\Isp\Billing\PrintTemplateController::class, 'store']);
                    Route::get('/variables', [App\Http\Controllers\Api\Isp\Billing\PrintTemplateController::class, 'variables']);
                    Route::get('/{id}', [App\Http\Controllers\Api\Isp\Billing\PrintTemplateController::class, 'show']);
                    Route::put('/{id}', [App\Http\Controllers\Api\Isp\Billing\PrintTemplateController::class, 'update']);
                    Route::delete('/{id}', [App\Http\Controllers\Api\Isp\Billing\PrintTemplateController::class, 'destroy']);
                    Route::get('/{id}/preview', [App\Http\Controllers\Api\Isp\Billing\PrintTemplateController::class, 'preview']);
                });

                // BGP Toolkit
                Route::prefix('tools/bgp')->group(function () {
                    Route::get('/lookup', [App\Http\Controllers\Api\Isp\Network\BgpToolkitController::class, 'lookup']);
                    Route::get('/prefixes', [App\Http\Controllers\Api\Isp\Network\BgpToolkitController::class, 'prefixes']);
                    Route::get('/download-address-list', [App\Http\Controllers\Api\Isp\Network\BgpToolkitController::class, 'downloadAddressList']);
                });

                // Activity Logs
                Route::prefix('activity-logs')->group(function () {
                    Route::get('/', [App\Http\Controllers\Api\Isp\Support\ActivityLogController::class, 'index']);
                });

                // Hotspot (Phase 9)
                Route::prefix('hotspot')->group(function () {
                    // IP Bindings
                    Route::get('/ip-bindings', [App\Http\Controllers\Api\Isp\Network\HotspotController::class, 'ipBindings']);
                    Route::post('/ip-bindings', [App\Http\Controllers\Api\Isp\Network\HotspotController::class, 'addIpBinding']);
                    Route::delete('/ip-bindings/{id}', [App\Http\Controllers\Api\Isp\Network\HotspotController::class, 'removeIpBinding']);
                    Route::patch('/ip-bindings/{id}/toggle', [App\Http\Controllers\Api\Isp\Network\HotspotController::class, 'toggleIpBinding']);
                    // Cookies
                    Route::get('/cookies', [App\Http\Controllers\Api\Isp\Network\HotspotController::class, 'cookies']);
                    Route::delete('/cookies/{id}', [App\Http\Controllers\Api\Isp\Network\HotspotController::class, 'removeCookie']);
                    // Interfaces (for Traffic Monitor)
                    Route::get('/interfaces', [App\Http\Controllers\Api\Isp\Network\HotspotController::class, 'interfaces']);
                    Route::get('/interface-traffic', [App\Http\Controllers\Api\Isp\Network\HotspotController::class, 'interfaceTraffic']);
                });

                // WhatsApp Blast & Templates
                Route::prefix('wa')->group(function () {
                    // Templates
                    Route::get('/templates', [App\Http\Controllers\Api\Isp\ThirdParty\WhatsAppBlastController::class, 'templates']);
                    Route::post('/templates', [App\Http\Controllers\Api\Isp\ThirdParty\WhatsAppBlastController::class, 'storeTemplate']);
                    Route::put('/templates/{id}', [App\Http\Controllers\Api\Isp\ThirdParty\WhatsAppBlastController::class, 'updateTemplate']);
                    Route::delete('/templates/{id}', [App\Http\Controllers\Api\Isp\ThirdParty\WhatsAppBlastController::class, 'destroyTemplate']);
                    // Blasts
                    Route::get('/blasts', [App\Http\Controllers\Api\Isp\ThirdParty\WhatsAppBlastController::class, 'blasts']);
                    Route::get('/blasts/{id}', [App\Http\Controllers\Api\Isp\ThirdParty\WhatsAppBlastController::class, 'showBlast']);
                    Route::post('/blasts', [App\Http\Controllers\Api\Isp\ThirdParty\WhatsAppBlastController::class, 'storeBlast']);
                    Route::post('/blasts/{id}/send', [App\Http\Controllers\Api\Isp\ThirdParty\WhatsAppBlastController::class, 'sendBlast']);
                    Route::delete('/blasts/{id}', [App\Http\Controllers\Api\Isp\ThirdParty\WhatsAppBlastController::class, 'destroyBlast']);
                    // Schedules
                    Route::get('/schedules', [App\Http\Controllers\Api\Isp\ThirdParty\WhatsAppBlastController::class, 'schedules']);
                    Route::post('/schedules', [App\Http\Controllers\Api\Isp\ThirdParty\WhatsAppBlastController::class, 'storeSchedule']);
                    Route::put('/schedules/{id}', [App\Http\Controllers\Api\Isp\ThirdParty\WhatsAppBlastController::class, 'updateSchedule']);
                    Route::delete('/schedules/{id}', [App\Http\Controllers\Api\Isp\ThirdParty\WhatsAppBlastController::class, 'destroySchedule']);
                });
            });
        });
    });
});

// Legacy routes (redirect to core)
Route::post('/login', function () {
    return response()->json(['message' => 'Please use /api/v1/login'], 301);
});
Route::post('/register', function () {
    return response()->json(['message' => 'Please use /api/v1/register'], 301);
});
