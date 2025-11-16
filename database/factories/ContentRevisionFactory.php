<?php

namespace Database\Factories;

use App\Models\Content;
use App\Models\ContentRevision;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentRevisionFactory extends Factory
{
    protected $model = ContentRevision::class;

    public function definition(): array
    {
        return [
            'content_id' => Content::factory(),
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(),
            'body' => $this->faker->paragraphs(3, true),
            'excerpt' => $this->faker->optional()->text(200),
            'slug' => $this->faker->slug(),
            'meta' => json_encode([
                'seo_title' => $this->faker->sentence(),
                'seo_description' => $this->faker->text(160),
            ]),
            'status' => $this->faker->randomElement(['draft', 'published', 'archived']),
            'note' => $this->faker->optional()->sentence(),
        ];
    }
}
