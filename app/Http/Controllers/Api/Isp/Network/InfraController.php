<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Network;

use App\Http\Controllers\Api\Core\BaseApiController;
use App\Models\Isp\ServiceNode;
use Illuminate\Http\Request;

class InfraController extends BaseApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = ServiceNode::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $searchTerm = is_string($search) ? $search : '';
            $query->where('name', 'like', '%'.$searchTerm.'%')
                ->orWhere('ip_address', 'like', '%'.$searchTerm.'%');
        }

        if ($request->has('type')) {
            $type = $request->input('type');
            if (is_string($type)) {
                $query->where('type', $type);
            }
        }

        if ($request->has('status')) {
            $status = $request->input('status');
            if (is_string($status)) {
                $query->where('status', $status);
            }
        }

        $perPage = $request->input('per_page');
        $perPageInt = is_numeric($perPage) ? (int) $perPage : 15;
        $nodes = $query->latest()->paginate($perPageInt);

        return $this->success($nodes, 'Infrastructure nodes retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:OLT,POP,Router',
            'ip_address' => 'required|ip',
            'location_lat' => 'nullable|numeric',
            'location_lng' => 'nullable|numeric',
            'status' => 'required|string|in:active,inactive,maintenance',
        ]);

        $node = ServiceNode::create($validated);

        return $this->success($node, 'Infrastructure node created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ServiceNode $node): \Illuminate\Http\JsonResponse
    {
        return $this->success($node, 'Infrastructure node retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ServiceNode $node): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|in:OLT,POP,Router',
            'ip_address' => 'sometimes|ip',
            'location_lat' => 'nullable|numeric',
            'location_lng' => 'nullable|numeric',
            'status' => 'sometimes|string|in:active,inactive,maintenance',
        ]);

        $node->update($validated);

        return $this->success($node, 'Infrastructure node updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceNode $node): \Illuminate\Http\JsonResponse
    {
        $node->delete();

        return $this->success(null, 'Infrastructure node deleted successfully');
    }

    /**
     * Get infrastructure statistics.
     */
    public function stats(): \Illuminate\Http\JsonResponse
    {
        $stats = [
            'total' => ServiceNode::count(),
            'active' => ServiceNode::where('status', 'active')->count(),
            'maintenance' => ServiceNode::where('status', 'maintenance')->count(),
            'inactive' => ServiceNode::where('status', 'inactive')->count(),
            'by_type' => [
                'OLT' => ServiceNode::where('type', 'OLT')->count(),
                'POP' => ServiceNode::where('type', 'POP')->count(),
                'Router' => ServiceNode::where('type', 'Router')->count(),
            ],
        ];

        return $this->success($stats, 'Infrastructure statistics retrieved successfully');
    }
}
