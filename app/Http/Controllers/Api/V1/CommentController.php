<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\Comment;
use App\Models\Content;
use Illuminate\Http\Request;

class CommentController extends BaseApiController
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

        return $this->success($comments, 'Comments retrieved successfully');
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

        return $this->success($comment->load('user'), 'Comment created successfully', 201);
    }

    public function adminIndex(Request $request)
    {
        $query = Comment::with(['content', 'user', 'parent']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $comments = $query->latest()->paginate(20);

        return $this->paginated($comments, 'Comments retrieved successfully');
    }

    public function approve(Comment $comment)
    {
        $comment->update(['status' => 'approved']);

        return $this->success($comment->load(['content', 'user']), 'Comment approved successfully');
    }

    public function reject(Comment $comment)
    {
        $comment->update(['status' => 'rejected']);

        return $this->success($comment->load(['content', 'user']), 'Comment rejected successfully');
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();

        return $this->success(null, 'Comment deleted successfully');
    }
}
