<?php

namespace Database\Factories\Isp;

use App\Models\Isp\IpPool;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<IpPool>
 */
class IpPoolFactory extends Factory
{
    protected $model = IpPool::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Default Pool',
            'network' => '10.10.10.0/24',
            'gateway' => '10.10.10.1',
            'status' => 'active',
        ];
    }
}
