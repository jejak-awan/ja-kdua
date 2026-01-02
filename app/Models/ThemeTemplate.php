<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThemeTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'conditions',
        'priority',
        'is_active',
        'header_data',
        'footer_data',
        'body_data'
    ];

    protected $casts = [
        'conditions' => 'array',
        'header_data' => 'array',
        'footer_data' => 'array',
        'body_data' => 'array',
        'is_active' => 'boolean',
    ];
}
