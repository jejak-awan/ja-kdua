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
        // 1. Create permissions
        $permissions = [
            // Content
            'view content', 'create content', 'edit content', 'delete content', 'publish content',
            'view content templates', 'create content templates', 'edit content templates', 'delete content templates',
            'view categories', 'create categories', 'edit categories', 'delete categories',
            'view tags', 'create tags', 'edit tags', 'delete tags',
            
            // Media
            'view media', 'upload media', 'edit media', 'delete media', // 'manage media' as fallback/alias
            'view files', 'upload files', 'edit files', 'delete files',
            
            // Engagement
            'view comments', 'create comments', 'edit comments', 'delete comments', 'approve comments',
            'view forms', 'create forms', 'edit forms', 'delete forms', 'view submissions',
            'view newsletter', 'create newsletter', 'edit newsletter', 'delete newsletter',

            // Check Access (Users & Roles)
            'view users', 'create users', 'edit users', 'delete users', 'verify users',
            'view roles', 'create roles', 'edit roles', 'delete roles',
            
            // Appearance
            'view themes', 'upload themes', 'edit themes', 'delete themes', 'manage themes',
            'view menus', 'create menus', 'edit menus', 'delete menus',
            'view widgets', 'create widgets', 'edit widgets', 'delete widgets',
            
            // System & Settings
            'view settings', 'manage settings',
            'view plugins', 'install plugins', 'edit plugins', 'delete plugins',
            'view redirects', 'create redirects', 'edit redirects', 'delete redirects',
            'view scheduled tasks', 'manage scheduled tasks',
            'view backups', 'create backups', 'download backups', 'delete backups',
            'view system', 'manage system',
            
            // Logs & Analytics
            'view logs', 'delete logs',
            'view analytics',
            'view activity logs',
            'view security logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
        
        // Ensure legacy 'manage' permissions exist for backward compatibility if needed, 
        // or rely on matching logic. For now, we prefer replacing them.
        // But to avoid breaking existing super-admins until re-seeded:
        $legacy = ['manage content', 'manage media', 'manage users', 'manage roles'];
        foreach ($legacy as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // 2. Define Roles and Assign Permissions

        // SUPER ADMIN - Gets everything via Gate::before in AppServiceProvider,
        // but it's good practice to assign permissions or just create the role.
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdminRole->syncPermissions(Permission::all());

        // ADMIN - Full operational control but NO system-level configuration
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        // Admin gets all permissions EXCEPT critical system ones
        $adminPermissions = Permission::whereNotIn('name', [
            'manage system',
            'view security logs',
            'manage backups',
            'manage scheduled tasks',
            'manage roles', 
            'delete users', // Safety
        ])->get();
        $adminRole->syncPermissions($adminPermissions);
        // Explicitly ensure 'manage users', 'create users', 'edit users' are there
        // (They are covered by "all except")

        // EDITOR - Focused on content management
        $editorRole = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $editorRole->syncPermissions([
            // Content
            'view content', 'create content', 'edit content', 'delete content', 'publish content',
            'view content templates', 'create content templates', 'edit content templates', 'delete content templates',
            'view categories', 'create categories', 'edit categories', 'delete categories',
            'view tags', 'create tags', 'edit tags', 'delete tags',
            // Media
            'view media', 'upload media', 'edit media', 'delete media',
            'view files', 'upload files', 'edit files', 'delete files',
            // Engagement
            'view comments', 'create comments', 'edit comments', 'delete comments', 'approve comments',
            'view forms', 'view submissions',
            'view newsletter', 'create newsletter', 'edit newsletter',
            // Analytics
            'view analytics',
        ]);

        // AUTHOR - Can create and manage own content
        $authorRole = Role::firstOrCreate(['name' => 'author', 'guard_name' => 'web']);
        $authorRole->syncPermissions([
            'view content', 'create content', 'edit content', 'delete content', // Ownership handled by Policy
            'view categories',
            'view tags', 
            'view media', 'upload media', // Authors need to upload images for posts
        ]);

        // MEMBER - Default user role
        $memberRole = Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);
        $memberRole->syncPermissions([
            'view comments', 'create comments', // Can comment
        ]);

        $this->command->info('Standard roles and permissions seeded successfully!');
    }
}
