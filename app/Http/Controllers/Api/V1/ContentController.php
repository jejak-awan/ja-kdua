<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Content;
use App\Models\ContentCustomField;
use App\Models\ContentRevision;
use App\Services\CacheService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ContentController extends BaseApiController
{
    public function index(Request $request)
    {
        $cacheKey = 'contents_published_'.md5($request->getQueryString());

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($request) {
            $query = Content::with(['author', 'category', 'tags'])
                ->where('status', 'published')
                ->where(function ($q) {
                    $q->whereNull('published_at')
                        ->orWhere('published_at', '<=', Carbon::now());
                });

            // Filter by type
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            // Filter by is_featured
            if ($request->has('is_featured')) {
                $query->where('is_featured', filter_var($request->is_featured, FILTER_VALIDATE_BOOLEAN));
            }

            // Filter by category
            if ($request->has('category')) {
                $query->whereHas('category', function ($q) use ($request) {
                    $q->where('slug', $request->category);
                });
            }

            // Filter by tag
            if ($request->has('tag')) {
                $query->whereHas('tags', function ($q) use ($request) {
                    $q->where('slug', $request->tag);
                });
            }

            // Sorting
            $sortBy = $request->get('sort', '-published_at');
            if (str_starts_with($sortBy, '-')) {
                $query->orderBy(substr($sortBy, 1), 'desc');
            } else {
                $query->orderBy($sortBy, 'asc');
            }

            // Pagination or limit
            $limit = $request->get('limit');
            if ($limit) {
                $contents = $query->limit($limit)->get();
                return $this->success($contents, 'Contents retrieved successfully');
            }

            $perPage = $request->get('per_page', 12);
            $contents = $query->paginate($perPage);

            return $this->paginated($contents, 'Contents retrieved successfully');
        });
    }

    public function show($slug)
    {
        $content = Content::with(['author', 'category', 'tags', 'comments' => function ($q) {
            $q->where('status', 'approved')->latest();
        }])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', Carbon::now());
            })
            ->firstOrFail();

        $content->increment('views');

        return $this->success($content, 'Content retrieved successfully');
    }

    public function related($slug)
    {
        $cacheKey = 'content_related_'.$slug;

        return Cache::remember($cacheKey, now()->addHours(2), function () use ($slug) {
            try {
                $content = Content::where('slug', $slug)->firstOrFail();

                // Get related content by tags first (more specific)
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
                    ->limit(5)
                    ->get();

                // If not enough, get more by category
                if ($relatedByTags->count() < 5 && $content->category_id) {
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
                        ->limit(5 - $relatedByTags->count())
                        ->get();

                    $related = $relatedByTags->concat($relatedByCategory);
                } else {
                    $related = $relatedByTags;
                }

                return $this->success($related, 'Related content retrieved successfully');
            } catch (\Exception $e) {
                return $this->success([], 'Related content retrieved successfully');
            }
        });
    }

    public function preview(Request $request, Content $content)
    {
        // Allow preview for draft content if user is the author or admin
        if ($content->status === 'draft' && $content->author_id !== $request->user()->id) {
            if (! $request->user()->hasRole('admin')) {
                return $this->forbidden('Unauthorized to preview this content');
            }
        }

        return $this->success($content->load(['author', 'category', 'tags', 'customFields.customField']), 'Content preview retrieved successfully');
    }

    public function adminIndex(Request $request)
    {
        $query = Content::with(['author', 'category', 'tags']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $contents = $query->latest()->paginate(12);

        return $this->paginated($contents, 'Contents retrieved successfully');
    }

    public function adminShow(Content $content)
    {
        return $this->success($content->load(['author', 'category', 'tags', 'allComments', 'customFields.customField.fieldGroup']), 'Content retrieved successfully');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'slug' => 'required|string|unique:contents,slug',
                'excerpt' => 'nullable|string',
                'body' => 'required|string',
                'featured_image' => 'nullable|string',
                'status' => 'required|in:draft,published,archived',
                'type' => 'required|in:post,page,custom',
                'category_id' => 'nullable|exists:categories,id',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:tags,id',
                'published_at' => 'nullable|date',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'meta_keywords' => 'nullable|string|max:255',
                'og_image' => 'nullable|string',
                'create_revision' => 'boolean',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $validated['author_id'] = $request->user()->id;

        // Handle published_at scheduling
        if ($request->has('published_at') && $request->published_at) {
            $validated['published_at'] = Carbon::parse($request->published_at);
        }

        $createRevision = $request->input('create_revision', false);
        unset($validated['create_revision']);

        $content = Content::create($validated);

        if ($request->has('tags')) {
            $content->tags()->sync($request->tags);
        }

        // Create initial revision if requested
        if ($createRevision) {
            ContentRevision::create([
                'content_id' => $content->id,
                'user_id' => $request->user()->id,
                'title' => $content->title,
                'body' => $content->body,
                'excerpt' => $content->excerpt,
                'slug' => $content->slug,
                'meta' => $content->meta,
                'status' => $content->status,
                'note' => 'Initial version',
            ]);
        }

        // Track media usage
        if ($content->featured_image) {
            $this->trackMediaUsage($content, 'featured_image');
        }
        if ($content->og_image) {
            $this->trackMediaUsage($content, 'og_image');
        }

        // Clear caches
        $cacheService = new CacheService;
        $cacheService->clearContentCaches($content->id);
        $cacheService->clearSeoCaches();

        // Save custom fields if provided
        if ($request->has('custom_fields')) {
            $this->saveCustomFields($content, $request->custom_fields);
        }

        // Index for search
        if ($content->status === 'published') {
            \App\Models\SearchIndex::index($content, [
                'title' => $content->title,
                'content' => strip_tags($content->body),
                'excerpt' => $content->excerpt,
                'url' => url('/content/'.$content->slug),
                'type' => $content->type,
            ]);
        }

        // Trigger webhook
        \App\Models\Webhook::triggerForEvent('content.created', $content->toArray());

        return $this->success($content->load(['author', 'category', 'tags', 'customFields.customField']), 'Content created successfully', 201);
    }

    protected function trackMediaUsage(Content $content, $fieldName)
    {
        $imagePath = $content->$fieldName;
        if (! $imagePath) {
            return;
        }

        // Extract media ID from path if stored in database
        // For now, we'll track by path pattern
        // This can be enhanced to extract actual media ID
    }

    public function update(Request $request, Content $content)
    {
        // Check if content is locked by another user
        if ($content->locked_by && $content->locked_by !== $request->user()->id) {
            $lockedBy = $content->lockedBy;

            return $this->error(
                'Content is currently being edited by '.$lockedBy->name,
                423,
                [],
                'CONTENT_LOCKED',
                [
                    'locked_by' => $lockedBy,
                    'locked_at' => $content->locked_at,
                ]
            );
        }

        try {
            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'slug' => 'sometimes|required|string|unique:contents,slug,'.$content->id,
                'excerpt' => 'nullable|string',
                'body' => 'sometimes|required|string',
                'featured_image' => 'nullable|string',
                'status' => 'sometimes|required|in:draft,published,archived',
                'type' => 'sometimes|required|in:post,page,custom',
                'category_id' => 'nullable|exists:categories,id',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:tags,id',
                'published_at' => 'nullable|date',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'meta_keywords' => 'nullable|string|max:255',
                'og_image' => 'nullable|string',
                'create_revision' => 'boolean',
                'revision_note' => 'nullable|string|max:500',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        // Handle published_at scheduling
        if ($request->has('published_at')) {
            $validated['published_at'] = $request->published_at ? Carbon::parse($request->published_at) : null;
        }

        $createRevision = $request->input('create_revision', false);
        $revisionNote = $request->input('revision_note');
        unset($validated['create_revision'], $validated['revision_note']);

        // Create revision before update if requested
        if ($createRevision) {
            ContentRevision::create([
                'content_id' => $content->id,
                'user_id' => $request->user()->id,
                'title' => $content->title,
                'body' => $content->body,
                'excerpt' => $content->excerpt,
                'slug' => $content->slug,
                'meta' => $content->meta,
                'status' => $content->status,
                'note' => $revisionNote ?? 'Revision before update',
            ]);
        }

        $content->update($validated);

        if ($request->has('tags')) {
            $content->tags()->sync($request->tags);
        }

        // Track media usage
        if ($request->has('featured_image')) {
            $this->trackMediaUsage($content, 'featured_image');
        }
        if ($request->has('og_image')) {
            $this->trackMediaUsage($content, 'og_image');
        }

        // Clear caches
        $cacheService = new CacheService;
        $cacheService->clearContentCaches($content->id);
        $cacheService->clearSeoCaches();

        // Save custom fields if provided
        if ($request->has('custom_fields')) {
            $this->saveCustomFields($content, $request->custom_fields);
        }

        // Update search index
        if ($content->status === 'published') {
            \App\Models\SearchIndex::index($content, [
                'title' => $content->title,
                'content' => strip_tags($content->body),
                'excerpt' => $content->excerpt,
                'url' => url('/content/'.$content->slug),
                'type' => $content->type,
            ]);
        } else {
            // Remove from index if not published
            \App\Models\SearchIndex::remove($content);
        }

        // Trigger webhook
        \App\Models\Webhook::triggerForEvent('content.updated', $content->toArray());

        return $this->success($content->load(['author', 'category', 'tags', 'customFields.customField']), 'Content updated successfully');
    }

    protected function saveCustomFields(Content $content, array $customFields)
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

    public function destroy(Content $content)
    {
        $contentId = $content->id;

        // Remove from search index
        \App\Models\SearchIndex::remove($content);

        $content->delete();

        // Trigger webhook before deletion
        \App\Models\Webhook::triggerForEvent('content.deleted', ['id' => $contentId]);

        // Clear caches
        $cacheService = new CacheService;
        $cacheService->clearContentCaches($contentId);
        $cacheService->clearSeoCaches();

        return $this->success(null, 'Content deleted successfully');
    }

    public function duplicate(Request $request, Content $content)
    {
        $newContent = $content->replicate();
        $newContent->title = $content->title.' (Copy)';
        $newContent->slug = $content->slug.'-copy-'.time();
        $newContent->status = 'draft';
        $newContent->author_id = $request->user()->id;
        $newContent->views = 0;
        $newContent->published_at = null;
        $newContent->save();

        // Copy tags
        if ($content->tags()->count() > 0) {
            $newContent->tags()->sync($content->tags->pluck('id'));
        }

        return $this->success($newContent->load(['author', 'category', 'tags']), 'Content duplicated successfully', 201);
    }

    public function bulkAction(Request $request)
    {
        try {
            $validated = $request->validate([
                'action' => 'required|in:publish,draft,archive,delete,change_category',
                'content_ids' => 'required|array',
                'content_ids.*' => 'exists:contents,id',
                'category_id' => 'required_if:action,change_category|exists:categories,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $contents = Content::whereIn('id', $validated['content_ids'])->get();

        foreach ($contents as $content) {
            switch ($validated['action']) {
                case 'publish':
                    $content->update(['status' => 'published', 'published_at' => now()]);
                    break;
                case 'draft':
                    $content->update(['status' => 'draft']);
                    break;
                case 'archive':
                    $content->update(['status' => 'archived']);
                    break;
                case 'delete':
                    $content->delete();
                    break;
                case 'change_category':
                    $content->update(['category_id' => $validated['category_id']]);
                    break;
            }
        }

        return $this->success([
            'affected' => $contents->count(),
        ], 'Bulk action completed successfully');
    }

    public function lock(Request $request, Content $content)
    {
        // Check if already locked by another user
        if ($content->locked_by && $content->locked_by !== $request->user()->id) {
            $lockedBy = $content->lockedBy;

            return $this->error(
                'Content is currently being edited by '.$lockedBy->name,
                423,
                [],
                'CONTENT_LOCKED',
                [
                    'locked_by' => $lockedBy,
                    'locked_at' => $content->locked_at,
                ]
            );
        }

        $content->update([
            'locked_by' => $request->user()->id,
            'locked_at' => now(),
        ]);

        return $this->success([
            'locked_by' => $request->user(),
            'locked_at' => $content->locked_at,
        ], 'Content locked successfully');
    }

    public function unlock(Request $request, Content $content)
    {
        // Only unlock if locked by current user or admin
        if ($content->locked_by && $content->locked_by !== $request->user()->id) {
            if (! $request->user()->hasRole('admin')) {
                return $this->forbidden('You can only unlock content you locked');
            }
        }

        $content->update([
            'locked_by' => null,
            'locked_at' => null,
        ]);

        return $this->success(null, 'Content unlocked successfully');
    }
}
