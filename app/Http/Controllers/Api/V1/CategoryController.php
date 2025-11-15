<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Category;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends BaseApiController
{
    public function index(Request $request)
    {
        $query = Category::where('is_active', true);

        // Get tree structure if requested
        if ($request->has('tree') && $request->tree) {
            $categories = Cache::remember('categories_tree', now()->addHours(24), function () {
                return Category::whereNull('parent_id')
                    ->where('is_active', true)
                    ->with(['children' => function ($q) {
                        $q->where('is_active', true)->orderBy('sort_order');
                    }])
                    ->orderBy('sort_order')
                    ->get();
            });

            return $this->success($categories, 'Categories tree retrieved successfully');
        }

        // Get flat list with parent info
        $categories = $query->with('parent')
            ->orderBy('sort_order')
            ->get();

        return $this->success($categories, 'Categories retrieved successfully');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:categories,slug',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'is_active' => 'boolean',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'integer|min:0',
        ]);

        // Prevent circular reference
        if ($validated['parent_id']) {
            $parent = Category::find($validated['parent_id']);
            if ($parent && $parent->parent_id == $request->input('id')) {
                return $this->validationError(['parent_id' => ['Cannot set parent to a child category']], 'Cannot set parent to a child category');
            }
        }

        $category = Category::create($validated);

        // Clear caches
        $cacheService = new CacheService;
        $cacheService->clearCategoryCaches();
        $cacheService->clearSeoCaches();

        return $this->success($category->load('parent'), 'Category created successfully', 201);
    }

    public function show(Category $category)
    {
        return $this->success($category->load(['parent', 'children', 'contents']), 'Category retrieved successfully');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:categories,slug,'.$category->id,
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'is_active' => 'boolean',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'integer|min:0',
        ]);

        // Prevent setting self as parent
        if (isset($validated['parent_id']) && $validated['parent_id'] == $category->id) {
            return $this->validationError(['parent_id' => ['Category cannot be its own parent']], 'Category cannot be its own parent');
        }

        // Prevent circular reference
        if (isset($validated['parent_id']) && $validated['parent_id']) {
            $parent = Category::find($validated['parent_id']);
            if ($parent) {
                // Check if parent is a descendant of this category
                $checkParent = $parent;
                while ($checkParent->parent_id) {
                    if ($checkParent->parent_id == $category->id) {
                        return $this->validationError(['parent_id' => ['Cannot set parent to a child category']], 'Cannot set parent to a child category');
                    }
                    $checkParent = Category::find($checkParent->parent_id);
                }
            }
        }

        $category->update($validated);

        return $this->success($category->load(['parent', 'children']), 'Category updated successfully');
    }

    public function destroy(Category $category)
    {
        // Check if has children
        if ($category->children()->count() > 0) {
            return $this->validationError([
                'category' => ['Cannot delete category with child categories'],
                'children_count' => $category->children()->count(),
            ], 'Cannot delete category with child categories');
        }

        // Check if has contents
        if ($category->contents()->count() > 0) {
            return $this->validationError([
                'category' => ['Cannot delete category with associated contents'],
                'contents_count' => $category->contents()->count(),
            ], 'Cannot delete category with associated contents');
        }

        $categoryId = $category->id;
        $category->delete();

        // Clear caches
        $cacheService = new CacheService;
        $cacheService->clearCategoryCaches($categoryId);
        $cacheService->clearSeoCaches();

        return $this->success(null, 'Category deleted successfully');
    }

    public function move(Request $request, Category $category)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'integer|min:0',
        ]);

        // Prevent setting self as parent
        if (isset($validated['parent_id']) && $validated['parent_id'] == $category->id) {
            return $this->validationError(['parent_id' => ['Category cannot be its own parent']], 'Category cannot be its own parent');
        }

        // Prevent circular reference
        if (isset($validated['parent_id']) && $validated['parent_id']) {
            $parent = Category::find($validated['parent_id']);
            if ($parent) {
                $checkParent = $parent;
                while ($checkParent->parent_id) {
                    if ($checkParent->parent_id == $category->id) {
                        return $this->validationError(['parent_id' => ['Cannot set parent to a child category']], 'Cannot set parent to a child category');
                    }
                    $checkParent = Category::find($checkParent->parent_id);
                }
            }
        }

        $category->update($validated);

        return $this->success($category->load(['parent', 'children']), 'Category moved successfully');
    }
}
