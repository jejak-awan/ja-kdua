<?php

declare(strict_types=1);

namespace App\Services\Isp\Core;

use App\Models\Isp\Customer\Customer;
use App\Models\Isp\Network\ServiceNode;
use Illuminate\Validation\ValidationException;

class ISPValidationService
{
    /**
     * Validate a customer for provisioning readiness.
     */
    public function validateProvisioning(Customer $customer): void
    {
        $errors = [];

        if (!$customer->identity_number) {
            $errors['identity_number'] = ['Identity number (NIK) is required for provisioning.'];
        } elseif (strlen($customer->identity_number) !== 16) {
            $errors['identity_number'] = ['Identity number (NIK) must be exactly 16 digits.'];
        }

        if (!$customer->router_id) {
            $errors['router_id'] = ['Service router must be assigned.'];
        }

        if (!$customer->billing_plan_id) {
            $errors['billing_plan_id'] = ['Billing plan must be assigned.'];
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Validate OLT port and ONU index.
     */
    public function validateOltPort(int $port, ?int $onuIndex = null): void
    {
        if ($port < 0 || $port > 15) {
            throw ValidationException::withMessages([
                'olt_port' => ['OLT Port must be between 0 and 15.'],
            ]);
        }

        if ($onuIndex !== null && ($onuIndex < 1 || $onuIndex > 128)) {
            throw ValidationException::withMessages([
                'olt_onu_index' => ['ONU Index must be between 1 and 128.'],
            ]);
        }
    }

    /**
     * Validate ODP port range.
     */
    public function validateOdpPort(int $port, int $maxPorts = 16): void
    {
        if ($port < 1 || $port > $maxPorts) {
            throw ValidationException::withMessages([
                'odp_port' => ["ODP Port must be between 1 and {$maxPorts}."],
            ]);
        }
    }

    /**
     * Validate Hardware Identifiers (SN/MAC).
     */
    public function validateHardwareIdentifer(string $identifier, string $type = 'SN'): void
    {
        if ($type === 'MAC') {
            if (!preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', $identifier)) {
                throw ValidationException::withMessages([
                    'mac_address' => ['Invalid MAC Address format.'],
                ]);
            }
        } else {
            if (strlen($identifier) < 8) {
                throw ValidationException::withMessages([
                    'serial_number' => ['Serial Number must be at least 8 characters.'],
                ]);
            }
        }
    }
}
