<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'changes',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    // Helper method to log activity
    public static function log(string $action, $model = null, array $changes = [], $user = null, $description = null): self
    {
        $user = $user ?? auth()->user();
        
        return self::create([
            'user_id' => $user?->id,
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->id,
            'description' => $description ?? self::generateDescription($action, $model),
            'changes' => $changes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    protected static function generateDescription(string $action, $model = null): string
    {
        $modelName = $model ? class_basename($model) : 'item';
        
        return match($action) {
            'created' => "Created {$modelName}",
            'updated' => "Updated {$modelName}",
            'deleted' => "Deleted {$modelName}",
            'viewed' => "Viewed {$modelName}",
            'published' => "Published {$modelName}",
            'unpublished' => "Unpublished {$modelName}",
            default => ucfirst($action) . " {$modelName}",
        };
    }
}
