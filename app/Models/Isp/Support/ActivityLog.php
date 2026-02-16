<?php

declare(strict_types=1);

namespace App\Models\Isp\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $action
 * @property string|null $resource_type
 * @property int|null $resource_id
 * @property string $description
 * @property array<string, mixed>|null $properties
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon|null $created_at
 */
class ActivityLog extends Model
{
    public $timestamps = false;

    protected $table = 'isp_activity_logs';

    protected $fillable = [
        'user_id',
        'action',
        'resource_type',
        'resource_id',
        'description',
        'properties',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<\App\Models\Core\User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Core\User::class);
    }

    /**
     * Color mapping for action badges.
     *
     * @return array<string, string>
     */
    public static function actionColors(): array
    {
        return [
            'create' => '#22c55e', // green
            'update' => '#3b82f6', // blue
            'delete' => '#ef4444', // red
            'login' => '#8b5cf6',  // purple
            'export' => '#f59e0b', // amber
            'other' => '#6b7280',  // gray
        ];
    }

    /**
     * Log an activity.
     *
     * @param  array<string, mixed>  $properties
     */
    public static function log(
        string $action,
        string $description,
        ?string $resourceType = null,
        ?int $resourceId = null,
        array $properties = []
    ): self {
        $request = request();

        return self::create([
            'user_id' => $request->user()?->id,
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'description' => $description,
            'properties' => $properties !== [] ? $properties : null,
            'ip_address' => \App\Helpers\IpHelper::getClientIp($request),
            'user_agent' => is_string($request->userAgent()) ? $request->userAgent() : null,
            'created_at' => now(),
        ]);
    }
}
