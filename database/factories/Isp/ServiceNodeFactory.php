<?php

namespace Database\Factories\Isp;

use App\Models\Isp\ServiceNode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Isp\ServiceNode>
 */
class ServiceNodeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ServiceNode::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->city.' '.$this->faker->companySuffix,
            'type' => $this->faker->randomElement(['OLT', 'POP', 'Router']),
            'ip_address' => $this->faker->ipv4,
            'location_lat' => $this->faker->latitude(-7.9, -7.8),
            'location_lng' => $this->faker->longitude(112.6, 112.7),
            'status' => $this->faker->randomElement(['active', 'active', 'active', 'maintenance', 'inactive']),
            'metadata' => [
                'vendor' => $this->faker->randomElement(['Mikrotik', 'Huawei', 'ZTE', 'Cisco']),
                'model' => $this->faker->bothify('Node-###??'),
                'uptime' => $this->faker->numberBetween(1000, 1000000),
            ],
        ];
    }
}
