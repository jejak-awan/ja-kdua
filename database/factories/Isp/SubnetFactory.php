<?php

namespace Database\Factories\Isp;

use App\Models\Isp\Subnet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Isp\Subnet>
 */
class SubnetFactory extends Factory
{
    protected $model = Subnet::class;

    public function definition(): array
    {
        return [
            'node_id' => 1,
            'name' => 'Default Subnet',
            'prefix' => '10.0.0.0/24',
            'type' => 'LAN',
            'status' => 'active',
        ];
    }
}
