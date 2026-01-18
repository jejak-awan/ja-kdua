<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuilderPreset extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'name',
        'settings',
        'is_system',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_system' => 'boolean',
    ];

    /**
     * Get the user that owns the preset.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for system presets.
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope for user presets.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId)->where('is_system', false);
    }

    /**
     * Scope by module type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}
