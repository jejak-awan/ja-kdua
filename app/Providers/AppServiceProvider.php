<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Logout;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register ThemeHooksService as singleton
        $this->app->singleton(\App\Services\ThemeHooksService::class);

        // Register ThemeCacheService as singleton
        $this->app->singleton(\App\Services\ThemeCacheService::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Inject global data for the Builder directly into the app view
        // This avoids making separate API calls for basic configuration data
        \Illuminate\Support\Facades\View::composer('app', function ($view) {
            try {
                // 1. Post Types
                // Fetch distinct types from Content table
                $postTypes = \App\Models\Content::distinct()->pluck('type')->filter()->values()->all();
                if (empty($postTypes)) {
                    $postTypes = ['post', 'page'];
                }
                // Map to label/value format
                $postTypeOptions = array_map(function($type) {
                    return ['label' => ucfirst($type), 'value' => $type];
                }, $postTypes);

                // 2. Roles
                // Fetch all roles
                $roles = \Spatie\Permission\Models\Role::pluck('name')->all();
                $roleOptions = array_map(function($role) {
                    return ['label' => ucfirst($role), 'value' => $role];
                }, $roles);

                // 3. Taxonomies (Static map for now as they are model-based)
                $taxonomyOptions = [
                    ['label' => 'Categories', 'value' => 'category'],
                    ['label' => 'Tags', 'value' => 'post_tag'],
                    ['label' => 'Project Categories', 'value' => 'project_category'],
                    ['label' => 'Project Tags', 'value' => 'project_tag']
                ];

                // 4. Users (for include_users)
                $users = \App\Models\User::select('id', 'name')->get();
                $userOptions = $users->map(function($user) {
                    return ['label' => $user->name, 'value' => $user->id];
                })->all();

                // 5. Posts (for include_posts) - Grouped by Type? Or just flat with 'type' property for filtering
                // We fetch ID, Title, Type. Limit to reasonable amount or all?
                // For now, let's try fetching all non-revision content.
                $posts = \App\Models\Content::where('type', '!=', 'revision')
                    ->select('id', 'title', 'type')
                    ->latest()
                    ->get();
                
                $postOptions = $posts->map(function($post) {
                    return [
                        'label' => $post->title, 
                        'value' => $post->id, 
                        'type' => $post->type // Included for filtering
                    ];
                })->all();

                // 6. Terms (for include_terms) - Categories and Tags
                // We need to fetch Categories and Tags.
                $categories = \App\Models\Category::select('id', 'name')->get()->map(function($c) {
                    return ['label' => $c->name, 'value' => $c->id, 'taxonomy' => 'category'];
                });
                
                $tags = \App\Models\Tag::select('id', 'name')->get()->map(function($t) {
                    return ['label' => $t->name, 'value' => $t->id, 'taxonomy' => 'post_tag'];
                });
                
                // Merge terms
                $termOptions = $categories->merge($tags)->values()->all();

                // 7. Forms (for FormPicker)
                $forms = \App\Models\Form::where('is_active', true)
                    ->select('id', 'name', 'slug')
                    ->latest()
                    ->get();
                
                $formOptions = $forms->map(function($form) {
                    return [
                        'label' => $form->name,
                        'value' => $form->slug
                    ];
                })->all();

                $view->with('jaCmsData', [
                    'postTypes' => $postTypeOptions,
                    'roles' => $roleOptions,
                    'taxonomies' => $taxonomyOptions,
                    'users' => $userOptions,
                    'posts' => $postOptions,
                    'terms' => $termOptions,
                    'forms' => $formOptions
                ]);

            } catch (\Exception $e) {
                // Fallback if DB fails
                 $view->with('jaCmsData', [
                    'postTypes' => [['label' => 'Posts', 'value' => 'post'], ['label' => 'Pages', 'value' => 'page']],
                    'roles' => [],
                    'taxonomies' => [],
                    'users' => [],
                    'posts' => [],
                    'terms' => []
                ]);
            }
        });

        // Implicitly grant "super-admin" role all permissions
        // This works in the app by using Gate::before or can be used by Spatie directly
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });

        \App\Models\MediaFolder::observe(\App\Observers\MediaFolderObserver::class);

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                // Load Performance Settings
                $performanceSettings = \App\Models\Setting::where('group', 'performance')
                    ->whereIn('key', [
                        'enable_cache', 'cache_driver', 'cache_ttl',
                        'enable_cdn', 'cdn_url', 'cdn_preset', 'cdn_included_dirs', 'cdn_excluded_extensions',
                        'enable_image_optimization', 'enable_lazy_loading',
                    ])
                    ->get()
                    ->flatMap(function ($setting) {
                        return [$setting->key => $setting->value];
                    });

                // Apply Cache Driver Strategy
                if (isset($performanceSettings['enable_cache']) &&
                    ! filter_var($performanceSettings['enable_cache'], FILTER_VALIDATE_BOOLEAN)) {
                    config(['cache.default' => 'array']); // Force disable if explicitly off
                } elseif (isset($performanceSettings['cache_driver'])) {
                    config(['cache.default' => $performanceSettings['cache_driver']]);
                }

                // Load Session Lifetime
                $sessionLifetime = \App\Models\Setting::where('key', 'session_lifetime')->value('value');
                if ($sessionLifetime) {
                    config(['session.lifetime' => (int) $sessionLifetime]);
                }

                // Load Media Settings (Storage Driver + AWS)
                $mediaSettings = \App\Models\Setting::where('group', 'media')
                    ->whereIn('key', [
                        'storage_driver',
                        'aws_access_key_id', 'aws_secret_access_key', 'aws_default_region', 'aws_bucket', 'aws_endpoint',
                        'google_client_id', 'google_client_secret', 'google_refresh_token', 'google_folder_id',
                        'ftp_host', 'ftp_username', 'ftp_password', 'ftp_root', 'ftp_port', 'ftp_ssl',
                        'dropbox_authorization_token',
                    ])
                    ->get()
                    ->flatMap(function ($setting) {
                        return [$setting->key => $setting->value];
                    });

                // Merge all settings for easier access
                $allSettings = $performanceSettings->merge($mediaSettings);

                if ($allSettings->isNotEmpty()) {
                    config([
                        // CDN
                        'cdn.enabled' => filter_var($allSettings['enable_cdn'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'cdn.url' => $allSettings['cdn_url'] ?? null,
                        'cdn.preset' => $allSettings['cdn_preset'] ?? 'custom',
                        'cdn.included_dirs' => $allSettings['cdn_included_dirs'] ?? 'assets,storage',
                        'cdn.excluded_extensions' => $allSettings['cdn_excluded_extensions'] ?? '.php,.json',

                        // Performance
                        'media.optimize' => filter_var($allSettings['enable_image_optimization'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'view.lazy_loading' => filter_var($allSettings['enable_lazy_loading'] ?? true, FILTER_VALIDATE_BOOLEAN),
                    ]);

                    // Apply Storage Settings
                    if (isset($allSettings['storage_driver'])) {
                        config(['filesystems.default' => $allSettings['storage_driver']]);

                        if ($allSettings['storage_driver'] === 's3') {
                            config([
                                'filesystems.disks.s3.key' => $allSettings['aws_access_key_id'] ?? env('AWS_ACCESS_KEY_ID'),
                                'filesystems.disks.s3.secret' => $allSettings['aws_secret_access_key'] ?? env('AWS_SECRET_ACCESS_KEY'),
                                'filesystems.disks.s3.region' => $allSettings['aws_default_region'] ?? env('AWS_DEFAULT_REGION', 'us-east-1'),
                                'filesystems.disks.s3.bucket' => $allSettings['aws_bucket'] ?? env('AWS_BUCKET'),
                                'filesystems.disks.s3.endpoint' => $allSettings['aws_endpoint'] ?? env('AWS_ENDPOINT'),
                                'filesystems.disks.s3.use_path_style_endpoint' => ! empty($allSettings['aws_endpoint']),
                            ]);
                        } elseif ($allSettings['storage_driver'] === 'google') {
                            config([
                                'filesystems.disks.google.driver' => 'google',
                                'filesystems.disks.google.clientId' => $allSettings['google_client_id'] ?? '',
                                'filesystems.disks.google.clientSecret' => $allSettings['google_client_secret'] ?? '',
                                'filesystems.disks.google.refreshToken' => $allSettings['google_refresh_token'] ?? '',
                                'filesystems.disks.google.folderId' => $allSettings['google_folder_id'] ?? '/',
                            ]);
                        } elseif ($allSettings['storage_driver'] === 'ftp') {
                            config([
                                'filesystems.disks.ftp.driver' => 'ftp',
                                'filesystems.disks.ftp.host' => $allSettings['ftp_host'] ?? '',
                                'filesystems.disks.ftp.username' => $allSettings['ftp_username'] ?? '',
                                'filesystems.disks.ftp.password' => $allSettings['ftp_password'] ?? '',
                                'filesystems.disks.ftp.port' => $allSettings['ftp_port'] ?? 21,
                                'filesystems.disks.ftp.root' => $allSettings['ftp_root'] ?? '',
                                'filesystems.disks.ftp.ssl' => filter_var($allSettings['ftp_ssl'] ?? false, FILTER_VALIDATE_BOOLEAN),
                            ]);
                        } elseif ($allSettings['storage_driver'] === 'dropbox') {
                            config([
                                'filesystems.disks.dropbox.driver' => 'dropbox',
                                'filesystems.disks.dropbox.authorizationToken' => $allSettings['dropbox_authorization_token'] ?? '',
                            ]);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently fail if database is not available
        }

        // --- Logging Implementations ---

        // 1. Slow Query Logging (> 1 second)
        try {
            DB::listen(function ($query) {
                if ($query->time > 1000) {
                    Log::channel('slow-queries')->warning('Slow query detected', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time_ms' => $query->time,
                        'url' => request()->fullUrl(),
                    ]);
                }
            });
        } catch (\Exception $e) {
            // Ignore if DB not ready
        }

        // 2. Security Logging (Auth Events)
        Event::listen(Login::class, function ($event) {
            Log::channel('security')->info('User logged in', [
                'user_id' => $event->user->id,
                'email' => $event->user->email,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        Event::listen(Failed::class, function ($event) {
            Log::channel('security')->warning('Login failed', [
                'email' => $event->credentials['email'] ?? null,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        Event::listen(Logout::class, function ($event) {
            if ($event->user) {
                Log::channel('security')->info('User logged out', [
                    'user_id' => $event->user->id,
                    'email' => $event->user->email,
                    'ip' => request()->ip(),
                ]);
            }
        });
    }
}
