<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class TwoFactorAuth extends Model
{
    protected $table = 'two_factor_auth';

    protected $fillable = [
        'user_id',
        'secret',
        'backup_codes',
        'enabled',
        'enabled_at',
        'recovery_codes_generated_at',
    ];

    protected $casts = [
        'backup_codes' => 'array',
        'enabled' => 'boolean',
        'enabled_at' => 'datetime',
        'recovery_codes_generated_at' => 'datetime',
    ];

    protected $hidden = [
        'secret',
        'backup_codes',
    ];

    /**
     * Get the user that owns the 2FA.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the decrypted secret.
     */
    public function getDecryptedSecret(): ?string
    {
        if (! $this->secret) {
            return null;
        }

        try {
            return Crypt::decryptString($this->secret);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Set the encrypted secret.
     */
    public function setSecret(string $secret): void
    {
        $this->secret = Crypt::encryptString($secret);
    }

    /**
     * Check if a backup code is valid and remove it if found.
     */
    public function verifyBackupCode(string $code): bool
    {
        if (! $this->backup_codes || ! is_array($this->backup_codes)) {
            return false;
        }

        foreach ($this->backup_codes as $index => $hashedCode) {
            if (password_verify($code, $hashedCode)) {
                // Remove used backup code
                unset($this->backup_codes[$index]);
                $this->backup_codes = array_values($this->backup_codes); // Re-index
                $this->save();

                return true;
            }
        }

        return false;
    }

    /**
     * Add backup codes (hashed).
     */
    public function setBackupCodes(array $codes): void
    {
        $hashedCodes = array_map(function ($code) {
            return password_hash($code, PASSWORD_BCRYPT);
        }, $codes);

        $this->backup_codes = $hashedCodes;
        $this->recovery_codes_generated_at = now();
    }

    /**
     * Get remaining backup codes count.
     */
    public function getRemainingBackupCodesCount(): int
    {
        if (! $this->backup_codes || ! is_array($this->backup_codes)) {
            return 0;
        }

        return count($this->backup_codes);
    }
}

