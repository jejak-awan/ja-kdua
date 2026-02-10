<?php

namespace App\Models\Core;

use Cron\CronExpression;
use Illuminate\Database\Eloquent\Model;

class ScheduledTask extends Model
{
    protected $table = 'scheduled_tasks';

    protected $fillable = [
        'name',
        'command',
        'schedule',
        'description',
        'is_active',
        'options',
        'last_run_at',
        'status',
        'output',
    ];

    protected $casts = [
        'options' => 'array',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];

    /**
     * Whitelist of allowed commands that can be executed from UI
     * Only these commands are safe to run via admin interface
     */
    const ALLOWED_COMMANDS = [
        // Cache management
        'cache:clear',
        'cache:warm',
        'cms:clear-cache',

        // Maintenance
        'logs:cleanup',
        'analytics:cleanup',
        'media:generate-thumbnails',
        'media:cleanup-temp',

        // Health & diagnostics
        'cms:health-check',
        'config:clear',
        'route:clear',
        'view:clear',

        // Backup
        'cms:backup',

        // Security
        'security:clear-blocked-ips',
        'security:clear-rate-limit',
        'security:audit-dependencies',
        'security:update-cf-ips',

        // Maintenance & Cleanup
        'logs:cleanup',
        'logs:cleanup-slow-queries',
        'logs:cleanup-csp-reports',

        // Queue Management
        'queue:work',
        'queue:restart',
        'queue:flush',
        'queue:prune-failed',
        'queue:retry',
        'queue:monitor',

        // System Optimization
        'optimize',
        'optimize:clear',
        'sanctum:prune-expired',
    ];

    /**
     * Commands that are NEVER allowed from UI
     */
    const BLOCKED_COMMANDS = [
        'migrate',
        'migrate:fresh',
        'migrate:rollback',
        'migrate:reset',
        'db:wipe',
        'db:seed',
        'down',
        'key:generate',
        'env:decrypt',
        'tinker',
        'make:',
    ];

    /**
     * Check if a command is allowed to be executed
     */
    public static function isCommandAllowed(string $command): bool
    {
        // Extract base command (before any arguments/options)
        $baseCommand = trim(explode(' ', $command)[0]);

        // Check against blocked commands (including partials like 'make:')
        foreach (self::BLOCKED_COMMANDS as $blocked) {
            if (str_starts_with($baseCommand, $blocked)) {
                return false;
            }
        }

        // Check if in allowed list
        return in_array($baseCommand, self::ALLOWED_COMMANDS);
    }

    /**
     * Validate cron expression
     */
    public static function isValidCronExpression(string $expression): bool
    {
        try {
            return CronExpression::isValidExpression($expression);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get allowed commands list for UI dropdown
     *
     * @return array<int, array{value: string, label: string}>
     */
    public static function getAllowedCommands(): array
    {
        $commands = array_map(function ($cmd) {
            return [
                'value' => $cmd,
                'label' => ucfirst(str_replace([':', '-'], [' › ', ' '], $cmd)),
            ];
        }, self::ALLOWED_COMMANDS);

        // Sort alphabetically by label
        usort($commands, function ($a, $b) {
            return strcasecmp($a['label'], $b['label']);
        });

        return $commands;
    }

    /**
     * Get next run time based on cron expression
     */
    public function getNextRunAt(): ?\DateTime
    {
        if (! $this->schedule || ! self::isValidCronExpression($this->schedule)) {
            return null;
        }

        try {
            $cron = new CronExpression($this->schedule);

            return $cron->getNextRunDate();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<$this>  $query
     * @return \Illuminate\Database\Eloquent\Builder<$this>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<$this>  $query
     * @return \Illuminate\Database\Eloquent\Builder<$this>
     */
    public function scopeDue($query)
    {
        return $query->active()
            ->where(function ($q) {
                $q->whereNull('last_run_at')
                    ->orWhere('last_run_at', '<', now()->subMinute());
            });
    }
}
