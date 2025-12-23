<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

trait HasQueryOptimization
{
    /**
     * Get default eager loading relationships
     */
    protected function getDefaultRelations(): array
    {
        return [];
    }

    /**
     * Apply eager loading to query
     */
    protected function applyEagerLoading(Builder $query, array $additionalRelations = []): Builder
    {
        $relations = array_merge($this->getDefaultRelations(), $additionalRelations);

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query;
    }

    /**
     * Optimize query with select specific columns
     */
    protected function optimizeSelect(Builder $query, array $columns = []): Builder
    {
        if (!empty($columns)) {
            $query->select($columns);
        }

        return $query;
    }

    /**
     * Apply pagination with optimization
     */
    protected function paginateOptimized(Builder $query, int $perPage = 15, array $columns = ['*'])
    {
        return $query->paginate($perPage, $columns);
    }

    /**
     * Cache query result
     */
    protected function cacheQuery(string $key, int $ttl, callable $callback)
    {
        return Cache::remember($key, now()->addMinutes($ttl), $callback);
    }

    /**
     * Clear related caches
     */
    protected function clearRelatedCaches(array $keys): void
    {
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Apply query constraints to avoid N+1
     */
    protected function withConstraints(Builder $query, array $relations = []): Builder
    {
        foreach ($relations as $relation => $constraints) {
            if (is_numeric($relation)) {
                $query->with($constraints);
            } else {
                $query->with([$relation => $constraints]);
            }
        }

        return $query;
    }

    /**
     * Select only needed columns from relationships
     */
    protected function selectRelationColumns(array $relations): array
    {
        $optimized = [];
        foreach ($relations as $key => $value) {
            if (is_numeric($key)) {
                $optimized[] = $value;
            } else {
                $optimized[$key] = function ($query) use ($value) {
                    $query->select($value);
                };
            }
        }

        return $optimized;
    }
}

