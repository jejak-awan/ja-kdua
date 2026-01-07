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

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage = min($request->input('per_page', 15), 100);
        $submissions = $query->latest()->paginate($perPage);

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
        $query = $form->submissions()
            ->where('status', '!=', 'archived')
            ->latest();

        // Date range filter
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $submissions = $query->get();

        // Build export data
        $data = [];
        $headers = ['ID', 'Submitted At', 'IP Address', 'Status'];

        // Collect all possible field keys from submissions
        $fieldKeys = [];
        foreach ($submissions as $submission) {
            if (is_array($submission->data)) {
                $fieldKeys = array_merge($fieldKeys, array_keys($submission->data));
            }
        }
        $fieldKeys = array_unique($fieldKeys);
        $headers = array_merge($headers, $fieldKeys);

        foreach ($submissions as $submission) {
            $row = [
                'ID' => $submission->id,
                'Submitted At' => $submission->created_at->format('Y-m-d H:i:s'),
                'IP Address' => $submission->ip_address,
                'Status' => $submission->status,
            ];

            // Add all field values
            foreach ($fieldKeys as $key) {
                $value = $submission->data[$key] ?? '';
                $row[$key] = is_array($value) ? implode(', ', $value) : $value;
            }

            $data[] = $row;
        }

        // Return as CSV if requested
        if ($request->input('format') === 'csv') {
            $filename = str_replace(' ', '_', $form->name).'_submissions_'.now()->format('Y-m-d').'.csv';

            $callback = function () use ($headers, $data) {
                $handle = fopen('php://output', 'w');

                // BOM for Excel UTF-8
                fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

                // Write headers
                fputcsv($handle, $headers);

                // Write data rows
                foreach ($data as $row) {
                    $orderedRow = [];
                    foreach ($headers as $header) {
                        $orderedRow[] = $row[$header] ?? '';
                    }
                    fputcsv($handle, $orderedRow);
                }

                fclose($handle);
            };

            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);
        }

        // Default: return JSON
        return $this->success([
            'form' => $form->name,
            'total' => $submissions->count(),
            'headers' => $headers,
            'data' => $data,
        ], 'Form submissions exported successfully');
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
