<?php

namespace App\Http\Controllers\Api\V1;

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
        try {
            $validated = $request->validate([
                'body' => 'required|string',
                'name' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255',
                'parent_id' => 'nullable|exists:comments,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

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

        // Multi-tenancy: Authors only see comments on their own content
        if (! $request->user()->can('manage comments')) {
            $query->whereHas('content', function ($q) use ($request) {
                $q->where('author_id', $request->user()->id);
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('content_id')) {
            $query->where('content_id', $request->content_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('body', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = min($request->input('per_page', 10), 100); // Max 100 per page
        $comments = $query->latest()->paginate($perPage);

        return $this->paginated($comments, 'Comments retrieved successfully');
    }

    public function statistics(Request $request)
    {
        $query = Comment::query();

        // Multi-tenancy scoping
        if (! $request->user()->can('manage comments')) {
            $query->whereHas('content', function ($q) use ($request) {
                $q->where('author_id', $request->user()->id);
            });
        }

        $stats = [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'approved' => (clone $query)->where('status', 'approved')->count(),
            'rejected' => (clone $query)->where('status', 'rejected')->count(),
            'spam' => (clone $query)->where('status', 'spam')->count(),
            'today' => (clone $query)->whereDate('created_at', today())->count(),
            'this_week' => (clone $query)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];

        return $this->success($stats, 'Comment statistics retrieved successfully');
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

    public function markAsSpam(Comment $comment)
    {
        $comment->update(['status' => 'spam']);

        return $this->success($comment->load(['content', 'user']), 'Comment marked as spam');
    }

    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:comments,id',
            'action' => 'required|in:approve,reject,spam,delete',
        ]);

        $count = count($validated['ids']);

        switch ($validated['action']) {
            case 'approve':
                Comment::whereIn('id', $validated['ids'])->update(['status' => 'approved']);
                $message = "{$count} comments approved";
                break;

            case 'reject':
                Comment::whereIn('id', $validated['ids'])->update(['status' => 'rejected']);
                $message = "{$count} comments rejected";
                break;

            case 'spam':
                Comment::whereIn('id', $validated['ids'])->update(['status' => 'spam']);
                $message = "{$count} comments marked as spam";
                break;

            case 'delete':
                Comment::whereIn('id', $validated['ids'])->delete();
                $message = "{$count} comments deleted";
                break;

            default:
                return $this->error('Invalid action', 400);
        }

        return $this->success(['affected' => $count], $message);
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();

        return $this->success(null, 'Comment deleted successfully');
    }
}
