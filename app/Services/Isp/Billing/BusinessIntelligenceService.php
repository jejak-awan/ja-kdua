<?php

declare(strict_types=1);

namespace App\Services\Isp\Billing;

use App\Models\Isp\Customer\Customer;
use App\Models\Isp\Billing\Invoice;
use App\Models\Isp\Billing\IspPlan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BusinessIntelligenceService
{
    /**
     * Calculate Monthly Recurring Revenue (MRR).
     * This sums the monthly price of all active fiber/hotspot plans.
     */
    public function getMrr(): float
    {
        return (float) DB::table('isp_customers')
            ->join('isp_plans', 'isp_customers.billing_plan_id', '=', 'isp_plans.id')
            ->where('isp_customers.status', 'active')
            ->whereNull('isp_plans.deleted_at')
            ->sum('isp_plans.price');
    }

    /**
     * Calculate Churn Rate for the last 12 months.
     * Returns an array of monthly churn percentages.
     *
     * @return array<string, float>
     */
    public function getChurnRate(int $months = 12): array
    {
        $churnData = [];
        $now = Carbon::now();

        for ($i = 0; $i < $months; $i++) {
            $month = $now->copy()->subMonths($i);
            $monthKey = $month->format('Y-m');

            // Total customers at start of month
            $totalAtStart = Customer::where('created_at', '<', $month->startOfMonth())->count();
            
            // Customers who became 'inactive' or 'cancelled' during this month
            $churnedThisMonth = Customer::whereIn('status', ['inactive', 'cancelled'])
                ->whereBetween('updated_at', [$month->startOfMonth(), $month->endOfMonth()])
                ->count();

            $rate = $totalAtStart > 0 ? ($churnedThisMonth / $totalAtStart) * 100 : 0.0;
            $churnData[$monthKey] = round($rate, 2);
        }

        return array_reverse($churnData);
    }

    /**
     * Get Financial Projections for the next 12 months.
     * Based on current MRR and average 6-month growth rate.
     *
     * @return array<string, float>
     */
    public function getFinancialProjections(int $forecastMonths = 12): array
    {
        $currentMrr = $this->getMrr();
        
        // Calculate average growth rate over last 6 months
        $growthRates = [];
        for ($i = 1; $i <= 6; $i++) {
            $end = Carbon::now()->subMonths($i-1)->startOfMonth();
            $start = Carbon::now()->subMonths($i)->startOfMonth();
            
            $countEnd = Customer::where('created_at', '<', $end)->count();
            $countStart = Customer::where('created_at', '<', $start)->count();
            
            if ($countStart > 0) {
                $growthRates[] = ($countEnd - $countStart) / $countStart;
            }
        }
        
        $avgGrowth = !empty($growthRates) ? array_sum($growthRates) / count($growthRates) : 0.05; // Default 5% if no data
        
        $projections = [];
        $runningMrr = $currentMrr;
        
        for ($j = 1; $j <= $forecastMonths; $j++) {
            $month = Carbon::now()->addMonths($j)->format('Y-m');
            $runningMrr = $runningMrr * (1 + $avgGrowth);
            $projections[$month] = round($runningMrr, 2);
        }
        
        return $projections;
    }

    /**
     * Calculate Customer Lifetime Value (CLV).
     * ARPU / Churn Rate.
     */
    public function getClv(): float
    {
        $activeCustomers = Customer::where('status', 'active')->count();
        if ($activeCustomers === 0) return 0.0;

        $mrr = $this->getMrr();
        $arpu = $mrr / $activeCustomers;
        
        // Get average churn rate of last 6 months
        $churnRates = $this->getChurnRate(6);
        $avgChurn = !empty($churnRates) ? array_sum($churnRates) / count($churnRates) : 2.0; // Default 2%
        
        if ($avgChurn <= 0) return $arpu * 24; // If no churn, assume 2 years retention

        return round($arpu / ($avgChurn / 100), 2);
    }
}
