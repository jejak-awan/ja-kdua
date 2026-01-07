<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'menu_id',
        'parent_id',
        'title',
        'url',
        'type',
        'target_id',
        'target_type',
        'icon',
        'css_class',
        'sort_order',
        'open_in_new_tab',
        'is_active',
    ];

    protected $casts = [
        'open_in_new_tab' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->where('is_active', true)->orderBy('sort_order');
    }

    public function target(): MorphTo
    {
        return $this->morphTo('target', 'target_type', 'target_id');
    }

    public function getUrlAttribute($value)
    {
        // If type is page, generate URL from target (frontend uses /:slug)
        if ($this->type === 'page' && $this->target) {
            return '/'.$this->target->slug;
        }

        // If type is post, generate URL from target (frontend uses /blog/:slug)
        if ($this->type === 'post' && $this->target) {
            return '/blog/'.$this->target->slug;
        }

        // If type is category, generate URL from target
        if ($this->type === 'category' && $this->target) {
            return '/category/'.$this->target->slug;
        }

        return $value;
    }
}
