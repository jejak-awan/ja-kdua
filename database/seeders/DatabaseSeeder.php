<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create roles and permissions first (required)
        $this->call(RolePermissionSeeder::class);

        // 2. Create default settings (required)
        $this->call(SettingsSeeder::class);
        $this->call(LanguageSeeder::class);
        $this->call(ScheduledTaskSeeder::class);
        $this->call(CommentSecuritySettingsSeeder::class);
        $this->call(BuilderPresetSeeder::class);

        // 3. Create admin user if not exists
        $admin = User::firstOrCreate(
            ['email' => 'admin@jejakawan.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('super-admin');

        // 4. Create test user (optional, for development)
        if (app()->environment('local', 'development', 'testing')) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

            // Optionally seed sample data in development
            // $this->call(SampleDataSeeder::class);
            $this->call(ThemeContentSeeder::class);
            $this->call(ContentTemplateSeeder::class);
            $this->call(JanariUpgradeSeeder::class);
        }

        $this->command->info('Database seeded successfully!');
    }
}
