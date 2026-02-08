<?php

declare(strict_types=1);

namespace App\Services\Isp;

use App\Models\Isp\Voucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VoucherService
{
    /**
     * Generate a batch of hotspot vouchers.
     *
     * @return array<string, mixed>
     */
    public function generateBatch(int $count, string $profile, float $price, ?string $prefix = ''): array
    {
        $batchId = (string) Str::uuid();
        $vouchers = [];

        DB::transaction(function () use ($profile, $price, $count, $prefix, $batchId, &$vouchers) {
            for ($i = 0; $i < $count; $i++) {
                $code = $prefix.Str::upper(Str::random(8));

                // Ensure uniqueness
                while (Voucher::where('code', $code)->exists()) {
                    $code = $prefix.Str::upper(Str::random(8));
                }

                $vouchers[] = Voucher::create([
                    'code' => $code,
                    'profile' => $profile,
                    'price' => $price,
                    'status' => 'Available',
                    'batch_id' => $batchId,
                ]);
            }
        });

        return [
            'batch_id' => $batchId,
            'count' => count($vouchers),
            'vouchers' => $vouchers,
        ];
    }

    /**
     * Mark a voucher as used.
     */
    public function useVoucher(string $code, int $userId): bool
    {
        $voucher = Voucher::where('code', $code)->where('status', 'Available')->first();

        if (! $voucher) {
            return false;
        }

        $voucher->update([
            'status' => 'Used',
            'used_at' => now(),
            'used_by' => $userId,
        ]);

        return true;
    }
}
