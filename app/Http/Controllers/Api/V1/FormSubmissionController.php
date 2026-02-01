<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Http\Request;
use App\Exports\FormSubmissionsExport;
use Maatwebsite\Excel\Facades\Excel;

class FormSubmissionController extends BaseApiController
{
    public function index(Request $request, ?Form $form = null)
    {
        $query = FormSubmission::with(['form', 'user']);

        // Multi-tenancy scoping
        if (! $request->user()->can('manage forms')) {
            $query->whereHas('form', function ($q) use ($request) {
                $q->where('author_id', $request->user()->id);
            });
        }

        if ($form) {
            $query->where('form_id', $form->id);
        } elseif ($request->has('form_id')) {
            $query->where('form_id', $request->form_id);
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

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('data', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting logic
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        
        // Validate sort column to prevent SQL injection or errors
        $allowedSortColumns = ['status', 'created_at', 'ip_address'];
        if (in_array($sortBy, $allowedSortColumns)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->latest(); // Default to created_at desc
        }

        $perPage = min($request->input('per_page', 15), 100);
        $submissions = $query->paginate($perPage);

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

    public function restore($id)
    {
        $submission = FormSubmission::withTrashed()->findOrFail($id);
        $submission->restore();

        return $this->success(null, 'Submission restored successfully');
    }

    public function forceDelete($id)
    {
        $submission = FormSubmission::withTrashed()->findOrFail($id);
        $submission->forceDelete();

        return $this->success(null, 'Submission permanently deleted');
    }

    public function export(Request $request, Form $form)
    {
        $query = $form->submissions();

        // Search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('data', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        // Date range filter
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Status filter (match index logic)
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Sort logic
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $allowedSortColumns = ['status', 'created_at', 'ip_address'];
        if (in_array($sortBy, $allowedSortColumns)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->latest();
        }

        // Collect field keys for headers
        $submissions = (clone $query)->get();
        $fieldKeys = [];
        foreach ($submissions as $submission) {
            if (is_array($submission->data)) {
                $fieldKeys = array_merge($fieldKeys, array_keys($submission->data));
            }
        }
        $fieldKeys = array_values(array_unique($fieldKeys));

        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = str_replace(' ', '_', $form->name) . "_submissions_{$timestamp}";
        $format = $request->input('format', 'xlsx');

        if ($format === 'csv') {
            return Excel::download(new FormSubmissionsExport($query, $fieldKeys), "{$filename}.csv", \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download(new FormSubmissionsExport($query, $fieldKeys), "{$filename}.xlsx");
    }

    public function statistics(Request $request, ?Form $form = null)
    {
        $query = FormSubmission::query();

        // Multi-tenancy scoping
        if (! $request->user()->can('manage forms')) {
            $query->whereHas('form', function ($q) use ($request) {
                $q->where('author_id', $request->user()->id);
            });
        }

        if ($form) {
            $query->where('form_id', $form->id);
        } elseif ($request->has('form_id')) {
            $query->where('form_id', $request->form_id);
        }

        $stats = [
            'total' => (clone $query)->count(),
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
