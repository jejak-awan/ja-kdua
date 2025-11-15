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

        // Create permissions if they don't exist
        $permissions = [
            'manage content', 'create content', 'edit content', 'delete content', 'publish content',
            'manage categories', 'manage tags', 'manage media', 'manage comments',
            'manage users', 'manage roles', 'manage permissions',
            'manage settings', 'manage forms', 'view analytics',
            'manage themes', 'manage plugins', 'manage menus', 'manage widgets',
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
