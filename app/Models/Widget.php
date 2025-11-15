<?php

namespace App\Models;

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

    public static function getByLocation($location)
    {
        return self::where('location', $location)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function getContent()
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

    protected function getRecentPosts()
    {
        $limit = $this->settings['limit'] ?? 5;

        return \App\Models\Content::where('status', 'published')
            ->where('type', 'post')
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    protected function getCategories()
    {
        return \App\Models\Category::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }
}
