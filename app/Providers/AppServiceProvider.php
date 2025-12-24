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
                $cacheDriver = \App\Models\Setting::where('key', 'cache_driver')->value('value');
                if ($cacheDriver) {
                    config(['cache.default' => $cacheDriver]);
                }
            }
        } catch (\Exception $e) {
            // Silently fail if database is not available
        }
    }
}
