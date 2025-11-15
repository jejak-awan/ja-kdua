<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Translation extends Model
{
    protected $fillable = [
        'language_id',
        'translatable_type',
        'translatable_id',
        'field',
        'value',
    ];

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function translatable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function getTranslation($model, $field, $languageCode = null)
    {
        if (! $languageCode) {
            $language = Language::getDefault();
            if (! $language) {
                return null;
            }
            $languageCode = $language->code;
        }

        $language = Language::where('code', $languageCode)->first();
        if (! $language) {
            return null;
        }

        $translation = static::where('translatable_type', get_class($model))
            ->where('translatable_id', $model->id)
            ->where('language_id', $language->id)
            ->where('field', $field)
            ->first();

        return $translation ? $translation->value : null;
    }

    public static function setTranslation($model, $field, $value, $languageCode)
    {
        $language = Language::where('code', $languageCode)->first();
        if (! $language) {
            return false;
        }

        return static::updateOrCreate(
            [
                'language_id' => $language->id,
                'translatable_type' => get_class($model),
                'translatable_id' => $model->id,
                'field' => $field,
            ],
            ['value' => $value]
        );
    }
}
