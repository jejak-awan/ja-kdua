<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Services\SearchService;
use App\Models\SearchQuery;
use Illuminate\Http\Request;

class SearchController extends BaseApiController
{
    protected $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:255',
            'type' => 'nullable|in:post,page,category,tag',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $filters = [];
        if ($request->has('type')) {
            $filters['type'] = $request->type;
        }
        if ($request->has('date_from')) {
            $filters['date_from'] = $request->date_from;
        }
        if ($request->has('date_to')) {
            $filters['date_to'] = $request->date_to;
        }

        $limit = $request->input('limit', 20);

        $results = $this->searchService->search($request->q, $filters, $limit);

        return $this->success($results, 'Search results retrieved successfully');
    }

    public function suggestions(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:255',
            'limit' => 'nullable|integer|min:1|max:10',
        ]);

        $limit = $request->input('limit', 5);
        $suggestions = $this->searchService->getSuggestions($request->q, $limit);

        return $this->success([
            'suggestions' => $suggestions,
        ], 'Search suggestions retrieved successfully');
    }

    public function popularQueries(Request $request)
    {
        $limit = $request->input('limit', 10);
        $days = $request->input('days', 30);

        $queries = SearchQuery::getPopularQueries($limit, $days);

        return $this->success($queries, 'Popular queries retrieved successfully');
    }

    public function noResultsQueries(Request $request)
    {
        $limit = $request->input('limit', 10);
        $days = $request->input('days', 30);

        $queries = SearchQuery::getNoResultsQueries($limit, $days);

        return $this->success($queries, 'No results queries retrieved successfully');
    }

    public function searchStats(Request $request)
    {
        $days = $request->input('days', 30);

        $stats = [
            'total_searches' => SearchQuery::where('searched_at', '>=', now()->subDays($days))->count(),
            'unique_queries' => SearchQuery::where('searched_at', '>=', now()->subDays($days))
                ->distinct('query')
                ->count('query'),
            'avg_results' => SearchQuery::where('searched_at', '>=', now()->subDays($days))
                ->avg('results_count'),
            'zero_result_searches' => SearchQuery::where('searched_at', '>=', now()->subDays($days))
                ->where('results_count', 0)
                ->count(),
            'popular_queries' => SearchQuery::getPopularQueries(10, $days),
        ];

        return $this->success($stats, 'Search statistics retrieved successfully');
    }

    public function reindex(Request $request)
    {
        // Only allow admins to reindex
        if (!$request->user()->hasRole('admin')) {
            return $this->forbidden('Unauthorized');
        }

        $result = $this->searchService->reindexAll();

        return $this->success([
            'indexed' => $result,
        ], 'Search index rebuilt successfully');
    }
}
