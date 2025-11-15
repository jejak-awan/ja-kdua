<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\ContentRevision;
use Illuminate\Http\Request;

class ContentRevisionController extends Controller
{
    public function index(Content $content)
    {
        $revisions = $content->revisions()->with('user')->latest()->paginate(20);

        return response()->json($revisions);
    }

    public function show(Content $content, ContentRevision $revision)
    {
        if ($revision->content_id !== $content->id) {
            return response()->json(['message' => 'Revision not found'], 404);
        }

        return response()->json($revision->load('user'));
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

        return response()->json($revision->load('user'), 201);
    }

    public function restore(Request $request, Content $content, ContentRevision $revision)
    {
        if ($revision->content_id !== $content->id) {
            return response()->json(['message' => 'Revision not found'], 404);
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

        return response()->json([
            'message' => 'Content restored successfully',
            'content' => $content->load(['author', 'category', 'tags']),
        ]);
    }

    public function destroy(Content $content, ContentRevision $revision)
    {
        if ($revision->content_id !== $content->id) {
            return response()->json(['message' => 'Revision not found'], 404);
        }

        $revision->delete();

        return response()->json(['message' => 'Revision deleted successfully']);
    }
}
