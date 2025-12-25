<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                // Load Cache Driver
                $cacheDriver = \App\Models\Setting::where('key', 'cache_driver')->value('value');
                if ($cacheDriver) {
                    config(['cache.default' => $cacheDriver]);
                }

                // Load CDN and Performance Settings
                $perfSettings = \App\Models\Setting::where('group', 'performance')
                    ->whereIn('key', [
                        'enable_cdn', 'cdn_url', 'cdn_preset', 'cdn_included_dirs', 'cdn_excluded_extensions',
                        'optimize_images', 'lazy_loading'
                    ]);

                // Load Media Settings (Storage Driver + AWS)
                $mediaSettings = \App\Models\Setting::where('group', 'media')
                    ->whereIn('key', [
                        'storage_driver',
                        'aws_access_key_id', 'aws_secret_access_key', 'aws_default_region', 'aws_bucket', 'aws_endpoint',
                        'google_client_id', 'google_client_secret', 'google_refresh_token', 'google_folder_id',
                        'ftp_host', 'ftp_username', 'ftp_password', 'ftp_root', 'ftp_port', 'ftp_ssl',
                        'dropbox_authorization_token'
                    ])
                    ->get()
                    ->flatMap(function ($setting) {
                        return [$setting->key => $setting->value];
                    });
                
                // Merge all settings
                $allSettings = $perfSettings->merge($mediaSettings);

                if ($allSettings->isNotEmpty()) {
                    config([
                        // CDN
                        'cdn.enabled' => filter_var($allSettings['enable_cdn'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'cdn.url' => $allSettings['cdn_url'] ?? null,
                        'cdn.preset' => $allSettings['cdn_preset'] ?? 'custom',
                        'cdn.included_dirs' => $allSettings['cdn_included_dirs'] ?? 'assets,storage',
                        'cdn.excluded_extensions' => $allSettings['cdn_excluded_extensions'] ?? '.php,.json',
                        
                        // Performance
                        'media.optimize' => filter_var($allSettings['optimize_images'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'view.lazy_loading' => filter_var($allSettings['lazy_loading'] ?? true, FILTER_VALIDATE_BOOLEAN),
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
                                'filesystems.disks.s3.use_path_style_endpoint' => !empty($allSettings['aws_endpoint']),
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
    }
}
