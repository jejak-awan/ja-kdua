<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Business;

use App\Exports\Isp\CustomerExport;
use App\Http\Controllers\Api\Core\BaseApiController;
use App\Imports\Isp\CustomerImport;
use App\Models\Core\User;
use App\Models\Isp\Customer;
use App\Services\Isp\RouterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class CustomerController extends BaseApiController
{
    protected RouterService $routerService;

    public function __construct(RouterService $routerService)
    {
        $this->routerService = $routerService;
    }

    /**
     * Get customer statistics.
     */
    public function stats(): \Illuminate\Http\JsonResponse
    {
        $total = User::whereHas('customer')->count();
        $active = User::whereHas('customer', function ($q) {
            $q->where('status', 'active');
        })->count();
        $isolated = User::whereHas('customer', function ($q) {
            $q->where('status', 'isolated');
        })->count();
        $inactive = User::whereHas('customer', function ($q) {
            $q->where('status', 'inactive');
        })->count();

        return $this->success([
            'total' => $total,
            'active' => $active,
            'isolated' => $isolated,
            'inactive' => $inactive,
        ], 'Statistics retrieved successfully');
    }

    /**
     * Display a listing of customers.
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = User::query()
            ->with(['customer.plan', 'media'])
            ->whereHas('roles', function ($q) {
                $q->where('name', 'member'); // Assuming 'member' is the role for customers
            });

        if ($request->has('search')) {
            $searchValue = $request->input('search');
            $search = is_string($searchValue) ? $searchValue : '';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('identity_number', 'like', "%{$search}%")
                            ->orWhere('mikrotik_login', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('status')) {
            $statusValue = $request->input('status');
            $status = is_string($statusValue) ? $statusValue : 'all';
            if ($status !== 'all') {
                $query->whereHas('customer', function ($q) use ($status) {
                    $q->where('status', $status);
                });
            }
        }

        /** @var \Illuminate\Pagination\LengthAwarePaginator<int, User> $customers */
        $customers = $query->latest()->paginate(15);

        return $this->success($customers, 'Customers retrieved successfully');
    }

    /**
     * Store a new customer.
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        // ... (validation remains the same) ...
        $validated = $request->validate([
            // User Data
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',

            // ISP Profile Data
            'identity_number' => 'nullable|string|max:50',
            'identity_type' => 'required|string|in:KTP,SIM,Passport',
            'address_street' => 'nullable|string',
            'address_village' => 'nullable|string',
            'address_district' => 'nullable|string',
            'address_city' => 'nullable|string',
            'address_province' => 'nullable|string',
            'address_postal_code' => 'nullable|string',
            'coordinates' => 'nullable|string',

            // Billing Data
            'billing_plan_id' => 'required|exists:isp_billing_plans,id',
            'billing_cycle_start' => 'required|integer|min:1|max:28',
            'installation_date' => 'nullable|date',
            'status' => 'required|in:active,isolated,inactive',

            // Mikrotik Data
            'router_id' => 'required|exists:isp_service_nodes,id', // Added router_id validation
            'mikrotik_login' => 'nullable|string|max:100',
            'mikrotik_password' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Create User
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password']),
            ]);

            $user->assignRole('member');

            // Create ISP Profile
            $customer = new Customer([
                'identity_number' => $validated['identity_number'] ?? null,
                'identity_type' => $validated['identity_type'],
                'address_street' => $validated['address_street'] ?? null,
                'address_village' => $validated['address_village'] ?? null,
                'address_district' => $validated['address_district'] ?? null,
                'address_city' => $validated['address_city'] ?? null,
                'address_province' => $validated['address_province'] ?? null,
                'address_postal_code' => $validated['address_postal_code'] ?? null,
                'coordinates' => $validated['coordinates'] ?? null,
                'billing_plan_id' => $validated['billing_plan_id'],
                'billing_cycle_start' => $validated['billing_cycle_start'],
                'installation_date' => $validated['installation_date'] ?? null,
                'status' => $validated['status'],
                'router_id' => $validated['router_id'],
                'mikrotik_login' => $validated['mikrotik_login'] ?? null,
                'mikrotik_password' => $validated['mikrotik_password'] ?? null,
            ]);

            $user->customer()->save($customer);

            // Provision on Mikrotik
            if ($customer->mikrotik_login && $customer->status === 'active') {
                $this->routerService->createCustomer($customer);
            }

            DB::commit();

            return $this->success($user->load('customer'), 'Customer created successfully', 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->error('Failed to create customer: '.$e->getMessage(), 500);
        }
    }

    /**
     * Display the specified customer.
     */
    public function show(int $id): \Illuminate\Http\JsonResponse
    {
        $user = User::with(['customer.plan', 'media', 'activityLogs'])->findOrFail($id);

        if (! $user->hasRole('member')) {
            return $this->error('User is not a customer', 404);
        }

        return $this->success($user, 'Customer retrieved successfully');
    }

    /**
     * Update the specified customer.
     */
    public function update(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            // User Data
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8', // Optional update

            // ISP Profile Data
            'identity_number' => 'nullable|string|max:50',
            'identity_type' => 'sometimes|string|in:KTP,SIM,Passport',
            'address_street' => 'nullable|string',
            'address_village' => 'nullable|string',
            'address_district' => 'nullable|string',
            'address_city' => 'nullable|string',
            'address_province' => 'nullable|string',
            'address_postal_code' => 'nullable|string',
            'coordinates' => 'nullable|string',

            // Billing Data
            'billing_plan_id' => 'sometimes|exists:isp_billing_plans,id',
            'billing_cycle_start' => 'sometimes|integer|min:1|max:28',
            'installation_date' => 'nullable|date',
            'status' => 'sometimes|in:active,isolated,inactive',

            // Mikrotik Data
            'mikrotik_login' => 'nullable|string|max:100',
            'mikrotik_password' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Update User
            $user->update($request->only(['name', 'email', 'phone']));

            if (! empty($validated['password'])) {
                $user->update(['password' => Hash::make($validated['password'])]);
            }

            // Update or Create ISP Profile
            $customerData = $request->only([
                'identity_number', 'identity_type', 'address_street', 'address_village',
                'address_district', 'address_city', 'address_province', 'address_postal_code',
                'coordinates', 'billing_plan_id', 'billing_cycle_start', 'installation_date',
                'status', 'mikrotik_login', 'mikrotik_password',
            ]);

            if ($user->customer) {
                $user->customer()->update($customerData);
            } else {
                $customer = new Customer($customerData);
                $user->customer()->save($customer);
            }

            // Provision on Mikrotik (Update)
            if ($user->customer && $user->customer->mikrotik_login) {
                $this->routerService->updateCustomer($user->customer);
            }

            DB::commit();

            return $this->success($user->load('customer'), 'Customer updated successfully');

        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->error('Failed to update customer: '.$e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified customer.
     */
    public function destroy(int $id): \Illuminate\Http\JsonResponse
    {
        $user = User::findOrFail($id);

        // Soft delete user and related customer profile
        $user->delete();
        if ($user->customer) {
            $this->routerService->deleteCustomer($user->customer);
            $user->customer()->delete();
        }

        return $this->success(null, 'Customer deleted successfully');
    }

    /**
     * Import customers from CSV.
     */
    public function import(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx',
        ]);

        try {
            Excel::import(new CustomerImport, $request->file('file'));

            return $this->success(null, 'Customers imported successfully');
        } catch (\Exception $e) {
            return $this->error('Import failed: '.$e->getMessage());
        }
    }

    /**
     * Export customers to CSV.
     */
    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(new CustomerExport, 'customers.xlsx');
    }
}
