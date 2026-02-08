<?php

namespace Database\Factories\Isp;

use App\Models\Isp\ServiceRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Isp\ServiceRequest>
 */
class ServiceRequestFactory extends Factory
{
    protected $model = ServiceRequest::class;

    public function definition(): array
    {
        return [
            'customer_id' => 1,
            'type' => $this->faker->randomElement(['Upgrade', 'Downgrade', 'Cancellation']),
            'details' => ['requested_plan_id' => 1],
            'status' => 'Pending',
        ];
    }
}
