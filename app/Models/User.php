<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'phone',
        'bio',
        'website',
        'location',
        'last_login_at',
        'last_login_ip',
        'preferences',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'preferences' => 'array',
        ];
    }

    /**
     * Get a user preference value.
     */
    public function getPreference(string $key, mixed $default = null): mixed
    {
        return data_get($this->preferences, $key, $default);
    }

    /**
     * Set a user preference value.
     *
     * @return $this
     */
    public function setPreference(string $key, mixed $value): self
    {
        $preferences = $this->preferences ?? [];
        data_set($preferences, $key, $value);
        $this->preferences = $preferences;

        return $this;
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function notifications()
    {
        return $this->hasMany(\App\Models\Notification::class);
    }

    /**
     * Get the two factor authentication record.
     */
    public function twoFactorAuth()
    {
        return $this->hasOne(\App\Models\TwoFactorAuth::class);
    }

    /**
     * Check if 2FA is enabled for this user.
     */
    public function hasTwoFactorEnabled(): bool
    {
        if (! \App\Models\Setting::get('enable_2fa', false)) {
            return false;
        }

        return $this->twoFactorAuth && $this->twoFactorAuth->enabled;
    }

    /**
     * Check if 2FA is required for this user (admin users).
     */
    public function requiresTwoFactor(): bool
    {
        // Check global enable switch
        if (! \App\Models\Setting::get('enable_2fa', false)) {
            return false;
        }

        $enforcement = \App\Models\Setting::get('two_factor_enforced_roles', 'no');

        if ($enforcement === 'all') {
            return true;
        }

        if ($enforcement === 'admin') {
            return $this->isAtLeastRole('admin');
        }

        return false;
    }

    /**
     * Get the numerical rank of the user's highest role.
     * Higher number means higher authority.
     */
    public function getRoleRank(): int
    {
        $roleRanks = [
            'super-admin' => 100,
            'admin' => 80,
            'editor' => 60,
            'author' => 40,
            'member' => 20,
        ];

        $userRoles = $this->getRoleNames();

        $maxRank = 0;
        foreach ($userRoles as $role) {
            if (isset($roleRanks[$role]) && $roleRanks[$role] > $maxRank) {
                $maxRank = $roleRanks[$role];
            }
        }

        return $maxRank;
    }

    /**
     * Compare this user's rank with another user.
     */
    public function isHigherThan(User $target): bool
    {
        return $this->getRoleRank() > $target->getRoleRank();
    }

    /**
     * Check if user has at least the minimum required role level.
     */
    public function isAtLeastRole(string $roleName): bool
    {
        $roleRanks = [
            'super-admin' => 100,
            'admin' => 80,
            'editor' => 60,
            'author' => 40,
            'member' => 20,
        ];

        if (! isset($roleRanks[$roleName])) {
            return false;
        }

        return $this->getRoleRank() >= $roleRanks[$roleName];
    }
}
