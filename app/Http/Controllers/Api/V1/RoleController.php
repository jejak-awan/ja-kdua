<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends BaseApiController
{
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $search = $request->input('search');

        $query = Role::with('permissions')->orderBy('name');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $roles = $query->paginate($limit);
 
        // Optimized: Batch fetch users count in one query to avoid N+1 issues
        $roleIds = $roles->getCollection()->pluck('id')->toArray();
        $userCounts = \DB::table(config('permission.table_names.model_has_roles'))
            ->whereIn(config('permission.column_names.role_pivot_key') ?? 'role_id', $roleIds)
            ->selectRaw((config('permission.column_names.role_pivot_key') ?? 'role_id') . ' as role_id, count(*) as total')
            ->groupBy('role_id')
            ->pluck('total', 'role_id');

        $roles->getCollection()->transform(function ($role) use ($userCounts) {
            $role->users_count = $userCounts[$role->id] ?? 0;
            return $role;
        });

        return $this->success($roles, 'Roles retrieved successfully');
    }

    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|string|in:delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:roles,id',
        ]);

        $action = $validated['action'];
        $ids = $validated['ids'];
        $count = 0;

        foreach ($ids as $id) {
            $role = Role::find($id);

            // Prevent deleting protected roles
            if ($role->name === 'super-admin') {
                continue;
            }

            // Prevent deleting roles with users
            if ($role->users()->count() > 0) {
                continue;
            }

            if ($action === 'delete') {
                $role->delete();
                $count++;
            }
        }

        return $this->success(null, ucfirst($action)." completed for {$count} roles");
    }

    public function show(Role $role)
    {
        $role->load('permissions');
        $role->users_count = \DB::table(config('permission.table_names.model_has_roles'))
            ->where(config('permission.column_names.role_pivot_key') ?? 'role_id', $role->id)
            ->count();

        return $this->success($role, 'Role retrieved successfully');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);

        if (! empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return $this->success($role->load('permissions'), 'Role created successfully', 201);
    }

    public function update(Request $request, Role $role)
    {
        // Prevent editing protected roles
        if ($role->name === 'super-admin') {
            return $this->error('Cannot modify protected role', 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:roles,name,'.$role->id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        if (isset($validated['name'])) {
            $role->update(['name' => $validated['name']]);
        }

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return $this->success($role->load('permissions'), 'Role updated successfully');
    }

    public function destroy(Role $role)
    {
        // Prevent deleting protected roles
        if ($role->name === 'super-admin') {
            return $this->error('Cannot delete protected role', 403);
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return $this->error('Cannot delete role with assigned users', 400);
        }

        $role->delete();

        return $this->success(null, 'Role deleted successfully');
    }

    public function permissions()
    {
        $permissions = Permission::orderBy('name')->get()->groupBy(function ($permission) {
            // Group by category (resource name - everything after the first word)
            $parts = explode(' ', $permission->name, 2);

            return isset($parts[1]) ? ucfirst($parts[1]) : 'General';
        });

        return $this->success($permissions, 'Permissions retrieved successfully');
    }

    public function syncPermissions(Request $request, Role $role)
    {
        // Prevent modifying super-admin permissions
        if ($role->name === 'super-admin') {
            return $this->error('Cannot modify super-admin permissions', 403);
        }

        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->syncPermissions($validated['permissions']);

        return $this->success($role->load('permissions'), 'Permissions synced successfully');
    }

    public function duplicate(Role $role)
    {
        $newRole = Role::create([
            'name' => $role->name.' (copy)',
            'guard_name' => 'web',
        ]);

        $newRole->syncPermissions($role->permissions);

        return $this->success($newRole->load('permissions'), 'Role duplicated successfully', 201);
    }
}
