<?php

namespace Database\Factories;

use App\Models\ContentRevision;
use App\Models\Content;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContentRevision>
 */
class ContentRevisionFactory extends Factory
{
    protected $model = ContentRevision::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence();
        
        return [
            'content_id' => Content::factory(),
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title) . '-' . uniqid(),
            'body' => fake()->paragraphs(5, true),
            'excerpt' => fake()->paragraph(),
            'meta' => [],
            'status' => fake()->randomElement(['draft', 'published', 'archived']),
            'note' => fake()->optional()->sentence(),
        ];
    }
}

