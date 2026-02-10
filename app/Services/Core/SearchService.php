<?php

namespace App\Services\Core;

use App\Models\Core\SearchIndex;
use App\Models\Core\SearchQuery;

class SearchService
{
    /**
     * Search the index
     *
     * @param  string  $query
     * @param  array<string, mixed>  $filters
     * @param  int  $limit
     * @return array{results: \Illuminate\Support\Collection<int, array<string, mixed>>, total: int, query: string, suggestions: array<int, array{text: string, type: string}>, is_loose?: bool}
     */
    public function search($query, $filters = [], $limit = 20): array
    {
        if (empty(trim($query))) {
            return [
                'results' => collect(),
                'total' => 0,
                'query' => (string) $query,
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
                /** @var SearchIndex $index */
                /** @var array<string, mixed> $mapped */
                $mapped = [
                    'id' => $index->id,
                    'type' => (string) $index->type,
                    'title' => (string) $index->title,
                    'excerpt' => is_scalar($index->excerpt) ? (string) $index->excerpt : null,
                    'url' => is_scalar($index->url) ? (string) $index->url : null,
                    'searchable_type' => (string) $index->searchable_type,
                    'searchable_id' => (int) $index->searchable_id,
                    'relevance_score' => $index->getAttribute('relevance_score'),
                ];

                return $mapped;
            }),
            'total' => $results->count(),
            'query' => (string) $query,
            'is_loose' => $isLoose,
            'suggestions' => $suggestions,
        ];
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<SearchIndex>  $queryBuilder
     * @param  string  $query
     */
    protected function applySearchLogic($queryBuilder, $query, bool $strict = true): void
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

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<SearchIndex>  $queryBuilder
     * @param  array<string, mixed>  $filters
     */
    protected function applyFilters($queryBuilder, $filters): void
    {
        if (isset($filters['type']) && is_string($filters['type'])) {
            $queryBuilder->where('type', $filters['type']);
        }
        if (isset($filters['date_from']) && is_string($filters['date_from'])) {
            $queryBuilder->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (isset($filters['date_to']) && is_string($filters['date_to'])) {
            $queryBuilder->whereDate('created_at', '<=', $filters['date_to']);
        }
    }

    /**
     * Search by specific type
     *
     * @param  string  $query
     * @param  string  $type
     * @return array{results: \Illuminate\Support\Collection<int, array<string, mixed>>, total: int, query: string, suggestions: array<int, array{text: string, type: string}>, is_loose?: bool}
     */
    public function searchByType($query, $type, int $limit = 20): array
    {
        return $this->search((string) $query, ['type' => $type], $limit);
    }

    /**
     * Get search suggestions
     *
     * @param  string  $query
     * @param  int  $limit
     * @return array<int, array{text: string, type: string}>
     */
    public function getSuggestions($query, $limit = 5): array
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

        /** @var array<int, array{text: string, type: string}> $result */
        $result = $suggestions->map(function ($index) {
            /** @var SearchIndex $index */
            return [
                'text' => (string) $index->title,
                'type' => (string) $index->type,
            ];
        })->toArray();

        return $result;
    }

    /**
     * Prepare query for MySQL FullText
     *
     * @param  string  $query
     */
    protected function prepareSearchQuery($query, bool $strict = true): string
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

    /**
     * Reindex all searchable items
     *
     * @return array{contents: int, categories: int, tags: int}
     */
    public function reindexAll(): array
    {
        // Reindex all content
        $contents = \App\Models\Core\Content::where('status', 'published')->get();
        foreach ($contents as $content) {
            SearchIndex::index($content, [
                'title' => $content->title,
                'content' => strip_tags($content->body ?? ''),
                'excerpt' => $content->excerpt,
                'url' => url('/content/'.$content->slug),
                'type' => $content->type,
            ]);
        }

        // Reindex categories
        $categories = \App\Models\Core\Category::where('is_active', true)->get();
        foreach ($categories as $category) {
            SearchIndex::index($category, [
                'title' => $category->name,
                'content' => $category->description ?? '',
                'url' => url('/category/'.$category->slug),
                'type' => 'category',
            ]);
        }

        // Reindex tags
        $tags = \App\Models\Core\Tag::all();
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
