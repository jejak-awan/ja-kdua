<?php

namespace Database\Seeders;

use App\Models\Isp\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class SupportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::role('ISP Member')->get();

        if ($users->isEmpty()) {
            $users = User::limit(5)->get();
        }

        $categories = ['Technical', 'Billing', 'Sales'];
        $priorities = ['Low', 'Medium', 'High'];
        $statuses = ['Open', 'In Progress', 'Resolved', 'Closed'];

        foreach ($users as $user) {
            // Create 1-2 tickets per user
            for ($i = 0; $i < rand(1, 2); $i++) {
                Ticket::create([
                    'user_id' => $user->id,
                    'subject' => 'Sample Issue: '.fake()->sentence(3),
                    'category' => $categories[array_rand($categories)],
                    'priority' => $priorities[array_rand($priorities)],
                    'status' => $statuses[array_rand($statuses)],
                    'message' => fake()->paragraph(3),
                    'created_at' => now()->subDays(rand(0, 30)),
                ]);
            }
        }
    }
}
