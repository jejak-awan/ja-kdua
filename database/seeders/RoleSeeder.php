<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define additional permissions if they don't exist
        $additionalPermissions = [
            'manage scheduled tasks',
            'manage backups',
            'manage system',
            'view logs',
        ];

        foreach ($additionalPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $rolesData = [
            'Technical Admin' => [
                'permissions' => [
                    'manage users',
                    'manage settings',
                    'manage files',
                    'manage themes',
                    'manage plugins',
                    'manage scheduled tasks',
                    'manage backups',
                    'manage system',
                    'view logs',
                    'view analytics',
                ],
                'user' => [
                    'name' => 'Technical Admin',
                    'email' => 'tech.admin@example.com',
                ],
            ],
            'Content Manager' => [
                'permissions' => [
                    'create content',
                    'edit content',
                    'delete content',
                    'publish content',
                    'manage content',
                    'manage categories',
                    'manage tags',
                    'manage media',
                    'manage comments',
                ],
                'user' => [
                    'name' => 'Content Manager',
                    'email' => 'content.manager@example.com',
                ],
            ],
            'SEO Specialist' => [
                'permissions' => [
                    'edit content',
                    'manage content',
                    'view analytics',
                    'manage categories',
                    'manage tags',
                ],
                'user' => [
                    'name' => 'SEO Specialist',
                    'email' => 'seo@example.com',
                ],
            ],
            'Community Moderator' => [
                'permissions' => [
                    'manage comments',
                    'manage users', // covering newsletter usually
                ],
                'user' => [
                    'name' => 'Community Moderator',
                    'email' => 'moderator@example.com',
                ],
            ],
            'Technical Support' => [
                'permissions' => [
                    'manage users',
                    'manage content',
                    'view logs',
                ],
                'user' => [
                    'name' => 'Technical Support',
                    'email' => 'support@example.com',
                ],
            ],
            'Media Assistant' => [
                'permissions' => [
                    'manage media',
                ],
                'user' => [
                    'name' => 'Media Assistant',
                    'email' => 'media@example.com',
                ],
            ],
            'Marketing Manager' => [
                'permissions' => [
                    'manage forms',
                    'view analytics',
                    'manage users', // newsletter
                ],
                'user' => [
                    'name' => 'Marketing Manager',
                    'email' => 'marketing@example.com',
                ],
            ],
        ];

        foreach ($rolesData as $roleName => $data) {
            // Create role
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            // Sync permissions (using names)
            $role->syncPermissions($data['permissions']);

            // Create test user
            $user = User::firstOrCreate(
                ['email' => $data['user']['email']],
                [
                    'name' => $data['user']['name'],
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]
            );

            // Assign role to user
            $user->syncRoles([$role]);
        }

        $this->command->info('RoleSeeder completed successfully with test users!');
    }
}
