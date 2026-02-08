<?php

namespace Database\Factories\Isp;

use App\Models\Isp\CustomerDevice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Isp\CustomerDevice>
 */
class CustomerDeviceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CustomerDevice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => User::factory(),
            'type' => $this->faker->randomElement(['ONU', 'ONT', 'CPE']),
            'serial_number' => $this->faker->unique()->bothify('SN-####-????'),
            'mac_address' => $this->faker->unique()->macAddress,
            'status' => $this->faker->randomElement(['online', 'online', 'online', 'offline', 'warning']),
            'metadata' => [
                'firmware' => 'v'.$this->faker->randomFloat(1, 1, 5),
                'signal_dbm' => $this->faker->numberBetween(-30, -15),
                'tx_rate' => $this->faker->numberBetween(10, 100).' Mbps',
                'rx_rate' => $this->faker->numberBetween(10, 100).' Mbps',
            ],
        ];
    }
}
