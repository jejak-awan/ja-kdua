<?php

namespace Database\Factories\Core;

use App\Models\Core\AnalyticsVisit;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnalyticsVisitFactory extends Factory
{
    protected $model = AnalyticsVisit::class;

    public function configure()
    {
        return $this->afterCreating(function (AnalyticsVisit $visit) {
            \App\Models\Core\AnalyticsSession::factory()->create([
                'session_id' => $visit->session_id,
                'started_at' => $visit->visited_at,
                'ended_at' => $visit->visited_at->copy()->addMinutes(5),
            ]);
        });
    }

    public function definition(): array
    {
        return [
            'session_id' => $this->faker->uuid(),
            'user_id' => null,
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'referer' => $this->faker->boolean(70) ? $this->faker->url() : null,
            'url' => $this->faker->url(),
            'method' => 'GET',
            'status_code' => 200,
            // device/location info now in sessions table
            'visited_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
