<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::where('is_active', true);

        // Get tree structure if requested
        if ($request->has('tree') && $request->tree) {
            return Cache::remember('categories_tree', now()->addHours(24), function () {
                $categories = Category::whereNull('parent_id')
                    ->where('is_active', true)
                    ->with(['children' => function ($q) {
                        $q->where('is_active', true)->orderBy('sort_order');
                    }])
                    ->orderBy('sort_order')
                    ->get();
                
                return response()->json($categories);
            });
        }

        // Get flat list with parent info
        $categories = $query->with('parent')
            ->orderBy('sort_order')
            ->get();

        return response()->json($categories);
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
                return response()->json(['message' => 'Cannot set parent to a child category'], 422);
            }
        }

        $category = Category::create($validated);

        // Clear caches
        $cacheService = new CacheService();
        $cacheService->clearCategoryCaches();
        $cacheService->clearSeoCaches();

        return response()->json($category->load('parent'), 201);
    }

    public function show(Category $category)
    {
        return response()->json($category->load(['parent', 'children', 'contents']));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'is_active' => 'boolean',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'integer|min:0',
        ]);

        // Prevent setting self as parent
        if ($validated['parent_id'] == $category->id) {
            return response()->json(['message' => 'Category cannot be its own parent'], 422);
        }

        // Prevent circular reference
        if ($validated['parent_id']) {
            $parent = Category::find($validated['parent_id']);
            if ($parent) {
                // Check if parent is a descendant of this category
                $checkParent = $parent;
                while ($checkParent->parent_id) {
                    if ($checkParent->parent_id == $category->id) {
                        return response()->json(['message' => 'Cannot set parent to a child category'], 422);
                    }
                    $checkParent = Category::find($checkParent->parent_id);
                }
            }
        }

        $category->update($validated);

        return response()->json($category->load(['parent', 'children']));
    }

    public function destroy(Category $category)
    {
        // Check if has children
        if ($category->children()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category with child categories',
                'children_count' => $category->children()->count(),
            ], 422);
        }

        // Check if has contents
        if ($category->contents()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category with associated contents',
                'contents_count' => $category->contents()->count(),
            ], 422);
        }

        $categoryId = $category->id;
        $category->delete();

        // Clear caches
        $cacheService = new CacheService();
        $cacheService->clearCategoryCaches($categoryId);
        $cacheService->clearSeoCaches();

        return response()->json(['message' => 'Category deleted successfully']);
    }

    public function move(Request $request, Category $category)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'integer|min:0',
        ]);

        // Prevent setting self as parent
        if (isset($validated['parent_id']) && $validated['parent_id'] == $category->id) {
            return response()->json(['message' => 'Category cannot be its own parent'], 422);
        }

        // Prevent circular reference
        if (isset($validated['parent_id']) && $validated['parent_id']) {
            $parent = Category::find($validated['parent_id']);
            if ($parent) {
                $checkParent = $parent;
                while ($checkParent->parent_id) {
                    if ($checkParent->parent_id == $category->id) {
                        return response()->json(['message' => 'Cannot set parent to a child category'], 422);
                    }
                    $checkParent = Category::find($checkParent->parent_id);
                }
            }
        }

        $category->update($validated);

        return response()->json($category->load(['parent', 'children']));
    }
}
