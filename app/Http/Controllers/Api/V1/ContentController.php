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

    public function show(\Illuminate\Http\Request $request, $slug)
    {
        $content = Content::with(['author', 'category', 'tags', 'menuItems.menu', 'comments' => function ($q) {
            $q->where('status', 'approved')->latest();
        }])
            ->where('slug', $slug)
            ->firstOrFail();

        // Check if content is published
        $isPublished = $content->status === 'published' && 
                       ($content->published_at === null || $content->published_at <= Carbon::now());

        // If not published, verify permissions
        if (!$isPublished) {
            $user = auth('sanctum')->user();
            
            if (!$user) {
                abort(404);
            }
            
            // Allow if user has manage/edit permission or is the author
            if (!$user->can('manage content') && !$user->can('edit content') && $user->id !== $content->author_id) {
                abort(404);
            }
        }

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

        // Multi-tenancy scoping
        if (!$request->user()->can('manage content') && !$request->user()->can('publish content')) {
            $query->where('author_id', $request->user()->id);
        }

        if ($request->has('status') && $request->status !== 'all') {
            if ($request->status === 'trashed') {
                $query->onlyTrashed();
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
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
        $content->load(['author', 'category', 'tags', 'allComments', 'customFields.customField.fieldGroup', 'lockedBy']);

        // Check if lock is active (e.g., within last 60 minutes)
        $isLocked = $content->locked_by !== null && $content->locked_at && $content->locked_at->diffInMinutes(now()) < 60;

        $content->lock_status = [
            'is_locked' => $isLocked,
            'locked_by' => $content->lockedBy,
            'locked_at' => $content->locked_at,
            'can_unlock' => request()->user()->id === $content->locked_by || request()->user()->can('manage content'),
        ];

        return $this->success($content, 'Content retrieved successfully');
    }

    public function stats(Request $request)
    {
        if (!$request->user()->can('view content')) {
            return $this->forbidden('Unauthorized to view content statistics');
        }

        $query = Content::query();
        
        // Scope stats if not a content manager
        if (!$request->user()->can('manage content')) {
            $query->where('author_id', $request->user()->id);
        }

        $stats = [
            'total' => (clone $query)->count(),
            'published' => (clone $query)->where('status', 'published')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'draft' => (clone $query)->where('status', 'draft')->count(),
            'archived' => (clone $query)->where('status', 'archived')->count(),
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
                'body' => 'required_without:blocks|string',
                'featured_image' => 'nullable|string',
                'status' => 'required|in:draft,pending,published,archived',
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
                'blocks' => 'nullable|array',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        // Approval Workflow: Authors cannot publish directly
        if (!$request->user()->can('publish content')) {
            if ($validated['status'] === 'published') {
                $validated['status'] = 'pending';
            }
        }

        $createRevision = $request->input('create_revision', false);

        $content = $this->contentService->create($validated, $request->user()->id, $createRevision);

        return $this->success($content->load(['author', 'category', 'tags', 'customFields.customField']), 'Content created successfully', 201);
    }

    public function update(Request $request, Content $content)
    {
        // Check if content is locked by another user
        if ($this->contentService->isLockedByOther($content, $request->user()->id)) {
            // Allow Admins and Super Admins to bypass the lock
            if (!$request->user()->hasRole('super-admin') && !$request->user()->hasRole('admin')) {
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
        }

        try {
            $rules = [
                'title' => 'sometimes|required|string|max:255',
                'slug' => 'sometimes|required|string|unique:contents,slug,'.$content->id,
                'excerpt' => 'nullable|string',
                'body' => 'nullable|string', // Allow null body for drafts
                'featured_image' => 'nullable|string',
                'status' => 'sometimes|required|in:draft,pending,published,archived',
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
                'blocks' => 'nullable|array',
            ];

            // If publishing, require body OR blocks
            if ($request->input('status') === 'published' || ($request->input('status') === null && $content->status === 'published')) {
               $rules['body'] = 'required_without:blocks|string';
            }

            $validated = $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('Content update validation failed', ['errors' => $e->errors(), 'input' => $request->all()]);
            return $this->validationError($e->errors());
        }

        // Ownership check
        if (!$request->user()->can('manage content') && !$request->user()->can('publish content')) {
             if ($content->author_id !== $request->user()->id) {
                 return $this->forbidden('You can only update your own content');
             }
        }

        // Approval Workflow: Authors cannot publish directly
        if (isset($validated['status']) && $validated['status'] === 'published') {
            if (!$request->user()->can('publish content')) {
                $validated['status'] = 'pending';
            }
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

        // Handle Menu Item Logic
        if ($request->has('menu_item')) {
            $menuData = $request->input('menu_item');
            if (!empty($menuData['add_to_menu']) && !empty($menuData['menu_id'])) {
                // Determine title: use provided label or content title
                $menuTitle = !empty($menuData['title']) ? $menuData['title'] : $content->title;
                
                // Check if already exists in this menu
                $menuItem = \App\Models\MenuItem::where('menu_id', $menuData['menu_id'])
                    ->where('target_id', $content->id)
                    ->where('target_type', get_class($content))
                    ->first();

                if ($menuItem) {
                    $menuItem->update([
                        'title' => $menuTitle,
                        'parent_id' => $menuData['parent_id'] ?? null,
                    ]);
                } else {
                    \App\Models\MenuItem::create([
                        'menu_id' => $menuData['menu_id'],
                        'title' => $menuTitle,
                        'target_id' => $content->id,
                        'target_type' => get_class($content),
                        'type' => $content->type === 'post' ? 'post' : 'page', // Map content type to menu item type
                        'parent_id' => $menuData['parent_id'] ?? null,
                        'url' => null, // Dynamic
                        'order' => 99, // Append to end
                    ]);
                }
            }
        }

        return $this->success($content->load(['author', 'category', 'tags', 'customFields.customField', 'menuItems.menu']), 'Content updated successfully');
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

    public function approve(Request $request, Content $content)
    {
        if (!$request->user()->can('approve content')) {
            return $this->forbidden('You do not have permission to approve content');
        }

        if ($content->status !== 'pending') {
            return $this->error('Only pending content can be approved', 400);
        }

        $content->update([
            'status' => 'published',
            'published_at' => $content->published_at ?? now(),
        ]);

        // Trigger webhook or notification?
        // ...

        app(CacheService::class)->clearContentCaches($content->id);

        return $this->success($content->load('author'), 'Content approved and published successfully');
    }

    public function reject(Request $request, Content $content)
    {
        if (!$request->user()->can('approve content')) {
            return $this->forbidden('You do not have permission to reject content');
        }

        if ($content->status !== 'pending') {
            return $this->error('Only pending content can be rejected', 400);
        }

        $content->update([
            'status' => 'draft',
        ]);

        return $this->success($content->load('author'), 'Content rejected and moved back to drafts');
    }

    public function bulkAction(Request $request)
    {
        try {
            $validated = $request->validate([
                'action' => 'required|in:publish,approve,reject,draft,archive,delete,change_category',
                'content_ids' => 'required|array',
                'content_ids.*' => 'exists:contents,id',
                'category_id' => 'required_if:action,change_category|exists:categories,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        // Ownership and permission check for bulk actions
        $query = Content::whereIn('id', $validated['content_ids']);
        if (!$request->user()->can('manage content') && !$request->user()->can('publish content')) {
             $query->where('author_id', $request->user()->id);
             
             // If author, they can't 'publish' or 'approve' or 'reject'
             if (in_array($validated['action'], ['publish', 'approve', 'reject'])) {
                 return $this->forbidden('You do not have permission to perform this action');
             }
        }

        $contentIds = $query->pluck('id')->toArray();

        $affected = $this->contentService->bulkAction(
            $validated['action'],
            $contentIds,
            $validated['category_id'] ?? null
        );

        return $this->success(['affected' => $affected], 'Bulk action completed successfully');
    }

    public function lock(Request $request, Content $content)
    {
        // ... existing lock code ...
        if ($this->contentService->isLockedByOther($content, $request->user()->id)) {
            // Allow Admins/Super Admins to steal the lock
            if (!$request->user()->hasRole('super-admin') && !$request->user()->hasRole('admin')) {
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

    public function restore(Request $request, $id)
    {
        if (!$request->user()->can('delete content')) {
             return $this->forbidden('You do not have permission to restore content');
        }

        $this->contentService->restore($id);

        return $this->success(null, 'Content restored successfully');
    }

    public function forceDelete(Request $request, $id)
    {
        if (!$request->user()->can('delete content') || !$request->user()->can('manage content')) {
             return $this->forbidden('You do not have permission to permanently delete content');
        }

        $this->contentService->forceDelete($id);

        return $this->success(null, 'Content permanently deleted');
    }
}
