<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ThemeTemplate;
use Illuminate\Http\Request;

class ThemeTemplateController extends Controller
{
    public function index()
    {
        return ThemeTemplate::orderBy('priority', 'desc')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'conditions' => 'nullable|array',
            'priority' => 'integer',
            'is_active' => 'boolean',
            'header_data' => 'nullable|array',
            'footer_data' => 'nullable|array',
            'body_data' => 'nullable|array',
        ]);

        $template = ThemeTemplate::create($validated);

        return response()->json($template, 201);
    }

    public function show(ThemeTemplate $themeTemplate)
    {
        return $themeTemplate;
    }

    public function update(Request $request, ThemeTemplate $themeTemplate)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'conditions' => 'nullable|array',
            'priority' => 'integer',
            'is_active' => 'boolean',
            'header_data' => 'nullable|array',
            'footer_data' => 'nullable|array',
            'body_data' => 'nullable|array',
        ]);

        $themeTemplate->update($validated);

        return $themeTemplate;
    }

    public function destroy(ThemeTemplate $themeTemplate)
    {
        $themeTemplate->delete();
        return response()->noContent();
    }
}
