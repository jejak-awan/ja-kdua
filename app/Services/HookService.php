<?php

namespace App\Services;

class HookService
{
    protected static $hooks = [];

    /**
     * Register a hook
     */
    public static function addHook(string $hookName, callable $callback, int $priority = 10)
    {
        if (!isset(self::$hooks[$hookName])) {
            self::$hooks[$hookName] = [];
        }

        self::$hooks[$hookName][] = [
            'callback' => $callback,
            'priority' => $priority,
        ];

        // Sort by priority
        usort(self::$hooks[$hookName], function ($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });
    }

    /**
     * Execute a hook
     */
    public static function doHook(string $hookName, ...$args)
    {
        if (!isset(self::$hooks[$hookName])) {
            return $args[0] ?? null;
        }

        $result = $args[0] ?? null;

        foreach (self::$hooks[$hookName] as $hook) {
            $result = call_user_func($hook['callback'], $result, ...$args);
        }

        return $result;
    }

    /**
     * Apply a filter
     */
    public static function applyFilter(string $filterName, $value, ...$args)
    {
        return self::doHook($filterName, $value, ...$args);
    }

    /**
     * Get all registered hooks
     */
    public static function getHooks()
    {
        return self::$hooks;
    }

    /**
     * Remove a hook
     */
    public static function removeHook(string $hookName, callable $callback = null)
    {
        if (!isset(self::$hooks[$hookName])) {
            return;
        }

        if ($callback === null) {
            unset(self::$hooks[$hookName]);
            return;
        }

        self::$hooks[$hookName] = array_filter(
            self::$hooks[$hookName],
            function ($hook) use ($callback) {
                return $hook['callback'] !== $callback;
            }
        );
    }
}

