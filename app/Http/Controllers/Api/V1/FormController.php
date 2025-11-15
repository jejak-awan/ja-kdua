<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormField;
use Illuminate\Http\Request;

class FormController extends Controller
{
    public function index(Request $request)
    {
        $query = Form::with('fields');

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $forms = $query->latest()->get();

        return response()->json($forms);
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

        $form = Form::create($validated);

        // Add fields if provided
        if ($request->has('fields')) {
            foreach ($request->fields as $fieldData) {
                $form->fields()->create($fieldData);
            }
        }

        return response()->json($form->load('fields'), 201);
    }

    public function show(Form $form)
    {
        return response()->json($form->load('fields'));
    }

    public function update(Request $request, Form $form)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:forms,slug,' . $form->id,
            'description' => 'nullable|string',
            'success_message' => 'nullable|string',
            'redirect_url' => 'nullable|url',
            'settings' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $form->update($validated);

        return response()->json($form->load('fields'));
    }

    public function destroy(Form $form)
    {
        $form->delete();

        return response()->json(['message' => 'Form deleted successfully']);
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

        return response()->json($field, 201);
    }

    public function updateField(Request $request, Form $form, FormField $formField)
    {
        if ($formField->form_id !== $form->id) {
            return response()->json(['message' => 'Field does not belong to this form'], 422);
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

        return response()->json($formField);
    }

    public function deleteField(Form $form, FormField $formField)
    {
        if ($formField->form_id !== $form->id) {
            return response()->json(['message' => 'Field does not belong to this form'], 422);
        }

        $formField->delete();

        return response()->json(['message' => 'Field deleted successfully']);
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

        return response()->json(['message' => 'Fields reordered successfully']);
    }

    public function submit(Request $request, Form $form)
    {
        if (!$form->is_active) {
            return response()->json(['message' => 'Form is not active'], 422);
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
            'ip_address' => $request->ip(),
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

        return response()->json([
            'message' => $form->success_message ?? 'Form submitted successfully',
            'submission_id' => $submission->id,
            'redirect_url' => $form->redirect_url,
        ], 201);
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
}
