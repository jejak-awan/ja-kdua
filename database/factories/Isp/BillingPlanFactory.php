<?php

namespace Database\Factories\Isp;

use App\Models\Isp\BillingPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Isp\BillingPlan>
 */
class BillingPlanFactory extends Factory
{
    protected $model = BillingPlan::class;

    public function definition(): array
    {
        return [
            'name' => 'Basic Plan',
            'price' => 25000,
            'features' => [],
        ];
    }
}
