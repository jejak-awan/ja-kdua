<?php

declare(strict_types=1);

namespace App\Services\Isp\Billing;

use App\Models\Isp\Customer\Customer;
use App\Models\Isp\Billing\Invoice;
use App\Models\Isp\Network\ServiceNode;
use Illuminate\Support\Facades\DB;

class BillingAnalyticsService
{
    /**
     * Calculate Average Revenue Per User (ARPU).
     */
    public function getArpu(int $months = 1): float
    {
        $activeCustomers = Customer::where('status', 'active')->count();
        if ($activeCustomers === 0) {
            return 0.0;
        }

        $startDate = now()->subMonths($months)->startOfMonth();

        $revenue = Invoice::where('created_at', '>=', $startDate)
            ->where('status', 'paid')
            ->sum('amount');

        return round((float) $revenue / $activeCustomers / $months, 2);
    }

    /**
     * Calculate ARPU per Node.
     *
     * @return array<string, float>
     */
    public function getArpuPerNode(): array
    {
        $nodes = ServiceNode::where('type', 'Router')->get();
        $results = [];

        foreach ($nodes as $node) {
            $customerIds = Customer::where('router_id', $node->id)->pluck('user_id');
            if ($customerIds->isEmpty()) {
                $results[$node->name] = 0.0;

                continue;
            }

            $revenue = Invoice::whereIn('user_id', $customerIds)
                ->where('status', 'paid')
                ->where('created_at', '>=', now()->subMonth())
                ->sum('amount');

            $results[$node->name] = round((float) $revenue / count($customerIds), 2);
        }

        return $results;
    }

    /**
     * Calculate churn probability for active customers.
     * High churn if:
     * 1. Has unpaid overdue invoices.
     * 2. Frequent disconnects in health logs (simulated analysis).
     *
     * @return array<int, mixed>
     */
    public function getChurnRiskCustomers(int $limit = 10): array
    {
        return Customer::with(['user', 'plan'])
            ->where('status', 'active')
            ->get()
            ->map(function ($customer) {
                $score = 0;

                // Risk 1: Overdue payment
                $overdueCount = Invoice::where('user_id', $customer->user_id)
                    ->where('status', 'unpaid')
                    ->where('due_date', '<', now())
                    ->count();
                $score += $overdueCount * 30;

                // Risk 2: Health issues (Metadata check)
                $lastAudit = $customer->router->metadata['last_audit'] ?? [];
                if (is_array($lastAudit) && ($lastAudit['ghost_count'] ?? 0) > 0) {
                    $score += 10;
                }

                return [
                    'customer_id' => $customer->id,
                    'name' => $customer->user->name,
                    'plan' => $customer->plan?->name,
                    'risk_score' => min($score, 100),
                    'status' => $score > 50 ? 'High Risk' : ($score > 20 ? 'Medium Risk' : 'Low Risk'),
                ];
            })
            ->sortByDesc('risk_score')
            ->take($limit)
            ->values()
            ->toArray();
    }

    /**
     * Get monthly growth trends.
     *
     * @return array<int, mixed>
     */
    public function getGrowthTrends(int $months = 6): array
    {
        return DB::table('isp_customers')
            ->select(
                DB::raw("to_char(created_at, 'YYYY-MM') as month"),
                DB::raw('COUNT(*) as total_customers'),
                DB::raw("SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_customers")
            )
            ->where('created_at', '>=', now()->subMonths($months))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get()
            ->toArray();
    }
}
