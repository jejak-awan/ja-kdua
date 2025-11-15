<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Http\Request;

class FormSubmissionController extends BaseApiController
{
    public function index(Request $request, ?Form $form = null)
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

        return $this->paginated($submissions, 'Form submissions retrieved successfully');
    }

    public function show(FormSubmission $formSubmission)
    {
        return $this->success($formSubmission->load(['form.fields', 'user']), 'Form submission retrieved successfully');
    }

    public function markAsRead(FormSubmission $formSubmission)
    {
        $formSubmission->markAsRead();

        return $this->success([
            'submission' => $formSubmission,
        ], 'Submission marked as read');
    }

    public function archive(FormSubmission $formSubmission)
    {
        $formSubmission->archive();

        return $this->success([
            'submission' => $formSubmission,
        ], 'Submission archived');
    }

    public function destroy(FormSubmission $formSubmission)
    {
        $formSubmission->delete();

        return $this->success(null, 'Submission deleted successfully');
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

        return $this->success([
            'form' => $form->name,
            'total' => $submissions->count(),
            'data' => $data,
        ], 'Form submissions exported successfully');
    }

    public function statistics(Request $request, ?Form $form = null)
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

        return $this->success($stats, 'Form submission statistics retrieved successfully');
    }
}
