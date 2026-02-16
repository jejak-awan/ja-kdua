<?php

declare(strict_types=1);

namespace App\Imports\Isp;

use App\Models\Isp\Billing\Voucher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class VoucherImport implements ToModel, WithHeadingRow, WithValidation
{
    protected \App\Services\Isp\Network\RadiusService $radius;

    public function __construct(\App\Services\Isp\Network\RadiusService $radius)
    {
        $this->radius = $radius;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public function model(array $row): ?Model
    {
        $code = is_string($row['code'] ?? null) ? (string) $row['code'] : '';
        $profile = is_string($row['profile'] ?? null) ? (string) $row['profile'] : '';
        $price = is_numeric($row['price'] ?? null) ? (float) $row['price'] : 0.0;
        $rawPartnerId = $row['mitra_id'] ?? $row['partner_id'] ?? null;
        $partnerId = is_numeric($rawPartnerId) ? (int) $rawPartnerId : null;
        $duration = is_string($row['duration'] ?? null) ? (string) $row['duration'] : null;

        if (empty($code) || empty($profile)) {
            return null;
        }

        // Create the voucher
        $voucher = new Voucher([
            'code' => $code,
            'profile' => $profile,
            'price' => $price,
            'partner_id' => $partnerId,
            'duration' => $duration,
            'status' => 'Available',
        ]);

        // Sync to Radius during model creation is tricky in Excel imports
        // But we can do it after save or use a transaction closure
        $this->syncToRadius($code, $profile, $duration);

        return $voucher;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'unique:isp_vouchers,code'],
            'profile' => ['required', 'string'],
            'price' => ['numeric', 'min:0'],
        ];
    }

    /**
     * Sync voucher to FreeRADIUS radcheck.
     */
    protected function syncToRadius(string $code, string $profile, ?string $duration): void
    {
        $radiusDb = $this->radius->getConnectionName();

        // 1. Add User-Password
        DB::connection($radiusDb)->table('radcheck')->updateOrInsert(
            ['username' => $code, 'attribute' => 'Cleartext-Password'],
            ['op' => ':=', 'value' => $code]
        );

        // 2. Add to Group
        DB::connection($radiusDb)->table('radusergroup')->updateOrInsert(
            ['username' => $code],
            ['groupname' => $profile, 'priority' => 1]
        );

        // 3. Add Validity
        if ($duration) {
            DB::connection($radiusDb)->table('radcheck')->updateOrInsert(
                ['username' => $code, 'attribute' => 'Max-All-Session'],
                ['op' => ':=', 'value' => $duration]
            );
        }
    }
}
