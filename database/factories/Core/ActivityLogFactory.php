<?php

namespace Database\Factories\Core;

use App\Models\Core\ActivityLog;
use App\Models\Core\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Core\ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => fake()->randomElement(['created', 'updated', 'deleted', 'viewed', 'login', 'logout']),
            'model_type' => fake()->randomElement(['App\\Models\\Content', 'App\\Models\\User', 'App\\Models\\Category']),
            'model_id' => fake()->numberBetween(1, 100),
            'description' => fake()->sentence(),
            'changes' => [],
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
