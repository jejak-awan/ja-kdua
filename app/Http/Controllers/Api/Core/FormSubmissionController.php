<?php

namespace App\Http\Controllers\Api\Core;

use App\Exports\FormSubmissionsExport;
use App\Models\Core\Form;
use App\Models\Core\FormField;
use App\Models\Core\FormSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class FormSubmissionController extends BaseApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, ?Form $form = null): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \App\Models\Core\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        $query = FormSubmission::with(['form', 'user']);

        // Multi-tenancy scoping
        if (! $user->can('manage forms')) {
            $query->whereHas('form', function ($q) use ($user) {
                $q->where('author_id', $user->id);
            });
        }

        if ($form) {
            $query->where('form_id', $form->id);
        } elseif ($request->has('form_id')) {
            $formIdRaw = $request->input('form_id');
            $formId = is_numeric($formIdRaw) ? (int) $formIdRaw : 0;
            $query->where('form_id', $formId);
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

        if ($request->has('status')) {
            $statusRaw = $request->input('status');
            $status = is_string($statusRaw) ? $statusRaw : '';
            $query->where('status', $status);
        }

        if ($request->filled('search')) {
            $searchRaw = $request->input('search');
            $search = is_string($searchRaw) ? $searchRaw : '';
            $query->where(function ($q) use ($search) {
                $q->where('data', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if ($request->has('date_from')) {
            $dateFromRaw = $request->input('date_from');
            $dateFrom = is_string($dateFromRaw) ? $dateFromRaw : null;
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($request->has('date_to')) {
            $dateToRaw = $request->input('date_to');
            $dateTo = is_string($dateToRaw) ? $dateToRaw : null;
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Sorting logic
        $sortByRaw = $request->input('sort_by', 'created_at');
        $sortBy = is_string($sortByRaw) ? $sortByRaw : 'created_at';
        $sortOrderRaw = $request->input('sort_order', 'desc');
        $sortOrder = is_string($sortOrderRaw) ? $sortOrderRaw : 'desc';

        // Validate sort column to prevent SQL injection or errors
        $allowedSortColumns = ['status', 'created_at', 'ip_address'];
        if (in_array($sortBy, $allowedSortColumns)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->latest(); // Default to created_at desc
        }

        $perPageRaw = $request->input('per_page', 15);
        $perPage = is_numeric($perPageRaw) ? min((int) $perPageRaw, 100) : 15;
        $submissions = $query->paginate($perPage);

        return $this->paginated($submissions, 'Form submissions retrieved successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(FormSubmission $formSubmission): \Illuminate\Http\JsonResponse
    {
        return $this->success($formSubmission->load(['form.fields', 'user']), 'Form submission retrieved successfully');
    }

    /**
     * Mark the specified resource as read.
     */
    public function markAsRead(FormSubmission $formSubmission): \Illuminate\Http\JsonResponse
    {
        $formSubmission->markAsRead();

        return $this->success([
            'submission' => $formSubmission,
        ], 'Submission marked as read');
    }

    /**
     * Archive the specified resource.
     */
    public function archive(FormSubmission $formSubmission): \Illuminate\Http\JsonResponse
    {
        $formSubmission->archive();

        return $this->success([
            'submission' => $formSubmission,
        ], 'Submission archived');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FormSubmission $formSubmission): \Illuminate\Http\JsonResponse
    {
        $formSubmission->delete();

        return $this->success(null, 'Submission deleted successfully');
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param  string|int  $id
     */
    public function restore($id): \Illuminate\Http\JsonResponse
    {
        /** @var FormSubmission $submission */
        $submission = FormSubmission::withTrashed()->findOrFail($id);
        $submission->restore();

        return $this->success(null, 'Submission restored successfully');
    }

    /**
     * Permanently remove the specified resource from storage.
     *
     * @param  string|int  $id
     */
    public function forceDelete($id): \Illuminate\Http\JsonResponse
    {
        /** @var FormSubmission $submission */
        $submission = FormSubmission::withTrashed()->findOrFail($id);
        $submission->forceDelete();

        return $this->success(null, 'Submission permanently deleted');
    }

    /**
     * Export the resource.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function export(Request $request, Form $form)
    {
        $query = $form->submissions();

        // Search filter
        if ($request->filled('search')) {
            $searchRaw = $request->input('search');
            $search = is_string($searchRaw) ? $searchRaw : '';
            $query->where(function ($q) use ($search) {
                $q->where('data', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        // Date range filter
        if ($request->has('date_from')) {
            $dateFromRaw = $request->input('date_from');
            $dateFrom = is_string($dateFromRaw) ? $dateFromRaw : null;
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($request->has('date_to')) {
            $dateToRaw = $request->input('date_to');
            $dateTo = is_string($dateToRaw) ? $dateToRaw : null;
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Status filter (match index logic)
        if ($request->has('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        // Sort logic
        $sortByRaw = $request->input('sort_by', 'created_at');
        $sortBy = is_string($sortByRaw) ? $sortByRaw : 'created_at';
        $sortOrderRaw = $request->input('sort_order', 'desc');
        $sortOrder = is_string($sortOrderRaw) ? $sortOrderRaw : 'desc';
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
            /** @var FormSubmission $submission */
            if ($submission->data) {
                $fieldKeys = array_merge($fieldKeys, array_keys($submission->data));
            }
        }
        $fieldKeys = array_values(array_unique($fieldKeys));

        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = str_replace(' ', '_', $form->name)."_submissions_{$timestamp}";
        $format = $request->input('format', 'xlsx');

        @ini_set('memory_limit', '512M');
        @set_time_limit(120);

        /** @var \Illuminate\Database\Eloquent\Builder<\App\Models\Core\FormSubmission> $exportQuery */
        $exportQuery = $query->getQuery();

        if ($format === 'csv') {
            return Excel::download(new FormSubmissionsExport($exportQuery, $fieldKeys), "{$filename}.csv", \Maatwebsite\Excel\Excel::CSV);
        }

        if ($format === 'pdf') {
            $submissions = $exportQuery->get();
            if (empty($fieldKeys)) {
                $fieldKeys = collect($submissions)->flatMap(function ($s) {
                    return array_keys($s->data ?? []);
                })->unique()->values()->toArray();
            }

            $html = view('pdf.submissions-list', [
                'form' => $form,
                'submissions' => $submissions,
                'headers' => $fieldKeys,
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

        return Excel::download(new FormSubmissionsExport($exportQuery, $fieldKeys), "{$filename}.xlsx");
    }

    /**
     * Export the resource as PDF.
     */
    public function exportPdf(FormSubmission $formSubmission): \Illuminate\Http\Response
    {
        @ini_set('memory_limit', '512M');
        $formSubmission->load(['form', 'user']);

        $html = view('pdf.submission', [
            'submission' => $formSubmission,
            'form' => $formSubmission->form,
            'data' => $formSubmission->data,
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

    /**
     * Get statistics.
     */
    public function statistics(Request $request, ?Form $form = null): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \App\Models\Core\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        $baseQuery = FormSubmission::query();

        // Multi-tenancy scoping
        if (! $user->can('manage forms')) {
            $baseQuery->whereHas('form', function ($q) use ($user) {
                $q->where('author_id', $user->id);
            });
        }

        if ($form) {
            $baseQuery->where('form_id', $form->id);
        } elseif ($request->has('form_id')) {
            $baseQuery->where('form_id', $request->input('form_id'));
        }

        // Date Range Logic
        $dateFromRaw = $request->input('date_from');
        $dateToRaw = $request->input('date_to');
        $dateFrom = is_string($dateFromRaw) ? $dateFromRaw : null;
        $dateTo = is_string($dateToRaw) ? $dateToRaw : null;

        if ($dateFrom && $dateTo) {
            $start = \Illuminate\Support\Carbon::parse($dateFrom)->startOfDay();
            $end = \Illuminate\Support\Carbon::parse($dateTo)->endOfDay();
            $daysCount = (int) $start->diffInDays($end) + 1;
        } else {
            $daysCountRaw = $request->input('days', 30);
            $daysCount = is_numeric($daysCountRaw) ? (int) $daysCountRaw : 30;
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
                'visits' => $found ? (int) $found->count : 0,
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
        for ($i = 0; $i < 24; $i++) {
            $found = $hourlyData->firstWhere('hour', $i);
            $hourlyStats[] = [
                'hour' => $i,
                'count' => $found ? (int) $found->count : 0,
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
        for ($i = 1; $i <= 7; $i++) {
            $found = $weeklyData->firstWhere('day', $i);
            $weeklyStats[] = [
                'day' => $daysOfWeek[$i - 1],
                'day_index' => $i, // Sunday = 1 to Saturday = 7
                'count' => $found ? (int) $found->count : 0,
            ];
        }
        $stats['weekly_stats'] = $weeklyStats;

        // Dynamic Field Analytics
        $formId = $form ? $form->id : $request->input('form_id');
        if ($formId) {
            $chartableFields = FormField::where('form_id', $formId)
                ->whereIn('type', ['select', 'radio', 'checkbox', 'checkbox_group', 'dropdown'])
                ->get(['id', 'name', 'label', 'type']);

            $stats['chartable_fields'] = $chartableFields;

            $selectedFieldNameRaw = $request->input('aggregate_field');
            $selectedFieldName = is_string($selectedFieldNameRaw) ? $selectedFieldNameRaw : '';
            if ($selectedFieldName && $chartableFields->contains('name', $selectedFieldName)) {
                $fieldData = (clone $periodQuery)
                    ->select('data->'.$selectedFieldName.' as label')
                    ->selectRaw('count(*) as count')
                    ->whereNotNull('data->'.$selectedFieldName)
                    ->groupBy('label')
                    ->orderBy('count', 'DESC')
                    ->get();

                $stats['field_distribution'] = $fieldData->map(function ($item) {
                    $itemData = (object) $item;
                    $label = property_exists($itemData, 'label') ? $itemData->label : null;
                    if (is_string($label)) {
                        $label = trim($label, '"');
                    }

                    return [
                        'label' => $label ?: 'Unknown',
                        'count' => property_exists($itemData, 'count') ? (int) $itemData->count : 0,
                    ];
                });
            }
        }

        return $this->success($stats, 'Form submission statistics retrieved successfully');
    }
}
