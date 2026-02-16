<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Billing;

use App\Http\Controllers\Api\Core\BaseApiController;
use App\Models\Isp\Billing\IspPlan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IspPlanController extends BaseApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = IspPlan::query();

        if ($request->has('search')) {
            $searchValue = $request->input('search');
            $search = is_string($searchValue) ? $searchValue : '';
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('mikrotik_group', 'like', "%{$search}%");
        }

        $perPage = $request->input('per_page', 10);
        $perPageInt = is_numeric($perPage) ? (int) $perPage : 10;

        /** @var \Illuminate\Pagination\LengthAwarePaginator<int, IspPlan> $profiles */
        $profiles = $query->latest()->paginate($perPageInt);

        return $this->success($profiles);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mikrotik_group' => 'required|string|max:255',
            'mikrotik_rate_limit' => 'nullable|string|max:255',
            'speed_limit' => 'nullable|string|max:255', // Legacy field, keeping optional
            'shared_users' => 'required|integer|min:1',
            'active_period' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'commission' => 'nullable|numeric|min:0',
            'type' => ['required', Rule::in(['prepaid', 'postpaid'])],
            'is_active' => 'boolean',
        ]);

        $profile = IspPlan::create($validated);

        return $this->success($profile, 'Subscription profile created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(IspPlan $subscriptionProfile): \Illuminate\Http\JsonResponse
    {
        return $this->success($subscriptionProfile);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, IspPlan $subscriptionProfile): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'mikrotik_group' => 'sometimes|required|string|max:255',
            'mikrotik_rate_limit' => 'nullable|string|max:255',
            'speed_limit' => 'nullable|string|max:255',
            'shared_users' => 'sometimes|required|integer|min:1',
            'active_period' => 'sometimes|required|integer|min:1',
            'price' => 'sometimes|required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'commission' => 'nullable|numeric|min:0',
            'type' => ['sometimes', 'required', Rule::in(['prepaid', 'postpaid'])],
            'is_active' => 'boolean',
        ]);

        $subscriptionProfile->update($validated);

        return $this->success($subscriptionProfile, 'Subscription profile updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(IspPlan $subscriptionProfile): \Illuminate\Http\JsonResponse
    {
        $subscriptionProfile->delete();

        return $this->success(null, 'Subscription profile deleted successfully');
    }
}
