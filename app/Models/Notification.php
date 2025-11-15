<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'action_url',
        'action_text',
        'is_read',
        'read_at',
        'data',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public static function createForUser($userId, $type, $title, $message, $actionUrl = null, $actionText = null, $data = [])
    {
        return static::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'action_text' => $actionText,
            'data' => $data,
        ]);
    }

    public static function createForAll($type, $title, $message, $actionUrl = null, $actionText = null, $data = [])
    {
        $users = \App\Models\User::all();
        $notifications = [];

        foreach ($users as $user) {
            $notifications[] = static::createForUser($user->id, $type, $title, $message, $actionUrl, $actionText, $data);
        }

        return $notifications;
    }
}
