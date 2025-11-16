<?php

namespace App\Services;

use App\Models\Theme;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ThemeService
{
    protected ?ThemeHooksService $hooks = null;
    protected ThemeCacheService $cache;

    public function __construct(?ThemeHooksService $hooks = null, ?ThemeCacheService $cache = null)
    {
        $this->hooks = $hooks ?? app(ThemeHooksService::class);
        $this->cache = $cache ?? app(ThemeCacheService::class);
    }
    /**
     * Get active theme by type (with cache)
     * Auto-activates default theme if no theme is active
     */
    public function getActiveTheme(string $type = 'frontend'): ?Theme
    {
        return $this->cache->getActiveTheme($type, function () use ($type) {
            return Theme::getActiveTheme($type);
        });
    }

    /**
     * Activate a theme
     */
    public function activateTheme(Theme $theme): bool
    {
        // Fire before activation hook
        if ($this->hooks) {
            $this->hooks->doAction('theme.before_activate', $theme);
        }

        // Validate theme before activation (warn but don't block if theme directory doesn't exist)
        $errors = $theme->validate();
        if (!empty($errors)) {
            // Log warnings but allow activation if theme is in database
            // Theme might be created manually or imported
            \Log::warning('Theme validation warnings: ' . implode(', ', $errors), [
                'theme_id' => $theme->id,
                'theme_slug' => $theme->slug,
            ]);
            
            // Only block activation if critical errors (like invalid JSON)
            $criticalErrors = array_filter($errors, function($error) {
                return strpos($error, 'Invalid theme.json format') !== false;
            });
            
            if (!empty($criticalErrors)) {
                throw new \Exception("Theme validation failed: " . implode(', ', $criticalErrors));
            }
        }

        // Check dependencies (warn but don't block)
        if (!$this->checkDependencies($theme)) {
            \Log::warning('Theme dependencies not met', [
                'theme_id' => $theme->id,
                'theme_slug' => $theme->slug,
            ]);
            // Don't throw, just log warning
        }

        // Activate theme
        try {
            $theme->activate();
        } catch (\Exception $e) {
            // If activate() throws, check if it's validation error
            if (strpos($e->getMessage(), 'invalid') !== false) {
                // Allow activation even if validation fails (theme might be created manually)
                \Log::warning('Theme activation with validation warnings: ' . $e->getMessage());
                // Manually activate
                Theme::where('id', '!=', $theme->id)
                    ->where('type', $theme->type)
                    ->update(['is_active' => false]);
                
                $theme->update([
                    'is_active' => true,
                    'status' => 'active',
                ]);
                
                Cache::forget("theme.active.{$theme->type}");
            } else {
                throw $e;
            }
        }

        // Clear relevant caches
        $this->clearThemeCache($theme);

        // Fire after activation hook
        if ($this->hooks) {
            $this->hooks->doAction('theme.activated', $theme);
        }

        return true;
    }

    /**
     * Deactivate a theme
     */
    public function deactivateTheme(Theme $theme): bool
    {
        $theme->deactivate();
        $this->clearThemeCache($theme);
        return true;
    }

    /**
     * Get theme setting with fallback
     */
    public function getThemeSetting(Theme $theme, string $key, $default = null)
    {
        // Check current theme settings
        $value = $theme->getSetting($key);
        if ($value !== null) {
            return $this->hooks ? $this->hooks->applyFilter('theme.setting', $value, $theme, $key, $default) : $value;
        }

        // Check parent theme if exists
        if ($theme->hasParent()) {
            $parent = $theme->getParent();
            if ($parent) {
                $value = $parent->getSetting($key);
                if ($value !== null) {
                    return $this->hooks ? $this->hooks->applyFilter('theme.setting', $value, $theme, $key, $default) : $value;
                }
            }
        }

        // Check manifest defaults
        $manifest = $theme->getManifest();
        if ($manifest && isset($manifest['settings_schema'][$key]['default'])) {
            $value = $manifest['settings_schema'][$key]['default'];
            return $this->hooks ? $this->hooks->applyFilter('theme.setting', $value, $theme, $key, $default) : $value;
        }

        return $this->hooks ? $this->hooks->applyFilter('theme.setting', $default, $theme, $key, $default) : $default;
    }

    /**
     * Load theme assets (CSS/JS) with cache
     */
    public function loadThemeAssets(Theme $theme): array
    {
        if (!$theme || !$theme->path) {
            return ['css' => [], 'js' => []];
        }

        return $this->cache->rememberAssets($theme, function () use ($theme) {
            $assets = [
                'css' => [],
                'js' => [],
            ];

            try {
                $themePath = $theme->getThemePath();
                $publicPath = $theme->getPublicPath();

            // Load CSS files from manifest or directory
            $manifest = $theme->getManifest();
            if ($manifest && isset($manifest['assets']['css'])) {
                // Use CSS files from manifest
                foreach ($manifest['assets']['css'] as $cssFile) {
                    $assets['css'][] = "themes/{$theme->path}/{$cssFile}";
                }
            } else {
                // Fallback: scan directory
                $cssDir = "{$themePath}/assets/css";
                if (is_dir($cssDir)) {
                    $cssFiles = glob("{$cssDir}/*.css");
                    foreach ($cssFiles as $file) {
                        $filename = basename($file);
                        $assets['css'][] = "themes/{$theme->path}/assets/css/{$filename}";
                    }
                }
            }

            // Load JS files from manifest or directory
            if ($manifest && isset($manifest['assets']['js'])) {
                // Use JS files from manifest
                foreach ($manifest['assets']['js'] as $jsFile) {
                    $assets['js'][] = "themes/{$theme->path}/{$jsFile}";
                }
            } else {
                // Fallback: scan directory
                $jsDir = "{$themePath}/assets/js";
                if (is_dir($jsDir)) {
                    $jsFiles = glob("{$jsDir}/*.js");
                    foreach ($jsFiles as $file) {
                        $filename = basename($file);
                        $assets['js'][] = "themes/{$theme->path}/assets/js/{$filename}";
                    }
                }
            }

            // Load parent theme assets if exists
            if ($theme->hasParent()) {
                $parent = $theme->getParent();
                if ($parent) {
                    $parentAssets = $this->loadThemeAssets($parent);
                    $assets['css'] = array_merge($parentAssets['css'], $assets['css']);
                    $assets['js'] = array_merge($parentAssets['js'], $assets['js']);
                }
            }

            } catch (\Exception $e) {
                \Log::warning('Failed to load theme assets: ' . $e->getMessage());
                // Return empty assets on error
            }

            return $assets;
        });
    }

    /**
     * Compile theme assets
     */
    public function compileThemeAssets(Theme $theme): bool
    {
        $themePath = $theme->getThemePath();
        
        // TODO: Implement asset compilation (minify, combine, etc.)
        // For now, just copy assets to public directory
        
        $publicThemePath = public_path("themes/{$theme->slug}");
        
        if (!is_dir($publicThemePath)) {
            File::makeDirectory($publicThemePath, 0755, true);
        }

        $assetsPath = "{$themePath}/assets";
        if (is_dir($assetsPath)) {
            File::copyDirectory($assetsPath, "{$publicThemePath}/assets");
        }

        $this->clearThemeCache($theme);
        
        return true;
    }

    /**
     * Validate theme structure
     */
    public function validateTheme(Theme $theme): array
    {
        return $theme->validate();
    }

    /**
     * Check theme dependencies
     */
    public function checkDependencies(Theme $theme): bool
    {
        if (!$theme->dependencies || empty($theme->dependencies)) {
            return true;
        }

        // Check required themes
        if (isset($theme->dependencies['themes'])) {
            foreach ($theme->dependencies['themes'] as $requiredTheme) {
                $parent = Theme::where('slug', $requiredTheme)->first();
                if (!$parent || !$parent->is_active) {
                    return false;
                }
            }
        }

        // Check required plugins (if plugin system exists)
        if (isset($theme->dependencies['plugins'])) {
            // TODO: Implement plugin dependency check
        }

        return true;
    }

    /**
     * Get theme custom CSS
     */
    public function getThemeCustomCss(Theme $theme): string
    {
        $css = $theme->custom_css ?? '';

        // Add parent theme custom CSS if exists
        if ($theme->hasParent()) {
            $parent = $theme->getParent();
            if ($parent && $parent->custom_css) {
                $css = $parent->custom_css . "\n\n" . $css;
            }
        }

        return $css;
    }

    /**
     * Apply theme CSS variables
     */
    public function getThemeCssVariables(Theme $theme): string
    {
        $variables = [];
        $manifest = $theme->getManifest();

        if ($manifest && isset($manifest['settings_schema'])) {
            foreach ($manifest['settings_schema'] as $key => $setting) {
                if ($setting['type'] === 'color') {
                    $value = $this->getThemeSetting($theme, $key, $setting['default'] ?? null);
                    if ($value) {
                        $cssKey = '--theme-' . str_replace('_', '-', $key);
                        $variables[] = "{$cssKey}: {$value};";
                    }
                }
            }
        }

        if (empty($variables)) {
            return '';
        }

        return ':root {' . "\n  " . implode("\n  ", $variables) . "\n}";
    }

    /**
     * Clear theme cache
     */
    public function clearThemeCache(?Theme $theme = null): void
    {
        if ($theme) {
            $this->cache->clearTheme($theme);
        } else {
            $this->cache->clearAll();
        }
    }

    /**
     * Get theme directory path
     */
    public function getThemeDirectory(): string
    {
        return storage_path('app/themes');
    }

    /**
     * Ensure theme directory exists
     */
    public function ensureThemeDirectory(): bool
    {
        $dir = $this->getThemeDirectory();
        
        if (!is_dir($dir)) {
            return File::makeDirectory($dir, 0755, true);
        }

        return true;
    }

    /**
     * Scan themes directory and register themes
     */
    public function scanThemes(): array
    {
        $themesDir = $this->getThemeDirectory();
        $themes = [];

        if (!is_dir($themesDir)) {
            return $themes;
        }

        $directories = File::directories($themesDir);

        foreach ($directories as $dir) {
            $slug = basename($dir);
            $manifestPath = "{$dir}/theme.json";

            if (file_exists($manifestPath)) {
                try {
                    $manifest = json_decode(file_get_contents($manifestPath), true);
                    
                    if ($manifest) {
                        // Use relative path (just slug) instead of full path
                        $theme = Theme::updateOrCreate(
                            ['slug' => $slug],
                            [
                                'name' => $manifest['name'] ?? $slug,
                                'type' => $manifest['type'] ?? 'frontend',
                                'path' => $slug, // Store relative path (slug)
                                'version' => $manifest['version'] ?? '1.0.0',
                                'description' => $manifest['description'] ?? null,
                                'author' => $manifest['author'] ?? null,
                                'author_url' => $manifest['author_url'] ?? null,
                                'license' => $manifest['license'] ?? null,
                                'parent_theme' => $manifest['parent_theme'] ?? null,
                                'dependencies' => $manifest['dependencies'] ?? null,
                                'supports' => $manifest['supports'] ?? null,
                                'requires_cms_version' => $manifest['requires']['cms_version'] ?? null,
                                'status' => 'active', // Set as active by default
                            ]
                        );

                        $themes[] = $theme;
                    }
                } catch (\Exception $e) {
                    // Log error but continue scanning
                    \Log::error("Failed to load theme {$slug}: " . $e->getMessage());
                }
            }
        }

        return $themes;
    }
}

