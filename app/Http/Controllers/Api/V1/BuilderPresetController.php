<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BuilderPreset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BuilderPresetController extends Controller
{
    /**
     * Get presets for a module type.
     * Returns both system presets and user's own presets.
     */
    public function index(Request $request): JsonResponse
    {
        $type = $request->query('type');

        $query = BuilderPreset::query();

        if ($type) {
            $query->ofType($type);
        }

        // Get system presets + user's presets
        $presets = $query->where(function ($q) use ($request) {
            $q->where('is_system', true)
                ->orWhere('user_id', $request->user()->id);
        })
            ->orderBy('is_system', 'desc') // System first
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $presets,
            'success' => true,
        ]);
    }

    /**
     * Store a new preset.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'settings' => 'required|array',
        ]);

        $preset = BuilderPreset::create([
            'user_id' => $request->user()->id,
            'type' => $validated['type'],
            'name' => $validated['name'],
            'settings' => $validated['settings'],
            'is_system' => false,
        ]);

        return response()->json([
            'data' => $preset,
            'success' => true,
            'message' => 'Preset saved successfully',
        ], 201);
    }

    /**
     * Update an existing preset.
     */
    public function update(Request $request, BuilderPreset $builderPreset): JsonResponse
    {
        // Only allow updating own presets (not system presets unless admin)
        if ($builderPreset->is_system && ! $request->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot modify system presets',
            ], 403);
        }

        if (! $builderPreset->is_system && $builderPreset->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'settings' => 'sometimes|array',
        ]);

        $builderPreset->update($validated);

        return response()->json([
            'data' => $builderPreset,
            'success' => true,
            'message' => 'Preset updated successfully',
        ]);
    }

    /**
     * Delete a preset.
     */
    public function destroy(Request $request, BuilderPreset $builderPreset): JsonResponse
    {
        // Only allow deleting own presets
        if ($builderPreset->is_system && ! $request->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete system presets',
            ], 403);
        }

        if (! $builderPreset->is_system && $builderPreset->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $builderPreset->delete();

        return response()->json([
            'success' => true,
            'message' => 'Preset deleted successfully',
        ]);
    }
}
