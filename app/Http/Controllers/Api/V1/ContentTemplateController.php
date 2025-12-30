<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ContentTemplate;
use Illuminate\Http\Request;

class ContentTemplateController extends BaseApiController
{
    public function index(Request $request)
    {
        $query = ContentTemplate::with('category');
        
        // Scope logic
        if ($request->user() && !$request->user()->can('manage templates')) {
            $query->where(function($q) use ($request) {
                 $q->whereNull('author_id')->orWhere('author_id', $request->user()->id);
            });
        } elseif (!$request->user()) {
             $query->whereNull('author_id');
        }

        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $perPage = $request->input('per_page', 10);
        $templates = $query->latest()->paginate($perPage);

        return $this->success($templates, 'Content templates retrieved successfully');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:content_templates,id',
        ]);

        $action = $request->input('action');
        $ids = $request->input('ids');
        
        $query = ContentTemplate::whereIn('id', $ids);
        
        // Scope
        if (!$request->user()->can('manage templates')) {
             $query->where('author_id', $request->user()->id);
        }

        $count = 0;

        if ($action === 'delete') {
            $count = $query->delete();
        }

        return $this->success(null, "Bulk action {$action} completed for {$count} templates");
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:content_templates,slug',
            'description' => 'nullable|string',
            'type' => 'required|in:post,page,custom',
            'title_template' => 'nullable|string',
            'body_template' => 'required|string',
            'excerpt_template' => 'nullable|string',
            'default_fields' => 'nullable|array',
            'meta' => 'nullable|array',
            'category_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
            'author_id' => 'nullable|exists:users,id',
        ]);
        
        // Author Assignment
        if ($request->user()->can('manage templates')) {
            // Admin can assign
        } else {
            $validated['author_id'] = $request->user()->id;
        }

        $template = ContentTemplate::create($validated);

        return $this->success($template->load('category'), 'Content template created successfully', 201);
    }

    public function show(ContentTemplate $contentTemplate)
    {
        // Permission/Ownership check?
        // Usually viewing templates is fine if they are visible in index?
        // But let's enforce consistency.
        if (request()->user() && !request()->user()->can('manage templates')) {
             if ($contentTemplate->author_id && $contentTemplate->author_id !== request()->user()->id) {
                 return $this->forbidden('You do not have permission to view this template');
             }
        }
        
        return $this->success($contentTemplate->load('category'), 'Content template retrieved successfully');
    }

    public function update(Request $request, ContentTemplate $contentTemplate)
    {
        // Ownership check
        if (!$request->user()->can('manage templates')) {
             if ($contentTemplate->author_id && $contentTemplate->author_id !== $request->user()->id) {
                 return $this->forbidden('You do not have permission to update this template');
             }
             if (is_null($contentTemplate->author_id)) {
                 return $this->forbidden('You cannot update global templates');
             }
             unset($request['author_id']);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:content_templates,slug,'.$contentTemplate->id,
            'description' => 'nullable|string',
            'type' => 'sometimes|required|in:post,page,custom',
            'title_template' => 'nullable|string',
            'body_template' => 'sometimes|required|string',
            'excerpt_template' => 'nullable|string',
            'default_fields' => 'nullable|array',
            'meta' => 'nullable|array',
            'category_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
            'author_id' => 'nullable|exists:users,id',
        ]);

        $contentTemplate->update($validated);

        return $this->success($contentTemplate->load('category'), 'Content template updated successfully');
    }

    public function destroy(ContentTemplate $contentTemplate)
    {
        // Ownership check
        if (!request()->user()->can('manage templates')) {
             if ($contentTemplate->author_id && $contentTemplate->author_id !== request()->user()->id) {
                 return $this->forbidden('You do not have permission to delete this template');
             }
             if (is_null($contentTemplate->author_id)) {
                 return $this->forbidden('You cannot delete global templates');
             }
        }

        $contentTemplate->delete();

        return $this->success(null, 'Content template deleted successfully');
    }

    public function createContent(Request $request, ContentTemplate $contentTemplate)
    {
        // Scope?
        if (request()->user() && !request()->user()->can('manage templates')) {
             if ($contentTemplate->author_id && $contentTemplate->author_id !== request()->user()->id) {
                 // But wait, can I use a template if it is Global? YES.
                 // So only forbidden if it's someone ELSE'S private template.
                 // Global (null) is OK.
                 return $this->forbidden('You do not have permission to use this template');
             }
             // Actually, index logic allows seeing Global or Own.
             // So if I can see it, I can use it?
             // Yes.
        }
        
        $data = $request->input('data', []);
        $data['author_id'] = $request->user()->id;

        $content = $contentTemplate->createContent($data);

        return $this->success($content->load(['author', 'category', 'tags']), 'Content created from template successfully', 201);
    }
}
