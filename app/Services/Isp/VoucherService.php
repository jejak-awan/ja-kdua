<?php

declare(strict_types=1);

namespace App\Services\Isp;

use App\Models\Isp\Voucher;
use App\Models\Isp\BillingPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\Isp\RadiusIntegration;

class VoucherService
{
    protected RadiusIntegration $radius;

    public function __construct(RadiusIntegration $radius)
    {
        $this->radius = $radius;
    }
    /**
     * Generate a batch of hotspot vouchers.
     *
     * @return array<string, mixed>
     */
    public function generateBatch(int $count, string $profile, float $price, ?string $prefix = '', string $pattern = 'mixed', ?string $duration = null): array
    {
        $batchId = (string) Str::uuid();
        $vouchers = [];

        DB::transaction(function () use ($profile, $price, $count, $prefix, $pattern, $duration, $batchId, &$vouchers) {
            for ($i = 0; $i < $count; $i++) {
                $code = $prefix . $this->generateCode($pattern);

                // Ensure uniqueness in local DB
                while (Voucher::where('code', $code)->exists()) {
                    $code = $prefix . $this->generateCode($pattern);
                }

                $voucher = Voucher::create([
                    'code' => $code,
                    'profile' => $profile,
                    'price' => $price,
                    'status' => 'Available',
                    'batch_id' => $batchId,
                ]);

                // Sync to RADIUS
                $this->syncToRadius($code, $profile, $duration);

                $vouchers[] = $voucher;
            }
        });

        return [
            'batch_id' => $batchId,
            'count' => count($vouchers),
            'vouchers' => $vouchers,
        ];
    }

    /**
     * Generate code based on pattern.
     */
    protected function generateCode(string $pattern): string
    {
        return match ($pattern) {
            'numbers' => (string) rand(100000, 999999),
            'lowercase' => Str::lower(Str::random(6)),
            'uppercase' => Str::upper(Str::random(6)),
            default => Str::random(8),
        };
    }

    /**
     * Sync voucher to FreeRADIUS radcheck.
     */
    protected function syncToRadius(string $code, string $profile, ?string $duration): void
    {
        $radiusDb = $this->radius->getConnectionName();

        // 1. Add User-Password (Username = Password for Hotspot/Voucher)
        DB::connection($radiusDb)->table('radcheck')->updateOrInsert(
            ['username' => $code, 'attribute' => 'Cleartext-Password'],
            ['op' => ':=', 'value' => $code]
        );

        // 2. Add to Group (Profile)
        DB::connection($radiusDb)->table('radusergroup')->updateOrInsert(
            ['username' => $code],
            ['groupname' => $profile, 'priority' => 1]
        );

        // 3. Add Validity (if provided, e.g. Max-All-Session)
        if ($duration) {
            DB::connection($radiusDb)->table('radcheck')->updateOrInsert(
                ['username' => $code, 'attribute' => 'Max-All-Session'],
                ['op' => ':=', 'value' => $duration]
            );
        }
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

    /**
     * Get sales report for a specific month.
     *
     * @return array<int, array{date: string, count: int, revenue: float}>
     */
    public function getSalesReport(int $year, int $month): array
    {
        $startDate = now()->setYear($year)->setMonth($month)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $results = Voucher::where('status', 'Used')
            ->whereBetween('used_at', [$startDate, $endDate])
            ->selectRaw('DATE(used_at) as date, COUNT(*) as count, SUM(price) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        /** @var array<int, array{date: string, count: int, revenue: float}> $report */
        $report = $results->map(function ($row) {
            $rowArr = (array) $row->getAttributes();
            return [
                'date' => is_string($rowArr['date'] ?? null) ? (string) $rowArr['date'] : '',
                'count' => is_numeric($rowArr['count'] ?? null) ? (int) $rowArr['count'] : 0,
                'revenue' => is_numeric($rowArr['revenue'] ?? null) ? (float) $rowArr['revenue'] : 0.0,
            ];
        })->toArray();

        return $report;
    }

    /**
     * Get today's and this month's sales summary.
     *
     * @return array{today: array{count: int, revenue: float}, month: array{count: int, revenue: float}}
     */
    public function getTodaysSummary(): array
    {
        $todayStart = now()->startOfDay();
        $monthStart = now()->startOfMonth();

        $todayStats = Voucher::where('status', 'Used')
            ->where('used_at', '>=', $todayStart)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(price), 0) as revenue')
            ->first();

        $monthStats = Voucher::where('status', 'Used')
            ->where('used_at', '>=', $monthStart)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(price), 0) as revenue')
            ->first();

        $todayArr = $todayStats ? (array) $todayStats->getAttributes() : [];
        $monthArr = $monthStats ? (array) $monthStats->getAttributes() : [];

        return [
            'today' => [
                'count' => is_numeric($todayArr['count'] ?? null) ? (int) $todayArr['count'] : 0,
                'revenue' => is_numeric($todayArr['revenue'] ?? null) ? (float) $todayArr['revenue'] : 0.0,
            ],
            'month' => [
                'count' => is_numeric($monthArr['count'] ?? null) ? (int) $monthArr['count'] : 0,
                'revenue' => is_numeric($monthArr['revenue'] ?? null) ? (float) $monthArr['revenue'] : 0.0,
            ],
        ];
    }

