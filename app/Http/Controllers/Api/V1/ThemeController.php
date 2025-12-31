<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Theme;
use App\Services\ThemeService;
use Illuminate\Http\Request;

class ThemeController extends BaseApiController
{
    protected ThemeService $themeService;

    public function __construct(ThemeService $themeService)
    {
        $this->themeService = $themeService;
    }

    public function index(Request $request)
    {
        $type = $request->get('type', 'frontend');
        $themes = Theme::ofType($type)->latest()->get();

        return $this->success($themes, 'Themes retrieved successfully');
    }

    // Store method removed (Themes are code-managed)

    public function show(Theme $theme)
    {
        // Load theme assets
        $assets = $this->themeService->loadThemeAssets($theme);
        $theme->assets = $assets;

        // Load manifest if available
        $manifest = $theme->getManifest();
        if ($manifest) {
            $theme->manifest = $manifest;
        } else {
            // Provide default settings schema if no manifest
            $theme->manifest = [
                'name' => $theme->name,
                'version' => $theme->version ?? '1.0.0',
                'description' => $theme->description ?? '',
                'author' => $theme->author ?? '',
                'settings_schema' => $this->themeService->getDefaultSettingsSchema(),
            ];
        }

        return $this->success($theme, 'Theme retrieved successfully');
    }

    public function update(Request $request, Theme $theme)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:themes,slug,'.$theme->id,
            'type' => 'sometimes|string|in:frontend,admin,email',
            'version' => 'nullable|string',
            'description' => 'nullable|string',
            'author' => 'nullable|string',
            'author_url' => 'nullable|url',
            'license' => 'nullable|string',
            'preview_image' => 'nullable|string',
            'settings' => 'nullable|array',
            'custom_css' => 'nullable|string',
            'parent_theme' => 'nullable|string|exists:themes,slug',
            'dependencies' => 'nullable|array',
            'supports' => 'nullable|array',
        ]);

        $theme->update($validated);
        
        // Clear cache after update
        $this->themeService->clearThemeCache($theme);

        return $this->success($theme, 'Theme updated successfully');
    }

    public function destroy(Theme $theme)
    {
        if ($theme->is_active) {
            return $this->validationError(
                ['theme' => ['Cannot delete active theme']],
                'Cannot delete active theme'
            );
        }

        $theme->delete();
        $this->themeService->clearThemeCache($theme);

        return $this->success(null, 'Theme deleted successfully');
    }

    public function activate(Theme $theme)
    {
        try {
            $this->themeService->activateTheme($theme);

            return $this->success([
                'theme' => $theme->fresh(),
            ], 'Theme activated successfully');
        } catch (\Exception $e) {
            \Log::error('Theme activation failed', [
                'theme_id' => $theme->id,
                'theme_slug' => $theme->slug,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return $this->error($e->getMessage(), 422);
        }
    }

    public function deactivate(Theme $theme)
    {
        try {
            $this->themeService->deactivateTheme($theme);

            return $this->success([
                'theme' => $theme->fresh(),
            ], 'Theme deactivated successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function getActive(Request $request)
    {
        try {
            $type = $request->get('type', 'frontend');
            $theme = $this->themeService->getActiveTheme($type);

            if (! $theme) {
                // Return null instead of 404 for public endpoint
                // Frontend can work without theme
                return $this->success(null, 'No active theme found');
            }

            // Load assets
            $assets = $this->themeService->loadThemeAssets($theme);
            $theme->assets = $assets;
            
            // Load manifest
            $theme->manifest = $theme->getManifest();

            return $this->success($theme, 'Active theme retrieved successfully');
        } catch (\Exception $e) {
            \Log::error('Failed to get active theme: ' . $e->getMessage());
            // Return null instead of error for public endpoint
            return $this->success(null, 'Theme service unavailable');
        }
    }

    public function updateSettings(Request $request, Theme $theme)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        try {
            // Merge with existing settings instead of replacing
            $existingSettings = $theme->settings ?? [];
            $newSettings = array_merge($existingSettings, $validated['settings']);
            
            $theme->update(['settings' => $newSettings]);
            $this->themeService->clearThemeCache($theme);

            return $this->success($theme->fresh(), 'Theme settings updated successfully');
        } catch (\Exception $e) {
            \Log::error('Failed to update theme settings: ' . $e->getMessage(), [
                'theme_id' => $theme->id,
                'error' => $e->getTraceAsString(),
            ]);
            
            return $this->error('Failed to update theme settings: ' . $e->getMessage(), 500);
        }
    }

    public function updateCustomCss(Request $request, Theme $theme)
    {
        $validated = $request->validate([
            'custom_css' => 'nullable|string',
        ]);

        $theme->update(['custom_css' => $validated['custom_css'] ?? '']);
        $this->themeService->clearThemeCache($theme);

        return $this->success($theme, 'Theme custom CSS updated successfully');
    }

    public function validate(Theme $theme)
    {
        $errors = $this->themeService->validateTheme($theme);

        if (empty($errors)) {
            return $this->success([
                'valid' => true,
                'theme' => $theme->fresh(),
            ], 'Theme is valid');
        }

        return $this->success([
            'valid' => false,
            'errors' => $errors,
            'theme' => $theme->fresh(),
        ], 'Theme validation completed');
    }

    // Legacy Blade methods removed

    public function scan()
    {
        try {
            $themes = $this->themeService->scanThemes();

            return $this->success([
                'themes' => $themes,
                'count' => count($themes),
            ], 'Themes scanned successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function getSetting(Theme $theme, Request $request)
    {
        $key = $request->get('key');
        $default = $request->get('default');

        if (!$key) {
            return $this->validationError(['key' => ['Key is required']], 'Key is required');
        }

        $value = $this->themeService->getThemeSetting($theme, $key, $default);

        return $this->success([
            'key' => $key,
            'value' => $value,
        ], 'Theme setting retrieved successfully');
    }

    // Import/Export methods removed

    // =====================================================
    // VUE SPA ENDPOINTS (New methods for Vue themes)
    // =====================================================

    /**
     * Get active theme menu locations
     */
    public function locations(Request $request)
    {
        $type = $request->get('type', 'frontend');
        $theme = $this->themeService->getActiveTheme($type);

        if (!$theme) {
            return $this->success([], 'No active theme found');
        }

        $locations = $this->themeService->getMenuLocations($theme);

        return $this->success($locations, 'Menu locations retrieved successfully');
    }


    /**
     * Get Vue components manifest
     */
    public function getComponents(Theme $theme)
    {
        try {
            $componentManifest = $theme->getComponentManifest();

            return $this->success([
                'components' => $componentManifest,
                'has_vue_components' => $theme->hasVueComponents(),
                'is_vue_based' => $theme->isVueBased(),
            ], 'Theme components retrieved successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Get theme configuration
     */
    public function getConfig(Theme $theme)
    {
        try {
            $config = $theme->getThemeConfig();

            return $this->success($config, 'Theme configuration retrieved successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Get theme composables
     */
    public function getComposables(Theme $theme)
    {
        try {
            $composablesPath = $theme->getComposablesPath();

            if (!$composablesPath) {
                return $this->success([
                    'has_composables' => false,
                    'message' => 'Theme does not have composables directory'
                ]);
            }

            $composableFiles = glob("{$composablesPath}/*.js");
            $composables = array_map('basename', $composableFiles);

            return $this->success([
                'has_composables' => true,
                'composables' => $composables,
            ], 'Theme composables retrieved successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}

