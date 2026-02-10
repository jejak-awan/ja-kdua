<?php

declare(strict_types=1);

namespace App\Services\Isp;

use App\Models\Core\User;
use App\Models\Isp\IspPlan;
use App\Models\Isp\Partner;
use App\Models\Isp\Transaction;
use App\Models\Isp\Voucher;
use App\Models\Isp\VoucherBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
     * @param  array<string, mixed>  $options  Optional: mitra_id, profile_id, created_by
     * @return array<string, mixed>
     */
    public function generateBatch(
        int $count,
        string $profile,
        float $price,
        ?string $prefix = '',
        string $pattern = 'mixed',
        ?string $duration = null,
        array $options = []
    ): array {
        $batchId = (string) Str::uuid();
        $vouchers = [];

        $partnerId = isset($options['partner_id']) && is_numeric($options['partner_id']) ? (int) $options['partner_id'] : null;
        $profileId = isset($options['profile_id']) && is_numeric($options['profile_id']) ? (int) $options['profile_id'] : null;
        $createdBy = isset($options['created_by']) && is_numeric($options['created_by']) ? (int) $options['created_by'] : null;

        // Resolve partner price from IspPlan if assigned
        $partnerPrice = $price;
        $commission = 0.0;
        if ($profileId !== null) {
            /** @var IspPlan|null $serviceProfile */
            $serviceProfile = IspPlan::find($profileId);
            if ($serviceProfile !== null && $serviceProfile->partner_price > 0) {
                $partnerPrice = (float) $serviceProfile->partner_price;
                $commission = $price - $partnerPrice;
            }
        }

        // If partner assigned, resolve commission from partner rate
        if ($partnerId !== null) {
            /** @var Partner|null $partner */
            $partner = Partner::find($partnerId);
            if ($partner !== null && $partner->commission_rate > 0 && $commission === 0.0) {
                $commission = round($price * ($partner->commission_rate / 100), 2);
            }
        }

        DB::transaction(function () use (
            $profile,
            $price,
            $count,
            $prefix,
            $pattern,
            $duration,
            $batchId,
            $partnerId,
            $profileId,
            $createdBy,
            $commission,
            &$vouchers
        ): void {
            // Create VoucherBatch record
            $batchCode = 'BATCH-'.now()->format('ymd').'-'.Str::upper(Str::random(4));
            VoucherBatch::create([
                'batch_code' => $batchCode,
                'profile_id' => $profileId,
                'partner_id' => $partnerId,
                'quantity' => $count,
                'price_per_unit' => $price,
                'total_value' => $price * $count,
                'status' => 'generated',
                'created_by' => $createdBy,
            ]);

            for ($i = 0; $i < $count; $i++) {
                $code = ($prefix ?? '').$this->generateCode($pattern);

                // Ensure uniqueness in local DB
                while (Voucher::where('code', $code)->exists()) {
                    $code = ($prefix ?? '').$this->generateCode($pattern);
                }

                $voucher = Voucher::create([
                    'code' => $code,
                    'profile' => $profile,
                    'profile_id' => $profileId,
                    'partner_id' => $partnerId,
                    'batch_id' => $batchId,
                    'batch_code' => $batchCode,
                    'price' => $price,
                    'commission' => $commission,
                    'status' => 'Available',
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
     * Create a single hotspot voucher.
     *
     * @param  array<string, mixed>  $data
     */
    public function createSingleVoucher(array $data, ?User $createdBy = null): Voucher
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $profile = is_string($data['profile'] ?? null) ? (string) $data['profile'] : '';
            $profileId = is_numeric($data['profile_id'] ?? null) ? (int) $data['profile_id'] : null;
            $partnerId = is_numeric($data['partner_id'] ?? null) ? (int) $data['partner_id'] : null;
            $price = is_numeric($data['price'] ?? null) ? (float) $data['price'] : 0.0;
            $code = is_string($data['code'] ?? null) ? (string) $data['code'] : '';
            $duration = is_string($data['duration'] ?? null) ? (string) $data['duration'] : null;

            // Calculate commission/HPP similar to generateBatch
            $commission = 0.0;
            if ($profileId !== null) {
                /** @var IspPlan|null $serviceProfile */
                $serviceProfile = IspPlan::find($profileId);
                if ($serviceProfile !== null && $serviceProfile->partner_price > 0) {
                    $commission = $price - (float) $serviceProfile->partner_price;
                }
            }

            if ($partnerId !== null && $commission === 0.0) {
                /** @var Partner|null $partner */
                $partner = Partner::find($partnerId);
                if ($partner !== null && $partner->commission_rate > 0) {
                    $commission = round($price * ($partner->commission_rate / 100), 2);
                }
            }

            // Deduct partner saldo if partner provided
            if ($partnerId !== null) {
                /** @var Partner $partner */
                $partner = Partner::findOrFail($partnerId);
                $cost = $price - $commission;

                if (! $partner->hasSufficientSaldo($cost)) {
                    throw new \RuntimeException('Insufficient partner saldo and debt limit exceeded');
                }

                $partner->addDebit(
                    $cost,
                    'voucher_purchase',
                    "Single voucher purchase: {$code}",
                    null,
                    null,
                    $createdBy?->id
                );
            }

            $voucher = Voucher::create([
                'code' => $code,
                'profile' => $profile,
                'profile_id' => $profileId,
                'partner_id' => $partnerId,
                'price' => $price,
                'commission' => $commission,
                'status' => 'Available',
                'sold_at' => $partnerId ? now() : null,
                'sold_by' => $partnerId ? $createdBy?->id : null,
            ]);

            $this->syncToRadius($code, $profile, $duration);

            return $voucher;
        });
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
     * Sell voucher(s) to a partner - deducts partner saldo and tracks sale.
     *
     * @param  array<int>  $voucherIds
     * @return array<string, mixed>
     */
    public function sellToPartner(array $voucherIds, Partner $partner, ?User $soldBy = null): array
    {
        $sold = [];

        DB::transaction(function () use ($voucherIds, $partner, $soldBy, &$sold): void {
            $vouchers = Voucher::whereIn('id', $voucherIds)
                ->where('status', 'Available')
                ->get();

            $totalCost = 0.0;

            foreach ($vouchers as $voucher) {
                $sellingPrice = (float) $voucher->price;
                $commission = (float) $voucher->commission;
                $partnerCost = $sellingPrice - $commission;

                $totalCost += $partnerCost;
            }

            // Check partner saldo or hutang limit
            if (! $partner->hasSufficientSaldo($totalCost)) {
                throw new \RuntimeException('Insufficient saldo and debt limit exceeded');
            }

            // Process each voucher
            foreach ($vouchers as $voucher) {
                $sellingPrice = (float) $voucher->price;
                $commission = (float) $voucher->commission;
                $partnerCost = $sellingPrice - $commission;

                // Update voucher
                $voucher->update([
                    'partner_id' => $partner->id,
                    'sold_at' => now(),
                    'sold_by' => $soldBy?->id,
                    'status' => 'Sold',
                ]);

                $sold[] = $voucher;
            }

            // Debit partner saldo in one transaction
            if ($totalCost > 0) {
                $partner->addDebit(
                    $totalCost,
                    'voucher_purchase',
                    'Purchased '.count($sold).' voucher(s)',
                    null,
                    null,
                    $soldBy?->id
                );
            }
        });

        return [
            'sold_count' => count($sold),
            'vouchers' => $sold,
        ];
    }

    /**
     * Get sales report for a specific month, optionally filtered by partner.
     *
     * @return array<int, array{date: string, count: int, revenue: float, commission: float}>
     */
    public function getSalesReport(int $year, int $month, ?int $partnerId = null): array
    {
        $startDate = now()->setYear($year)->setMonth($month)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $query = Voucher::whereIn('status', ['Used', 'Sold'])
            ->whereBetween('used_at', [$startDate, $endDate]);

        if ($partnerId !== null) {
            $query->where('partner_id', $partnerId);
        }

        $results = $query
            ->selectRaw('DATE(used_at) as date, COUNT(*) as count, SUM(price) as revenue, COALESCE(SUM(commission), 0) as commission')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        /** @var array<int, array{date: string, count: int, revenue: float, commission: float}> $report */
        $report = $results->map(function ($row) {
            $rowArr = (array) $row->getAttributes();

            return [
                'date' => is_string($rowArr['date'] ?? null) ? (string) $rowArr['date'] : '',
                'count' => is_numeric($rowArr['count'] ?? null) ? (int) $rowArr['count'] : 0,
                'revenue' => is_numeric($rowArr['revenue'] ?? null) ? (float) $rowArr['revenue'] : 0.0,
                'commission' => is_numeric($rowArr['commission'] ?? null) ? (float) $rowArr['commission'] : 0.0,
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

        $todayStats = Voucher::whereIn('status', ['Used', 'Sold'])
            ->where('used_at', '>=', $todayStart)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(price), 0) as revenue')
            ->first();

        $monthStats = Voucher::whereIn('status', ['Used', 'Sold'])
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

    /**
     * Get stock summary grouped by profile.
     *
     * @return array<int, array{profile: string, available: int, sold: int, used: int, total: int, value: float}>
     */
    public function getStockSummary(): array
    {
        $results = Voucher::selectRaw(
            'profile, '.
            'SUM(CASE WHEN status = \'Available\' THEN 1 ELSE 0 END) as available, '.
            'SUM(CASE WHEN status = \'Sold\' THEN 1 ELSE 0 END) as sold, '.
            'SUM(CASE WHEN status = \'Used\' THEN 1 ELSE 0 END) as used, '.
            'COUNT(*) as total, '.
            'SUM(CASE WHEN status = \'Available\' THEN price ELSE 0 END) as value'
        )
            ->groupBy('profile')
            ->get();

        /** @var array<int, array{profile: string, available: int, sold: int, used: int, total: int, value: float}> $summary */
        $summary = $results->map(function ($row) {
            $rowArr = (array) $row->getAttributes();

            return [
                'profile' => is_string($rowArr['profile'] ?? null) ? (string) $rowArr['profile'] : '',
                'available' => is_numeric($rowArr['available'] ?? null) ? (int) $rowArr['available'] : 0,
                'sold' => is_numeric($rowArr['sold'] ?? null) ? (int) $rowArr['sold'] : 0,
                'used' => is_numeric($rowArr['used'] ?? null) ? (int) $rowArr['used'] : 0,
                'total' => is_numeric($rowArr['total'] ?? null) ? (int) $rowArr['total'] : 0,
                'value' => is_numeric($rowArr['value'] ?? null) ? (float) $rowArr['value'] : 0.0,
            ];
        })->toArray();

        return $summary;
    }

    /**
     * Get global stock summary (totals).
     *
     * @return array{total_stock: int, total_available: int, total_sold: int, total_used: int, total_value: float, total_hpp: float}
     */
    public function getGlobalStockSummary(): array
    {
        $stats = Voucher::selectRaw(
            'COUNT(*) as total_stock, '.
            'SUM(CASE WHEN status = \'Available\' THEN 1 ELSE 0 END) as total_available, '.
            'SUM(CASE WHEN status = \'Sold\' THEN 1 ELSE 0 END) as total_sold, '.
            'SUM(CASE WHEN status = \'Used\' THEN 1 ELSE 0 END) as total_used, '.
            'SUM(CASE WHEN status = \'Available\' THEN price ELSE 0 END) as total_value, '.
            'SUM(CASE WHEN status = \'Available\' THEN (price - COALESCE(commission, 0)) ELSE 0 END) as total_hpp'
        )->first();

        $statsArr = $stats ? (array) $stats->getAttributes() : [];

        return [
            'total_stock' => is_numeric($statsArr['total_stock'] ?? null) ? (int) $statsArr['total_stock'] : 0,
            'total_available' => is_numeric($statsArr['total_available'] ?? null) ? (int) $statsArr['total_available'] : 0,
            'total_sold' => is_numeric($statsArr['total_sold'] ?? null) ? (int) $statsArr['total_sold'] : 0,
            'total_used' => is_numeric($statsArr['total_used'] ?? null) ? (int) $statsArr['total_used'] : 0,
            'total_value' => is_numeric($statsArr['total_value'] ?? null) ? (float) $statsArr['total_value'] : 0.0,
            'total_hpp' => is_numeric($statsArr['total_hpp'] ?? null) ? (float) $statsArr['total_hpp'] : 0.0,
        ];
    }

    // ============================================================
    // EXPORT METHODS
    // ============================================================

    /**
     * Export vouchers to RouterOS import script format.
     * Useful for manual backup/import to Mikrotik.
     *
     * @param  array<string, mixed>  $filters
     */
    public function exportToScript(array $filters = []): string
    {
        $query = Voucher::query();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['batch_id'])) {
            $query->where('batch_id', $filters['batch_id']);
        }
        if (! empty($filters['profile'])) {
            $query->where('profile', $filters['profile']);
        }

        $vouchers = $query->get();

        $script = "# RouterOS Voucher Import Script\n";
        $script .= '# Generated: '.now()->toDateTimeString()."\n";
        $script .= '# Total: '.$vouchers->count()." vouchers\n\n";

        foreach ($vouchers as $voucher) {
            $name = (string) $voucher->code;
            $password = (string) $voucher->code;
            $profile = (string) ($voucher->profile ?? 'default');
            $comment = 'Generated voucher - Batch: '.($voucher->batch_id ?? 'N/A');

            $script .= "/ip hotspot user add name=\"{$name}\" password=\"{$password}\" profile=\"{$profile}\" comment=\"{$comment}\"\n";
        }

        return $script;
    }

    /**
     * Export vouchers to CSV format.
     *
     * @param  array<string, mixed>  $filters
     */
    public function exportToCsv(array $filters = []): string
    {
        $query = Voucher::query();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['batch_id'])) {
            $query->where('batch_id', $filters['batch_id']);
        }
        if (! empty($filters['profile'])) {
            $query->where('profile', $filters['profile']);
        }

        $vouchers = $query->get();

        $csv = "Code,Profile,Status,Price,Commission,Partner,Created At,Used At,Sold At\n";

        foreach ($vouchers as $voucher) {
            $code = (string) $voucher->code;
            $profile = (string) ($voucher->profile ?? 'default');
            $status = (string) $voucher->status;
            $price = number_format((float) $voucher->price, 0, '', '');
            $commission = number_format((float) ($voucher->commission ?? 0), 0, '', '');
            $partnerName = $voucher->partner ? (string) $voucher->partner->name : '';
            $createdAt = $voucher->created_at ? $voucher->created_at->format('Y-m-d H:i:s') : '';
            $usedAt = $voucher->used_at ? $voucher->used_at->format('Y-m-d H:i:s') : '';
            $soldAt = $voucher->sold_at ? $voucher->sold_at->format('Y-m-d H:i:s') : '';

            $csv .= "\"{$code}\",\"{$profile}\",\"{$status}\",{$price},{$commission},\"{$partnerName}\",\"{$createdAt}\",\"{$usedAt}\",\"{$soldAt}\"\n";
        }

        return $csv;
    }

    /**
     * Delete multiple vouchers by ID.
     *
     * @param  array<int>  $ids
     */
    public function bulkDelete(array $ids): void
    {
        Voucher::whereIn('id', $ids)
            ->where('status', '!=', 'Used')
            ->delete();
    }

    /**
     * Delete vouchers by criteria (e.g. Expired before date).
     *
     * @param  array<string, mixed>  $criteria
     * @return int Number of deleted records
     */
    public function deleteByCriteria(array $criteria): int
    {
        $query = Voucher::query();

        if (isset($criteria['status'])) {
            $query->where('status', $criteria['status']);
        } else {
            $query->whereIn('status', ['Expired', 'Available']);
        }

        if (isset($criteria['before_date'])) {
            $query->where('created_at', '<', $criteria['before_date']);
        }

        // Safety: Never delete Sold or Used via this method unless explicitly allowed?
        // Let's keep it strict for cleanup.
        $query->whereNotIn('status', ['Sold', 'Used']);

        $deletedCount = $query->delete();

        return is_numeric($deletedCount) ? (int) $deletedCount : 0;
    }

    /**
     * Update status for multiple vouchers.
     *
     * @param  array<int>  $ids
     */
    public function bulkUpdateStatus(array $ids, string $status): void
    {
        Voucher::whereIn('id', $ids)
            ->where('status', '!=', 'Used')
            ->update([
                'status' => $status,
                'updated_at' => now(),
            ]);
    }

    /**
     * Refund a sold voucher.
     */
    public function refundVoucher(Voucher $voucher, ?User $actor = null): void
    {
        if ($voucher->status !== 'Sold' && $voucher->status !== 'Available') {
            return;
        }

        DB::transaction(function () use ($voucher, $actor) {
            $partner = $voucher->partner;

            if ($partner !== null && $voucher->status === 'Sold') {
                $cost = (float) $voucher->price - (float) $voucher->commission;

                $partner->addCredit(
                    $cost,
                    'refund',
                    "Voucher refund: {$voucher->code}",
                    null,
                    null,
                    $actor?->id
                );
            }

            $voucher->update([
                'status' => 'Available',
                'partner_id' => null,
                'sold_at' => null,
                'sold_by' => null,
                'used_at' => null,
            ]);
        });
    }

    /**
     * Reset voucher usage counter in RADIUS.
     */
    public function resetVoucherCounter(Voucher $voucher): void
    {
        try {
            // Logic to clear usage in radacct or similar
            // For now, we clear the used_at in our DB
            $voucher->update(['used_at' => null]);

            // If FreeRADIUS integration is active, clear stats there too
            // RadiusIntegration::clearUsage($voucher->code);
        } catch (\Exception $e) {
            throw new \RuntimeException("Radius reset failed: {$e->getMessage()}");
        }
    }
}
