<?php

namespace App\Rules;

use App\Models\Setting;
use Illuminate\Contracts\Validation\Rule;

class StrongPassword implements Rule
{
    protected int $minLength;

    protected bool $requireUppercase;

    protected bool $requireLowercase;

    protected bool $requireNumber;

    protected bool $requireSymbol;

    protected array $failedChecks = [];

    /**
     * Create a new rule instance.
     * Reads password requirements from settings.
     */
    public function __construct()
    {
        $this->minLength = (int) Setting::get('password_min_length', 8);
        $this->requireUppercase = (bool) Setting::get('password_require_uppercase', true);
        $this->requireLowercase = (bool) Setting::get('password_require_lowercase', true);
        $this->requireNumber = (bool) Setting::get('password_require_number', true);
        $this->requireSymbol = (bool) Setting::get('password_require_symbol', false);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->failedChecks = [];

        if (strlen($value) < $this->minLength) {
            $this->failedChecks[] = "at least {$this->minLength} characters";
        }

        // Check uppercase requirement
        if ($this->requireUppercase && ! preg_match('/[A-Z]/', $value)) {
            $this->failedChecks[] = 'one uppercase letter';
        }

        // Check lowercase requirement
        if ($this->requireLowercase && ! preg_match('/[a-z]/', $value)) {
            $this->failedChecks[] = 'one lowercase letter';
        }

        // Check number requirement
        if ($this->requireNumber && ! preg_match('/[0-9]/', $value)) {
            $this->failedChecks[] = 'one number';
        }

        // Check symbol requirement
        if ($this->requireSymbol && ! preg_match('/[!@#$%^&*()_+\-=\[\]{};\':\"\\\\|,.<>\/?]/', $value)) {
            $this->failedChecks[] = 'one symbol (!@#$%^&*...)';
        }

        return empty($this->failedChecks);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if (empty($this->failedChecks)) {
            return 'The :attribute does not meet the password requirements.';
        }

        $requirements = implode(', ', $this->failedChecks);

        return "The :attribute must contain: {$requirements}.";
    }
}
