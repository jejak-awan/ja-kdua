<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityLog extends Model
{
    protected $fillable = [
        'user_id',
        'event_type',
        'ip_address',
        'user_agent',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper method to log security events
    public static function log(string $eventType, $user = null, ?string $ipAddress = null, ?string $description = null, array $metadata = [])
    {
        return self::create([
            'user_id' => $user?->id,
            'event_type' => $eventType,
            'ip_address' => $ipAddress ?? request()->ip(),
            'user_agent' => request()->userAgent(),
            'description' => $description ?? self::getDefaultDescription($eventType),
            'metadata' => $metadata,
        ]);
    }

    protected static function getDefaultDescription(string $eventType): string
    {
        return match ($eventType) {
            'login_failed' => 'Failed login attempt',
            'login_success' => 'Successful login',
            'login_blocked' => 'Login blocked due to too many failed attempts',
            'ip_blocked' => 'IP address blocked',
            'ip_unblocked' => 'IP address unblocked',
            'suspicious_activity' => 'Suspicious activity detected',
            'password_changed' => 'Password changed',
            'permission_denied' => 'Permission denied',
            default => ucfirst(str_replace('_', ' ', $eventType)),
        };
    }
}
