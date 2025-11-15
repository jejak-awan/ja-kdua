<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\ContentRevision;
use App\Models\ContentCustomField;
use App\Models\MediaUsage;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ContentController extends Controller
{
    public function index(Request $request)
    {
        $cacheKey = 'contents_published_' . md5($request->getQueryString());
        
        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($request) {
            $query = Content::with(['author', 'category', 'tags'])
                ->where('status', 'published')
                ->where(function ($q) {
                    $q->whereNull('published_at')
                      ->orWhere('published_at', '<=', Carbon::now());
                });

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

            $contents = $query->latest('published_at')->paginate(12);

            return response()->json($contents);
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

        return response()->json($content);
    }

    public function preview(Request $request, Content $content)
    {
        // Allow preview for draft content if user is the author or admin
        if ($content->status === 'draft' && $content->author_id !== $request->user()->id) {
            if (!$request->user()->hasRole('admin')) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        return response()->json($content->load(['author', 'category', 'tags', 'customFields.customField']));
    }

    public function adminIndex(Request $request)
    {
        $query = Content::with(['author', 'category', 'tags']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $contents = $query->latest()->paginate(12);

        return response()->json($contents);
    }

    public function adminShow(Content $content)
    {
        return response()->json($content->load(['author', 'category', 'tags', 'allComments', 'customFields.customField.fieldGroup']));
    }

    public function store(Request $request)
    {
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
        $cacheService = new CacheService();
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
                'url' => url('/content/' . $content->slug),
                'type' => $content->type,
            ]);
        }

        // Trigger webhook
        \App\Models\Webhook::triggerForEvent('content.created', $content->toArray());

        return response()->json($content->load(['author', 'category', 'tags', 'customFields.customField']), 201);
    }

    protected function trackMediaUsage(Content $content, $fieldName)
    {
        $imagePath = $content->$fieldName;
        if (!$imagePath) return;

        // Extract media ID from path if stored in database
        // For now, we'll track by path pattern
        // This can be enhanced to extract actual media ID
    }

    public function update(Request $request, Content $content)
    {
        // Check if content is locked by another user
        if ($content->locked_by && $content->locked_by !== $request->user()->id) {
            $lockedBy = $content->lockedBy;
            return response()->json([
                'message' => 'Content is currently being edited by ' . $lockedBy->name,
                'locked_by' => $lockedBy,
                'locked_at' => $content->locked_at,
            ], 423); // 423 Locked
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:contents,slug,' . $content->id,
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
        $cacheService = new CacheService();
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
                'url' => url('/content/' . $content->slug),
                'type' => $content->type,
            ]);
        } else {
            // Remove from index if not published
            \App\Models\SearchIndex::remove($content);
        }

        // Trigger webhook
        \App\Models\Webhook::triggerForEvent('content.updated', $content->toArray());

        return response()->json($content->load(['author', 'category', 'tags', 'customFields.customField']));
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
        $cacheService = new CacheService();
        $cacheService->clearContentCaches($contentId);
        $cacheService->clearSeoCaches();

        return response()->json(['message' => 'Content deleted successfully']);
    }

    public function duplicate(Request $request, Content $content)
    {
        $newContent = $content->replicate();
        $newContent->title = $content->title . ' (Copy)';
        $newContent->slug = $content->slug . '-copy-' . time();
        $newContent->status = 'draft';
        $newContent->author_id = $request->user()->id;
        $newContent->views = 0;
        $newContent->published_at = null;
        $newContent->save();

        // Copy tags
        if ($content->tags()->count() > 0) {
            $newContent->tags()->sync($content->tags->pluck('id'));
        }

        return response()->json($newContent->load(['author', 'category', 'tags']), 201);
    }

    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:publish,draft,archive,delete,change_category',
            'content_ids' => 'required|array',
            'content_ids.*' => 'exists:contents,id',
            'category_id' => 'required_if:action,change_category|exists:categories,id',
        ]);

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

        return response()->json([
            'message' => 'Bulk action completed successfully',
            'affected' => $contents->count(),
        ]);
    }

    public function lock(Request $request, Content $content)
    {
        // Check if already locked by another user
        if ($content->locked_by && $content->locked_by !== $request->user()->id) {
            $lockedBy = $content->lockedBy;
            return response()->json([
                'message' => 'Content is currently being edited by ' . $lockedBy->name,
                'locked_by' => $lockedBy,
                'locked_at' => $content->locked_at,
            ], 423); // 423 Locked
        }

        $content->update([
            'locked_by' => $request->user()->id,
            'locked_at' => now(),
        ]);

        return response()->json([
            'message' => 'Content locked successfully',
            'locked_by' => $request->user(),
            'locked_at' => $content->locked_at,
        ]);
    }

    public function unlock(Request $request, Content $content)
    {
        // Only unlock if locked by current user or admin
        if ($content->locked_by && $content->locked_by !== $request->user()->id) {
            if (!$request->user()->hasRole('admin')) {
                return response()->json(['message' => 'You can only unlock content you locked'], 403);
            }
        }

        $content->update([
            'locked_by' => null,
            'locked_at' => null,
        ]);

        return response()->json(['message' => 'Content unlocked successfully']);
    }
}
