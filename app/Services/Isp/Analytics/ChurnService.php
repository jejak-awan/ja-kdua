<?php

declare(strict_types=1);

namespace App\Services\Isp\Analytics;

use App\Models\Isp\Customer\Customer;
use App\Models\Isp\Billing\Invoice;
use App\Models\Isp\Support\ServiceRequest;
use Carbon\Carbon;

class ChurnService
{
    /**
     * Calculate churn risk score for a customer.
     * Scale: 0-100 (Higher is riskier)
     */
    public function calculateRiskScore(Customer $customer): int
    {
        $score = 0;

        // 1. Complaint Frequency (Last 30 days)
        $complaintCount = ServiceRequest::where('customer_id', $customer->id)
            ->whereIn('type', ['Technical', 'Complaint'])
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->count();
        
        $score += ($complaintCount * 20);

        // 2. Payment Reliability (Overdue invoices)
        $overdueCount = Invoice::where('user_id', $customer->user_id)
            ->where('status', 'unpaid')
            ->where('due_date', '<', Carbon::now())
            ->count();
            
        $score += ($overdueCount * 15);

        // 3. Status Weighting
        if ($customer->status === 'isolated') {
            $score += 10;
        }

        // 4. Cap the score at 100
        return (int) min($score, 100);
    }

    /**
     * Get risk level string and color metadata.
     *
     * @return array{level: string, color: string, score: int}
     */
    public function getRiskMetadata(Customer $customer): array
    {
        $score = $this->calculateRiskScore($customer);

        if ($score >= 60) {
            return ['level' => 'Critical', 'color' => 'destructive', 'score' => $score];
        }

        if ($score >= 30) {
            return ['level' => 'Warning', 'color' => 'warning', 'score' => $score];
        }

        return ['level' => 'Healthy', 'color' => 'success', 'score' => $score];
    }
}
