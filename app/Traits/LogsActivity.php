<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Isp\ActivityLog;

/**
 * Trait to automatically log create, update, and delete events on Eloquent models.
 * Add this trait to any ISP model that should be tracked in the activity log.
 *
 * Usage: `use \App\Traits\LogsActivity;` in any Model class.
 */
trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function ($model): void {
            $modelName = class_basename($model);
            ActivityLog::log(
                'create',
                $modelName.' created: '.($model->name ?? $model->code ?? '#'.$model->id),
                $modelName,
                (int) $model->id,
                ['attributes' => $model->getAttributes()]
            );
        });

        static::updated(function ($model): void {
            $changes = $model->getChanges();
            unset($changes['updated_at']);

            if ($changes === []) {
                return;
            }

            $modelName = class_basename($model);
            $original = [];
            foreach (array_keys($changes) as $key) {
                $original[$key] = $model->getOriginal($key);
            }

            ActivityLog::log(
                'update',
                $modelName.' updated: '.($model->name ?? $model->code ?? '#'.$model->id),
                $modelName,
                (int) $model->id,
                ['old' => $original, 'new' => $changes]
            );
        });

        static::deleted(function ($model): void {
            $modelName = class_basename($model);
            ActivityLog::log(
                'delete',
                $modelName.' deleted: '.($model->name ?? $model->code ?? '#'.$model->id),
                $modelName,
                (int) $model->id,
                ['attributes' => $model->getAttributes()]
            );
        });
    }
}
