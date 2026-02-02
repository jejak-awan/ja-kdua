<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use Illuminate\Http\Request;
use App\Exports\FormSubmissionsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

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

        @ini_set('memory_limit', '512M');
        @set_time_limit(120);

        if ($format === 'csv') {
            return Excel::download(new FormSubmissionsExport($query, $fieldKeys), "{$filename}.csv", \Maatwebsite\Excel\Excel::CSV);
        }

        if ($format === 'pdf') {
            $submissions = $query->get();
            if (empty($fieldKeys)) {
                $fieldKeys = collect($submissions)->flatMap(function ($s) {
                    return array_keys($s->data ?? []);
                })->unique()->values()->toArray();
            }

            $html = view('pdf.submissions-list', [
                'form' => $form,
                'submissions' => $submissions,
                'headers' => $fieldKeys
            ])->render();

            $mpdf = new \Mpdf\Mpdf([
                'format' => 'A4-L',
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
            ]);
            $mpdf->WriteHTML($html);
            return response($mpdf->Output("{$filename}.pdf", 'D'))
                ->header('Content-Type', 'application/pdf');
        }

        return Excel::download(new FormSubmissionsExport($query, $fieldKeys), "{$filename}.xlsx");
    }

    public function exportPdf(FormSubmission $formSubmission)
    {
        @ini_set('memory_limit', '512M');
        $formSubmission->load(['form', 'user']);
        
        $html = view('pdf.submission', [
            'submission' => $formSubmission,
            'form' => $formSubmission->form,
            'data' => $formSubmission->data
        ])->render();

        $mpdf = new \Mpdf\Mpdf([
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
        ]);
        $mpdf->WriteHTML($html);
        return response($mpdf->Output("submission-{$formSubmission->id}.pdf", 'D'))
            ->header('Content-Type', 'application/pdf');
    }

    public function statistics(Request $request, ?Form $form = null)
    {
        $baseQuery = FormSubmission::query();

        // Multi-tenancy scoping
        if (! $request->user()->can('manage forms')) {
            $baseQuery->whereHas('form', function ($q) use ($request) {
                $q->where('author_id', $request->user()->id);
            });
        }

        if ($form) {
            $baseQuery->where('form_id', $form->id);
        } elseif ($request->has('form_id')) {
            $baseQuery->where('form_id', $request->form_id);
        }

        // Date Range Logic
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        
        if ($dateFrom && $dateTo) {
            $start = \Illuminate\Support\Carbon::parse($dateFrom)->startOfDay();
            $end = \Illuminate\Support\Carbon::parse($dateTo)->endOfDay();
            $daysCount = $start->diffInDays($end) + 1;
        } else {
            $daysCount = (int) $request->input('days', 30);
            $start = now()->subDays($daysCount - 1)->startOfDay();
            $end = now()->endOfDay();
        }

        $prevStart = (clone $start)->subDays($daysCount);
        $prevEnd = (clone $end)->subDays($daysCount);

        // Core Stats for Current Period
        $periodQuery = (clone $baseQuery)->whereBetween('created_at', [$start, $end]);
        $prevQuery = (clone $baseQuery)->whereBetween('created_at', [$prevStart, $prevEnd]);

        $stats = [
            'total' => (clone $periodQuery)->count(),
            'previous_total' => (clone $prevQuery)->count(),
            'new' => (clone $periodQuery)->where('status', 'new')->count(),
            'read' => (clone $periodQuery)->where('status', 'read')->count(),
            'archived' => (clone $periodQuery)->where('status', 'archived')->count(),
            
            // Fixed stats for general context
            'all_time_total' => (clone $baseQuery)->count(),
            'today' => (clone $baseQuery)->whereDate('created_at', today())->count(),
        ];

        // Growth calculation
        $stats['growth'] = $stats['previous_total'] > 0 
            ? round((($stats['total'] - $stats['previous_total']) / $stats['previous_total']) * 100, 1)
            : ($stats['total'] > 0 ? 100 : 0);

        // Time-series data (Daily) - Using Carbon objects now
        $dailyData = (clone $periodQuery)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        $dailyStats = [];
        $current = clone $start;
        while ($current->lte($end)) {
            $dateStr = $current->format('Y-m-d');
            $found = $dailyData->firstWhere('date', $dateStr);
            $dailyStats[] = [
                'period' => $dateStr, // Matches LineChart expected field
                'visits' => $found ? (int)$found->count : 0
            ];
            $current->addDay();
        }
        $stats['daily_stats'] = $dailyStats;

        // Peak Activity (Hourly)
        $hourlyData = (clone $periodQuery)
            ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('count(*) as count'))
            ->groupBy('hour')
            ->orderBy('hour', 'ASC')
            ->get();
        
        $hourlyStats = [];
        for ($i=0; $i<24; $i++) {
            $found = $hourlyData->firstWhere('hour', $i);
            $hourlyStats[] = [
                'hour' => $i,
                'count' => $found ? (int)$found->count : 0
            ];
        }
        $stats['hourly_stats'] = $hourlyStats;

        // Peak Activity (Weekly)
        $weeklyData = (clone $periodQuery)
            ->select(DB::raw('DAYOFWEEK(created_at) as day'), DB::raw('count(*) as count'))
            ->groupBy('day')
            ->orderBy('day', 'ASC')
            ->get();
        
        $daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $weeklyStats = [];
        for ($i=1; $i<=7; $i++) {
            $found = $weeklyData->firstWhere('day', $i);
            $weeklyStats[] = [
                'day' => $daysOfWeek[$i-1],
                'day_index' => $i, // Sunday = 1 to Saturday = 7
                'count' => $found ? (int)$found->count : 0
            ];
        }
        $stats['weekly_stats'] = $weeklyStats;

        // Dynamic Field Analytics
        $formId = $form ? $form->id : $request->form_id;
        if ($formId) {
            $chartableFields = FormField::where('form_id', $formId)
                ->whereIn('type', ['select', 'radio', 'checkbox', 'checkbox_group', 'dropdown'])
                ->get(['id', 'name', 'label', 'type']);
            
            $stats['chartable_fields'] = $chartableFields;

            $selectedFieldName = $request->input('aggregate_field');
            if ($selectedFieldName && $chartableFields->contains('name', $selectedFieldName)) {
                $fieldData = (clone $periodQuery)
                    ->select('data->'.$selectedFieldName.' as label')
                    ->selectRaw('count(*) as count')
                    ->whereNotNull('data->'.$selectedFieldName)
                    ->groupBy('label')
                    ->orderBy('count', 'DESC')
                    ->get();
                
                $stats['field_distribution'] = $fieldData->map(function($item) {
                    $label = $item->label;
                    if (is_string($label)) {
                        $label = trim($label, '"');
                    }
                    return [
                        'label' => $label ?: 'Unknown',
                        'count' => (int)$item->count
                    ];
                });
            }
        }

        return $this->success($stats, 'Form submission statistics retrieved successfully');
    }
}
