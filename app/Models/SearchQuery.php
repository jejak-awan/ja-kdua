<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchQuery extends Model
{
    protected $table = 'search_queries';

    protected $fillable = [
        'query',
        'results_count',
        'user_id',
        'ip_address',
        'user_agent',
        'filters',
        'searched_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'results_count' => 'integer',
        'searched_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log($query, $resultsCount, $filters = [])
    {
        return self::create([
            'query' => $query,
            'results_count' => $resultsCount,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'filters' => $filters,
            'searched_at' => now(),
        ]);
    }

    public static function getPopularQueries($limit = 10, $days = 30)
    {
        return self::where('searched_at', '>=', now()->subDays($days))
            ->select('query', \DB::raw('count(*) as count'))
            ->groupBy('query')
            ->orderByDesc('count')
            ->limit($limit)
            ->get();
    }

    public static function getNoResultsQueries($limit = 10, $days = 30)
    {
        return self::where('searched_at', '>=', now()->subDays($days))
            ->where('results_count', 0)
            ->select('query', \DB::raw('count(*) as count'))
            ->groupBy('query')
            ->orderByDesc('count')
            ->limit($limit)
            ->get();
    }
}
