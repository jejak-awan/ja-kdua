<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create permissions
        $permissions = [
            // Content
            'create content',
            'edit content',
            'delete content',
            'publish content',
            'manage content',
            'manage content templates',
            'manage categories',
            'manage tags',
            
            // Media
            'manage media',
            'manage files',
            
            // Interaction
            'manage comments',
            'manage forms',
            
            // System & Settings
            'manage users',
            'manage roles',
            'manage settings',
            'manage themes',
            'manage plugins',
            'manage menus',
            'manage widgets',
            'manage redirects',
            'manage scheduled tasks',
            'manage backups',
            'manage system',
            
            // Logs & Analytics
            'view logs',
            'view analytics',
            'view activity logs',
            'view security logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // 2. Define Roles and Assign Permissions

        // SUPER ADMIN - Gets everything via Gate::before in AppServiceProvider,
        // but it's good practice to assign permissions or just create the role.
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdminRole->syncPermissions(Permission::all());

        // ADMIN - Full operational control but NO system-level configuration
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::whereNotIn('name', [
            'manage scheduled tasks',
            'manage backups',
            'manage system',
            'manage roles', // Only super-admin should manage roles for security
        ])->get());

        // EDITOR - Focused on content management
        $editorRole = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $editorRole->syncPermissions([
            'create content',
            'edit content',
            'delete content',
            'publish content',
            'manage content',
            'manage categories',
            'manage tags',
            'manage media',
            'manage comments',
            'view analytics',
        ]);

        // AUTHOR - Can create and manage own content
        $authorRole = Role::firstOrCreate(['name' => 'author', 'guard_name' => 'web']);
        $authorRole->syncPermissions([
            'create content',
            'edit content',
            'manage media',
        ]);

        // MEMBER - Default user role
        $memberRole = Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);
        // Members typically have no admin permissions

        $this->command->info('Standard roles and permissions seeded successfully!');
    }
}
