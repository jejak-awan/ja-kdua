<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Content;
use App\Models\ContentCustomField;
use App\Models\ContentRevision;
use App\Services\CacheService;
use App\Services\ContentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ContentController extends BaseApiController
{
    protected ContentService $contentService;

    public function __construct()
    {
        $this->contentService = new ContentService();
    }

    public function index(Request $request)
    {
        $result = $this->contentService->getPublishedContents($request);

        if ($result['paginated']) {
            return $this->paginated($result['data'], 'Contents retrieved successfully');
        }

        return $this->success($result['data'], 'Contents retrieved successfully');
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
        $related = $this->contentService->getRelatedContent($slug);

        return $this->success($related, 'Related content retrieved successfully');
    }

    public function preview(Request $request, Content $content)
    {
        // Allow preview for draft content if user is the author or admin
        if ($content->status === 'draft' && $content->author_id !== $request->user()->id) {
            if (! $request->user()->can('manage content')) {
                return $this->forbidden('Unauthorized to preview this content');
            }
        }

        return $this->success([
            'content' => $content->load(['author', 'category', 'tags', 'customFields.customField']),
            'preview_url' => rtrim(config('app.frontend_url'), '/') . '/' . ltrim($content->slug, '/'),
        ], 'Content preview retrieved successfully');
    }

    public function adminIndex(Request $request)
    {
        $query = Content::with(['author', 'category', 'tags']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Create a limit for per_page to prevent abuse, e.g., max 100
        $perPage = (int) $request->input('per_page', 12);
        if ($perPage <= 0 || $perPage > 100) {
            $perPage = 12;
        }

        $contents = $query->latest()->paginate($perPage);

        return $this->paginated($contents, 'Contents retrieved successfully');
    }

    public function adminShow(Content $content)
    {
        return $this->success($content->load(['author', 'category', 'tags', 'allComments', 'customFields.customField.fieldGroup']), 'Content retrieved successfully');
    }

    public function stats(Request $request)
    {
        if (!$request->user()->can('manage content')) {
            return $this->forbidden('Unauthorized to view content statistics');
        }

        $stats = [
            'total' => Content::count(),
            'published' => Content::where('status', 'published')->count(),
            'draft' => Content::where('status', 'draft')->count(),
            'archived' => Content::where('status', 'archived')->count(),
        ];

        return $this->success($stats, 'Content statistics retrieved successfully');
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
                'custom_fields' => 'nullable|array',
                'is_featured' => 'boolean',
                'new_tags' => 'nullable|array',
                'new_tags.*' => 'string|max:50',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $createRevision = $request->input('create_revision', false);

        $content = $this->contentService->create($validated, $request->user()->id, $createRevision);

        return $this->success($content->load(['author', 'category', 'tags', 'customFields.customField']), 'Content created successfully', 201);
    }

    public function update(Request $request, Content $content)
    {
        // Check if content is locked by another user
        if ($this->contentService->isLockedByOther($content, $request->user()->id)) {
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
            $rules = [
                'title' => 'sometimes|required|string|max:255',
                'slug' => 'sometimes|required|string|unique:contents,slug,'.$content->id,
                'excerpt' => 'nullable|string',
                'body' => 'nullable|string', // Allow null body for drafts
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
                'custom_fields' => 'nullable|array',
                'is_featured' => 'boolean',
                'new_tags' => 'nullable|array',
                'new_tags.*' => 'string|max:50',
            ];

            // If publishing, require body
            if ($request->input('status') === 'published' || ($request->input('status') === null && $content->status === 'published')) {
               $rules['body'] = 'required|string';
            }

            $validated = $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('Content update validation failed', ['errors' => $e->errors(), 'input' => $request->all()]);
            return $this->validationError($e->errors());
        }

        $createRevision = $request->input('create_revision', false);
        $revisionNote = $request->input('revision_note');

        $content = $this->contentService->update(
            $content, 
            $validated, 
            $request->user()->id, 
            $createRevision, 
            $revisionNote
        );

        return $this->success($content->load(['author', 'category', 'tags', 'customFields.customField']), 'Content updated successfully');
    }

    public function toggleFeatured(Request $request, Content $content)
    {
        $isFeatured = $this->contentService->toggleFeatured($content);
        
        return $this->success(['is_featured' => $isFeatured], 'Content featured status updated');
    }

    /**
     * Auto-save draft (lightweight save without revisions, webhooks, or search indexing)
     */
    public function autosave(Request $request, Content $content = null)
    {
        try {
            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'slug' => 'sometimes|string',
                'excerpt' => 'nullable|string',
                'body' => 'nullable|string',
                'featured_image' => 'nullable|string',
                'type' => 'sometimes|in:post,page,custom',
                'category_id' => 'nullable|exists:categories,id',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:tags,id',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'meta_keywords' => 'nullable|string|max:255',
                'og_image' => 'nullable|string',
                'custom_fields' => 'nullable|array',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        // Force status to draft for auto-save
        $validated['status'] = 'draft';

        if ($content) {
            // Update existing content
            // Check if content is locked by another user
             if ($this->contentService->isLockedByOther($content, $request->user()->id)) {
                return $this->error('Content is currently being edited by another user', 423);
            }

            // Validate slug uniqueness if changed
            if (isset($validated['slug']) && $validated['slug'] !== $content->slug) {
                $exists = Content::where('slug', $validated['slug'])
                    ->where('id', '!=', $content->id)
                    ->exists();
                if ($exists) {
                    unset($validated['slug']); // Don't update slug if conflict
                }
            }

            // Use service update but without revisions
            $this->contentService->update($content, $validated, $request->user()->id, false);

            return $this->success([
                'id' => $content->id,
                'saved_at' => $content->updated_at,
            ], 'Draft auto-saved successfully');
        } else {
            // Create new draft
            if (!isset($validated['title']) || empty($validated['title'])) {
                return $this->error('Title is required for auto-save', 422);
            }

            // Generate slug if not provided
            if (!isset($validated['slug']) || empty($validated['slug'])) {
                $validated['slug'] = \Illuminate\Support\Str::slug($validated['title']);
            }
            
            // Ensure slug is unique
            $validated['slug'] = $this->contentService->generateUniqueSlug($validated['slug']);

            // Use service create
            $content = $this->contentService->create($validated, $request->user()->id, false);

            return $this->success([
                'id' => $content->id,
                'saved_at' => $content->created_at,
            ], 'Draft auto-saved successfully', 201);
        }
    }

    public function destroy(Content $content)
    {
        $this->contentService->delete($content);

        return $this->success(null, 'Content deleted successfully');
    }

    public function duplicate(Request $request, Content $content)
    {
        $newContent = $this->contentService->duplicate($content, $request->user()->id);

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

        $affected = $this->contentService->bulkAction(
            $validated['action'],
            $validated['content_ids'],
            $validated['category_id'] ?? null
        );

        return $this->success(['affected' => $affected], 'Bulk action completed successfully');
    }

    public function lock(Request $request, Content $content)
    {
        if ($this->contentService->isLockedByOther($content, $request->user()->id)) {
            $lockedBy = $content->lockedBy;

            return $this->error(
                'Content is currently being edited by ' . $lockedBy->name,
                423,
                [],
                'CONTENT_LOCKED',
                [
                    'locked_by' => $lockedBy,
                    'locked_at' => $content->locked_at,
                ]
            );
        }

        $this->contentService->lock($content, $request->user()->id);

        return $this->success([
            'locked_by' => $request->user(),
            'locked_at' => $content->fresh()->locked_at,
        ], 'Content locked successfully');
    }

    public function unlock(Request $request, Content $content)
    {
        if ($this->contentService->isLockedByOther($content, $request->user()->id)) {
            if (!$request->user()->can('manage content')) {
                return $this->forbidden('You can only unlock content you locked');
            }
        }

        $this->contentService->unlock($content);

        return $this->success(null, 'Content unlocked successfully');
    }
}
