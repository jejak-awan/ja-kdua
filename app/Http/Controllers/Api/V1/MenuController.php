<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuController extends BaseApiController
{
    public function index(Request $request)
    {
        $query = Menu::with('items');

        if ($request->has('location')) {
            $query->where('location', $request->location);
        }

        // Soft deletes filter
        if ($request->has('trashed')) {
            $trashed = $request->trashed;
            if ($trashed === 'only') {
                $query->onlyTrashed();
            } elseif ($trashed === 'with') {
                $query->withTrashed();
            }
        }

        $menus = $query->latest()->get();
        $trashedCount = Menu::onlyTrashed()->count();

        return response()->json([
            'success' => true,
            'message' => 'Menus retrieved successfully',
            'data' => $menus,
            'meta' => [
                'trashed_count' => $trashedCount,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                \Illuminate\Validation\Rule::unique('menus')->whereNull('deleted_at'),
            ],
            'location' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        // Auto-generate slug from name if not provided
        if (empty($validated['slug'])) {
            $baseSlug = \Illuminate\Support\Str::slug($validated['name']);
            $slug = $baseSlug;
            $counter = 1;
            while (Menu::withTrashed()->where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$counter++;
            }
            $validated['slug'] = $slug;
        }

        $menu = Menu::create($validated);

        return $this->success($menu->load('items'), 'Menu created successfully', 201);
    }

    public function show($id)
    {
        $menu = Menu::withTrashed()->findOrFail($id);

        return $this->success($menu->load(['items.children']), 'Menu retrieved successfully');
    }

    public function update(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => [
                'sometimes',
                'required',
                'string',
                \Illuminate\Validation\Rule::unique('menus')->ignore($menu->id)->whereNull('deleted_at'),
            ],
            'location' => 'nullable|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $menu->update($validated);

        return $this->success($menu->load('items'), 'Menu updated successfully');
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();

        return $this->success(null, 'Menu deleted successfully');
    }

    public function restore($id)
    {
        $menu = Menu::withTrashed()->findOrFail($id);
        $menu->restore();

        return $this->success(null, 'Menu restored successfully');
    }

    public function forceDelete($id)
    {
        $menu = Menu::withTrashed()->findOrFail($id);

        // Delete menu items
        $menu->items()->delete();

        $menu->forceDelete();

        return $this->success(null, 'Menu permanently deleted');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:menus,id',
            'action' => 'required|in:delete,restore,force_delete',
        ]);

        $ids = $request->ids;
        $action = $request->action;

        try {
            if ($action === 'delete') {
                Menu::whereIn('id', $ids)->delete();

                return $this->success(null, 'Selected menus deleted successfully');
            } elseif ($action === 'restore') {
                Menu::withTrashed()->whereIn('id', $ids)->restore();

                return $this->success(null, 'Selected menus restored successfully');
            } elseif ($action === 'force_delete') {
                $menus = Menu::withTrashed()->whereIn('id', $ids)->get();
                foreach ($menus as $menu) {
                    $menu->items()->delete(); // Soft delete items first just in case, orforce delete depending on requirement. Usually items are Cascade.
                    // But actually $menu->items() returns checking menu_id.
                    // Let's force delete items associated.
                    $menu->items()->forceDelete();
                    $menu->forceDelete();
                }

                return $this->success(null, 'Selected menus permanently deleted');
            }
        } catch (\Exception $e) {
            return $this->error('Bulk action failed: '.$e->getMessage(), 500);
        }

        return $this->error('Invalid action', 422);
    }

    public function addItem(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|string',
            'type' => 'required|in:link,page,post,category,custom,column_group',
            'target_id' => 'nullable|integer',
            'target_type' => 'nullable|string',
            'parent_id' => 'nullable|exists:menu_items,id',
            'icon' => 'nullable|string',
            'css_class' => 'nullable|string',
            'description' => 'nullable|string',
            'badge' => 'nullable|string',
            'badge_color' => 'nullable|string',
            'image' => 'nullable|string',
            'image_size' => 'nullable|string',
            'mega_menu_layout' => 'nullable|string',
            'mega_menu_column' => 'nullable|integer',
            'mega_menu_show_dividers' => 'boolean',
            'hide_label' => 'boolean',
            'heading' => 'nullable|string',
            'show_heading_line' => 'boolean',
            'sort_order' => 'integer',
            'open_in_new_tab' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // Security: Validate target_type against whitelist
        if (!empty($validated['target_type']) && !in_array($validated['target_type'], MenuItem::ALLOWED_TARGET_TYPES)) {
            return $this->error('Invalid target type', 422);
        }

        // Auto-assign target_type based on type if not provided
        if (empty($validated['target_type']) && ! empty($validated['type'])) {
            if (in_array($validated['type'], ['page', 'post'])) {
                $validated['target_type'] = 'App\Models\Content';
            } elseif ($validated['type'] === 'category') {
                $validated['target_type'] = 'App\Models\Category';
            }
        }

        $item = $menu->items()->create($validated);

        return $this->success($item->load('children'), 'Menu item created successfully', 201);
    }

    public function items($id)
    {
        $menu = Menu::withTrashed()->findOrFail($id);

        // Return all items flattened, frontend will build the tree
        return $this->success($menu->allItems, 'Menu items retrieved successfully');
    }

    public function updateItem(Request $request, Menu $menu, MenuItem $menuItem)
    {
        if ($menuItem->menu_id !== $menu->id) {
            return $this->validationError(['menu_item' => ['Menu item does not belong to this menu']], 'Menu item does not belong to this menu');
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'url' => 'nullable|string',
            'type' => 'sometimes|required|in:link,page,post,category,custom,column_group',
            'target_id' => 'nullable|integer',
            'target_type' => 'nullable|string',
            'parent_id' => 'nullable|exists:menu_items,id',
            'icon' => 'nullable|string',
            'css_class' => 'nullable|string',
            'description' => 'nullable|string',
            'badge' => 'nullable|string',
            'badge_color' => 'nullable|string',
            'image' => 'nullable|string',
            'image_size' => 'nullable|string',
            'mega_menu_layout' => 'nullable|string',
            'mega_menu_column' => 'nullable|integer',
            'mega_menu_show_dividers' => 'boolean',
            'hide_label' => 'boolean',
            'heading' => 'nullable|string',
            'show_heading_line' => 'boolean',
            'sort_order' => 'integer',
            'open_in_new_tab' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // Security: Validate target_type against whitelist
        if (!empty($validated['target_type']) && !in_array($validated['target_type'], MenuItem::ALLOWED_TARGET_TYPES)) {
            return $this->error('Invalid target type', 422);
        }

        // Auto-assign target_type based on type if not provided
        if (empty($validated['target_type']) && ! empty($validated['type'])) {
            if (in_array($validated['type'], ['page', 'post'])) {
                $validated['target_type'] = 'App\Models\Content';
            } elseif ($validated['type'] === 'category') {
                $validated['target_type'] = 'App\Models\Category';
            }
        }

        $menuItem->update($validated);

        return $this->success($menuItem->load('children'), 'Menu item updated successfully');
    }

    public function deleteItem(Menu $menu, MenuItem $menuItem)
    {
        if ($menuItem->menu_id !== $menu->id) {
            return $this->validationError(['menu_item' => ['Menu item does not belong to this menu']], 'Menu item does not belong to this menu');
        }

        $menuItem->delete();

        return $this->success(null, 'Menu item deleted successfully');
    }

    public function reorderItems(Request $request, Menu $menu)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:menu_items,id',
            'items.*.sort_order' => 'required|integer',
            'items.*.parent_id' => 'nullable|exists:menu_items,id',
        ]);

        DB::transaction(function () use ($request, $menu) {
            foreach ($request->items as $itemData) {
                $updateData = [
                    'sort_order' => $itemData['sort_order'],
                    'parent_id' => $itemData['parent_id'] ?? null,
                ];

                // Include optional mega menu fields if present
                $optionalFields = [
                    'title', 'url', 'icon', 'css_class', 'description',
                    'badge', 'badge_color', 'open_in_new_tab', 'image', 'image_size',
                    'mega_menu_layout', 'mega_menu_column', 'mega_menu_show_dividers',
                    'hide_label', 'heading', 'show_heading_line',
                ];
                foreach ($optionalFields as $field) {
                    if (array_key_exists($field, $itemData)) {
                        $updateData[$field] = $itemData[$field];
                    }
                }

                MenuItem::where('id', $itemData['id'])
                    ->where('menu_id', $menu->id)
                    ->update($updateData);
            }
        });

        return $this->success(null, 'Menu items reordered successfully');
    }

    public function getByLocation(Request $request, $location)
    {
        $menu = Menu::getByLocation($location);

        if (! $menu) {
            return $this->success(null, 'No menu assigned to this location');
        }

        return $this->success($menu->load(['items.children.children']), 'Menu retrieved successfully');
    }
}
