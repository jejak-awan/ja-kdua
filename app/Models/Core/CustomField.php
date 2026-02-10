<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomField extends Model
{
    protected $fillable = [
        'field_group_id',
        'name',
        'slug',
        'type',
        'label',
        'description',
        'default_value',
        'options',
        'validation_rules',
        'is_required',
        'is_searchable',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'validation_rules' => 'array',
        'is_required' => 'boolean',
        'is_searchable' => 'boolean',
    ];

    /**
     * @return BelongsTo<FieldGroup, $this>
     */
    public function fieldGroup(): BelongsTo
    {
        return $this->belongsTo(FieldGroup::class);
    }

    /**
     * @return HasMany<ContentCustomField, $this>
     */
    public function contentValues(): HasMany
    {
        return $this->hasMany(ContentCustomField::class);
    }

    /**
     * @param  int|string  $contentId
     * @return mixed
     */
    public function getValueForContent($contentId)
    {
        /** @var \App\Models\Core\ContentCustomField|null $value */
        $value = $this->contentValues()->where('content_id', $contentId)->first();

        return $value ? $value->value : $this->default_value;
    }
}
