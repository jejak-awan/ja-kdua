<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class Widget extends Model
{
    protected $fillable = [
        'title',
        'type',
        'location',
        'content',
        'settings',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, self>
     */
    public static function getByLocation(string $location): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('location', $location)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function getContent(): mixed
    {
        switch ($this->type) {
            case 'recent_posts':
                return $this->getRecentPosts();
            case 'categories':
                return $this->getCategories();
            case 'text':
            case 'html':
            default:
                return $this->content;
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Core\Content>
     */
    protected function getRecentPosts(): \Illuminate\Database\Eloquent\Collection
    {
        /** @var array<string, mixed> $settings */
        $settings = $this->settings ?? [];
        /** @var int $limit */
        $limit = $settings['limit'] ?? 5;

        return \App\Models\Core\Content::where('status', 'published')
            ->where('type', 'post')
            ->latest('published_at')
            ->limit((int) $limit)
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Core\Category>
     */
    protected function getCategories(): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\Core\Category::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }
}
