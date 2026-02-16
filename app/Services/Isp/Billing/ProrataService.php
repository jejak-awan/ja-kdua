<?php

declare(strict_types=1);

namespace App\Services\Isp\Billing;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProrataService
{
    /**
     * Calculate the pro-rated amount for a given period.
     *
     * @param  float  $basePrice  The full monthly price
     * @param  Carbon  $start  The start of the pro-rated period
     * @param  Carbon  $end  The end of the pro-rated period (e.g. the next billing date)
     * @param  bool  $inclusive  Whether to include the end date in the calculation
     */
    public function calculate(float $basePrice, Carbon $start, Carbon $end, bool $inclusive = false): float
    {
        // 1. Determine total days in the month (average or actual)
        // Usually safer to use actual days in the start month to be precise
        $daysInMonth = $start->daysInMonth;

        // 2. Calculate daily rate
        $dailyRate = $basePrice / $daysInMonth;

        // 3. Calculate number of days
        $days = $start->diffInDays($end);
        if ($inclusive) {
            $days += 1;
        }

        $amount = round($dailyRate * $days, 2);

        Log::info("Prorata calculation: {$basePrice} from {$start->toDateString()} to {$end->toDateString()} ({$days} days) = {$amount}");

        return $amount;
    }

    /**
     * Calculate the adjustment amount for a plan change mid-cycle.
     */
    public function calculateAdjustment(float $oldPrice, float $newPrice, Carbon $changeDate, Carbon $cycleEndDate): float
    {
        // Remaining portion of the old plan to "credit back" (theoretical)
        $oldRemaining = $this->calculate($oldPrice, $changeDate, $cycleEndDate);

        // Remaining portion of the new plan to "charge"
        $newRemaining = $this->calculate($newPrice, $changeDate, $cycleEndDate);

        // The adjustment is the difference
        return round($newRemaining - $oldRemaining, 2);
    }
}
