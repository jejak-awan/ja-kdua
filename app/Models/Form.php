<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Form extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'author_id',
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

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

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
