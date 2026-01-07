<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

class SessionManager
{
    /**
     * Set session lifetime based on user role
     */
    public static function setLifetimeForUser($user): void
    {
        if (! $user) {
            return;
        }

        // Determine lifetime based on role hierarchy
        $lifetime = $user->isAtLeastRole('admin')
            ? config('session.admin_lifetime', 120)  // 2 hours for admins
            : config('session.user_lifetime', 480);  // 8 hours for regular users

        // Set session lifetime dynamically
        config(['session.lifetime' => $lifetime]);

        // Also set cookie lifetime to match
        Session::put('session_lifetime', $lifetime);
    }

    /**
     * Get current session lifetime
     */
    public static function getLifetime(): int
    {
        return Session::get('session_lifetime', config('session.lifetime'));
    }
}
