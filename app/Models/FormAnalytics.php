<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormAnalytics extends Model
{
    use HasFactory;

    protected $table = 'form_analytics';

    protected $fillable = [
        'form_id',
        'date',
        'views',
        'starts',
        'submissions',
    ];

    protected $casts = [
        'date' => 'date',
        'views' => 'integer',
        'starts' => 'integer',
        'submissions' => 'integer',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
}
