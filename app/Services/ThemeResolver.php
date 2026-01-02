<?php

namespace App\Services;

use App\Models\ThemeTemplate;
use Illuminate\Http\Request;

class ThemeResolver
{
    /**
     * Resolve the active theme template for the given context.
     *
     * @param array $context ['route_name' => string, 'url' => string, 'post_type' => string|null, 'is_home' => bool]
     * @return ThemeTemplate|null
     */
    public function resolve(array $context)
    {
        // 1. Fetch all active templates, ordered by priority (desc)
        // Optimization: Cache this query
        $templates = ThemeTemplate::where('is_active', true)
            ->orderBy('priority', 'desc')
            ->get();

        foreach ($templates as $template) {
            if ($this->matches($template, $context)) {
                return $template;
            }
        }

        return null;
    }

    protected function matches(ThemeTemplate $template, array $context)
    {
        $conditions = $template->conditions ?? [];

        // If no conditions, it doesn't match anything automatically? 
        // Or should it be a "Global Fallback"? 
        // Usually "Entire Site" is an explicit condition type 'all'.
        if (empty($conditions)) {
            return false;
        }

        // Check each condition group (OR logic? or AND? Divi is usually OR between groups, AND within group?)
        // My UI saves as array of objects. Let's assume ANY condition match makes it apply (OR).
        // Or if I supported exclude rules, it would be complex.
        // Simple MVP: Array of conditions. If ANY matches, it applies.
        
        foreach ($conditions as $condition) {
            $type = $condition['type'] ?? null;
            $value = $condition['value'] ?? null;

            switch ($type) {
                case 'all':
                    return true;

                case 'homepage':
                    if (!empty($context['is_home']) || $context['url'] === '/' || $context['route_name'] === 'home') {
                        return true;
                    }
                    break;

                case 'post_type':
                    if (isset($context['post_type']) && $context['post_type'] === $value) {
                        return true;
                    }
                    break;
                
                case 'archive':
                    if (isset($context['is_archive']) && $context['is_archive']) {
                        return true;
                    }
                    break;

                case 'category':
                    if (isset($context['is_category']) && $context['is_category']) {
                        // If value is provided, check if it matches specific category
                        if ($value && isset($context['category_id']) && (string)$context['category_id'] === (string)$value) {
                            return true;
                        }
                        // If no value, match all categories
                        if (!$value) return true;
                    }
                    break;

                case '404':
                    // Frontend handles 404 detection
                    if (isset($context['is_404']) && $context['is_404']) {
                        return true;
                    }
                    break;
            }
        }

        return false;
    }
}