    // ============================================================
    // EXPORT METHODS (Phase 10)
    // ============================================================

    /**
     * Export vouchers to RouterOS import script format.
     * Useful for manual backup/import to Mikrotik.
     *
     * @param array<string, mixed> $filters
     */
    public function exportToScript(array $filters = []): string
    {
        $query = Voucher::query();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['batch_id'])) {
            $query->where('batch_id', $filters['batch_id']);
        }
        if (!empty($filters['profile'])) {
            $query->where('profile', $filters['profile']);
        }

        $vouchers = $query->get();

        $script = "# RouterOS Voucher Import Script\n";
        $script .= "# Generated: " . now()->toDateTimeString() . "\n";
        $script .= "# Total: " . $vouchers->count() . " vouchers\n\n";

        foreach ($vouchers as $voucher) {
            $name = (string) $voucher->code;
            $password = (string) $voucher->code;
            $profile = (string) ($voucher->profile ?? 'default');
            $comment = "Generated voucher - Batch: " . ($voucher->batch_id ?? 'N/A');

            $script .= "/ip hotspot user add name=\"{$name}\" password=\"{$password}\" profile=\"{$profile}\" comment=\"{$comment}\"\n";
        }

        return $script;
    }

    /**
     * Export vouchers to CSV format.
     *
     * @param array<string, mixed> $filters
     */
    public function exportToCsv(array $filters = []): string
    {
        $query = Voucher::query();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['batch_id'])) {
            $query->where('batch_id', $filters['batch_id']);
        }
        if (!empty($filters['profile'])) {
            $query->where('profile', $filters['profile']);
        }

        $vouchers = $query->get();

        $csv = "Code,Profile,Status,Price,Validity (Days),Created At,Used At\n";

        foreach ($vouchers as $voucher) {
            $code = (string) $voucher->code;
            $profile = (string) ($voucher->profile ?? 'default');
            $status = (string) $voucher->status;
            $price = number_format((float) $voucher->price, 0, '', '');
            $validity = (string) ($voucher->validity_days ?? '');
            $createdAt = $voucher->created_at ? $voucher->created_at->format('Y-m-d H:i:s') : '';
            $usedAt = $voucher->used_at ? $voucher->used_at->format('Y-m-d H:i:s') : '';

            $csv .= "\"{$code}\",\"{$profile}\",\"{$status}\",{$price},{$validity},\"{$createdAt}\",\"{$usedAt}\"\n";
        }

        return $csv;
    }
}
