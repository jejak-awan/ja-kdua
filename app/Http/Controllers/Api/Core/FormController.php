<?php

namespace App\Http\Controllers\Api\Core;

use App\Models\Core\Form;
use Illuminate\Http\Request;

class FormController extends BaseApiController
{
    /**
     * List forms.
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \App\Models\Core\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        $query = Form::query();

        // Multi-tenancy scoping
        if (! $user->can('manage forms')) {
            $userId = (int) $user->id;
            $query->where('author_id', $userId);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Soft deletes filter
        if ($request->has('trashed')) {
            $trashed = $request->input('trashed');
            if ($trashed === 'only') {
                $query->onlyTrashed();
            } elseif ($trashed === 'with') {
                $query->withTrashed();
            }
        }

        $forms = $query->withCount(['submissions as submission_count'])->latest()->get();

        return $this->success($forms, 'Forms retrieved successfully');
    }

    /**
     * Create new form.
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \App\Models\Core\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

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

        $validated['author_id'] = (int) $user->id;

        /** @var Form $form */
        $form = Form::create($validated);

        return $this->success($form, 'Form created successfully', 201);
    }

    /**
     * Display the specified form.
     */
    public function show(Request $request, Form $form): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \App\Models\Core\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if (! $user->can('manage forms') && $form->author_id !== $user->id) {
            return $this->forbidden('You do not have permission to view this form');
        }

        return $this->success($form, 'Form retrieved successfully');
    }

    /**
     * Update the specified form.
     */
    public function update(Request $request, Form $form): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \App\Models\Core\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if (! $user->can('manage forms') && $form->author_id !== $user->id) {
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

    /**
     * Remove the specified form.
     */
    public function destroy(Request $request, Form $form): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \App\Models\Core\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if (! $user->can('manage forms') && $form->author_id !== $user->id) {
            return $this->forbidden('You do not have permission to delete this form');
        }

        $form->delete();

        return $this->success(null, 'Form deleted successfully');
    }

    /**
     * Restore trashed form.
     *
     * @param  int|string  $id
     */
    public function restore(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \App\Models\Core\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        $form = Form::withTrashed()->findOrFail($id);
        /** @var Form $form */
        if (! $user->can('manage forms') && $form->author_id !== $user->id) {
            return $this->forbidden('You do not have permission to restore this form');
        }

        if (! $form->trashed()) {
            return $this->error('Form is not deleted', 400);
        }

        $form->restore();

        return $this->success(null, 'Form restored successfully');
    }

    /**
     * Permanently delete form.
     *
     * @param  int|string  $id
     */
    public function forceDelete(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \App\Models\Core\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        $form = Form::withTrashed()->findOrFail($id);
        /** @var Form $form */
        if (! $user->can('manage forms') && $form->author_id !== $user->id) {
            return $this->forbidden('You do not have permission to delete this form');
        }

        // Delete fields
        $form->fields()->delete();
        // Delete submissions
        $form->submissions()->delete();

        $form->forceDelete();

        return $this->success(null, 'Form permanently deleted');
    }

    /**
     * Track form event.
     */
    public function track(Request $request, Form $form): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'event' => 'required|in:view,start',
        ]);

        $eventRaw = $request->input('event');
        $event = is_string($eventRaw) ? $eventRaw : '';

        if ($event === 'view') {
            $form->incrementViewCount();
        } elseif ($event === 'start') {
            $form->incrementStartCount();
        }

        return $this->success(null, 'Event tracked successfully');
    }

    /**
     * Handle form submission.
     */
    public function submit(Request $request, Form $form): \Illuminate\Http\JsonResponse
    {
        if (! $form->is_active) {
            return $this->validationError(['form' => ['Form is not active']], 'Form is not active');
        }

        // Build validation rules from blocks
        $rules = [];
        $blocks = (array) $form->blocks;
        if (! empty($blocks)) {
            $rules = $this->extractRulesFromBlocks($blocks);
        }

        // Check for captcha if enabled for contact forms
        if (\App\Services\Core\CaptchaService::isEnabled('contact')) {
            $request->validate([
                'captcha_token' => 'required|string',
                'captcha_answer' => 'required|string',
            ]);

            $captchaService = new \App\Services\Core\CaptchaService;
            $captchaTokenRaw = $request->input('captcha_token');
            $captchaAnswerRaw = $request->input('captcha_answer');
            $captchaToken = is_string($captchaTokenRaw) ? $captchaTokenRaw : '';
            $captchaAnswer = is_string($captchaAnswerRaw) ? $captchaAnswerRaw : '';

            if (! $captchaService->verify($captchaToken, $captchaAnswer)) {
                return $this->error('Invalid captcha', 422);
            }
        }

        $validated = $request->validate($rules);

        $user = $request->user();
        /** @var \App\Models\Core\User|null $user */

        // Create submission
        $submissionData = [
            'user_id' => $user?->id,
            'data' => $validated,
            'ip_address' => \App\Helpers\IpHelper::getClientIp($request),
            'user_agent' => is_string($request->userAgent()) ? $request->userAgent() : '',
        ];

        /** @var \App\Models\Core\FormSubmission $submission */
        $submission = $form->submissions()->create($submissionData);

        // Increment submission count
        $form->incrementSubmissionCount();

        // Send email notification if configured
        $settings = is_array($form->settings) ? $form->settings : [];
        if (isset($settings['email_notifications']) && $settings['email_notifications']) {
            $this->sendFormNotification($form, $submission);
        }

        // Trigger webhook
        \App\Models\Core\Webhook::triggerForEvent('form.submitted', [
            'form_id' => $form->id,
            'form_name' => (string) $form->name,
            'submission_id' => $submission->id,
            'data' => $validated,
        ]);

        return $this->success([
            'submission_id' => $submission->id,
            'redirect_url' => (string) $form->redirect_url,
        ], is_string($form->success_message) ? $form->success_message : 'Form submitted successfully', 201);
    }

    /**
     * Send form submission notification.
     */
    protected function sendFormNotification(Form $form, \App\Models\Core\FormSubmission $submission): void
    {
        // Email notification logic
        // This can be enhanced with actual email sending
        \Log::info('Form submission notification', [
            'form' => (string) $form->name,
            'submission_id' => $submission->id,
        ]);
    }

    /**
     * Extract validation rules from form blocks.
     *
     * @param  array<mixed, mixed>  $blocks
     * @return array<string, array<int, string>>
     */
    protected function extractRulesFromBlocks(array $blocks): array
    {
        $rules = [];
        $formBlocks = ['form_input', 'form_textarea', 'form_select', 'form_checkbox', 'form_radio'];

        foreach ($blocks as $block) {
            if (! is_array($block)) {
                continue;
            }
            $type = is_string($block['type'] ?? null) ? $block['type'] : '';
            $settingsRaw = $block['settings'] ?? null;
            $settings = is_array($settingsRaw) ? $settingsRaw : [];

            if (in_array($type, $formBlocks)) {
                $fieldId = is_string($settings['field_id'] ?? null) ? $settings['field_id'] : null;
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
                        $inputType = is_string($settings['type'] ?? null) ? $settings['type'] : 'text';
                        if ($inputType === 'email') {
                            $fieldRules[] = 'email';
                        }
                        if ($inputType === 'number') {
                            $fieldRules[] = 'numeric';
                        }
                        $fieldRules[] = 'string';
                        $fieldRules[] = 'max:255';
                    } elseif ($type === 'form_textarea') {
                        $fieldRules[] = 'string';
                    }

                    $rules[$fieldId] = $fieldRules;
                }
            }

            // Recurse into children
            $children = $block['children'] ?? null;
            if (is_array($children)) {
                $rules = array_merge($rules, $this->extractRulesFromBlocks($children));
            }
        }

        return $rules;
    }

    /**
     * Duplicate the specified form.
     */
    public function duplicate(Request $request, Form $form): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \App\Models\Core\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if (! $user->can('manage forms') && $form->author_id !== $user->id) {
            return $this->forbidden('You do not have permission to duplicate this form');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:forms,slug',
            'copy_submissions' => 'boolean',
        ]);

        $titleRaw = $request->input('title');
        $title = is_string($titleRaw) ? $titleRaw : (string) $form->name.' (Copy)';
        $slugRaw = $request->input('slug');
        $slug = is_string($slugRaw) ? $slugRaw : (string) $form->slug.'-copy';
        $copySubmissions = $request->boolean('copy_submissions');

        $except = ['slug', 'name', 'submission_count', 'view_count', 'start_count'];
        /** @var Form $replicated */
        $replicated = $form->replicate($except);
        $replicated->name = $title;
        $replicated->slug = $slug;
        $replicated->is_active = false;
        $replicated->author_id = (int) $user->id;
        $replicated->submission_count = 0;
        $replicated->view_count = 0;
        $replicated->start_count = 0;
        $replicated->save();

        if ($copySubmissions) {
            // Bulk insert for performance
            $submissionsData = $form->submissions()->get()->map(function (\App\Models\Core\FormSubmission $submission) use ($replicated) {
                return [
                    'form_id' => $replicated->id,
                    'user_id' => $submission->user_id,
                    'data' => json_encode($submission->data),
                    'ip_address' => $submission->ip_address,
                    'user_agent' => $submission->user_agent,
                    'created_at' => now()->toDateTimeString(),
                    'updated_at' => now()->toDateTimeString(),
                ];
            })->toArray();

            if (! empty($submissionsData)) {
                \App\Models\Core\FormSubmission::insert($submissionsData);
                $replicated->submission_count = count($submissionsData);
                $replicated->save();
            }
        }

        return $this->success($replicated, 'Form duplicated successfully', 201);
    }

    /**
     * Bulk actions for forms.
     */
    public function bulkAction(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \App\Models\Core\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:forms,id',
            'action' => 'required|in:delete,restore,force_delete',
        ]);

        $idsRaw = $request->input('ids');
        $ids = is_array($idsRaw) ? $idsRaw : [];
        $actionRaw = $request->input('action');
        $action = is_string($actionRaw) ? $actionRaw : '';

        try {
            if ($action === 'delete') {
                $query = Form::whereIn('id', $ids);

                if (! $user->can('manage forms')) {
                    $query->where('author_id', $user->id);
                }

                $query->delete();

                return $this->success(null, 'Selected forms deleted successfully');
            } elseif ($action === 'restore') {
                $query = Form::withTrashed()->whereIn('id', $ids);
                if (! $user->can('manage forms')) {
                    $query->where('author_id', $user->id);
                }
                $query->restore();

                return $this->success(null, 'Selected forms restored successfully');
            } elseif ($action === 'force_delete') {
                $query = Form::withTrashed()->whereIn('id', $ids);
                if (! $user->can('manage forms')) {
                    $query->where('author_id', $user->id);
                }

                $forms = $query->get();
                foreach ($forms as $form) {
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
