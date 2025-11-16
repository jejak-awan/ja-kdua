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
        //
    }
}
