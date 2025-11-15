<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\MediaFolder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Media>
 */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileName = fake()->word() . '.' . fake()->randomElement(['jpg', 'png', 'gif', 'pdf', 'doc']);
        $mimeType = $this->getMimeType($fileName);
        
        return [
            'name' => fake()->words(2, true),
            'file_name' => $fileName,
            'mime_type' => $mimeType,
            'disk' => 'public',
            'path' => 'media/' . $fileName,
            'size' => fake()->numberBetween(1000, 10000000),
            'alt' => fake()->optional()->sentence(),
            'description' => fake()->optional()->paragraph(),
            'folder_id' => null,
        ];
    }

    /**
     * Indicate that the media is an image.
     */
    public function image(): static
    {
        $fileName = fake()->word() . '.' . fake()->randomElement(['jpg', 'png', 'gif']);
        
        return $this->state(fn (array $attributes) => [
            'file_name' => $fileName,
            'mime_type' => $this->getMimeType($fileName),
            'path' => 'media/' . $fileName,
        ]);
    }

    /**
     * Indicate that the media is a document.
     */
    public function document(): static
    {
        $fileName = fake()->word() . '.' . fake()->randomElement(['pdf', 'doc', 'docx']);
        
        return $this->state(fn (array $attributes) => [
            'file_name' => $fileName,
            'mime_type' => $this->getMimeType($fileName),
            'path' => 'media/' . $fileName,
        ]);
    }

    /**
     * Indicate that the media is in a folder.
     */
    public function inFolder(MediaFolder $folder = null): static
    {
        return $this->state(fn (array $attributes) => [
            'folder_id' => $folder?->id ?? MediaFolder::factory()->create()->id,
        ]);
    }

    /**
     * Get MIME type based on file extension.
     */
    protected function getMimeType(string $fileName): string
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
        
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}

