<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\CustomField;
use Illuminate\Http\Request;

class CustomFieldController extends BaseApiController
{
    public function index(Request $request)
    {
        $query = CustomField::with('fieldGroup');

        if ($request->has('field_group_id')) {
            $query->where('field_group_id', $request->input('field_group_id'));
        }

        if ($request->has('type')) {
            $query->where('type', $request->input('type'));
        }

        $fields = $query->orderBy('sort_order')->get();

        return $this->success($fields, 'Custom fields retrieved successfully');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'field_group_id' => 'nullable|exists:field_groups,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string',
            'type' => 'required|in:text,textarea,number,date,datetime,select,multiselect,checkbox,radio,file,image,url,email,color',
            'label' => 'nullable|string',
            'description' => 'nullable|string',
            'default_value' => 'nullable|string',
            'options' => 'nullable|array',
            'validation_rules' => 'nullable|array',
            'is_required' => 'boolean',
            'is_searchable' => 'boolean',
            'sort_order' => 'integer',
        ]);

        // Ensure unique slug within field group
        if ($validated['field_group_id']) {
            $exists = CustomField::where('field_group_id', $validated['field_group_id'])
                ->where('slug', $validated['slug'])
                ->exists();

            if ($exists) {
                return $this->validationError(['slug' => ['Slug already exists in this field group']], 'Slug already exists in this field group');
            }
        }

        $field = CustomField::create($validated);

        return $this->success($field->load('fieldGroup'), 'Custom field created successfully', 201);
    }

    public function show(CustomField $customField)
    {
        return $this->success($customField->load('fieldGroup'), 'Custom field retrieved successfully');
    }

    public function update(Request $request, CustomField $customField)
    {
        $validated = $request->validate([
            'field_group_id' => 'nullable|exists:field_groups,id',
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string',
            'type' => 'sometimes|required|in:text,textarea,number,date,datetime,select,multiselect,checkbox,radio,file,image,url,email,color',
            'label' => 'nullable|string',
            'description' => 'nullable|string',
            'default_value' => 'nullable|string',
            'options' => 'nullable|array',
            'validation_rules' => 'nullable|array',
            'is_required' => 'boolean',
            'is_searchable' => 'boolean',
            'sort_order' => 'integer',
        ]);

        // Check unique slug if changed
        if (isset($validated['slug']) && $validated['slug'] !== $customField->slug) {
            $fieldGroupId = $validated['field_group_id'] ?? $customField->field_group_id;
            $exists = CustomField::where('field_group_id', $fieldGroupId)
                ->where('slug', $validated['slug'])
                ->where('id', '!=', $customField->id)
                ->exists();

            if ($exists) {
                return $this->validationError(['slug' => ['Slug already exists in this field group']], 'Slug already exists in this field group');
            }
        }

        $customField->update($validated);

        return $this->success($customField->load('fieldGroup'), 'Custom field updated successfully');
    }

    public function destroy(CustomField $customField)
    {
        $customField->delete();

        return $this->success(null, 'Custom field deleted successfully');
    }

    public function getFieldTypes()
    {
        $types = [
            'text' => 'Text Input',
            'textarea' => 'Textarea',
            'number' => 'Number',
            'date' => 'Date',
            'datetime' => 'Date & Time',
            'select' => 'Select Dropdown',
            'multiselect' => 'Multi Select',
            'checkbox' => 'Checkbox',
            'radio' => 'Radio Button',
            'file' => 'File Upload',
            'image' => 'Image Upload',
            'url' => 'URL',
            'email' => 'Email',
            'color' => 'Color Picker',
        ];

        return $this->success($types, 'Field types retrieved successfully');
    }
}
