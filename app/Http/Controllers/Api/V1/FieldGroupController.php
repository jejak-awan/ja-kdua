<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\FieldGroup;
use Illuminate\Http\Request;

class FieldGroupController extends BaseApiController
{
    public function index(Request $request)
    {
        $query = FieldGroup::with('fields');

        if ($request->has('applies_to')) {
            $query->where('applies_to', $request->applies_to);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $groups = $query->orderBy('sort_order')->get();

        return $this->success($groups, 'Field groups retrieved successfully');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:field_groups,slug',
            'description' => 'nullable|string',
            'applies_to' => 'required|string',
            'conditions' => 'nullable|array',
            'sort_order' => 'integer',
        ]);

        $group = FieldGroup::create($validated);

        return $this->success($group->load('fields'), 'Field group created successfully', 201);
    }

    public function show(FieldGroup $fieldGroup)
    {
        return $this->success($fieldGroup->load('fields'), 'Field group retrieved successfully');
    }

    public function update(Request $request, FieldGroup $fieldGroup)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:field_groups,slug,' . $fieldGroup->id,
            'description' => 'nullable|string',
            'applies_to' => 'sometimes|required|string',
            'conditions' => 'nullable|array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $fieldGroup->update($validated);

        return $this->success($fieldGroup->load('fields'), 'Field group updated successfully');
    }

    public function destroy(FieldGroup $fieldGroup)
    {
        $fieldGroup->delete();

        return $this->success(null, 'Field group deleted successfully');
    }
}
