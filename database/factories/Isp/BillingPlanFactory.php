<?php

namespace Database\Factories\Isp;

use App\Models\Isp\IspPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Isp\IspPlan>
 */
class BillingPlanFactory extends Factory
{
    protected $model = IspPlan::class;

    public function definition(): array
    {
        return [
            'name' => 'Basic Plan',
            'price' => 25000,
            'features' => [],
        ];
    }
}
