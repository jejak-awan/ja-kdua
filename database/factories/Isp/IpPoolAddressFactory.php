<?php

namespace Database\Factories\Isp;

use App\Models\Isp\IpPoolAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<IpPoolAddress>
 */
class IpPoolAddressFactory extends Factory
{
    protected $model = IpPoolAddress::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ip_address' => '10.10.10.2',
            'status' => 'available',
        ];
    }
}
