<?php

declare(strict_types=1);

namespace App\Services\Isp\Core;

use App\Models\Isp\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditTrailService
{
    /**
     * Log an event/change for a model.
     *
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    public function log(Model $model, string $event, ?array $oldValues = null, ?array $newValues = null, ?string $notes = null): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => $event,
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'notes' => $notes,
        ]);
    }

    /**
     * Shortcut to log a model update by comparing dirty attributes.
     */
    public function logUpdate(Model $model, ?string $notes = null): void
    {
        if (!$model->isDirty()) {
            return;
        }

        $newValues = $model->getDirty();
        $oldValues = [];

        foreach ($newValues as $key => $value) {
            $oldValues[$key] = $model->getOriginal($key);
        }

        $this->log($model, 'updated', $oldValues, $newValues, $notes);
    }
}
