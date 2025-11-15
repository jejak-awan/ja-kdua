<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        $query = Setting::query();

        if ($request->has('group')) {
            $query->where('group', $request->group);
        }

        if ($request->has('public_only')) {
            $query->where('is_public', true);
        }

        $settings = $query->orderBy('group')->orderBy('key')->get();

        return response()->json([
            'data' => $settings,
            'success' => true,
        ]);
    }

    public function getGroup($group)
    {
        $settings = Setting::getGroup($group);

        return response()->json($settings);
    }

    public function show($key)
    {
        $setting = Setting::where('key', $key)->firstOrFail();

        return response()->json($setting);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|unique:settings,key',
            'value' => 'nullable',
            'type' => 'required|in:string,integer,boolean,json,text',
            'group' => 'required|string',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        $setting = Setting::create($validated);

        return response()->json($setting, 201);
    }

    public function update(Request $request, Setting $setting)
    {
        $validated = $request->validate([
            'value' => 'nullable',
            'type' => 'sometimes|in:string,integer,boolean,json,text',
            'group' => 'sometimes|string',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        $setting->update($validated);

        return response()->json($setting);
    }

    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable',
            'settings.*.type' => 'sometimes|in:string,integer,boolean,json,text',
            'settings.*.group' => 'sometimes|string',
        ]);

        foreach ($validated['settings'] as $settingData) {
            Setting::set(
                $settingData['key'],
                $settingData['value'] ?? null,
                $settingData['type'] ?? 'string',
                $settingData['group'] ?? 'general'
            );
        }

        return response()->json([
            'message' => 'Settings updated successfully',
            'success' => true,
        ]);
    }

    public function destroy(Setting $setting)
    {
        $setting->delete();

        return response()->json(['message' => 'Setting deleted successfully']);
    }
}
