<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Form;
use Illuminate\Http\Request;

class FormController extends BaseApiController
{
    public function index(Request $request)
    {
        $query = Form::query();

        // Multi-tenancy scoping
        if (! $request->user()->can('manage forms')) {
            $query->where('author_id', $request->user()->id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Soft deletes filter
        if ($request->has('trashed')) {
            $trashed = $request->trashed;
            if ($trashed === 'only') {
                $query->onlyTrashed();
            } elseif ($trashed === 'with') {
                $query->withTrashed();
            }
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
            'blocks' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $validated['author_id'] = $request->user()->id;

        $form = Form::create($validated);

        return $this->success($form, 'Form created successfully', 201);
    }

    public function show(Request $request, Form $form)
    {
        if (! $request->user()->can('manage forms') && $form->author_id !== $request->user()->id) {
            return $this->forbidden('You do not have permission to view this form');
        }

        return $this->success($form, 'Form retrieved successfully');
    }

    public function update(Request $request, Form $form)
    {
        if (! $request->user()->can('manage forms') && $form->author_id !== $request->user()->id) {
            return $this->forbidden('You do not have permission to update this form');
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:forms,slug,'.$form->id,
            'description' => 'nullable|string',
            'success_message' => 'nullable|string',
            'redirect_url' => 'nullable|url',
            'settings' => 'nullable|array',
            'blocks' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $form->update($validated);

        return $this->success($form, 'Form updated successfully');
    }

    public function destroy(Request $request, Form $form)
    {
        if (! $request->user()->can('manage forms') && $form->author_id !== $request->user()->id) {
            return $this->forbidden('You do not have permission to delete this form');
        }

        $form->delete();

        return $this->success(null, 'Form deleted successfully');
    }

    public function restore($id, Request $request)
    {
        $form = Form::withTrashed()->findOrFail($id);

        if (! $request->user()->can('manage forms') && $form->author_id !== $request->user()->id) {
            return $this->forbidden('You do not have permission to restore this form');
        }

        if (! $form->trashed()) {
            return $this->error('Form is not deleted', 400);
        }

        $form->restore();

        return $this->success(null, 'Form restored successfully');
    }

    public function forceDelete($id, Request $request)
    {
        $form = Form::withTrashed()->findOrFail($id);

        if (! $request->user()->can('manage forms') && $form->author_id !== $request->user()->id) {
            return $this->forbidden('You do not have permission to delete this form');
        }

        // Delete fields
        $form->fields()->delete();
        // Delete submissions
        $form->submissions()->delete();

        $form->forceDelete();

        return $this->success(null, 'Form permanently deleted');
    }


    public function submit(Request $request, Form $form)
    {
        if (! $form->is_active) {
            return $this->validationError(['form' => ['Form is not active']], 'Form is not active');
        }

        // Build validation rules from blocks
        $rules = [];
        if ($form->blocks && count($form->blocks) > 0) {
            $rules = $this->extractRulesFromBlocks($form->blocks);
        }

        // Check for captcha if enabled for contact forms
        if (\App\Services\CaptchaService::isEnabled('contact')) {
            $request->validate([
                'captcha_token' => 'required|string',
                'captcha_answer' => 'required|string',
            ]);

            $captchaService = new \App\Services\CaptchaService();
            if (!$captchaService->verify($request->captcha_token, $request->captcha_answer)) {
                return $this->error('Invalid captcha', 422);
            }
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

    protected function extractRulesFromBlocks($blocks)
    {
        $rules = [];
        $formBlocks = ['form_input', 'form_textarea', 'form_select', 'form_checkbox', 'form_radio'];

        foreach ($blocks as $block) {
            $type = $block['type'] ?? '';
            $settings = $block['settings'] ?? [];

            if (in_array($type, $formBlocks)) {
                $fieldId = $settings['field_id'] ?? null;
                if ($fieldId) {
                    $fieldRules = [];
                    
                    // Basic required check
                    if (isset($settings['is_required']) && $settings['is_required']) {
                        $fieldRules[] = 'required';
                    } else {
                        $fieldRules[] = 'nullable';
                    }

                    // Type specific rules
                    if ($type === 'form_input') {
                        $inputType = $settings['type'] ?? 'text';
                        if ($inputType === 'email') $fieldRules[] = 'email';
                        if ($inputType === 'number') $fieldRules[] = 'numeric';
                        $fieldRules[] = 'string';
                        $fieldRules[] = 'max:255';
                    } elseif ($type === 'form_textarea') {
                        $fieldRules[] = 'string';
                    }

                    $rules[$fieldId] = $fieldRules;
                }
            }

            // Recurse into children
            if (isset($block['children']) && is_array($block['children'])) {
                $rules = array_merge($rules, $this->extractRulesFromBlocks($block['children']));
            }
        }

        return $rules;
    }

    public function duplicate(Request $request, Form $form)
    {
        if (! $request->user()->can('manage forms') && $form->author_id !== $request->user()->id) {
            return $this->forbidden('You do not have permission to duplicate this form');
        }

        $newForm = $form->replicate(['slug', 'name']);
        $newForm->name = $form->name.' (Copy)';
        $newForm->slug = $form->slug.'-copy-'.time();
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
            'ids' => 'required|array',
            'ids.*' => 'exists:forms,id',
            'action' => 'required|in:delete,restore,force_delete',
        ]);

        $ids = $request->ids;
        $action = $request->action;

        try {
            if ($action === 'delete') {
                $query = Form::whereIn('id', $ids);

                if (! $request->user()->can('manage forms')) {
                    $query->where('author_id', $request->user()->id);
                }

                $query->delete();

                return $this->success(null, 'Selected forms deleted successfully');
            } elseif ($action === 'restore') {
                $query = Form::withTrashed()->whereIn('id', $ids);
                if (! $request->user()->can('manage forms')) {
                    $query->where('author_id', $request->user()->id);
                }
                $query->restore();

                return $this->success(null, 'Selected forms restored successfully');
            } elseif ($action === 'force_delete') {
                $query = Form::withTrashed()->whereIn('id', $ids);
                if (! $request->user()->can('manage forms')) {
                    $query->where('author_id', $request->user()->id);
                }

                $forms = $query->get();
                foreach ($forms as $form) {
                    $form->fields()->delete();
                    $form->submissions()->delete();
                    $form->forceDelete();
                }

                return $this->success(null, 'Selected forms permanently deleted');
            }
        } catch (\Exception $e) {
            return $this->error('Bulk action failed: '.$e->getMessage(), 500);
        }

        return $this->error('Invalid action', 422);
    }
}
