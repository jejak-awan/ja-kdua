<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormSubmission extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'form_id',
        'user_id',
        'data',
        'ip_address',
        'user_agent',
        'status',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead()
    {
        $this->update(['status' => 'read']);
    }

    public function archive()
    {
        $this->update(['status' => 'archived']);
    }

    public function getFieldValue($fieldName)
    {
        return $this->data[$fieldName] ?? null;
    }
}
