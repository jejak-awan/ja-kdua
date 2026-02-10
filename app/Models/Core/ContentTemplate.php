<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContentTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'title_template',
        'body_template',
        'excerpt_template',
        'default_fields',
        'meta',
        'category_id',
        'is_active',
        'usage_count',
        'author_id',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    protected $casts = [
        'default_fields' => 'array',
        'meta' => 'array',
        'is_active' => 'boolean',
        'usage_count' => 'integer',
    ];

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createContent(array $data = []): \App\Models\Core\Content
    {
        $title = $this->replaceTemplateVariables($this->title_template ?? '{{ title }}', $data);
        $body = $this->replaceTemplateVariables((string) $this->body_template, $data);
        $excerpt = $this->excerpt_template ? $this->replaceTemplateVariables($this->excerpt_template, $data) : null;

        /** @var array<string, mixed> $contentData */
        $contentData = [
            'title' => $title,
            'body' => $body,
            'excerpt' => $excerpt,
            'type' => $this->type,
            'category_id' => $this->category_id,
            'status' => 'draft',
        ];

        // Merge with provided data
        $contentData = array_merge($contentData, $data);

        // Create content
        /** @var \App\Models\Core\Content $content */
        $content = \App\Models\Core\Content::create($contentData);

        // Apply default custom fields if any
        if (is_array($this->default_fields)) {
            foreach ($this->default_fields as $fieldSlug => $value) {
                if (is_string($fieldSlug)) {
                    $content->setCustomFieldValue($fieldSlug, $value);
                }
            }
        }

        // Increment usage count
        $this->increment('usage_count');

        return $content;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function replaceTemplateVariables(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            if (is_scalar($value)) {
                $template = str_replace('{{'.$key.'}}', (string) $value, $template);
                $template = str_replace('{{ $'.$key.' }}', (string) $value, $template);
            }
        }

        return $template;
    }
}
