<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $query = Menu::with('items');

        if ($request->has('location')) {
            $query->where('location', $request->location);
        }

        $menus = $query->latest()->get();

        return response()->json($menus);
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

        return response()->json($menu->load('items'), 201);
    }

    public function show(Menu $menu)
    {
        return response()->json($menu->load(['items.children']));
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

        return response()->json($menu->load('items'));
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();

        return response()->json(['message' => 'Menu deleted successfully']);
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

        return response()->json($item->load('children'), 201);
    }

    public function updateItem(Request $request, Menu $menu, MenuItem $menuItem)
    {
        if ($menuItem->menu_id !== $menu->id) {
            return response()->json(['message' => 'Menu item does not belong to this menu'], 422);
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

        return response()->json($menuItem->load('children'));
    }

    public function deleteItem(Menu $menu, MenuItem $menuItem)
    {
        if ($menuItem->menu_id !== $menu->id) {
            return response()->json(['message' => 'Menu item does not belong to this menu'], 422);
        }

        $menuItem->delete();

        return response()->json(['message' => 'Menu item deleted successfully']);
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

        return response()->json(['message' => 'Menu items reordered successfully']);
    }

    public function getByLocation(Request $request, $location)
    {
        $menu = Menu::getByLocation($location);
        
        if (!$menu) {
            return response()->json(['message' => 'Menu not found for location'], 404);
        }

        return response()->json($menu->load(['items.children']));
    }
}
