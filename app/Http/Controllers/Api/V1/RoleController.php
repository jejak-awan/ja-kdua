<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends BaseApiController
{
    public function index(Request $request)
    {
        $roles = Role::with('permissions')->orderBy('name')->get();

        return $this->success($roles, 'Roles retrieved successfully');
    }

    public function show(Role $role)
    {
        $role->load('permissions');
        
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

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return $this->success($role->load('permissions'), 'Role created successfully', 201);
    }

    public function update(Request $request, Role $role)
    {
        // Prevent editing protected roles
        if (in_array($role->name, ['super-admin', 'admin'])) {
            return $this->error('Cannot modify protected role', 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:roles,name,' . $role->id,
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
        if (in_array($role->name, ['super-admin', 'admin', 'author', 'editor'])) {
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
            // Group by category (first part of permission name)
            $parts = explode(' ', $permission->name);
            return $parts[0] ?? 'general';
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
            'name' => $role->name . ' (copy)',
            'guard_name' => 'web',
        ]);

        $newRole->syncPermissions($role->permissions);

        return $this->success($newRole->load('permissions'), 'Role duplicated successfully', 201);
    }
}

