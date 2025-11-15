<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreateAdminUser extends Command
{
    protected $signature = 'user:create-admin {--email=admin@example.com} {--password=admin123} {--name=Admin}';

    protected $description = 'Create an admin user';

    public function handle()
    {
        $email = $this->option('email');
        $password = $this->option('password');
        $name = $this->option('name');

        // Check if user exists
        if (User::where('email', $email)->exists()) {
            $this->error("User with email {$email} already exists!");

            return 1;
        }

        // Create user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        // Assign admin role
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $user->assignRole($adminRole);

        $this->info('✅ Admin user created successfully!');
        $this->info("Email: {$email}");
        $this->info("Password: {$password}");
        $this->warn('⚠️  Please change the password after first login!');

        return 0;
    }
}
