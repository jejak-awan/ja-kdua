<?php

declare(strict_types=1);

namespace App\Services\Isp\Billing;

use App\Models\Isp\Billing\Invoice;
use App\Models\Isp\Billing\PaymentGateway;
use Illuminate\Support\Facades\Log;

class PaymentGatewayService
{
    /**
     * Get active gateway
     */
    public function getActiveGateway(): ?PaymentGateway
    {
        return PaymentGateway::where('is_active', true)->first();
    }

    /**
     * Initialize payment transaction
     *
     * @return array<string, mixed>
     */
    public function initializePayment(Invoice $invoice): array
    {
        $gateway = $this->getActiveGateway();

        if (! $gateway) {
            // Fallback to simulation if no gateway configured
            return $this->simulateMidtrans($invoice);
        }

        switch ($gateway->driver) {
            case 'midtrans':
                return $this->initializeMidtrans($invoice, $gateway->config);
            default:
                return $this->simulateMidtrans($invoice);
        }
    }

    /**
     * Initialize Midtrans payment
     *
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    protected function initializeMidtrans(Invoice $invoice, array $config): array
    {
        // Real implementation would use Midtrans SDK here
        // For now, we simulate but acknowledge config presence
        Log::info('Initializing Midtrans with config', ['invoice_id' => $invoice->id]);

        return $this->simulateMidtrans($invoice);
    }

    /**
     * Simulation for UI testing
     *
     * @return array<string, mixed>
     */
    protected function simulateMidtrans(Invoice $invoice): array
    {
        $token = 'snap-token-'.bin2hex(random_bytes(16));
        $redirectUrl = 'https://app.sandbox.midtrans.com/snap/v2/vtweb/'.$token;

        return [
            'token' => $token,
            'redirect_url' => $redirectUrl,
            'amount' => $invoice->amount,
            'invoice_id' => $invoice->id,
            'simulated' => true,
        ];
    }
}
