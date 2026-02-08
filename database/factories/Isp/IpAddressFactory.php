<?php

namespace Database\Factories\Isp;

use App\Models\Isp\IpAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Isp\IpAddress>
 */
class IpAddressFactory extends Factory
{
    protected $model = IpAddress::class;

    public function definition(): array
    {
        return [
            'subnet_id' => 1,
            'address' => $this->faker->ipv4,
            'status' => 'Available',
        ];
    }
}
