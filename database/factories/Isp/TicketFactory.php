<?php

namespace Database\Factories\Isp;

use App\Models\Isp\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Isp\Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'customer_id' => 1,
            'subject' => 'Issue',
            'status' => 'open',
            'priority' => 'medium',
        ];
    }
}
