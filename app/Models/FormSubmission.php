<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $form_id
 * @property int|null $user_id
 * @property array $data
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read int|null $count
 * @property-read string|null $label
 * @property-read \App\Models\Form $form
 * @property-read \App\Models\User|null $user
 */
class FormSubmission extends Model
{
    use HasFactory, SoftDeletes;

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
