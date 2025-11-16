<?php

namespace Database\Factories;

use App\Models\AnalyticsSession;
use App\Models\AnalyticsVisit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnalyticsVisitFactory extends Factory
{
    protected $model = AnalyticsVisit::class;

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
            'device_type' => $this->faker->randomElement(['desktop', 'mobile', 'tablet']),
            'browser' => $this->faker->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera']),
            'os' => $this->faker->randomElement(['Windows', 'macOS', 'Linux', 'iOS', 'Android']),
            'country' => $this->faker->country(),
            'city' => $this->faker->city(),
            'visited_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }
}

