<?php

declare(strict_types=1);

namespace App\Traits\Isp;

use App\Models\Isp\AuditLog;
use App\Services\Isp\Core\AuditTrailService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\App;

trait HasAuditTrail
{
    public static function bootHasAuditTrail(): void
    {
        static::created(function (Model $model) {
            self::getAuditService()->log($model, 'created', null, $model->getAttributes());
        });

        static::updated(function (Model $model) {
            $newValues = $model->getDirty();
            $oldValues = [];

            foreach ($newValues as $key => $value) {
                $oldValues[$key] = $model->getOriginal($key);
            }

            self::getAuditService()->log($model, 'updated', $oldValues, $newValues);
        });

        static::deleted(function (Model $model) {
            self::getAuditService()->log($model, 'deleted', $model->getAttributes(), null);
        });
    }

    protected static function getAuditService(): AuditTrailService
    {
        return App::make(AuditTrailService::class);
    }

    /**
     * Get the audit logs for the model.
     *
     * @return MorphMany<AuditLog, $this>
     */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }
}
