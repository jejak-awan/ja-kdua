<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Form;
use App\Models\FormField;
use Illuminate\Http\Request;

class FormController extends BaseApiController
{
    public function index(Request $request)
    {
        $query = Form::with('fields');

        // Multi-tenancy scoping
        if (!$request->user()->can('manage forms')) {
            $query->where('author_id', $request->user()->id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $forms = $query->latest()->get();

        return $this->success($forms, 'Forms retrieved successfully');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:forms,slug',
            'description' => 'nullable|string',
            'success_message' => 'nullable|string',
            'redirect_url' => 'nullable|url',
            'settings' => 'nullable|array',
        ]);

        $validated['author_id'] = $request->user()->id;

        $form = Form::create($validated);

        // Add fields if provided
        if ($request->has('fields')) {
            foreach ($request->fields as $fieldData) {
                $form->fields()->create($fieldData);
            }
        }

        return $this->success($form->load('fields'), 'Form created successfully', 201);
    }

    public function show(Request $request, Form $form)
    {
        if (!$request->user()->can('manage forms') && $form->author_id !== $request->user()->id) {
            return $this->forbidden('You do not have permission to view this form');
        }

        return $this->success($form->load('fields'), 'Form retrieved successfully');
    }

    public function update(Request $request, Form $form)
    {
        if (!$request->user()->can('manage forms') && $form->author_id !== $request->user()->id) {
            return $this->forbidden('You do not have permission to update this form');
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:forms,slug,'.$form->id,
            'description' => 'nullable|string',
            'success_message' => 'nullable|string',
            'redirect_url' => 'nullable|url',
            'settings' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $form->update($validated);

        // Sync fields
        if ($request->has('fields')) {
            $existingFieldIds = $form->fields()->pluck('id')->toArray();
            $requestFieldIds = [];

            foreach ($request->fields as $fieldData) {
                if (isset($fieldData['id']) && in_array($fieldData['id'], $existingFieldIds)) {
                    // Update existing
                    $requestFieldIds[] = $fieldData['id'];
                    $field = FormField::find($fieldData['id']);
                    $field->update($fieldData);
                } else {
                    // Create new and track ID
                    // Ensure form_id is set
                    $fieldData['form_id'] = $form->id;
                    $newField = $form->fields()->create($fieldData);
                    $requestFieldIds[] = $newField->id;
                }
            }

            // Delete fields not in request
            $toDelete = array_diff($existingFieldIds, $requestFieldIds);
            if (!empty($toDelete)) {
                FormField::whereIn('id', $toDelete)->delete();
            }
        }

        return $this->success($form->load('fields'), 'Form updated successfully');
    }

    public function destroy(Request $request, Form $form)
    {
        if (!$request->user()->can('manage forms') && $form->author_id !== $request->user()->id) {
            return $this->forbidden('You do not have permission to delete this form');
        }

        $form->delete();

        return $this->success(null, 'Form deleted successfully');
    }

    public function addField(Request $request, Form $form)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'label' => 'required|string|max:255',
            'type' => 'required|in:text,email,textarea,number,select,checkbox,radio,file,date,url,tel',
            'placeholder' => 'nullable|string',
            'help_text' => 'nullable|string',
            'options' => 'nullable|array',
            'validation_rules' => 'nullable|array',
            'is_required' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $field = $form->fields()->create($validated);

        return $this->success($field, 'Form field created successfully', 201);
    }

    public function updateField(Request $request, Form $form, FormField $formField)
    {
        if ($formField->form_id !== $form->id) {
            return $this->validationError(['field' => ['Field does not belong to this form']], 'Field does not belong to this form');
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'label' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:text,email,textarea,number,select,checkbox,radio,file,date,url,tel',
            'placeholder' => 'nullable|string',
            'help_text' => 'nullable|string',
            'options' => 'nullable|array',
            'validation_rules' => 'nullable|array',
            'is_required' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $formField->update($validated);

        return $this->success($formField, 'Form field updated successfully');
    }

    public function deleteField(Form $form, FormField $formField)
    {
        if ($formField->form_id !== $form->id) {
            return $this->validationError(['field' => ['Field does not belong to this form']], 'Field does not belong to this form');
        }

        $formField->delete();

        return $this->success(null, 'Field deleted successfully');
    }

    public function reorderFields(Request $request, Form $form)
    {
        $request->validate([
            'fields' => 'required|array',
            'fields.*.id' => 'required|exists:form_fields,id',
            'fields.*.sort_order' => 'required|integer',
        ]);

        foreach ($request->fields as $fieldData) {
            FormField::where('id', $fieldData['id'])
                ->where('form_id', $form->id)
                ->update(['sort_order' => $fieldData['sort_order']]);
        }

        return $this->success(null, 'Fields reordered successfully');
    }

    public function submit(Request $request, Form $form)
    {
        if (! $form->is_active) {
            return $this->validationError(['form' => ['Form is not active']], 'Form is not active');
        }

        // Build validation rules from form fields
        $rules = [];
        foreach ($form->fields as $field) {
            $rules[$field->name] = $field->getValidationRules();
        }

        $validated = $request->validate($rules);

        // Create submission
        $submission = $form->submissions()->create([
            'user_id' => $request->user()?->id,
            'data' => $validated,
            'ip_address' => \App\Helpers\IpHelper::getClientIp($request),
            'user_agent' => $request->userAgent(),
        ]);

        // Increment submission count
        $form->incrementSubmissionCount();

        // Send email notification if configured
        if (isset($form->settings['email_notifications']) && $form->settings['email_notifications']) {
            $this->sendFormNotification($form, $submission);
        }

        // Trigger webhook
        \App\Models\Webhook::triggerForEvent('form.submitted', [
            'form_id' => $form->id,
            'form_name' => $form->name,
            'submission_id' => $submission->id,
            'data' => $validated,
        ]);

        return $this->success([
            'submission_id' => $submission->id,
            'redirect_url' => $form->redirect_url,
        ], $form->success_message ?? 'Form submitted successfully', 201);
    }

    protected function sendFormNotification(Form $form, $submission)
    {
        // Email notification logic
        // This can be enhanced with actual email sending
        \Log::info('Form submission notification', [
            'form' => $form->name,
            'submission_id' => $submission->id,
        ]);
    }
    
    public function duplicate(Request $request, Form $form)
    {
        if (!$request->user()->can('manage forms') && $form->author_id !== $request->user()->id) {
            return $this->forbidden('You do not have permission to duplicate this form');
        }

        $newForm = $form->replicate(['slug', 'name']);
        $newForm->name = $form->name . ' (Copy)';
        $newForm->slug = $form->slug . '-copy-' . time();
        $newForm->is_active = false;
        $newForm->author_id = $request->user()->id; // Assign to current user
        $newForm->save();

        // Duplicate fields
        foreach ($form->fields as $field) {
            $newField = $field->replicate();
            $newField->form_id = $newForm->id;
            $newField->save();
        }

        return $this->success($newForm->load('fields'), 'Form duplicated successfully', 201);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:forms,id',
            'action' => 'required|in:delete',
        ]);

        $ids = $request->ids;
        $action = $request->action;

        try {
            if ($action === 'delete') {
                $query = Form::whereIn('id', $ids);
                
                if (!$request->user()->can('manage forms')) {
                    $query->where('author_id', $request->user()->id);
                }

                $query->delete();
                return $this->success(null, 'Selected forms deleted successfully');
            }
        } catch (\Exception $e) {
            return $this->error('Bulk action failed: ' . $e->getMessage(), 500);
        }

        return $this->error('Invalid action', 422);
    }
}
