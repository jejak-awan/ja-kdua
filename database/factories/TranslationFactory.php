<?php

namespace Database\Factories;

use App\Models\Content;
use App\Models\Language;
use App\Models\Translation;
use Illuminate\Database\Eloquent\Factories\Factory;

class TranslationFactory extends Factory
{
    protected $model = Translation::class;

    public function definition(): array
    {
        return [
            'translatable_type' => Content::class,
            'translatable_id' => Content::factory(),
            'language_id' => Language::factory(),
            'field' => $this->faker->randomElement(['title', 'body', 'excerpt', 'meta_description']),
            'value' => $this->faker->sentence(),
        ];
    }

    public function forContent(Content $content): static
    {
        return $this->state(fn (array $attributes) => [
            'translatable_type' => Content::class,
            'translatable_id' => $content->id,
        ]);
    }

    public function forLanguage(Language $language): static
    {
        return $this->state(fn (array $attributes) => [
            'language_id' => $language->id,
        ]);
    }

    public function title(): static
    {
        return $this->state(fn (array $attributes) => [
            'field' => 'title',
            'value' => $this->faker->sentence(),
        ]);
    }

    public function body(): static
    {
        return $this->state(fn (array $attributes) => [
            'field' => 'body',
            'value' => $this->faker->paragraphs(3, true),
        ]);
    }
}

