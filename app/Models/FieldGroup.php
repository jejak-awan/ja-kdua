<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FieldGroup extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'applies_to',
        'conditions',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'conditions' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * @return HasMany<CustomField, $this>
     */
    public function fields(): HasMany
    {
        return $this->hasMany(CustomField::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<CustomField, $this>
     */
    public function activeFields(): HasMany
    {
        return $this->hasMany(CustomField::class)->where('is_active', true)->orderBy('sort_order');
    }
}
