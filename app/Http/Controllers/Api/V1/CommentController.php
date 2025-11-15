<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Content;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Content $content)
    {
        $comments = Comment::with(['user', 'replies' => function ($q) {
            $q->where('status', 'approved')->with('user');
        }])
            ->where('content_id', $content->id)
            ->whereNull('parent_id')
            ->where('status', 'approved')
            ->latest()
            ->get();

        return response()->json($comments);
    }

    public function store(Request $request, Content $content)
    {
        $validated = $request->validate([
            'body' => 'required|string',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        // If user is authenticated, use user data
        if ($request->user()) {
            $validated['user_id'] = $request->user()->id;
        } else {
            // For guest comments, name and email are required
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
            ]);
        }

        $validated['content_id'] = $content->id;
        $validated['status'] = 'pending'; // All comments need approval

        $comment = Comment::create($validated);

        return response()->json($comment->load('user'), 201);
    }

    public function adminIndex(Request $request)
    {
        $query = Comment::with(['content', 'user', 'parent']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $comments = $query->latest()->paginate(20);

        return response()->json($comments);
    }

    public function approve(Comment $comment)
    {
        $comment->update(['status' => 'approved']);

        return response()->json($comment->load(['content', 'user']));
    }

    public function reject(Comment $comment)
    {
        $comment->update(['status' => 'rejected']);

        return response()->json($comment->load(['content', 'user']));
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully']);
    }
}
