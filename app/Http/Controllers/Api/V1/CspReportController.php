<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\CspReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CspReportController extends BaseApiController
{
    /**
     * Receive CSP violation reports from browsers
     * Public endpoint, no authentication required
     */
    public function store(Request $request)
    {
        try {
            $report = $request->input('csp-report');
            
            if (!$report) {
                return response()->json(['status' => 'ignored'], 200);
            }
            
            CspReport::create([
                'document_uri' => $report['document-uri'] ?? '',
                'violated_directive' => $report['violated-directive'] ?? '',
                'blocked_uri' => $report['blocked-uri'] ?? '',
                'source_file' => $report['source-file'] ?? null,
                'line_number' => $report['line-number'] ?? null,
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
                'raw_report' => $report,
                'status' => 'new',
            ]);
            
            return response()->json(['status' => 'received'], 200);
            
        } catch (\Exception $e) {
            Log::error('CSP report storage failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Get CSP reports for admin dashboard
     */
    public function index(Request $request)
    {
        $query = CspReport::query();
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('directive')) {
            $query->where('violated_directive', 'like', "%{$request->directive}%");
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $reports = $query->latest()->paginate($request->input('per_page', 50));
        
        return $this->paginated($reports, 'CSP reports retrieved successfully');
    }

    /**
     * Bulk action on CSP reports
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'action' => 'required|in:mark_reviewed,mark_false_positive,delete',
        ]);
        
        $query = CspReport::whereIn('id', $validated['ids']);
        
        switch ($validated['action']) {
            case 'mark_reviewed':
                $query->update(['status' => 'reviewed']);
                break;
            case 'mark_false_positive':
                $query->update(['status' => 'false_positive']);
                break;
            case 'delete':
                $query->delete();
                break;
        }
        
        return $this->success(null, 'Bulk action completed successfully');
    }

    /**
     * Get CSP report statistics
     */
    public function statistics()
    {
        $stats = [
            'total' => CspReport::count(),
            'new' => CspReport::where('status', 'new')->count(),
            'by_directive' => CspReport::select('violated_directive', \DB::raw('count(*) as count'))
                ->groupBy('violated_directive')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
            'recent_trend' => CspReport::select(\DB::raw('DATE(created_at) as date'), \DB::raw('count(*) as count'))
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ];
        
        return $this->success($stats, 'CSP statistics retrieved successfully');
    }
}
