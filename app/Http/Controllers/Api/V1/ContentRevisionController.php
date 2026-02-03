<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Content;
use App\Models\ContentRevision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContentRevisionController extends BaseApiController
{
    public function index(Content $content)
    {
        try {
            $revisions = $content->revisions()->with('user')->latest()->paginate(20);

            return $this->paginated($revisions, 'Content revisions retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Content revisions index error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'content_id' => $content->id,
            ]);

            // Return empty paginated response instead of error
            return $this->paginated(
                \Illuminate\Pagination\LengthAwarePaginator::make([], 0, 20),
                'Content revisions retrieved successfully'
            );
        }
    }

    public function show(Content $content, ContentRevision $revision)
    {
        if ($revision->content_id !== $content->id) {
            return $this->notFound('Revision');
        }

        return $this->success($revision->load('user'), 'Content revision retrieved successfully');
    }

    public function store(Request $request, Content $content)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
            'note' => 'nullable|string|max:500', // Legacy support
        ]);

        $reason = $validated['reason'] ?? $validated['note'] ?? 'Auto-saved revision';

        // Prepare standard revision metadata
        $meta = $content->meta ?? [];
        $meta['revision_data'] = [
            'excerpt' => $content->excerpt,
            'slug' => $content->slug,
            'status' => $content->status,
        ];

        // Create revision from current content state
        $revision = ContentRevision::create([
            'content_id' => $content->id,
            'author_id' => $request->user()->id,
            'title' => $content->title,
            'body' => $content->body,
            'blocks' => $content->blocks,
            'meta' => $meta,
            'reason' => $reason,
        ]);

        return $this->success($revision->load('author'), 'Content revision created successfully', 201);
    }

    public function restore(Request $request, Content $content, ContentRevision $revision)
    {
        if ($revision->content_id !== $content->id) {
            return $this->notFound('Revision');
        }

        // Backup current state
        $currentMeta = $content->meta ?? [];
        $currentMeta['revision_data'] = [
            'excerpt' => $content->excerpt,
            'slug' => $content->slug,
            'status' => $content->status,
        ];

        ContentRevision::create([
            'content_id' => $content->id,
            'author_id' => $request->user()->id,
            'title' => $content->title,
            'body' => $content->body,
            'blocks' => $content->blocks,
            'meta' => $currentMeta,
            'reason' => 'Backup before restore',
        ]);

        // Restore content from revision
        $revisionData = $revision->meta['revision_data'] ?? [];
        
        $content->update([
            'title' => $revision->title,
            'body' => $revision->body,
            'blocks' => $revision->blocks,
            'excerpt' => $revisionData['excerpt'] ?? $content->excerpt, // Fallback to current if missing
            'slug' => $revisionData['slug'] ?? $content->slug,
            'meta' => $revision->meta, // This might overwrite revision_data into content meta, which is fine
            'status' => $revisionData['status'] ?? 'draft', // Safe default
        ]);

        return $this->success([
            'content' => $content->load(['author', 'category', 'tags']),
        ], 'Content restored successfully');
    }

    public function destroy(Content $content, ContentRevision $revision)
    {
        if ($revision->content_id !== $content->id) {
            return $this->notFound('Revision');
        }

        $revision->delete();

        return $this->success(null, 'Revision deleted successfully');
    }
}
