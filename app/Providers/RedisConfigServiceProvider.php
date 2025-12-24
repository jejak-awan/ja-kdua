<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class RedisConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     * Load Redis settings from database and merge with config.
     */
    public function boot(): void
    {
        // Only load if redis_settings table exists and we're not in console during migration
        if ($this->app->runningInConsole() && $this->isMigrating()) {
            return;
        }

        try {
            if (!Schema::hasTable('redis_settings')) {
                return;
            }

            $this->loadRedisSettingsFromDatabase();
        } catch (\Exception $e) {
            // Silently fail if database is not available
            \Log::debug('RedisConfigServiceProvider: Could not load settings - ' . $e->getMessage());
        }
    }

    /**
     * Check if we're running migrations
     */
    protected function isMigrating(): bool
    {
        return in_array('migrate', $_SERVER['argv'] ?? []) 
            || in_array('migrate:fresh', $_SERVER['argv'] ?? [])
            || in_array('migrate:refresh', $_SERVER['argv'] ?? []);
    }

    /**
     * Load Redis settings from database and apply to config
     */
    protected function loadRedisSettingsFromDatabase(): void
    {
        // Use model collection to properly apply value accessor (decryption)
        $redisSettings = \App\Models\RedisSetting::all();
        
        if ($redisSettings->isEmpty()) {
            return;
        }

        // Build settings array using model accessor (handles decryption)
        $settings = $redisSettings->mapWithKeys(function ($setting) {
            return [$setting->key => $setting->value]; // This uses getValueAttribute accessor
        });

        // Map RedisSetting keys to config values
        $configMap = [
            'redis_enabled' => null, // Special handling
            'redis_host' => 'database.redis.default.host',
            'redis_port' => 'database.redis.default.port',
            'redis_password' => 'database.redis.default.password',
            'redis_database' => 'database.redis.default.database',
            'redis_prefix' => 'database.redis.options.prefix',
            'cache_driver' => 'cache.default',
        ];

        foreach ($settings as $key => $value) {
            // Skip empty values - use .env defaults
            if ($value === null || $value === '') {
                continue;
            }

            // Handle redis_enabled specially - DEPRECATED: Controlled by global cache_driver
            if ($key === 'redis_enabled') {
                continue;
            }

            // Apply config if mapping exists
            if (isset($configMap[$key]) && $configMap[$key] !== null) {
                config([$configMap[$key] => $value]);
                
                // Also update cache connection for consistency
                if ($key === 'redis_host') {
                    config(['database.redis.cache.host' => $value]);
                }
                if ($key === 'redis_port') {
                    config(['database.redis.cache.port' => $value]);
                }
                if ($key === 'redis_password') {
                    config(['database.redis.cache.password' => $value]);
                }
            }
        }
    }
}
