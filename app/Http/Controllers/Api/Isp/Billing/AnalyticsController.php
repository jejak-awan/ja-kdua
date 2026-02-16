<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Billing;

use App\Http\Controllers\Controller;
use App\Services\Isp\Billing\BusinessIntelligenceService;
use App\Services\Isp\Billing\BillingAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    protected BusinessIntelligenceService $biService;
    protected BillingAnalyticsService $billingService;

    public function __construct(
        BusinessIntelligenceService $biService,
        BillingAnalyticsService $billingService
    ) {
        $this->biService = $biService;
        $this->billingService = $billingService;
    }

    /**
     * Get high-level BI metrics for the executive dashboard.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'mrr' => $this->biService->getMrr(),
            'arpu' => $this->billingService->getArpu(),
            'clv' => $this->biService->getClv(),
            'churn_risk_count' => count($this->billingService->getChurnRiskCustomers(100)),
            'growth_trends' => $this->biService->getChurnRate(6), // Reusing format for now
        ]);
    }

    /**
     * Get financial projections and trends.
     */
    public function projections(): JsonResponse
    {
        return response()->json([
            'current_mrr' => $this->biService->getMrr(),
            'projections' => $this->biService->getFinancialProjections(12),
            'historical_churn' => $this->biService->getChurnRate(12),
            'customer_growth' => $this->billingService->getGrowthTrends(6),
        ]);
    }

    /**
     * Get detailed churn risk analysis.
     */
    public function churnRisk(): JsonResponse
    {
        return response()->json($this->billingService->getChurnRiskCustomers(20));
    }
}
