<?php

namespace App\Imports\Isp;

use App\Models\IspCustomer;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CustomerImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Skip if name is empty
        if (empty($row['nama_pelanggan'])) {
            return null;
        }

        $phone = $this->sanitizePhone($row['phone'] ?? '');

        // Find or create user
        $user = User::firstOrCreate(
            ['phone' => $phone],
            [
                'name' => $row['nama_pelanggan'],
                'email' => $phone.'@example.com', // Dummy email if needed
                'password' => Hash::make($phone), // Default password is phone number
                'role' => 'customer', // Assuming 'customer' role exists or just default user
            ]
        );

        // Determine status
        $status = isset($row['aktif']) && $row['aktif'] == 1 ? 'active' : 'inactive';

        // Update or Create IspCustomer
        return IspCustomer::updateOrCreate(
            ['user_id' => $user->id],
            [
                'identity_number' => $row['identitas'] ?? null,
                'address_street' => $row['alamat'] ?? null,
                'status' => $status,
                // Add default billing plan if needed or leave null
            ]
        );
    }

    private function sanitizePhone($phone)
    {
        // Simple sanitization, remove non-numeric
        return preg_replace('/[^0-9]/', '', $phone);
    }
}
