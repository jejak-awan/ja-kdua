<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewsletterSubscriber extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'email',
        'name',
        'status',
        'subscribed_at',
        'unsubscribed_at',
        'source',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    // Status: subscribed, unsubscribed, pending
    public function scopeSubscribed($query)
    {
        return $query->where('status', 'subscribed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}

