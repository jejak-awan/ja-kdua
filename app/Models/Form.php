<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'success_message',
        'redirect_url',
        'settings',
        'is_active',
        'submission_count',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'submission_count' => 'integer',
    ];

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('sort_order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class)->latest();
    }

    public function incrementSubmissionCount()
    {
        $this->increment('submission_count');
    }

    public function getUnreadSubmissionsCount()
    {
        return $this->submissions()->where('status', 'new')->count();
    }
}
