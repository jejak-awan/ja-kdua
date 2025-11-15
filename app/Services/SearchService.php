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
            ];
        }

        $searchQuery = SearchIndex::query();

        // Full-text search (MySQL/MariaDB)
        if (config('database.default') === 'mysql' || config('database.default') === 'mariadb') {
            $searchQuery->whereRaw(
                'MATCH(title, content) AGAINST(? IN BOOLEAN MODE)',
                [$this->prepareSearchQuery($query)]
            );
        } else {
            // Fallback for SQLite/PostgreSQL
            $terms = explode(' ', $query);
            $searchQuery->where(function ($q) use ($terms) {
                foreach ($terms as $term) {
                    $q->where('title', 'like', "%{$term}%")
                        ->orWhere('content', 'like', "%{$term}%");
                }
            });
        }

        // Apply filters
        if (isset($filters['type'])) {
            $searchQuery->where('type', $filters['type']);
        }

        if (isset($filters['date_from'])) {
            $searchQuery->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $searchQuery->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Order by relevance
        $searchQuery->orderByDesc('relevance_score')
            ->orderByDesc('created_at');

        $results = $searchQuery->limit($limit)->get();

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
        ];
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

        $suggestions = SearchIndex::where('title', 'like', "%{$query}%")
            ->orWhere('content', 'like', "%{$query}%")
            ->select('title', 'type')
            ->distinct()
            ->limit($limit)
            ->get()
            ->map(function ($index) {
                return [
                    'text' => $index->title,
                    'type' => $index->type,
                ];
            });

        return $suggestions;
    }

    protected function prepareSearchQuery($query)
    {
        // Prepare query for MySQL FULLTEXT search
        $terms = explode(' ', trim($query));
        $prepared = [];

        foreach ($terms as $term) {
            $term = trim($term);
            if (strlen($term) >= 3) {
                $prepared[] = "+{$term}*";
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
