<?php

namespace App\Models\Core;

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
 * @property array<string, mixed>|null $meta
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @property string|null $og_image
 * @property int|null $locked_by
 * @property \Illuminate\Support\Carbon|null $locked_at
 * @property array<int, mixed>|null $blocks
 * @property array<string, mixed>|null $global_variables
 * @property bool $comment_status
 * @property string $editor_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $visits_count
 * @property array<string, mixed>|null $lock_status
 * @property-read \App\Models\Core\User $author
 * @property-read \App\Models\Core\Category|null $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Core\Tag> $tags
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Core\Comment> $comments
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Core\ContentRevision> $revisions
 * @property-read \App\Models\Core\User|null $lockedBy
 */
class Content extends Model
{
    /** @use HasFactory<\Database\Factories\Core\ContentFactory> */
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
            while (\App\Models\Core\Content::withTrashed()->where('slug', $newSlug)->exists()) {
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

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'content_tag');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Core\Comment, $this>
     */
    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Core\Comment, $this>
     */
    public function allComments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Core\ContentRevision, $this>
     */
    public function revisions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ContentRevision::class)->orderBy('created_at', 'desc');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\Core\ContentRevision, $this>
     */
    public function latestRevision(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ContentRevision::class)->latestOfMany();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Core\ContentCustomField, $this>
     */
    public function customFields(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ContentCustomField::class);
    }

    /**
     * Get a custom field value.
     */
    public function getCustomFieldValue(string $fieldSlug): mixed
    {
        $field = \App\Models\Core\CustomField::where('slug', $fieldSlug)->first();
        if (! $field) {
            return null;
        }

        $value = $this->customFields()->where('custom_field_id', $field->id)->first();

        return $value ? $value->value : $field->getAttribute('default_value');
    }

    /**
     * Set a custom field value.
     */
    public function setCustomFieldValue(string $fieldSlug, mixed $value): bool
    {
        $field = \App\Models\Core\CustomField::where('slug', $fieldSlug)->first();
        if (! $field) {
            return false;
        }

        $this->customFields()->updateOrCreate(
            ['custom_field_id' => $field->id],
            ['value' => $value]
        );

        return true;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Core\AnalyticsVisit>
     */
    public function analyticsVisits(): \Illuminate\Database\Eloquent\Builder
    {
        // Match visits by URL slug - using where clause
        return AnalyticsVisit::where('url', 'like', '%'.$this->slug.'%');
    }

    /**
     * @return MorphMany<\App\Models\Core\MenuItem, $this>
     */
    public function menuItems(): MorphMany
    {
        return $this->morphMany(MenuItem::class, 'target');
    }
}
