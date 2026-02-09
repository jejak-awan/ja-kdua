<?php

namespace App\Imports\Isp;

use App\Models\Isp\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CustomerImport implements ToModel, WithHeadingRow
{
    /**
     * @param array<string, mixed> $row
     * @return Model|null
     */
    public function model(array $row): ?Model
    {
        // Skip if name is empty
        if (empty($row['nama_pelanggan'])) {
            return null;
        }

        /** @var string|null $phoneRaw */
        $phoneRaw = $row['phone'] ?? '';
        $phone = $this->sanitizePhone($phoneRaw ?? '');

        // Find or create user
        /** @var string $name */
        $name = $row['nama_pelanggan'];
        $user = User::firstOrCreate(
            ['phone' => $phone],
            [
                'name' => $name,
                'email' => $phone.'@example.com', // Dummy email if needed
                'password' => Hash::make($phone), // Default password is phone number
                'role' => 'customer', // Assuming 'customer' role exists or just default user
            ]
        );

        // Determine status
        $status = isset($row['aktif']) && $row['aktif'] == 1 ? 'active' : 'inactive';

        // Update or Create Customer
        /** @var string|null $identity */
        $identity = $row['identitas'] ?? null;
        /** @var string|null $alamat */
        $alamat = $row['alamat'] ?? null;

        return Customer::updateOrCreate(
            ['user_id' => $user->id],
            [
                'identity_number' => $identity,
                'address_street' => $alamat,
                'status' => $status,
                // Add default billing plan if needed or leave null
            ]
        );
    }

    /**
     * @param mixed $phone
     * @return string
     */
    private function sanitizePhone(mixed $phone): string
    {
        if (!is_string($phone) && !is_numeric($phone)) {
            return '';
        }
        // Simple sanitization, remove non-numeric
        return (string) preg_replace('/[^0-9]/', '', (string) $phone);
    }
}
