<?php

namespace App\Services;

use App\Models\SearchIndex;
use App\Models\SearchQuery;

class SearchService
{
    public function search($query, $filters = [], $limit = 20)
    {
        if (empty(trim($query))) {
            return [
                'results' => [],
                'total' => 0,
                'query' => $query,
                'suggestions' => [],
            ];
        }

        $searchQuery = SearchIndex::query();
        $isLoose = false;
        $suggestions = [];

        // 1. Try Strict Search (AND)
        $this->applySearchLogic($searchQuery, $query, true);
        $this->applyFilters($searchQuery, $filters);

        $results = $searchQuery->orderByDesc('relevance_score')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        // 2. Fallback to Loose Search (OR) if no results
        if ($results->isEmpty()) {
            $isLoose = true;
            $searchQuery = SearchIndex::query();
            $this->applySearchLogic($searchQuery, $query, false);
            $this->applyFilters($searchQuery, $filters);

            $results = $searchQuery->orderByDesc('relevance_score')
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();

            // 3. If still empty, get suggestions
            if ($results->isEmpty()) {
                $suggestions = $this->getSuggestions($query);
            }
        }

        // Log search query
        SearchQuery::log($query, $results->count(), $filters);

        return [
            'results' => $results->map(function ($index) {
                return [
                    'id' => $index->id,
                    'type' => $index->type,
                    'title' => $index->title,
                    'excerpt' => $index->excerpt,
                    'url' => $index->url,
                    'searchable_type' => $index->searchable_type,
                    'searchable_id' => $index->searchable_id,
                    'relevance_score' => $index->relevance_score,
                ];
            }),
            'total' => $results->count(),
            'query' => $query,
            'is_loose' => $isLoose,
            'suggestions' => $suggestions,
        ];
    }

    protected function applySearchLogic($queryBuilder, $query, $strict = true)
    {
        if (config('database.default') === 'mysql' || config('database.default') === 'mariadb') {
            $prepared = $this->prepareSearchQuery($query, $strict);
            if ($prepared) {
                $queryBuilder->whereRaw(
                    'MATCH(title, content) AGAINST(? IN BOOLEAN MODE)',
                    [$prepared]
                );
            }
        } else {
            // Fallback for SQLite/PostgreSQL
            $terms = explode(' ', $query);
            $queryBuilder->where(function ($q) use ($terms, $strict) {
                foreach ($terms as $term) {
                    if ($strict) {
                        $q->where(function ($sub) use ($term) {
                            $sub->where('title', 'like', "%{$term}%")
                                ->orWhere('content', 'like', "%{$term}%");
                        });
                    } else {
                        $q->orWhere('title', 'like', "%{$term}%")
                            ->orWhere('content', 'like', "%{$term}%");
                    }
                }
            });
        }
    }

    protected function applyFilters($queryBuilder, $filters)
    {
        if (isset($filters['type'])) {
            $queryBuilder->where('type', $filters['type']);
        }
        if (isset($filters['date_from'])) {
            $queryBuilder->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $queryBuilder->whereDate('created_at', '<=', $filters['date_to']);
        }
    }

    public function searchByType($query, $type, $limit = 20)
    {
        return $this->search($query, ['type' => $type], $limit);
    }

    public function getSuggestions($query, $limit = 5)
    {
        if (empty(trim($query))) {
            return [];
        }

        // 1. Try simple substring match
        $suggestions = SearchIndex::where(function ($q) use ($query) {
            $q->where('title', 'like', "%{$query}%")
                ->orWhere('content', 'like', "%{$query}%");
        })
            ->select('title', 'type')
            ->distinct()
            ->limit($limit)
            ->get();

        // 2. If empty, try SOUNDEX (MySQL only) for typo tolerance
        if ($suggestions->isEmpty() && (config('database.default') === 'mysql' || config('database.default') === 'mariadb')) {
            $suggestions = SearchIndex::whereRaw('SOUNDEX(title) = SOUNDEX(?)', [$query])
                ->select('title', 'type')
                ->distinct()
                ->limit($limit)
                ->get();
        }

        return $suggestions->map(function ($index) {
            return [
                'text' => $index->title,
                'type' => $index->type,
            ];
        });
    }

    protected function prepareSearchQuery($query, $strict = true)
    {
        // Prepare query for MySQL FULLTEXT search
        $terms = explode(' ', trim($query));
        $prepared = [];

        foreach ($terms as $term) {
            $term = trim($term);
            if (strlen($term) >= 2) {
                // Strict: +term* (must contain term)
                // Loose: term* (optional)
                $prefix = $strict ? '+' : '';
                $prepared[] = "{$prefix}{$term}*";
            }
        }

        return implode(' ', $prepared);
    }

    public function reindexAll()
    {
        // Reindex all content
        $contents = \App\Models\Content::where('status', 'published')->get();
        foreach ($contents as $content) {
            SearchIndex::index($content, [
                'title' => $content->title,
                'content' => strip_tags($content->body),
                'excerpt' => $content->excerpt,
                'url' => url('/content/'.$content->slug),
                'type' => $content->type,
            ]);
        }

        // Reindex categories
        $categories = \App\Models\Category::where('is_active', true)->get();
        foreach ($categories as $category) {
            SearchIndex::index($category, [
                'title' => $category->name,
                'content' => $category->description ?? '',
                'url' => url('/category/'.$category->slug),
                'type' => 'category',
            ]);
        }

        // Reindex tags
        $tags = \App\Models\Tag::all();
        foreach ($tags as $tag) {
            SearchIndex::index($tag, [
                'title' => $tag->name,
                'content' => $tag->description ?? '',
                'url' => url('/tag/'.$tag->slug),
                'type' => 'tag',
            ]);
        }

        return [
            'contents' => $contents->count(),
            'categories' => $categories->count(),
            'tags' => $tags->count(),
        ];
    }
}
