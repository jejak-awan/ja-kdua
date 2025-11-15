<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseApiController;
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
            'slug' => 'sometimes|required|string|unique:menus,slug,' . $menu->id,
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
        
        if (!$menu) {
            return $this->notFound('Menu');
        }

        return $this->success($menu->load(['items.children']), 'Menu retrieved successfully');
    }
}
