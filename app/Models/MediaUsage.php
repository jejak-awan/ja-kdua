<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MediaUsage extends Model
{
    protected $table = 'media_usage';

    protected $fillable = [
        'media_id',
        'model_type',
        'model_id',
        'field_name',
    ];

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    // Helper method to track media usage
    public static function track($mediaId, $model, $fieldName = null)
    {
        // Remove old usage for this field if exists
        if ($fieldName) {
            self::where('model_type', $model->getMorphClass())
                ->where('model_id', $model->id)
                ->where('field_name', $fieldName)
                ->delete();
        }

        return self::create([
            'media_id' => $mediaId,
            'model_type' => $model->getMorphClass(),
            'model_id' => $model->id,
            'field_name' => $fieldName,
        ]);
    }

    // Helper method to remove media usage
    public static function untrack($mediaId = null, $model = null, $fieldName = null)
    {
        $query = self::query();

        if ($mediaId) {
            $query->where('media_id', $mediaId);
        }

        if ($model) {
            $query->where('model_type', $model->getMorphClass())
                ->where('model_id', $model->id);
        }

        if ($fieldName) {
            $query->where('field_name', $fieldName);
        }

        return $query->delete();
    }
}
