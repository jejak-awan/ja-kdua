<?php

namespace App\Services;

use App\Models\Content;
use App\Models\ContentCustomField;
use App\Models\ContentRevision;
use App\Models\MediaUsage;
use App\Models\SearchIndex;
use App\Models\Tag;
use App\Models\Webhook;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ContentService
{
    protected CacheService $cacheService;

    public function __construct()
    {
        $this->cacheService = new CacheService;
    }

    /**
     * Get published contents with filtering and caching
     */
    public function getPublishedContents(Request $request): array
    {
        $cacheKey = 'contents_published_'.md5($request->getQueryString());

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($request) {
            $query = Content::with(['author', 'category', 'tags'])
                ->where('status', 'published')
                ->where(function ($q) {
                    $q->whereNull('published_at')
                        ->orWhere('published_at', '<=', Carbon::now());
                });

            $this->applyFilters($query, $request);
            $this->applySorting($query, $request);

            // Limit or pagination
            $limit = $request->get('limit');
            if ($limit) {
                return [
                    'data' => $query->limit($limit)->get(),
                    'paginated' => false,
                ];
            }

            $perPage = $request->get('per_page', 12);

            return [
                'data' => $query->paginate($perPage),
                'paginated' => true,
            ];
        });
    }

    /**
     * Apply common filters to content query
     */
    public function applyFilters($query, Request $request): void
    {
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('is_featured')) {
            $query->where('is_featured', filter_var($request->is_featured, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->has('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('slug', $request->tag);
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
    }

    /**
     * Apply sorting to query
     */
    public function applySorting($query, Request $request): void
    {
        $sortBy = $request->get('sort', '-published_at');
        if (str_starts_with($sortBy, '-')) {
            $query->orderBy(substr($sortBy, 1), 'desc');
        } else {
            $query->orderBy($sortBy, 'asc');
        }
    }

    /**
     * Get related content by tags and category
     */
    public function getRelatedContent(string $slug, int $limit = 5): array
    {
        $cacheKey = 'content_related_'.$slug;

        return Cache::remember($cacheKey, now()->addHours(2), function () use ($slug, $limit) {
            $content = Content::where('slug', $slug)->first();
            if (! $content) {
                return [];
            }

            // Get related by tags first
            $relatedByTags = Content::where('status', 'published')
                ->where('id', '!=', $content->id)
                ->where(function ($q) {
                    $q->whereNull('published_at')
                        ->orWhere('published_at', '<=', Carbon::now());
                })
                ->whereHas('tags', function ($q) use ($content) {
                    $q->whereIn('tags.id', $content->tags->pluck('id'));
                })
                ->with(['author', 'category', 'tags'])
                ->latest('published_at')
                ->limit($limit)
                ->get();

            // Fill with category-related if not enough
            if ($relatedByTags->count() < $limit && $content->category_id) {
                $relatedByCategory = Content::where('status', 'published')
                    ->where('id', '!=', $content->id)
                    ->where('category_id', $content->category_id)
                    ->whereNotIn('id', $relatedByTags->pluck('id'))
                    ->where(function ($q) {
                        $q->whereNull('published_at')
                            ->orWhere('published_at', '<=', Carbon::now());
                    })
                    ->with(['author', 'category', 'tags'])
                    ->latest('published_at')
                    ->limit($limit - $relatedByTags->count())
                    ->get();

                return $relatedByTags->concat($relatedByCategory)->toArray();
            }

            return $relatedByTags->toArray();
        });
    }

    /**
     * Create new content
     */
    public function create(array $data, int $userId, bool $createRevision = false): Content
    {
        $data['author_id'] = $userId;

        // Handle published_at scheduling
        if (isset($data['published_at']) && $data['published_at']) {
            $data['published_at'] = Carbon::parse($data['published_at']);
        }

        // Extract related data
        $tags = $data['tags'] ?? [];
        $newTags = $data['new_tags'] ?? [];
        $customFields = $data['custom_fields'] ?? null;
        // Keep editor_type
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title']);
        }

        $content = Content::create($data);

        // Create new tags and get their IDs
        foreach ($newTags as $tagName) {
            $tag = Tag::firstOrCreate(
                ['slug' => Str::slug($tagName)],
                ['name' => $tagName, 'slug' => Str::slug($tagName)]
            );
            $tags[] = $tag->id;
        }

        // Sync tags
        if (! empty($tags)) {
            $content->tags()->sync($tags);
        }

        // Save custom fields
        if ($customFields !== null) {
            $this->saveCustomFields($content, $customFields);
        }

        // Track media usage
        if (! empty($content->featured_image)) {
            $this->trackMediaUsage($content, 'featured_image');
        }
        if (! empty($content->og_image)) {
            $this->trackMediaUsage($content, 'og_image');
        }

        // Create initial revision if requested
        if ($createRevision) {
            $this->createRevision($content, $userId, 'Initial version');
        }

        // Index for search
        if ($content->status === 'published') {
            $this->indexForSearch($content);
        }

        // Trigger webhook
        Webhook::triggerForEvent('content.created', $content->toArray());

        // Clear caches
        $this->clearContentCaches($content->id);

        return $content;
    }

    /**
     * Update existing content
     */
    public function update(Content $content, array $data, int $userId, bool $createRevision = false, ?string $revisionNote = null): Content
    {
        // Create revision before update if requested
        if ($createRevision) {
            $this->createRevision($content, $userId, $revisionNote ?? 'Revision before update');
        }

        // Extract related data
        $tags = $data['tags'] ?? [];
        $newTags = $data['new_tags'] ?? [];
        $customFields = $data['custom_fields'] ?? null;
        // Keep editor_type
        unset($data['create_revision'], $data['revision_note'], $data['tags'], $data['new_tags'], $data['custom_fields']);

        // Handle published_at
        if (isset($data['published_at'])) {
            $data['published_at'] = $data['published_at'] ? Carbon::parse($data['published_at']) : null;
        }

        $content->update($data);

        // Create new tags and get their IDs
        foreach ($newTags as $tagName) {
            $tag = Tag::firstOrCreate(
                ['slug' => Str::slug($tagName)],
                ['name' => $tagName, 'slug' => Str::slug($tagName)]
            );
            $tags[] = $tag->id;
        }

        // Sync tags
        if (! empty($tags)) {
            $content->tags()->sync($tags);
        }

        // Save custom fields
        if ($customFields !== null) {
            $this->saveCustomFields($content, $customFields);
        }

        // Track media usage
        if (array_key_exists('featured_image', $data)) {
            $this->trackMediaUsage($content, 'featured_image');
        }
        if (array_key_exists('og_image', $data)) {
            $this->trackMediaUsage($content, 'og_image');
        }

        // Update search index
        if ($content->status === 'published') {
            $this->indexForSearch($content);
        } else {
            SearchIndex::remove($content);
        }

        // Trigger webhook
        Webhook::triggerForEvent('content.updated', $content->toArray());

        // Clear caches
        $this->clearContentCaches($content->id);

        return $content;
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured(Content $content): bool
    {
        $content->is_featured = ! $content->is_featured;
        $content->save();

        $this->clearContentCaches($content->id);

        return $content->is_featured;
    }

    /**
     * Track media usage
     */
    protected function trackMediaUsage(Content $content, string $fieldName): void
    {
        $mediaId = $content->{$fieldName};
        if ($mediaId) {
            MediaUsage::track($mediaId, $content, $fieldName);
        } else {
            MediaUsage::untrack(null, $content, $fieldName);
        }
    }

    /**
     * Delete content
     */
    public function delete(Content $content): void
    {
        $contentId = $content->id;

        SearchIndex::remove($content);
        
        // Untrack media usage
        MediaUsage::untrack(null, $content);

        $content->delete();

        Webhook::triggerForEvent('content.deleted', ['id' => $contentId]);
        $this->clearContentCaches($contentId);
    }

    /**
     * Duplicate content
     */
    public function duplicate(Content $content, int $userId): Content
    {
        $newContent = $content->replicate();
        $newContent->title = $content->title.' (Copy)';
        $newContent->slug = $this->generateUniqueSlug($newContent->title);
        $newContent->status = 'draft';
        $newContent->author_id = $userId;
        $newContent->views = 0;
        $newContent->published_at = null;
        $newContent->is_featured = false;
        $newContent->save();

        // Copy tags
        if ($content->tags()->count() > 0) {
            $newContent->tags()->sync($content->tags->pluck('id'));
        }

        return $newContent;
    }

    /**
     * Perform bulk action on contents
     */
    public function bulkAction(string $action, array $contentIds, ?int $categoryId = null): int
    {
        $contents = Content::withTrashed()->whereIn('id', $contentIds)->get();

        foreach ($contents as $content) {
            switch ($action) {
                case 'publish':
                    $content->update(['status' => 'published', 'published_at' => $content->published_at ?? now()]);
                    break;
                case 'approve':
                    $content->update(['status' => 'published', 'published_at' => $content->published_at ?? now()]);
                    break;
                case 'reject':
                    $content->update(['status' => 'draft']);
                    break;
                case 'draft':
                    $content->update(['status' => 'draft']);
                    break;
                case 'archive':
                    $content->update(['status' => 'archived']);
                    break;
                case 'delete':
                    // Use model deletion to trigger events (for slug releasing)
                    $content->delete();
                    break;
                case 'change_category':
                    if ($categoryId) {
                        $content->update(['category_id' => $categoryId]);
                    }
                    break;
                case 'restore':
                    if ($content->trashed()) {
                        $content->restore();
                    }
                    break;
                case 'force_delete':
                    $content->forceDelete();
                    break;
            }
        }

        // Clear all content cache since we don't know exactly which pages are affected
        $this->cacheService->clearContentCaches();

        return $contents->count();
    }

    /**
     * Save custom fields
     */
    public function saveCustomFields(Content $content, array $customFields): void
    {
        foreach ($customFields as $fieldSlug => $value) {
            $field = \App\Models\CustomField::where('slug', $fieldSlug)->first();
            if ($field) {
                ContentCustomField::updateOrCreate(
                    [
                        'content_id' => $content->id,
                        'custom_field_id' => $field->id,
                    ],
                    ['value' => is_array($value) ? json_encode($value) : $value]
                );
            }
        }
    }

    /**
     * Create content revision
     */
    public function createRevision(Content $content, int $userId, string $note = ''): ContentRevision
    {
        return ContentRevision::create([
            'content_id' => $content->id,
            'user_id' => $userId,
            'title' => $content->title,
            'body' => $content->body,
            'excerpt' => $content->excerpt,
            'slug' => $content->slug,
            'meta' => $content->meta,
            'status' => $content->status,
            'note' => $note,
        ]);
    }

    /**
     * Index content for search
     */
    public function indexForSearch(Content $content): void
    {
        SearchIndex::index($content, [
            'title' => $content->title,
            'content' => strip_tags($content->body ?? ''),
            'excerpt' => $content->excerpt,
            'url' => url('/content/'.$content->slug),
            'type' => $content->type,
        ]);
    }

    /**
     * Check if content is locked by another user
     */
    public function isLockedByOther(Content $content, int $userId): bool
    {
        return $content->locked_by && $content->locked_by !== $userId;
    }

    /**
     * Lock content for editing
     */
    public function lock(Content $content, int $userId): void
    {
        $content->update([
            'locked_by' => $userId,
            'locked_at' => now(),
        ]);
    }

    /**
     * Unlock content
     */
    public function unlock(Content $content): void
    {
        $content->update([
            'locked_by' => null,
            'locked_at' => null,
        ]);
    }

    /**
     * Clear content-related caches
     */
    protected function clearContentCaches(?int $contentId = null): void
    {
        $this->cacheService->clearContentCaches($contentId);
        $this->cacheService->clearSeoCaches();
    }

    /**
     * Generate unique slug
     */
    public function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $baseSlug = $slug;
        $counter = 1;

        while (Content::withTrashed()->where('slug', $slug)->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }

    /**
     * Restore trashed content
     */
    public function restore(int $id): bool
    {
        $content = Content::withTrashed()->findOrFail($id);
        if ($content->trashed()) {
            $content->restore();
            $this->clearContentCaches($id);
            Webhook::triggerForEvent('content.restored', ['id' => $id]);

            return true;
        }

        return false;
    }

    /**
     * Force delete content
     */
    public function forceDelete(int $id): bool
    {
        $content = Content::withTrashed()->findOrFail($id);
        SearchIndex::remove($content);

        // Untrack media usage
        MediaUsage::untrack(null, $content);

        $content->forceDelete();
        Webhook::triggerForEvent('content.force_deleted', ['id' => $id]);
        $this->clearContentCaches($id);

        return true;
    }
    /**
     * Empty trash
     */
    public function emptyTrash(): int
    {
        $count = Content::onlyTrashed()->count();
        Content::onlyTrashed()->forceDelete();
        
        $this->cacheService->clearContentCaches();
        
        return $count;
    }
}
