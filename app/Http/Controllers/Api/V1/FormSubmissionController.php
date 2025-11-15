<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Http\Request;

class FormSubmissionController extends BaseApiController
{
    public function index(Request $request, Form $form = null)
    {
        $query = FormSubmission::with(['form', 'user']);

        if ($form) {
            $query->where('form_id', $form->id);
        } elseif ($request->has('form_id')) {
            $query->where('form_id', $request->form_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $submissions = $query->latest()->paginate(50);

        return response()->json($submissions);
    }

    public function show(FormSubmission $formSubmission)
    {
        return response()->json($formSubmission->load(['form.fields', 'user']));
    }

    public function markAsRead(FormSubmission $formSubmission)
    {
        $formSubmission->markAsRead();

        return response()->json([
            'message' => 'Submission marked as read',
            'submission' => $formSubmission,
        ]);
    }

    public function archive(FormSubmission $formSubmission)
    {
        $formSubmission->archive();

        return response()->json([
            'message' => 'Submission archived',
            'submission' => $formSubmission,
        ]);
    }

    public function destroy(FormSubmission $formSubmission)
    {
        $formSubmission->delete();

        return response()->json(['message' => 'Submission deleted successfully']);
    }

    public function export(Request $request, Form $form)
    {
        $submissions = $form->submissions()
            ->where('status', '!=', 'archived')
            ->latest()
            ->get();

        $data = [];
        foreach ($submissions as $submission) {
            $row = [
                'ID' => $submission->id,
                'Submitted At' => $submission->created_at->format('Y-m-d H:i:s'),
                'IP Address' => $submission->ip_address,
            ];

            foreach ($submission->data as $key => $value) {
                $row[$key] = is_array($value) ? implode(', ', $value) : $value;
            }

            $data[] = $row;
        }

        return response()->json([
            'form' => $form->name,
            'total' => $submissions->count(),
            'data' => $data,
        ]);
    }

    public function statistics(Request $request, Form $form = null)
    {
        $query = FormSubmission::query();

        if ($form) {
            $query->where('form_id', $form->id);
        } elseif ($request->has('form_id')) {
            $query->where('form_id', $request->form_id);
        }

        $stats = [
            'total' => $query->count(),
            'new' => (clone $query)->where('status', 'new')->count(),
            'read' => (clone $query)->where('status', 'read')->count(),
            'archived' => (clone $query)->where('status', 'archived')->count(),
            'today' => (clone $query)->whereDate('created_at', today())->count(),
            'this_week' => (clone $query)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => (clone $query)->whereMonth('created_at', now()->month)->count(),
        ];

        return response()->json($stats);
    }
}
