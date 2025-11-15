<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchIndex extends Model
{
    protected $table = 'search_indexes';

    protected $fillable = [
        'searchable_type',
        'searchable_id',
        'title',
        'content',
        'excerpt',
        'meta',
        'url',
        'type',
        'relevance_score',
    ];

    protected $casts = [
        'meta' => 'array',
        'relevance_score' => 'integer',
    ];

    public function searchable()
    {
        return $this->morphTo();
    }

    public static function index($model, $data = [])
    {
        $searchableType = get_class($model);
        $searchableId = $model->id;

        // Build searchable content
        $title = $data['title'] ?? $model->title ?? $model->name ?? '';
        $content = $data['content'] ?? $model->body ?? $model->description ?? '';
        $excerpt = $data['excerpt'] ?? $model->excerpt ?? null;
        $url = $data['url'] ?? null;
        $type = $data['type'] ?? null;

        // Calculate relevance score (can be enhanced)
        $relevanceScore = self::calculateRelevance($title, $content);

        return self::updateOrCreate(
            [
                'searchable_type' => $searchableType,
                'searchable_id' => $searchableId,
            ],
            [
                'title' => $title,
                'content' => $content,
                'excerpt' => $excerpt,
                'meta' => $data['meta'] ?? [],
                'url' => $url,
                'type' => $type,
                'relevance_score' => $relevanceScore,
            ]
        );
    }

    public static function remove($model)
    {
        return self::where('searchable_type', get_class($model))
            ->where('searchable_id', $model->id)
            ->delete();
    }

    protected static function calculateRelevance($title, $content)
    {
        // Simple relevance calculation
        // Title matches are more important than content matches
        $score = 0;
        $score += strlen($title) * 10; // Title weight
        $score += strlen($content) * 1; // Content weight

        return $score;
    }
}
