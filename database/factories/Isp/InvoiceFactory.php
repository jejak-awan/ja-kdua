<?php

namespace Database\Factories\Isp;

use App\Models\Isp\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Isp\Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'customer_id' => 1,
            'number' => 'INV-'.now()->timestamp,
            'amount' => 50000,
            'status' => 'unpaid',
            'due_at' => now()->addDays(7),
        ];
    }
}
