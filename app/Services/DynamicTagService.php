<?php

namespace App\Services;

use App\Models\Content;
use App\Models\Setting;

class DynamicTagService
{
    /**
     * Resolve all dynamic tags in blocks array
     * 
     * @param array $blocks The blocks array with potential dynamic tags
     * @param Content|null $content The content context for post/page tags
     * @param array|null $loopItem Loop item context if in a loop
     * @return array Blocks with resolved tags
     */
    public function resolveBlocks(array $blocks, ?Content $content = null, ?array $loopItem = null): array
    {
        return $this->processBlocks($blocks, $content, $loopItem);
    }

    /**
     * Recursively process blocks and resolve dynamic tags
     */
    protected function processBlocks(array $blocks, ?Content $content, ?array $loopItem): array
    {
        foreach ($blocks as &$block) {
            // Process settings
            if (isset($block['settings']) && is_array($block['settings'])) {
                foreach ($block['settings'] as $key => &$value) {
                    if (is_string($value) && str_starts_with($value, '@dynamic:')) {
                        $tag = str_replace('@dynamic:', '', $value);
                        $value = $this->resolveTag($tag, $content, $loopItem);
                    }
                }
            }
            
            // Process children recursively
            if (isset($block['children']) && is_array($block['children'])) {
                $block['children'] = $this->processBlocks($block['children'], $content, $loopItem);
            }
        }
        
        return $blocks;
    }

    /**
     * Resolve a single dynamic tag to its value
     * 
     * @param string $tag The tag like "{{post_title}}"
     * @param Content|null $content Content context
     * @param array|null $loopItem Loop item context
     * @return string The resolved value
     */
    public function resolveTag(string $tag, ?Content $content = null, ?array $loopItem = null): string
    {
        // Strip {{ and }}
        $key = trim(str_replace(['{{', '}}'], '', $tag));
        
        // Post/Page tags
        if ($content && str_starts_with($key, 'post_')) {
            return match($key) {
                'post_title' => $content->title ?? '',
                'post_excerpt' => $content->excerpt ?? '',
                'post_content' => $content->body ?? '',
                'post_date' => $content->published_at?->format('M d, Y') ?? $content->created_at?->format('M d, Y') ?? '',
                'post_author' => $content->author?->name ?? '',
                'post_author_avatar' => $content->author?->avatar ?? '',
                'post_featured_image' => $content->featured_image ?? '',
                'post_url' => url('/' . $content->slug),
                'post_category' => $content->category?->name ?? '',
                'post_tags' => $content->tags?->pluck('name')->join(', ') ?? '',
                default => ''
            };
        }
        
        // Loop item tags
        if ($loopItem && str_starts_with($key, 'loop_')) {
            return match($key) {
                'loop_title' => $loopItem['title'] ?? '',
                'loop_excerpt' => $loopItem['excerpt'] ?? '',
                'loop_date' => $loopItem['date'] ?? '',
                'loop_author' => $loopItem['author'] ?? '',
                'loop_thumbnail' => $loopItem['thumbnail'] ?? $loopItem['featured_image'] ?? '',
                'loop_url' => $loopItem['url'] ?? '',
                'loop_category' => $loopItem['category'] ?? '',
                'loop_index' => (string)($loopItem['index'] ?? 0),
                default => ''
            };
        }
        
        // Site tags
        if (str_starts_with($key, 'site_') || str_starts_with($key, 'current_')) {
            return match($key) {
                'site_title' => Setting::get('site_title', config('app.name')),
                'site_tagline' => Setting::get('site_tagline', ''),
                'site_logo' => Setting::get('site_logo', ''),
                'current_date' => now()->format('M d, Y'),
                'current_year' => (string)now()->year,
                default => ''
            };
        }
        
        // Archive tags
        if (str_starts_with($key, 'archive_')) {
            return match($key) {
                'archive_title' => 'Archive',
                'archive_description' => '',
                'archive_count' => '0',
                default => ''
            };
        }
        
        // User tags (no user context on public frontend, return empty)
        if (str_starts_with($key, 'user_')) {
            $user = auth()->user();
            if (!$user) return '';
            
            return match($key) {
                'user_name' => $user->name ?? '',
                'user_email' => $user->email ?? '',
                'user_avatar' => $user->avatar ?? '',
                default => ''
            };
        }
        
        return '';
    }
}
