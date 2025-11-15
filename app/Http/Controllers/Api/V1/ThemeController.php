<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\Theme;
use Illuminate\Http\Request;

class ThemeController extends BaseApiController
{
    public function index()
    {
        $themes = Theme::latest()->get();
        return $this->success($themes, 'Themes retrieved successfully');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:themes,slug',
            'version' => 'nullable|string',
            'description' => 'nullable|string',
            'author' => 'nullable|string',
            'author_url' => 'nullable|url',
            'preview_image' => 'nullable|string',
            'settings' => 'nullable|array',
            'custom_css' => 'nullable|string',
        ]);

        $theme = Theme::create($validated);

        return $this->success($theme, 'Theme created successfully', 201);
    }

    public function show(Theme $theme)
    {
        return $this->success($theme, 'Theme retrieved successfully');
    }

    public function update(Request $request, Theme $theme)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:themes,slug,' . $theme->id,
            'version' => 'nullable|string',
            'description' => 'nullable|string',
            'author' => 'nullable|string',
            'author_url' => 'nullable|url',
            'preview_image' => 'nullable|string',
            'settings' => 'nullable|array',
            'custom_css' => 'nullable|string',
        ]);

        $theme->update($validated);

        return $this->success($theme, 'Theme updated successfully');
    }

    public function destroy(Theme $theme)
    {
        if ($theme->is_active) {
            return $this->validationError(['theme' => ['Cannot delete active theme']], 'Cannot delete active theme');
        }

        $theme->delete();

        return $this->success(null, 'Theme deleted successfully');
    }

    public function activate(Theme $theme)
    {
        $theme->activate();

        return $this->success([
            'theme' => $theme->fresh(),
        ], 'Theme activated successfully');
    }

    public function getActive()
    {
        $theme = Theme::getActiveTheme();
        
        if (!$theme) {
            return $this->notFound('Active theme');
        }

        return $this->success($theme, 'Active theme retrieved successfully');
    }

    public function updateSettings(Request $request, Theme $theme)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        $theme->update(['settings' => $validated['settings']]);

        return $this->success($theme, 'Theme settings updated successfully');
    }

    public function updateCustomCss(Request $request, Theme $theme)
    {
        $validated = $request->validate([
            'custom_css' => 'nullable|string',
        ]);

        $theme->update(['custom_css' => $validated['custom_css'] ?? '']);

        return $this->success($theme, 'Theme custom CSS updated successfully');
    }
}
