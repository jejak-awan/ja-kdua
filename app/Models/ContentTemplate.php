<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContentTemplate extends Model
{
    use HasFactory, SoftDeletes;

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

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function createContent($data = [])
    {
        $title = $this->replaceTemplateVariables($this->title_template ?? '{{ title }}', $data);
        $body = $this->replaceTemplateVariables($this->body_template, $data);
        $excerpt = $this->excerpt_template ? $this->replaceTemplateVariables($this->excerpt_template, $data) : null;

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
        $content = \App\Models\Content::create($contentData);

        // Apply default custom fields if any
        if ($this->default_fields) {
            foreach ($this->default_fields as $fieldSlug => $value) {
                $content->setCustomFieldValue($fieldSlug, $value);
            }
        }

        // Increment usage count
        $this->increment('usage_count');

        return $content;
    }

    protected function replaceTemplateVariables($template, array $data)
    {
        foreach ($data as $key => $value) {
            $template = str_replace('{{'.$key.'}}', $value, $template);
            $template = str_replace('{{ $'.$key.' }}', $value, $template);
        }

        return $template;
    }
}
