<?php

use Illuminate\Support\Facades\Route;

// API Version 1
Route::prefix('v1')->group(function () {
    // Public endpoint to clear rate limit (no auth required for emergency)
    Route::post('/clear-rate-limit', [App\Http\Controllers\Api\V1\SystemController::class, 'clearRateLimit']);
    
    // Authentication Routes (rate limiting handled by SecurityService with progressive blocking)
    Route::post('/login', [App\Http\Controllers\Api\V1\AuthController::class, 'login'])
        ->middleware('throttle:60,1'); // Increase Laravel throttle to avoid conflict with SecurityService
    Route::post('/register', [App\Http\Controllers\Api\V1\AuthController::class, 'register'])
        ->middleware('throttle:3,1'); // 3 attempts per minute
    Route::post('/logout', [App\Http\Controllers\Api\V1\AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/user', [App\Http\Controllers\Api\V1\AuthController::class, 'user'])->middleware('auth:sanctum');

    // Email Verification
    Route::get('/email/verify/{id}/{hash}', [App\Http\Controllers\Api\V1\AuthController::class, 'verifyEmail'])->name('verification.verify');
    Route::post('/verify-email', [App\Http\Controllers\Api\V1\AuthController::class, 'verifyEmailApi'])->middleware('throttle:5,1');
    Route::post('/resend-verification', [App\Http\Controllers\Api\V1\AuthController::class, 'resendVerificationEmailApi'])->middleware('throttle:3,1');
    Route::post('/email/verification-notification', [App\Http\Controllers\Api\V1\AuthController::class, 'resendVerificationEmail'])->middleware('auth:sanctum');

    // Password Reset (with rate limiting)
    Route::post('/forgot-password', [App\Http\Controllers\Api\V1\AuthController::class, 'forgotPassword'])
        ->middleware('throttle:3,1'); // 3 attempts per minute
    Route::post('/reset-password', [App\Http\Controllers\Api\V1\AuthController::class, 'resetPassword'])
        ->middleware('throttle:3,1'); // 3 attempts per minute

    // User Profile Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [App\Http\Controllers\Api\V1\UserController::class, 'profile']);
        Route::put('/profile', [App\Http\Controllers\Api\V1\UserController::class, 'updateProfile']);
        Route::post('/profile/avatar', [App\Http\Controllers\Api\V1\UserController::class, 'uploadAvatar']);
        Route::put('/profile/password', [App\Http\Controllers\Api\V1\UserController::class, 'updatePassword']);
        Route::get('/profile/login-history', [App\Http\Controllers\Api\V1\UserController::class, 'loginHistory']);
        Route::get('/profile/preferences', [App\Http\Controllers\Api\V1\UserController::class, 'getPreferences']);
        Route::put('/profile/preferences', [App\Http\Controllers\Api\V1\UserController::class, 'updatePreferences']);


        // Two-Factor Authentication Routes
        Route::prefix('two-factor')->group(function () {
            Route::get('/status', [App\Http\Controllers\Api\V1\TwoFactorController::class, 'status']);
            Route::post('/generate', [App\Http\Controllers\Api\V1\TwoFactorController::class, 'generate']);
            Route::post('/verify', [App\Http\Controllers\Api\V1\TwoFactorController::class, 'verify']);
            Route::post('/disable', [App\Http\Controllers\Api\V1\TwoFactorController::class, 'disable']);
            Route::post('/regenerate-backup-codes', [App\Http\Controllers\Api\V1\TwoFactorController::class, 'regenerateBackupCodes']);
        });
    });

    // Two-Factor Authentication Verification (for login, no auth required)
    Route::post('/two-factor/verify-code', [App\Http\Controllers\Api\V1\TwoFactorController::class, 'verifyCode'])->middleware('throttle:5,1');

    // Public CMS API (with rate limiting: 100 requests per minute)
    Route::prefix('cms')->middleware('throttle:100,1')->group(function () {
        Route::get('/contents', [App\Http\Controllers\Api\V1\ContentController::class, 'index']);
        Route::get('/contents/{slug}', [App\Http\Controllers\Api\V1\ContentController::class, 'show']);
        Route::get('/contents/{slug}/related', [App\Http\Controllers\Api\V1\ContentController::class, 'related']);
        Route::get('/categories', [App\Http\Controllers\Api\V1\CategoryController::class, 'index']);
        Route::get('/tags', [App\Http\Controllers\Api\V1\TagController::class, 'index']);
        
        // Public theme endpoint (no auth required)
        Route::get('/themes/active', [App\Http\Controllers\Api\V1\ThemeController::class, 'getActive']);

        // Comments (public)
        Route::get('/contents/{content}/comments', [App\Http\Controllers\Api\V1\CommentController::class, 'index']);
        Route::post('/contents/{content}/comments', [App\Http\Controllers\Api\V1\CommentController::class, 'store']);

        // Newsletter (public subscription)
        Route::post('/newsletter/subscribe', [App\Http\Controllers\Api\V1\NewsletterController::class, 'subscribe'])->middleware('throttle:10,1');
        Route::post('/newsletter/unsubscribe', [App\Http\Controllers\Api\V1\NewsletterController::class, 'unsubscribe'])->middleware('throttle:10,1');

        // Forms (public submission - with stricter rate limiting: 10 requests per minute)
        Route::get('/forms/{slug}', function ($slug) {
            $form = \App\Models\Form::where('slug', $slug)->where('is_active', true)->with('fields')->firstOrFail();

            return response()->json($form);
        })->middleware('throttle:10,1'); // 10 form views per minute

        // Search (public - with rate limiting: 30 requests per minute)
        Route::get('/search', [App\Http\Controllers\Api\V1\SearchController::class, 'search'])->middleware('throttle:30,1');
        Route::get('/search/suggestions', [App\Http\Controllers\Api\V1\SearchController::class, 'suggestions'])->middleware('throttle:30,1');

        // Languages (public)
        Route::get('/languages', [App\Http\Controllers\Api\V1\LanguageController::class, 'index']);
    });

    // Analytics Event Tracking (public - with rate limiting: 60 requests per minute)
    Route::prefix('analytics')->middleware('throttle:60,1')->group(function () {
        Route::post('/track-visit', [App\Http\Controllers\Api\V1\AnalyticsController::class, 'trackVisit']);
        Route::post('/track', [App\Http\Controllers\Api\V1\AnalyticsController::class, 'trackEvent']);
        Route::post('/track/batch', [App\Http\Controllers\Api\V1\AnalyticsController::class, 'trackBatch']);
    });

    // Admin CMS API (requires authentication with rate limiting: 300 requests per minute)
    // Increased from 60 to 300 to allow dashboard concurrent requests (7-10 on initial load)
    Route::prefix('admin/cms')->middleware(['auth:sanctum', 'throttle:300,1'])->group(function () {
        // Contents
        Route::get('contents', [App\Http\Controllers\Api\V1\ContentController::class, 'adminIndex']);
        Route::get('contents/{content}', [App\Http\Controllers\Api\V1\ContentController::class, 'adminShow']);
        Route::post('contents', [App\Http\Controllers\Api\V1\ContentController::class, 'store'])->middleware('permission:create content');
        Route::post('contents/autosave', [App\Http\Controllers\Api\V1\ContentController::class, 'autosave'])->middleware('permission:create content');
        Route::put('contents/{content}', [App\Http\Controllers\Api\V1\ContentController::class, 'update'])->middleware('permission:edit content');
        Route::patch('contents/{content}/autosave', [App\Http\Controllers\Api\V1\ContentController::class, 'autosave'])->middleware('permission:edit content');
        Route::delete('contents/{content}', [App\Http\Controllers\Api\V1\ContentController::class, 'destroy'])->middleware('permission:delete content');
        Route::post('contents/{content}/duplicate', [App\Http\Controllers\Api\V1\ContentController::class, 'duplicate'])->middleware('permission:create content');
        Route::post('contents/bulk-action', [App\Http\Controllers\Api\V1\ContentController::class, 'bulkAction'])->middleware('permission:edit content');

        // Newsletter
        Route::get('newsletter/subscribers', [App\Http\Controllers\Api\V1\NewsletterController::class, 'index'])->middleware('permission:manage users');
        Route::delete('newsletter/subscribers/{id}', [App\Http\Controllers\Api\V1\NewsletterController::class, 'destroy'])->middleware('permission:manage users');
        Route::get('newsletter/export', [App\Http\Controllers\Api\V1\NewsletterController::class, 'export'])->middleware('permission:manage users');

        // Content Revisions
        Route::get('contents/{content}/revisions', [App\Http\Controllers\Api\V1\ContentRevisionController::class, 'index']);
        Route::get('contents/{content}/revisions/{revision}', [App\Http\Controllers\Api\V1\ContentRevisionController::class, 'show']);
        Route::post('contents/{content}/revisions', [App\Http\Controllers\Api\V1\ContentRevisionController::class, 'store']);
        Route::post('contents/{content}/revisions/{revision}/restore', [App\Http\Controllers\Api\V1\ContentRevisionController::class, 'restore'])->middleware('permission:edit content');
        Route::delete('contents/{content}/revisions/{revision}', [App\Http\Controllers\Api\V1\ContentRevisionController::class, 'destroy']);

        // Content Locking
        Route::post('contents/{content}/lock', [App\Http\Controllers\Api\V1\ContentController::class, 'lock']);
        Route::post('contents/{content}/unlock', [App\Http\Controllers\Api\V1\ContentController::class, 'unlock']);

        // Categories
        Route::apiResource('categories', App\Http\Controllers\Api\V1\CategoryController::class)->middleware('permission:manage categories');
        Route::post('categories/{category}/move', [App\Http\Controllers\Api\V1\CategoryController::class, 'move'])->middleware('permission:manage categories');

        // Tags
        // Statistics route must be defined before apiResource to avoid route conflict
        Route::get('tags/statistics', [App\Http\Controllers\Api\V1\TagController::class, 'statistics'])->middleware('permission:manage tags');
        Route::apiResource('tags', App\Http\Controllers\Api\V1\TagController::class)->middleware('permission:manage tags');

        // Media (with rate limiting for uploads - increased for better UX)
        Route::post('media/upload', [App\Http\Controllers\Api\V1\MediaController::class, 'upload'])
            ->middleware('throttle:30,1'); // 30 uploads per minute (increased from 10)
        Route::post('media/upload-multiple', [App\Http\Controllers\Api\V1\MediaController::class, 'uploadMultiple'])
            ->middleware('throttle:10,1'); // 10 batch uploads per minute (increased from 5)
        Route::get('media', [App\Http\Controllers\Api\V1\MediaController::class, 'index']);
        Route::get('media/{media}', [App\Http\Controllers\Api\V1\MediaController::class, 'show']);
        Route::put('media/{media}', [App\Http\Controllers\Api\V1\MediaController::class, 'update'])->middleware('permission:manage media');
        Route::delete('media/{media}', [App\Http\Controllers\Api\V1\MediaController::class, 'destroy'])->middleware('permission:manage media');
        Route::post('media/bulk-action', [App\Http\Controllers\Api\V1\MediaController::class, 'bulkAction'])->middleware('permission:manage media');
        Route::post('media/download-zip', [App\Http\Controllers\Api\V1\MediaController::class, 'downloadZip'])->middleware('permission:manage media');
        Route::post('media/{media}/thumbnail', [App\Http\Controllers\Api\V1\MediaController::class, 'generateThumbnail'])->middleware('permission:manage media');
        Route::post('media/{media}/resize', [App\Http\Controllers\Api\V1\MediaController::class, 'resize'])->middleware('permission:manage media');
        Route::post('media/{media}/edit', [App\Http\Controllers\Api\V1\MediaController::class, 'edit'])->middleware('permission:manage media');
        Route::get('media/{media}/usage', [App\Http\Controllers\Api\V1\MediaController::class, 'usage'])->middleware('permission:manage media');

        // Media Folders
        Route::apiResource('media-folders', App\Http\Controllers\Api\V1\MediaFolderController::class)->middleware('permission:manage media');
        Route::post('media-folders/{mediaFolder}/move', [App\Http\Controllers\Api\V1\MediaFolderController::class, 'move'])->middleware('permission:manage media');

        // Comments (admin)
        Route::get('comments', [App\Http\Controllers\Api\V1\CommentController::class, 'adminIndex']);
        Route::get('comments/statistics', [App\Http\Controllers\Api\V1\CommentController::class, 'statistics']);
        Route::post('comments/bulk', [App\Http\Controllers\Api\V1\CommentController::class, 'bulkAction'])->middleware('permission:manage comments');
        Route::put('comments/{comment}/approve', [App\Http\Controllers\Api\V1\CommentController::class, 'approve'])->middleware('permission:manage comments');
        Route::put('comments/{comment}/reject', [App\Http\Controllers\Api\V1\CommentController::class, 'reject'])->middleware('permission:manage comments');
        Route::put('comments/{comment}/spam', [App\Http\Controllers\Api\V1\CommentController::class, 'markAsSpam'])->middleware('permission:manage comments');
        Route::delete('comments/{comment}', [App\Http\Controllers\Api\V1\CommentController::class, 'destroy'])->middleware('permission:manage comments');

        // Users Management
        Route::apiResource('users', App\Http\Controllers\Api\V1\UserController::class)->middleware('permission:manage users');
        Route::post('users/{user}/force-logout', [App\Http\Controllers\Api\V1\UserController::class, 'forceLogout'])->middleware('permission:manage users');

        // Roles (for user management)
        Route::get('roles', [App\Http\Controllers\Api\V1\RoleController::class, 'index'])->middleware('permission:manage users');
        Route::get('roles/permissions', [App\Http\Controllers\Api\V1\RoleController::class, 'permissions'])->middleware('permission:manage users');
        Route::post('roles', [App\Http\Controllers\Api\V1\RoleController::class, 'store'])->middleware('permission:manage users');
        Route::get('roles/{role}', [App\Http\Controllers\Api\V1\RoleController::class, 'show'])->middleware('permission:manage users');
        Route::put('roles/{role}', [App\Http\Controllers\Api\V1\RoleController::class, 'update'])->middleware('permission:manage users');
        Route::delete('roles/{role}', [App\Http\Controllers\Api\V1\RoleController::class, 'destroy'])->middleware('permission:manage users');
        Route::post('roles/{role}/permissions', [App\Http\Controllers\Api\V1\RoleController::class, 'syncPermissions'])->middleware('permission:manage users');
        Route::post('roles/{role}/duplicate', [App\Http\Controllers\Api\V1\RoleController::class, 'duplicate'])->middleware('permission:manage users');

        // Activity Logs
        Route::get('activity-logs', [App\Http\Controllers\Api\V1\ActivityLogController::class, 'index'])->middleware('permission:manage users');
        Route::post('activity-logs/clear', [App\Http\Controllers\Api\V1\ActivityLogController::class, 'clear'])->middleware('permission:manage settings');
        Route::get('activity-logs/recent', [App\Http\Controllers\Api\V1\ActivityLogController::class, 'recent'])->middleware('permission:manage users');
        Route::get('activity-logs/statistics', [App\Http\Controllers\Api\V1\ActivityLogController::class, 'statistics'])->middleware('permission:manage users');
        Route::get('activity-logs/export', [App\Http\Controllers\Api\V1\ActivityLogController::class, 'export'])->middleware('permission:manage users');
        Route::get('activity-logs/user/{userId}', [App\Http\Controllers\Api\V1\ActivityLogController::class, 'userActivity'])->middleware('permission:manage users');
        Route::get('activity-logs/{activityLog}', [App\Http\Controllers\Api\V1\ActivityLogController::class, 'show'])->middleware('permission:manage users');

        // Login History (Admin)
        Route::get('login-history', [App\Http\Controllers\Api\V1\LoginHistoryController::class, 'index'])->middleware('permission:manage users');
        Route::post('login-history/clear', [App\Http\Controllers\Api\V1\LoginHistoryController::class, 'clear'])->middleware('permission:manage settings');
        Route::get('login-history/statistics', [App\Http\Controllers\Api\V1\LoginHistoryController::class, 'statistics'])->middleware('permission:manage users');
        Route::get('login-history/export', [App\Http\Controllers\Api\V1\LoginHistoryController::class, 'export'])->middleware('permission:manage users');

        // SEO Tools
        Route::get('seo/sitemap', [App\Http\Controllers\Api\V1\SeoController::class, 'generateSitemap']);
        Route::get('seo/robots-txt', [App\Http\Controllers\Api\V1\SeoController::class, 'getRobotsTxt'])->middleware('permission:manage settings');
        Route::put('seo/robots-txt', [App\Http\Controllers\Api\V1\SeoController::class, 'updateRobotsTxt'])->middleware('permission:manage settings');
        Route::get('contents/{content}/seo-analysis', [App\Http\Controllers\Api\V1\SeoController::class, 'analyzeContent'])->middleware('permission:edit content');
        Route::get('contents/{content}/schema', [App\Http\Controllers\Api\V1\SeoController::class, 'generateSchema']);

        // Redirects
        // Statistics route must be defined before apiResource to avoid route conflict
        Route::get('redirects/statistics', [App\Http\Controllers\Api\V1\RedirectController::class, 'statistics'])->middleware('permission:manage settings');
        Route::apiResource('redirects', App\Http\Controllers\Api\V1\RedirectController::class)->middleware('permission:manage settings');

        // Cache Management
        Route::post('cache/clear', function () {
            $cacheService = new \App\Services\CacheService;
            $cacheService->clearAll();

            return response()->json(['message' => 'All caches cleared successfully']);
        })->middleware('permission:manage settings');

        Route::post('cache/clear-content', function () {
            $cacheService = new \App\Services\CacheService;
            $cacheService->clearContentCaches();

            return response()->json(['message' => 'Content caches cleared successfully']);
        })->middleware('permission:manage settings');

        Route::post('cache/warm-up', function () {
            $cacheService = new \App\Services\CacheService;
            $cacheService->warmUp();

            return response()->json(['message' => 'Cache warmed up successfully']);
        })->middleware('permission:manage settings');

        // Backups
        // Statistics route must be defined before apiResource to avoid route conflict
        Route::get('backups/statistics', [App\Http\Controllers\Api\V1\BackupController::class, 'stats'])->middleware('permission:manage settings');
        Route::get('backups/stats', [App\Http\Controllers\Api\V1\BackupController::class, 'stats'])->middleware('permission:manage settings');
        Route::match(['GET', 'POST'], 'backups/schedule', [App\Http\Controllers\Api\V1\BackupController::class, 'schedule'])->middleware('permission:manage settings');
        Route::post('backups/cleanup', [App\Http\Controllers\Api\V1\BackupController::class, 'cleanup'])->middleware('permission:manage settings');
        Route::apiResource('backups', App\Http\Controllers\Api\V1\BackupController::class)->middleware('permission:manage settings');
        Route::post('backups/{backup}/restore', [App\Http\Controllers\Api\V1\BackupController::class, 'restore'])->middleware('permission:manage settings');
        Route::get('backups/{backup}/download', [App\Http\Controllers\Api\V1\BackupController::class, 'download'])->middleware('permission:manage settings');

        // Security
        Route::get('security/logs', [App\Http\Controllers\Api\V1\SecurityController::class, 'index'])->middleware('permission:manage settings');
        Route::post('security/logs/clear', [App\Http\Controllers\Api\V1\SecurityController::class, 'clear'])->middleware('permission:manage settings');
        Route::get('security/logs/{securityLog}', [App\Http\Controllers\Api\V1\SecurityController::class, 'show'])->middleware('permission:manage settings');
        Route::get('security/stats', [App\Http\Controllers\Api\V1\SecurityController::class, 'stats'])->middleware('permission:manage settings');
        Route::get('security/alerts', [App\Http\Controllers\Api\V1\SecurityController::class, 'alerts'])->middleware('permission:manage settings');
        
        // IP Blocklist
        Route::get('security/blocklist', [App\Http\Controllers\Api\V1\SecurityController::class, 'getBlocklist'])->middleware('permission:manage settings');
        Route::post('security/block-ip', [App\Http\Controllers\Api\V1\SecurityController::class, 'blockIp'])->middleware('permission:manage settings');
        Route::post('security/unblock-ip', [App\Http\Controllers\Api\V1\SecurityController::class, 'unblockIp'])->middleware('permission:manage settings');
        Route::post('security/bulk-block', [App\Http\Controllers\Api\V1\SecurityController::class, 'bulkBlock'])->middleware('permission:manage settings');
        Route::post('security/bulk-unblock', [App\Http\Controllers\Api\V1\SecurityController::class, 'bulkUnblock'])->middleware('permission:manage settings');
        
        // IP Whitelist
        Route::get('security/whitelist', [App\Http\Controllers\Api\V1\SecurityController::class, 'getWhitelist'])->middleware('permission:manage settings');
        Route::post('security/whitelist', [App\Http\Controllers\Api\V1\SecurityController::class, 'addToWhitelist'])->middleware('permission:manage settings');
        Route::delete('security/whitelist', [App\Http\Controllers\Api\V1\SecurityController::class, 'removeFromWhitelist'])->middleware('permission:manage settings');
        Route::post('security/bulk-whitelist', [App\Http\Controllers\Api\V1\SecurityController::class, 'bulkWhitelist'])->middleware('permission:manage settings');
        Route::post('security/bulk-remove-whitelist', [App\Http\Controllers\Api\V1\SecurityController::class, 'bulkRemoveWhitelist'])->middleware('permission:manage settings');
        
        // IP Check & Clear
        Route::get('security/check-ip', [App\Http\Controllers\Api\V1\SecurityController::class, 'checkIp'])->middleware('permission:manage settings');
        Route::post('security/clear-failed-attempts', [App\Http\Controllers\Api\V1\SecurityController::class, 'clearFailedAttempts'])->middleware('permission:manage settings');

        // Themes
        Route::apiResource('themes', App\Http\Controllers\Api\V1\ThemeController::class)->middleware('permission:manage themes');
        Route::post('themes/{theme}/activate', [App\Http\Controllers\Api\V1\ThemeController::class, 'activate'])->middleware('permission:manage themes');
        Route::post('themes/{theme}/deactivate', [App\Http\Controllers\Api\V1\ThemeController::class, 'deactivate'])->middleware('permission:manage themes');
        Route::get('themes/active', [App\Http\Controllers\Api\V1\ThemeController::class, 'getActive']);
        Route::post('themes/scan', [App\Http\Controllers\Api\V1\ThemeController::class, 'scan'])->middleware('permission:manage themes');
        Route::post('themes/{theme}/validate', [App\Http\Controllers\Api\V1\ThemeController::class, 'validate'])->middleware('permission:manage themes');
        Route::get('themes/{theme}/assets', [App\Http\Controllers\Api\V1\ThemeController::class, 'getAssets'])->middleware('permission:manage themes');
        Route::post('themes/{theme}/compile', [App\Http\Controllers\Api\V1\ThemeController::class, 'compileAssets'])->middleware('permission:manage themes');
        Route::get('themes/{theme}/setting', [App\Http\Controllers\Api\V1\ThemeController::class, 'getSetting'])->middleware('permission:manage themes');
        Route::put('themes/{theme}/settings', [App\Http\Controllers\Api\V1\ThemeController::class, 'updateSettings'])->middleware('permission:manage themes');
        Route::put('themes/{theme}/custom-css', [App\Http\Controllers\Api\V1\ThemeController::class, 'updateCustomCss'])->middleware('permission:manage themes');
        Route::get('themes/{theme}/export', [App\Http\Controllers\Api\V1\ThemeController::class, 'export'])->middleware('permission:manage themes');
        Route::post('themes/import', [App\Http\Controllers\Api\V1\ThemeController::class, 'import'])->middleware('permission:manage themes');
        Route::get('themes/{theme}/partials', [App\Http\Controllers\Api\V1\ThemeController::class, 'getPartials'])->middleware('permission:manage themes');
        Route::get('themes/{theme}/layouts', [App\Http\Controllers\Api\V1\ThemeController::class, 'getLayouts'])->middleware('permission:manage themes');
        Route::post('themes/{theme}/partials/render', [App\Http\Controllers\Api\V1\ThemeController::class, 'renderPartial'])->middleware('permission:manage themes');
        Route::post('themes/{theme}/layouts/render', [App\Http\Controllers\Api\V1\ThemeController::class, 'renderLayout'])->middleware('permission:manage themes');

        // Menus
        Route::apiResource('menus', App\Http\Controllers\Api\V1\MenuController::class)->middleware('permission:manage menus');
        Route::post('menus/{menu}/items', [App\Http\Controllers\Api\V1\MenuController::class, 'addItem'])->middleware('permission:manage menus');
        Route::put('menus/{menu}/items/{menuItem}', [App\Http\Controllers\Api\V1\MenuController::class, 'updateItem'])->middleware('permission:manage menus');
        Route::delete('menus/{menu}/items/{menuItem}', [App\Http\Controllers\Api\V1\MenuController::class, 'deleteItem'])->middleware('permission:manage menus');
        Route::post('menus/{menu}/reorder', [App\Http\Controllers\Api\V1\MenuController::class, 'reorderItems'])->middleware('permission:manage menus');
        Route::get('menus/location/{location}', [App\Http\Controllers\Api\V1\MenuController::class, 'getByLocation']);

        // Widgets
        Route::apiResource('widgets', App\Http\Controllers\Api\V1\WidgetController::class)->middleware('permission:manage widgets');
        Route::get('widgets/location/{location}', [App\Http\Controllers\Api\V1\WidgetController::class, 'getByLocation']);
        Route::post('widgets/reorder', [App\Http\Controllers\Api\V1\WidgetController::class, 'reorder'])->middleware('permission:manage widgets');

        // Plugins
        Route::apiResource('plugins', App\Http\Controllers\Api\V1\PluginController::class)->middleware('permission:manage plugins');
        Route::post('plugins/{plugin}/activate', [App\Http\Controllers\Api\V1\PluginController::class, 'activate'])->middleware('permission:manage plugins');
        Route::post('plugins/{plugin}/deactivate', [App\Http\Controllers\Api\V1\PluginController::class, 'deactivate'])->middleware('permission:manage plugins');
        Route::put('plugins/{plugin}/settings', [App\Http\Controllers\Api\V1\PluginController::class, 'updateSettings'])->middleware('permission:manage plugins');
        Route::get('plugins/active', [App\Http\Controllers\Api\V1\PluginController::class, 'getActive']);

        // Webhooks
        // Statistics route must be defined before apiResource to avoid route conflict
        Route::get('webhooks/statistics', [App\Http\Controllers\Api\V1\WebhookController::class, 'statistics'])->middleware('permission:manage settings');
        Route::apiResource('webhooks', App\Http\Controllers\Api\V1\WebhookController::class)->middleware('permission:manage settings');
        Route::post('webhooks/{webhook}/test', [App\Http\Controllers\Api\V1\WebhookController::class, 'test'])->middleware('permission:manage settings');

        // Custom Fields
        Route::apiResource('field-groups', App\Http\Controllers\Api\V1\FieldGroupController::class)->middleware('permission:manage content');
        Route::apiResource('custom-fields', App\Http\Controllers\Api\V1\CustomFieldController::class)->middleware('permission:manage content');
        Route::get('custom-fields/types', [App\Http\Controllers\Api\V1\CustomFieldController::class, 'getFieldTypes']);

        // Forms
        Route::apiResource('forms', App\Http\Controllers\Api\V1\FormController::class)->middleware('permission:manage forms');
        Route::post('forms/{form}/fields', [App\Http\Controllers\Api\V1\FormController::class, 'addField'])->middleware('permission:manage forms');
        Route::put('forms/{form}/fields/{formField}', [App\Http\Controllers\Api\V1\FormController::class, 'updateField'])->middleware('permission:manage forms');
        Route::delete('forms/{form}/fields/{formField}', [App\Http\Controllers\Api\V1\FormController::class, 'deleteField'])->middleware('permission:manage forms');
        Route::post('forms/{form}/reorder-fields', [App\Http\Controllers\Api\V1\FormController::class, 'reorderFields'])->middleware('permission:manage forms');
        // Form submission (public - with rate limiting: 10 requests per minute to prevent spam)
        Route::post('forms/{form}/submit', [App\Http\Controllers\Api\V1\FormController::class, 'submit'])->middleware('throttle:10,1');

        // Form Submissions
        Route::get('forms/{form}/submissions', [App\Http\Controllers\Api\V1\FormSubmissionController::class, 'index'])->middleware('permission:manage forms');
        Route::get('form-submissions', [App\Http\Controllers\Api\V1\FormSubmissionController::class, 'index'])->middleware('permission:manage forms');
        Route::get('form-submissions/{formSubmission}', [App\Http\Controllers\Api\V1\FormSubmissionController::class, 'show'])->middleware('permission:manage forms');
        Route::put('form-submissions/{formSubmission}/read', [App\Http\Controllers\Api\V1\FormSubmissionController::class, 'markAsRead'])->middleware('permission:manage forms');
        Route::put('form-submissions/{formSubmission}/archive', [App\Http\Controllers\Api\V1\FormSubmissionController::class, 'archive'])->middleware('permission:manage forms');
        Route::delete('form-submissions/{formSubmission}', [App\Http\Controllers\Api\V1\FormSubmissionController::class, 'destroy'])->middleware('permission:manage forms');
        Route::get('forms/{form}/submissions/export', [App\Http\Controllers\Api\V1\FormSubmissionController::class, 'export'])->middleware('permission:manage forms');
        Route::get('forms/{form}/submissions/statistics', [App\Http\Controllers\Api\V1\FormSubmissionController::class, 'statistics'])->middleware('permission:manage forms');

        // Analytics
        Route::prefix('analytics')->middleware('permission:view analytics')->group(function () {
            Route::get('overview', [App\Http\Controllers\Api\V1\AnalyticsController::class, 'overview']);
            Route::get('visits', [App\Http\Controllers\Api\V1\AnalyticsController::class, 'visits']);
            Route::get('top-pages', [App\Http\Controllers\Api\V1\AnalyticsController::class, 'topPages']);
            Route::get('top-content', [App\Http\Controllers\Api\V1\AnalyticsController::class, 'topContent']);
            Route::get('devices', [App\Http\Controllers\Api\V1\AnalyticsController::class, 'devices']);
            Route::get('browsers', [App\Http\Controllers\Api\V1\AnalyticsController::class, 'browsers']);
            Route::get('countries', [App\Http\Controllers\Api\V1\AnalyticsController::class, 'countries']);
            Route::get('referrers', [App\Http\Controllers\Api\V1\AnalyticsController::class, 'referrers']);
            Route::get('events', [App\Http\Controllers\Api\V1\AnalyticsController::class, 'events']);
            Route::get('event-stats', [App\Http\Controllers\Api\V1\AnalyticsController::class, 'eventStats']);
            // Realtime endpoint needs higher rate limit for polling
            Route::get('realtime', [App\Http\Controllers\Api\V1\AnalyticsController::class, 'realTime'])
                ->middleware('throttle:120,1'); // 120 requests per minute for real-time polling
            // Export analytics data to CSV
            Route::get('export', [App\Http\Controllers\Api\V1\AnalyticsController::class, 'export']);
        });

        // Search
        Route::prefix('search')->group(function () {
            Route::get('', [App\Http\Controllers\Api\V1\SearchController::class, 'search']);
            Route::get('suggestions', [App\Http\Controllers\Api\V1\SearchController::class, 'suggestions']);
            Route::get('popular-queries', [App\Http\Controllers\Api\V1\SearchController::class, 'popularQueries'])->middleware('permission:view analytics');
            Route::get('no-results-queries', [App\Http\Controllers\Api\V1\SearchController::class, 'noResultsQueries'])->middleware('permission:view analytics');
            Route::get('stats', [App\Http\Controllers\Api\V1\SearchController::class, 'searchStats'])->middleware('permission:view analytics');
            Route::post('reindex', [App\Http\Controllers\Api\V1\SearchController::class, 'reindex'])->middleware('permission:manage content');
        });

        // Settings
        Route::apiResource('settings', App\Http\Controllers\Api\V1\SettingController::class)->middleware('permission:manage settings');
        Route::get('settings/group/{group}', [App\Http\Controllers\Api\V1\SettingController::class, 'getGroup'])->middleware('permission:manage settings');
        Route::post('settings/bulk-update', [App\Http\Controllers\Api\V1\SettingController::class, 'bulkUpdate'])->middleware('permission:manage settings');

        // Email Templates
        Route::apiResource('email-templates', App\Http\Controllers\Api\V1\EmailTemplateController::class)->middleware('permission:manage settings');
        Route::post('email-templates/{emailTemplate}/preview', [App\Http\Controllers\Api\V1\EmailTemplateController::class, 'preview'])->middleware('permission:manage settings');
        Route::post('email-templates/{emailTemplate}/send-test', [App\Http\Controllers\Api\V1\EmailTemplateController::class, 'sendTest'])->middleware('permission:manage settings');

        // Email Testing & Verification
        Route::prefix('email-test')->middleware('permission:manage settings')->group(function () {
            Route::post('test-connection', [App\Http\Controllers\Api\V1\EmailTestController::class, 'testConnection']);
            Route::post('send-test', [App\Http\Controllers\Api\V1\EmailTestController::class, 'sendTest']);
            Route::get('queue-status', [App\Http\Controllers\Api\V1\EmailTestController::class, 'getQueueStatus']);
            Route::get('recent-logs', [App\Http\Controllers\Api\V1\EmailTestController::class, 'getRecentLogs']);
            Route::get('validate-config', [App\Http\Controllers\Api\V1\EmailTestController::class, 'validateConfig']);
        });

        // Notifications
        Route::get('notifications', [App\Http\Controllers\Api\V1\NotificationController::class, 'index']);
        Route::get('notifications/unread-count', [App\Http\Controllers\Api\V1\NotificationController::class, 'unreadCount']);
        Route::put('notifications/{notification}/read', [App\Http\Controllers\Api\V1\NotificationController::class, 'markAsRead']);
        Route::put('notifications/read-all', [App\Http\Controllers\Api\V1\NotificationController::class, 'markAllAsRead']);
        Route::delete('notifications/{notification}', [App\Http\Controllers\Api\V1\NotificationController::class, 'destroy']);

        // Content Templates
        Route::apiResource('content-templates', App\Http\Controllers\Api\V1\ContentTemplateController::class)->middleware('permission:manage content');
        Route::post('content-templates/{contentTemplate}/create-content', [App\Http\Controllers\Api\V1\ContentTemplateController::class, 'createContent'])->middleware('permission:create content');

        // Content Preview
        Route::get('contents/{content}/preview', [App\Http\Controllers\Api\V1\ContentController::class, 'preview'])->middleware('permission:edit content');

        // Scheduled Tasks
        Route::apiResource('scheduled-tasks', App\Http\Controllers\Api\V1\ScheduledTaskController::class)->middleware('permission:manage settings');
        Route::post('scheduled-tasks/{id}/run', [App\Http\Controllers\Api\V1\ScheduledTaskController::class, 'run'])->middleware('permission:manage settings');

        // File Manager
        Route::get('file-manager', [App\Http\Controllers\Api\V1\FileManagerController::class, 'index'])->middleware('permission:manage media');
        Route::post('file-manager/upload', [App\Http\Controllers\Api\V1\FileManagerController::class, 'upload'])->middleware('permission:manage media');
        Route::delete('file-manager', [App\Http\Controllers\Api\V1\FileManagerController::class, 'delete'])->middleware('permission:manage media');
        Route::post('file-manager/folder', [App\Http\Controllers\Api\V1\FileManagerController::class, 'createFolder'])->middleware('permission:manage media');
        Route::delete('file-manager/folder', [App\Http\Controllers\Api\V1\FileManagerController::class, 'deleteFolder'])->middleware('permission:manage media');

        // Log Viewer
        Route::get('logs', [App\Http\Controllers\Api\V1\LogController::class, 'index'])->middleware('permission:manage settings');
        Route::post('logs/clear', [App\Http\Controllers\Api\V1\LogController::class, 'clear'])->middleware('permission:manage settings');
        Route::get('logs/{filename}', [App\Http\Controllers\Api\V1\LogController::class, 'show'])->middleware('permission:manage settings');
        Route::get('logs/{filename}/download', [App\Http\Controllers\Api\V1\LogController::class, 'download'])->middleware('permission:manage settings');

        // System Information
        Route::get('system/info', [App\Http\Controllers\Api\V1\SystemController::class, 'info'])->middleware('permission:manage settings');
        Route::get('system/health', [App\Http\Controllers\Api\V1\SystemController::class, 'health'])->middleware('permission:manage settings');
        Route::get('system/health/detailed', [App\Http\Controllers\Api\V1\SystemController::class, 'systemHealth'])->middleware('permission:manage settings');
        Route::get('system/statistics', [App\Http\Controllers\Api\V1\SystemController::class, 'statistics'])->middleware('permission:manage settings');
        Route::get('system/cache', [App\Http\Controllers\Api\V1\SystemController::class, 'cache'])->middleware('permission:manage settings');
        Route::get('system/cache-status', [App\Http\Controllers\Api\V1\SystemController::class, 'cacheStatus'])->middleware('permission:manage settings');
        Route::post('system/cache/clear', [App\Http\Controllers\Api\V1\SystemController::class, 'clearCache'])->middleware('permission:manage settings');
        Route::post('system/cache/warm', [App\Http\Controllers\Api\V1\SystemController::class, 'warmCache'])->middleware('permission:manage settings');
        Route::get('system/cache/warming-stats', [App\Http\Controllers\Api\V1\SystemController::class, 'cacheWarmingStats'])->middleware('permission:manage settings');
        Route::get('system/query-performance', [App\Http\Controllers\Api\V1\SystemController::class, 'queryPerformance'])->middleware('permission:manage settings');
        Route::post('system/rate-limit/clear', [App\Http\Controllers\Api\V1\SystemController::class, 'clearRateLimit'])->middleware('permission:manage settings');

        // Redis Management
        Route::get('redis/settings', [App\Http\Controllers\Api\V1\RedisController::class, 'index'])->middleware('permission:manage settings');
        Route::put('redis/settings', [App\Http\Controllers\Api\V1\RedisController::class, 'update'])->middleware('permission:manage settings');
        Route::get('redis/test-connection', [App\Http\Controllers\Api\V1\RedisController::class, 'testConnection'])->middleware('permission:manage settings');
        Route::get('redis/info', [App\Http\Controllers\Api\V1\RedisController::class, 'info'])->middleware('permission:manage settings');
        Route::get('redis/cache-stats', [App\Http\Controllers\Api\V1\RedisController::class, 'cacheStats'])->middleware('permission:manage settings');
        Route::post('redis/flush-cache', [App\Http\Controllers\Api\V1\RedisController::class, 'flushCache'])->middleware('permission:manage settings');

        // Multi-language Support
        // These routes must be defined BEFORE apiResource to avoid route conflicts
        Route::get('languages/ui-stats', [App\Http\Controllers\Api\V1\LanguageController::class, 'uiStats'])->middleware('permission:manage settings');
        Route::get('languages/{language}/export-pack', [App\Http\Controllers\Api\V1\LanguageController::class, 'exportPack'])->middleware('permission:manage settings');
        Route::post('languages/import-pack', [App\Http\Controllers\Api\V1\LanguageController::class, 'importPack'])->middleware('permission:manage settings');
        Route::post('languages/{language}/set-default', [App\Http\Controllers\Api\V1\LanguageController::class, 'setDefault'])->middleware('permission:manage settings');
        Route::apiResource('languages', App\Http\Controllers\Api\V1\LanguageController::class)->middleware('permission:manage settings');
    });
});

// Legacy routes (redirect to v1)
Route::post('/login', function () {
    return response()->json(['message' => 'Please use /api/v1/login'], 301);
});
Route::post('/register', function () {
    return response()->json(['message' => 'Please use /api/v1/register'], 301);
});
