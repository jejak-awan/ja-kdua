<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

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
        ];
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
        return $this->twoFactorAuth && $this->twoFactorAuth->enabled;
    }

    /**
     * Check if 2FA is required for this user (admin users).
     */
    public function requiresTwoFactor(): bool
    {
        return $this->hasRole('admin') || $this->hasRole('super-admin');
    }
}
