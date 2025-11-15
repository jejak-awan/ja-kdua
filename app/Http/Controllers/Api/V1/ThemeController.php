<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    public function index()
    {
        $themes = Theme::latest()->get();
        return response()->json($themes);
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

        return response()->json($theme, 201);
    }

    public function show(Theme $theme)
    {
        return response()->json($theme);
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

        return response()->json($theme);
    }

    public function destroy(Theme $theme)
    {
        if ($theme->is_active) {
            return response()->json(['message' => 'Cannot delete active theme'], 422);
        }

        $theme->delete();

        return response()->json(['message' => 'Theme deleted successfully']);
    }

    public function activate(Theme $theme)
    {
        $theme->activate();

        return response()->json([
            'message' => 'Theme activated successfully',
            'theme' => $theme->fresh(),
        ]);
    }

    public function getActive()
    {
        $theme = Theme::getActiveTheme();
        
        if (!$theme) {
            return response()->json(['message' => 'No active theme'], 404);
        }

        return response()->json($theme);
    }

    public function updateSettings(Request $request, Theme $theme)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        $theme->update(['settings' => $validated['settings']]);

        return response()->json($theme);
    }

    public function updateCustomCss(Request $request, Theme $theme)
    {
        $validated = $request->validate([
            'custom_css' => 'nullable|string',
        ]);

        $theme->update(['custom_css' => $validated['custom_css'] ?? '']);

        return response()->json($theme);
    }
}
