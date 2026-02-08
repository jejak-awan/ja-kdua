<?php

namespace Database\Factories\Isp;

use App\Models\Isp\ServiceProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Isp\ServiceProfile>
 */
class ServiceProfileFactory extends Factory
{
    protected $model = ServiceProfile::class;

    public function definition(): array
    {
        return [
            'name' => 'Home Basic',
            'download_speed' => 20480,
            'upload_speed' => 5120,
            'type' => 'Queue',
        ];
    }
}
