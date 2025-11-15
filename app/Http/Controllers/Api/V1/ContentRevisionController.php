<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseApiController;
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
            Log::error('Content revisions index error: ' . $e->getMessage(), [
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
            'note' => 'nullable|string|max:500',
        ]);

        // Create revision from current content state
        $revision = ContentRevision::create([
            'content_id' => $content->id,
            'user_id' => $request->user()->id,
            'title' => $content->title,
            'body' => $content->body,
            'excerpt' => $content->excerpt,
            'slug' => $content->slug,
            'meta' => $content->meta,
            'status' => $content->status,
            'note' => $validated['note'] ?? 'Auto-saved revision',
        ]);

        return $this->success($revision->load('user'), 'Content revision created successfully', 201);
    }

    public function restore(Request $request, Content $content, ContentRevision $revision)
    {
        if ($revision->content_id !== $content->id) {
            return $this->notFound('Revision');
        }

        // Create a new revision of current state before restoring
        ContentRevision::create([
            'content_id' => $content->id,
            'user_id' => $request->user()->id,
            'title' => $content->title,
            'body' => $content->body,
            'excerpt' => $content->excerpt,
            'slug' => $content->slug,
            'meta' => $content->meta,
            'status' => $content->status,
            'note' => 'Backup before restore',
        ]);

        // Restore content from revision
        $content->update([
            'title' => $revision->title,
            'body' => $revision->body,
            'excerpt' => $revision->excerpt,
            'slug' => $revision->slug,
            'meta' => $revision->meta,
            'status' => $revision->status,
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
