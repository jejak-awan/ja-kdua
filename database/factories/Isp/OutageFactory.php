<?php

namespace Database\Factories\Isp;

use App\Models\Isp\Outage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Isp\Outage>
 */
class OutageFactory extends Factory
{
    protected $model = Outage::class;

    public function definition(): array
    {
        return [
            'title' => 'Sample Outage',
            'description' => 'Something went wrong',
            'type' => 'Unscheduled',
            'status' => 'Investigating',
            'started_at' => now(),
        ];
    }
}
