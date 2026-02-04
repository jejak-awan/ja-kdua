<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlowQuery extends Model
{
    protected $fillable = [
        'query',
        'bindings',
        'duration',
        'route',
        'user_id',

    ];

    protected $casts = [
        'bindings' => 'array',
        'duration' => 'integer',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSlow($query, $threshold = 1000)
    {
        return $query->where('duration', '>=', $threshold);
    }

    public function scopeByRoute($query, $route)
    {
        return $query->where('route', $route);
    }
}
