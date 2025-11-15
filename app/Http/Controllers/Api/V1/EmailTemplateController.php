<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
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

        return response()->json($templates);
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

        return response()->json($template, 201);
    }

    public function show(EmailTemplate $emailTemplate)
    {
        return response()->json($emailTemplate);
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

        return response()->json($emailTemplate);
    }

    public function destroy(EmailTemplate $emailTemplate)
    {
        $emailTemplate->delete();

        return response()->json(['message' => 'Email template deleted successfully']);
    }

    public function preview(Request $request, EmailTemplate $emailTemplate)
    {
        $data = $request->input('data', []);
        $rendered = $emailTemplate->render($data);

        return response()->json($rendered);
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

            return response()->json(['message' => 'Test email sent successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send test email: ' . $e->getMessage()], 500);
        }
    }
}
