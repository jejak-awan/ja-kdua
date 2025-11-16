<?php

/**
 * Global Helper Functions for JA-CMS Theme System
 * 
 * These functions provide a convenient API for interacting with the theme system.
 * Vue SPA handles rendering, so only data/configuration helpers are needed.
 */

if (!function_exists('theme')) {
    /**
     * Get active theme
     * 
     * @param string $type Theme type (frontend, admin, email)
     * @return \App\Models\Theme|null
     */
    function theme(string $type = 'frontend'): ?\App\Models\Theme
    {
        return \App\Helpers\ThemeHelper::activeTheme($type);
    }
}

if (!function_exists('theme_setting')) {
    /**
     * Get theme setting value
     * 
     * @param string $key Setting key
     * @param mixed $default Default value if not found
     * @param string $type Theme type
     * @return mixed
     */
    function theme_setting(string $key, $default = null, string $type = 'frontend'): mixed
    {
        return \App\Helpers\ThemeHelper::setting($key, $default, $type);
    }
}

if (!function_exists('theme_asset')) {
    /**
     * Get theme asset URL
     * 
     * @param string $path Relative path to asset
     * @param string $type Theme type
     * @return string|null
     */
    function theme_asset(string $path, string $type = 'frontend'): ?string
    {
        return \App\Helpers\ThemeHelper::asset($path, $type);
    }
}

if (!function_exists('theme_supports')) {
    /**
     * Check if theme supports a feature
     * 
     * @param string $feature Feature name (e.g., 'custom_logo', 'menus')
     * @param string $type Theme type
     * @return bool
     */
    function theme_supports(string $feature, string $type = 'frontend'): bool
    {
        return \App\Helpers\ThemeHelper::supports($feature, $type);
    }
}

if (!function_exists('theme_custom_css')) {
    /**
     * Get theme custom CSS
     * 
     * @param string $type Theme type
     * @return string|null
     */
    function theme_custom_css(string $type = 'frontend'): ?string
    {
        return \App\Helpers\ThemeHelper::customCss($type);
    }
}

if (!function_exists('theme_menu')) {
    /**
     * Get theme menu data (for API/Vue consumption)
     * 
     * @param string $slug Menu slug
     * @param string $type Theme type
     * @return array|null
     */
    function theme_menu(string $slug, string $type = 'frontend'): ?array
    {
        return \App\Helpers\ThemeHelper::getMenu($slug, $type);
    }
}


