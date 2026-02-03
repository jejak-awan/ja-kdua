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
            'author_id' => User::factory(),
            'title' => $this->faker->sentence(),
            'body' => $this->faker->paragraphs(3, true),
            'blocks' => [],
            'meta' => [
                'seo_title' => $this->faker->sentence(),
                'seo_description' => $this->faker->text(160),
                'revision_data' => [
                    'excerpt' => $this->faker->text(200),
                    'slug' => $this->faker->slug(),
                    'status' => $this->faker->randomElement(['draft', 'published', 'archived']),
                ],
            ],
            'reason' => $this->faker->optional()->sentence(),
        ];
    }
}
