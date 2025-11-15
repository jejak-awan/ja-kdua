<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\Widget;
use Illuminate\Http\Request;

class WidgetController extends BaseApiController
{
    public function index(Request $request)
    {
        $query = Widget::query();

        if ($request->has('location')) {
            $query->where('location', $request->location);
        }

        $widgets = $query->orderBy('sort_order')->get();

        return $this->success($widgets, 'Widgets retrieved successfully');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:text,html,recent_posts,categories,custom',
            'location' => 'nullable|string',
            'content' => 'nullable|string',
            'settings' => 'nullable|array',
            'sort_order' => 'integer',
        ]);

        $widget = Widget::create($validated);

        return $this->success($widget, 'Widget created successfully', 201);
    }

    public function show(Widget $widget)
    {
        return $this->success($widget, 'Widget retrieved successfully');
    }

    public function update(Request $request, Widget $widget)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:text,html,recent_posts,categories,custom',
            'location' => 'nullable|string',
            'content' => 'nullable|string',
            'settings' => 'nullable|array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $widget->update($validated);

        return $this->success($widget, 'Widget updated successfully');
    }

    public function destroy(Widget $widget)
    {
        $widget->delete();

        return $this->success(null, 'Widget deleted successfully');
    }

    public function getByLocation(Request $request, $location)
    {
        $widgets = Widget::getByLocation($location);
        return $this->success($widgets, 'Widgets retrieved successfully');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'widgets' => 'required|array',
            'widgets.*.id' => 'required|exists:widgets,id',
            'widgets.*.sort_order' => 'required|integer',
        ]);

        foreach ($request->widgets as $widgetData) {
            Widget::where('id', $widgetData['id'])
                ->update(['sort_order' => $widgetData['sort_order']]);
        }

        return $this->success(null, 'Widgets reordered successfully');
    }
}
