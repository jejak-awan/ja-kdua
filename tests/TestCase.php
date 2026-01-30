<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Creates the application.
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Initialize session for tests that need it (login, etc.)
        $this->withSession([]);

        // Add viaRemember macro to RequestGuard for AuthenticateSession compatibility
        // RequestGuard (used by Sanctum) doesn't have viaRemember() method by default
        // but AuthenticateSession middleware calls it
        \Illuminate\Auth\RequestGuard::macro('viaRemember', function () {
            return false; // Token-based auth never uses "remember me"
        });

        // Seed permissions and roles
        $this->seedPermissionsAndRoles();
    }

    /**
     * Seed permissions and roles for testing.
     */
    protected function seedPermissionsAndRoles(): void
    {
        // Clear cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Flush cache to reset rate limiters
        \Illuminate\Support\Facades\Cache::flush();

        // If permissions already exist, we don't need to seed them again
        if (Permission::exists()) {
            return;
        }

        // Create permissions if they don't exist
        // Include all permissions used by api.php routes
        $permissions = [
            // Content
            'view content', 'create content', 'edit content', 'delete content', 'publish content', 'approve content', 'manage content',
            'view content templates', 'create content templates', 'edit content templates', 'delete content templates',
            'view categories', 'create categories', 'edit categories', 'delete categories', 'manage categories',
            'view tags', 'create tags', 'edit tags', 'delete tags', 'manage tags',
            // Media
            'view media', 'upload media', 'edit media', 'delete media', 'manage media',
            'view files', 'upload files', 'edit files', 'delete files', 'manage files',
            // Engagement
            'view comments', 'create comments', 'edit comments', 'delete comments', 'approve comments', 'manage comments',
            'view forms', 'create forms', 'edit forms', 'delete forms', 'manage forms',
            'view newsletter', 'manage newsletter',
            // Users & Roles
            'view users', 'create users', 'edit users', 'delete users', 'manage users',
            'view roles', 'create roles', 'edit roles', 'delete roles', 'manage roles', 'manage permissions',
            // Appearance
            'view themes', 'upload themes', 'edit themes', 'delete themes', 'manage themes',
            'view menus', 'create menus', 'edit menus', 'delete menus', 'manage menus',
            'view widgets', 'create widgets', 'edit widgets', 'delete widgets', 'manage widgets',
            // System & Settings
            'view settings', 'manage settings',
            'view plugins', 'install plugins', 'edit plugins', 'delete plugins', 'manage plugins',
            'view redirects', 'create redirects', 'edit redirects', 'delete redirects',
            'view scheduled tasks', 'manage scheduled tasks',
            'view backups', 'create backups', 'download backups', 'delete backups', 'manage backups',
            'view system', 'manage system',
            // Logs & Analytics
            'view logs', 'delete logs',
            'view analytics',
            'view activity logs',
            'view activity logs',
            'view security logs',
            // Missing permissions
            'manage comments', 'manage content', 'publish content', 'approve content',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }



        // Create admin role if it doesn't exist
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->givePermissionTo(Permission::all());
    }

    /**
     * Create an authenticated user for testing.
     */
    protected function createUser(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    /**
     * Create an admin user for testing.
     */
    protected function createAdminUser(array $attributes = []): User
    {
        $user = $this->createUser($attributes);
        $user->assignRole('admin');

        return $user;
    }

    /**
     * Act as a user for testing.
     */
    protected function actingAsUser(?User $user = null): self
    {
        $user = $user ?? $this->createUser();

        return $this->actingAs($user, 'sanctum');
    }

    /**
     * Act as an admin user for testing.
     */
    protected function actingAsAdmin(?User $user = null): self
    {
        $user = $user ?? $this->createAdminUser();

        return $this->actingAs($user, 'sanctum');
    }
}
