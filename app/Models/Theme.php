<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Theme extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'type',
        'path',
        'parent_theme',
        'version',
        'description',
        'author',
        'author_url',
        'license',
        'preview_image',
        'settings',
        'custom_css',
        'dependencies',
        'supports',
        'is_active',
        'status',
        'update_url',
        'auto_update',
        'requires_cms_version',
        'last_updated_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'dependencies' => 'array',
        'supports' => 'array',
        'is_active' => 'boolean',
        'auto_update' => 'boolean',
        'last_updated_at' => 'datetime',
    ];

    /**
     * Get active theme by type
     * Auto-activates default theme if no theme is active
     */
    public static function getActiveTheme(string $type = 'frontend'): ?self
    {
        return Cache::remember("theme.active.{$type}", 3600, function () use ($type) {
            $activeTheme = self::where('is_active', true)
                ->where('type', $type)
                ->where('status', 'active')
                ->first();
            
            // If no active theme, try to auto-activate default theme
            if (!$activeTheme) {
                $defaultTheme = self::where('type', $type)
                    ->where('slug', 'default')
                    ->first();
                
                // If no default theme, get first available theme
                if (!$defaultTheme) {
                    $defaultTheme = self::where('type', $type)
                        ->orderBy('id')
                        ->first();
                }
                
                // Auto-activate if found
                if ($defaultTheme) {
                    try {
                        $defaultTheme->update([
                            'is_active' => true,
                            'status' => 'active',
                        ]);
                        Cache::forget("theme.active.{$type}");
                        return $defaultTheme->fresh();
                    } catch (\Exception $e) {
                        \Log::warning('Failed to auto-activate default theme: ' . $e->getMessage());
                    }
                }
            }
            
            return $activeTheme;
        });
    }

    /**
     * Activate this theme
     */
    public function activate(): bool
    {
        // Validate theme before activation (warn but don't block)
        $errors = $this->validate();
        if (!empty($errors)) {
            // Log warnings but allow activation
            \Log::warning("Theme '{$this->name}' has validation warnings but will be activated", [
                'theme_id' => $this->id,
                'errors' => $errors,
            ]);
            
            // Only block if critical errors (invalid JSON)
            $criticalErrors = array_filter($errors, function($error) {
                return strpos($error, 'Invalid theme.json format') !== false;
            });
            
            if (!empty($criticalErrors)) {
                throw new \Exception("Theme '{$this->name}' is invalid and cannot be activated: " . implode(', ', $criticalErrors));
            }
        }

        // Deactivate all other themes of the same type
        self::where('id', '!=', $this->id)
            ->where('type', $this->type)
            ->update(['is_active' => false]);

        // Activate this theme
        $this->update([
            'is_active' => true,
            'status' => 'active',
        ]);

        // Clear cache
        Cache::forget("theme.active.{$this->type}");

        return true;
    }

    /**
     * Deactivate this theme
     */
    public function deactivate(): bool
    {
        $this->update(['is_active' => false]);
        Cache::forget("theme.active.{$this->type}");
        return true;
    }

    /**
     * Get theme path
     */
    public function getThemePath(): string
    {
        if ($this->path) {
            // If path is relative, make it absolute
            if (!str_starts_with($this->path, '/')) {
                return storage_path("app/themes/{$this->path}");
            }
            return $this->path;
        }

        return storage_path("app/themes/{$this->slug}");
    }

    /**
     * Get theme public path
     */
    public function getPublicPath(): string
    {
        return "themes/{$this->slug}";
    }

    /**
     * Check if theme has parent
     */
    public function hasParent(): bool
    {
        return !empty($this->parent_theme);
    }

    /**
     * Get parent theme
     */
    public function getParent(): ?self
    {
        if (!$this->hasParent()) {
            return null;
        }

        return self::where('slug', $this->parent_theme)->first();
    }

    /**
     * Check if theme supports a feature
     */
    public function supports(string $feature): bool
    {
        if (!$this->supports) {
            return false;
        }

        return in_array($feature, $this->supports) || 
               (isset($this->supports[$feature]) && $this->supports[$feature] === true);
    }

    /**
     * Get theme setting
     */
    public function getSetting(string $key, $default = null)
    {
        if (!$this->settings) {
            return $default;
        }

        return $this->settings[$key] ?? $default;
    }

    /**
     * Set theme setting
     */
    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->update(['settings' => $settings]);
    }

    /**
     * Validate theme structure
     */
    public function validate(): array
    {
        $errors = [];
        $themePath = $this->getThemePath();

        // Check if theme directory exists
        if (!is_dir($themePath)) {
            $errors[] = "Theme directory does not exist: {$themePath}";
            return $errors;
        }

        // Check for theme.json
        $manifestPath = "{$themePath}/theme.json";
        if (!file_exists($manifestPath)) {
            $errors[] = "Theme manifest (theme.json) not found";
        } else {
            // Validate manifest
            $manifest = json_decode(file_get_contents($manifestPath), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $errors[] = "Invalid theme.json format: " . json_last_error_msg();
            }
        }

        // Check required directories for Vue themes
        $requiredDirs = ['assets']; // templates no longer required for Vue SPA
        foreach ($requiredDirs as $dir) {
            if (!is_dir("{$themePath}/{$dir}")) {
                $errors[] = "Required directory missing: {$dir}";
            }
        }
        
        // Check for Vue components directory (optional but recommended)
        if (!is_dir("{$themePath}/components")) {
            // Not an error, just a warning
            \Log::info("Theme '{$this->name}' does not have a components directory");
        }

        // Update status based on validation
        if (empty($errors)) {
            $this->update(['status' => 'active']);
        } else {
            $this->update(['status' => 'broken']);
        }

        return $errors;
    }

    /**
     * Get theme manifest
     */
    public function getManifest(): ?array
    {
        $manifestPath = "{$this->getThemePath()}/theme.json";
        
        if (!file_exists($manifestPath)) {
            return null;
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        
        return $manifest ?: null;
    }

    /**
     * Check if theme has updates available
     */
    public function hasUpdate(): bool
    {
        if (!$this->update_url) {
            return false;
        }

        // TODO: Implement update check logic
        return false;
    }

    /**
     * Get CSS assets
     */
    public function getCssAssets(): array
    {
        $assets = [];
        $themePath = $this->getThemePath();
        $cssDir = "{$themePath}/assets/css";

        if (is_dir($cssDir)) {
            $files = glob("{$cssDir}/*.css");
            foreach ($files as $file) {
                $assets[] = basename($file);
            }
        }

        return $assets;
    }

    /**
     * Get JS assets
     */
    public function getJsAssets(): array
    {
        $assets = [];
        $themePath = $this->getThemePath();
        $jsDir = "{$themePath}/assets/js";

        if (is_dir($jsDir)) {
            $files = glob("{$jsDir}/*.js");
            foreach ($files as $file) {
                $assets[] = basename($file);
            }
        }

        return $assets;
    }

    /**
     * Scope: Get themes by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Get active themes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get themes by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // =====================================================
    // VUE SPA METHODS (Added for Vue-based themes)
    // =====================================================

    /**
     * Check if theme has Vue components
     */
    public function hasVueComponents(): bool
    {
        $themePath = $this->getThemePath();
        $componentsDir = "{$themePath}/components";
        
        return is_dir($componentsDir) && count(glob("{$componentsDir}/*")) > 0;
    }

    /**
     * Get Vue components directory path
     */
    public function getVueComponentsPath(): ?string
    {
        $themePath = $this->getThemePath();
        $componentsDir = "{$themePath}/components";
        
        return is_dir($componentsDir) ? $componentsDir : null;
    }

    /**
     * Get components manifest from theme.json
     */
    public function getComponentManifest(): array
    {
        $manifest = $this->getManifest();
        
        return $manifest['components'] ?? [];
    }

    /**
     * Get composables directory path
     */
    public function getComposablesPath(): ?string
    {
        $themePath = $this->getThemePath();
        $composablesDir = "{$themePath}/composables";
        
        return is_dir($composablesDir) ? $composablesDir : null;
    }

    /**
     * Get theme configuration
     */
    public function getThemeConfig(): array
    {
        $manifest = $this->getManifest();
        
        return [
            'settings_schema' => $manifest['settings_schema'] ?? [],
            'supports' => $manifest['supports'] ?? [],
            'menus' => $manifest['menus'] ?? [],
            'components' => $manifest['components'] ?? [],
        ];
    }

    /**
     * Check if theme is Vue-based
     */
    public function isVueBased(): bool
    {
        $manifest = $this->getManifest();
        
        return isset($manifest['framework']) && $manifest['framework'] === 'vue';
    }
}
