<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $excerpt
 * @property string|null $body
 * @property string|null $featured_image
 * @property bool $is_featured
 * @property string $status
 * @property string $type
 * @property int $author_id
 * @property int|null $category_id
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property int $views
 * @property array|null $meta
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @property string|null $og_image
 * @property int|null $locked_by
 * @property \Illuminate\Support\Carbon|null $locked_at
 * @property array|null $blocks
 * @property array|null $global_variables
 * @property bool $comment_status
 * @property string $editor_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $visits_count
 * @property array|null $lock_status
 * @property-read \App\Models\User $author
 * @property-read \App\Models\Category|null $category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Comment[] $comments
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ContentRevision[] $revisions
 * @property-read \App\Models\User|null $lockedBy
 */
class Content extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted()
    {
        static::deleting(function ($content) {
            // When soft deleting, rename the slug to free it up for reuse
            if ($content->isForceDeleting()) {
                return;
            }

            $timestamp = now()->timestamp;
            $newSlug = $content->slug.'__trashed__'.$timestamp;

            // Ensure even the trashed slug is unique (rare but possible collision)
            while (\App\Models\Content::withTrashed()->where('slug', $newSlug)->exists()) {
                $newSlug = $content->slug.'__trashed__'.$timestamp.'_'.rand(100, 999);
            }

            $content->slug = $newSlug;
            $content->save();
        });
    }

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'featured_image',
        'is_featured',
        'status',
        'type',
        'author_id',
        'category_id',
        'published_at',
        'views',
        'meta',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image',
        'locked_by',
        'locked_at',
        'blocks',
        'global_variables',
        'comment_status',
        'editor_type',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'locked_at' => 'datetime',
        'meta' => 'array',
        'blocks' => 'array',
        'global_variables' => 'array',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'content_tag');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id');
    }

    public function allComments()
    {
        return $this->hasMany(Comment::class);
    }

    public function revisions()
    {
        return $this->hasMany(ContentRevision::class)->orderBy('created_at', 'desc');
    }

    public function latestRevision()
    {
        return $this->hasOne(ContentRevision::class)->latestOfMany();
    }

    public function customFields()
    {
        return $this->hasMany(ContentCustomField::class);
    }

    public function getCustomFieldValue($fieldSlug)
    {
        $field = \App\Models\CustomField::where('slug', $fieldSlug)->first();
        if (! $field) {
            return null;
        }

        $value = $this->customFields()->where('custom_field_id', $field->id)->first();

        return $value ? $value->value : $field->default_value;
    }

    public function setCustomFieldValue($fieldSlug, $value)
    {
        $field = \App\Models\CustomField::where('slug', $fieldSlug)->first();
        if (! $field) {
            return false;
        }

        $this->customFields()->updateOrCreate(
            ['custom_field_id' => $field->id],
            ['value' => $value]
        );

        return true;
    }

    public function analyticsVisits()
    {
        // Match visits by URL slug - using where clause
        return AnalyticsVisit::where('url', 'like', '%'.$this->slug.'%');
    }

    public function menuItems(): MorphMany
    {
        return $this->morphMany(MenuItem::class, 'target');
    }
}
