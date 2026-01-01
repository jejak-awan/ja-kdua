<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\SlowQuery;
use Illuminate\Http\Request;

class SlowQueryController extends BaseApiController
{
    public function index(Request $request)
    {
        $query = SlowQuery::with('user');
        
        if ($request->filled('route')) {
            $query->where('route', 'like', "%{$request->route}%");
        }
        
        if ($request->filled('min_duration')) {
            $query->where('duration', '>=', $request->min_duration);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $queries = $query->latest()->paginate($request->input('per_page', 50));
        
        return $this->paginated($queries, 'Slow queries retrieved');
    }
    
    public function statistics()
    {
        $stats = [
            'total' => SlowQuery::count(),
            'avg_duration' => (int) SlowQuery::avg('duration'),
            'max_duration' => (int) SlowQuery::max('duration'),
            'today' => SlowQuery::whereDate('created_at', today())->count(),
        ];
        
        return $this->success($stats, 'Statistics retrieved');
    }
}
