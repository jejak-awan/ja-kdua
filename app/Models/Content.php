<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Content extends Model
{
    use HasFactory, SoftDeletes;

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
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'locked_at' => 'datetime',
        'meta' => 'array',
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
}
