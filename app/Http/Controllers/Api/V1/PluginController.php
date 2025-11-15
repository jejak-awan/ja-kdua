<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Plugin;
use Illuminate\Http\Request;

class PluginController extends Controller
{
    public function index()
    {
        $plugins = Plugin::latest()->get();
        return response()->json($plugins);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:plugins,slug',
            'version' => 'nullable|string',
            'description' => 'nullable|string',
            'author' => 'nullable|string',
            'author_url' => 'nullable|url',
            'plugin_url' => 'nullable|url',
            'main_file' => 'nullable|string',
            'settings' => 'nullable|array',
            'priority' => 'integer|min:1|max:100',
        ]);

        $plugin = Plugin::create($validated);

        return response()->json($plugin, 201);
    }

    public function show(Plugin $plugin)
    {
        return response()->json($plugin);
    }

    public function update(Request $request, Plugin $plugin)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:plugins,slug,' . $plugin->id,
            'version' => 'nullable|string',
            'description' => 'nullable|string',
            'author' => 'nullable|string',
            'author_url' => 'nullable|url',
            'plugin_url' => 'nullable|url',
            'main_file' => 'nullable|string',
            'settings' => 'nullable|array',
            'priority' => 'integer|min:1|max:100',
        ]);

        $plugin->update($validated);

        return response()->json($plugin);
    }

    public function destroy(Plugin $plugin)
    {
        if ($plugin->is_active) {
            return response()->json(['message' => 'Cannot delete active plugin. Deactivate it first.'], 422);
        }

        $plugin->delete();

        return response()->json(['message' => 'Plugin deleted successfully']);
    }

    public function activate(Plugin $plugin)
    {
        $plugin->activate();

        return response()->json([
            'message' => 'Plugin activated successfully',
            'plugin' => $plugin->fresh(),
        ]);
    }

    public function deactivate(Plugin $plugin)
    {
        $plugin->deactivate();

        return response()->json([
            'message' => 'Plugin deactivated successfully',
            'plugin' => $plugin->fresh(),
        ]);
    }

    public function updateSettings(Request $request, Plugin $plugin)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        $plugin->update(['settings' => $validated['settings']]);

        return response()->json($plugin);
    }

    public function getActive()
    {
        $plugins = Plugin::getActivePlugins();
        return response()->json($plugins);
    }
}
