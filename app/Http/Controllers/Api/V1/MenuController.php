<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\Request;

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

        return $this->success($menus, 'Menus retrieved successfully');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:menus,slug',
            'location' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $menu = Menu::create($validated);

        return $this->success($menu->load('items'), 'Menu created successfully', 201);
    }

    public function show(Menu $menu)
    {
        return $this->success($menu->load(['items.children']), 'Menu retrieved successfully');
    }

    public function update(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:menus,slug,'.$menu->id,
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
            return $this->error('Bulk action failed: ' . $e->getMessage(), 500);
        }

        return $this->error('Invalid action', 422);
    }

    public function addItem(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|string',
            'type' => 'required|in:link,page,category,custom',
            'target_id' => 'nullable|integer',
            'target_type' => 'nullable|string',
            'parent_id' => 'nullable|exists:menu_items,id',
            'icon' => 'nullable|string',
            'css_class' => 'nullable|string',
            'sort_order' => 'integer',
            'open_in_new_tab' => 'boolean',
        ]);

        $item = $menu->items()->create($validated);

        return $this->success($item->load('children'), 'Menu item created successfully', 201);
    }

    public function updateItem(Request $request, Menu $menu, MenuItem $menuItem)
    {
        if ($menuItem->menu_id !== $menu->id) {
            return $this->validationError(['menu_item' => ['Menu item does not belong to this menu']], 'Menu item does not belong to this menu');
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'url' => 'nullable|string',
            'type' => 'sometimes|required|in:link,page,category,custom',
            'target_id' => 'nullable|integer',
            'target_type' => 'nullable|string',
            'parent_id' => 'nullable|exists:menu_items,id',
            'icon' => 'nullable|string',
            'css_class' => 'nullable|string',
            'sort_order' => 'integer',
            'open_in_new_tab' => 'boolean',
            'is_active' => 'boolean',
        ]);

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

        foreach ($request->items as $itemData) {
            MenuItem::where('id', $itemData['id'])
                ->where('menu_id', $menu->id)
                ->update([
                    'sort_order' => $itemData['sort_order'],
                    'parent_id' => $itemData['parent_id'] ?? null,
                ]);
        }

        return $this->success(null, 'Menu items reordered successfully');
    }

    public function getByLocation(Request $request, $location)
    {
        $menu = Menu::getByLocation($location);

        if (! $menu) {
            return $this->notFound('Menu');
        }

        return $this->success($menu->load(['items.children']), 'Menu retrieved successfully');
    }
}
