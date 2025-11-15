<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends BaseApiController
{
    public function index(Request $request)
    {
        $query = EmailTemplate::query();

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $templates = $query->latest()->get();

        return $this->success($templates, 'Email templates retrieved successfully');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:email_templates,slug',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'text_body' => 'nullable|string',
            'variables' => 'nullable|array',
            'category' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $template = EmailTemplate::create($validated);

        return $this->success($template, 'Email template created successfully', 201);
    }

    public function show(EmailTemplate $emailTemplate)
    {
        return $this->success($emailTemplate, 'Email template retrieved successfully');
    }

    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:email_templates,slug,' . $emailTemplate->id,
            'subject' => 'sometimes|required|string|max:255',
            'body' => 'sometimes|required|string',
            'text_body' => 'nullable|string',
            'variables' => 'nullable|array',
            'category' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $emailTemplate->update($validated);

        return $this->success($emailTemplate, 'Email template updated successfully');
    }

    public function destroy(EmailTemplate $emailTemplate)
    {
        $emailTemplate->delete();

        return $this->success(null, 'Email template deleted successfully');
    }

    public function preview(Request $request, EmailTemplate $emailTemplate)
    {
        $data = $request->input('data', []);
        $rendered = $emailTemplate->render($data);

        return $this->success($rendered, 'Email template preview generated successfully');
    }

    public function sendTest(Request $request, EmailTemplate $emailTemplate)
    {
        $request->validate([
            'email' => 'required|email',
            'data' => 'nullable|array',
        ]);

        $data = $request->input('data', []);
        $rendered = $emailTemplate->render($data);

        try {
            \Mail::raw($rendered['text_body'] ?? strip_tags($rendered['body']), function ($message) use ($request, $rendered) {
                $message->to($request->email)
                    ->subject($rendered['subject']);
            });

            return $this->success(null, 'Test email sent successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to send test email: ' . $e->getMessage(), 500, [], 'EMAIL_SEND_ERROR');
        }
    }
}
