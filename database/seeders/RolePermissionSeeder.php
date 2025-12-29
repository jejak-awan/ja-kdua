<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'create content',
            'edit content',
            'delete content',
            'publish content',
            'manage content',
            'manage content templates',
            'manage categories',
            'manage tags',
            'manage media',
            'manage files', // NEW: Admin-level file system access
            'manage comments',
            'manage users',
            'manage forms',
            'manage settings',
            'view analytics',
            'manage themes',
            'manage plugins',
            'manage menus',
            'manage widgets',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->givePermissionTo(Permission::all());

        $editorRole = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $editorRole->givePermissionTo([
            'create content',
            'edit content',
            'publish content',
            'manage categories',
            'manage tags',
            'manage media',
            'manage comments',
        ]);

        // Author role - can create and edit own content
        $authorRole = Role::firstOrCreate(['name' => 'author', 'guard_name' => 'web']);
        $authorRole->givePermissionTo([
            'create content',
            'edit content',
            'manage media',
        ]);

        $memberRole = Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);
        // Members have no special permissions by default
    }
}
