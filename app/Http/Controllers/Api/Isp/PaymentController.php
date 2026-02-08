<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\Isp\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends BaseApiController
{
    /**
     * Create a payment transaction for an invoice.
     */
    public function createTransaction(Request $request, Invoice $invoice): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (! $user || $invoice->user_id !== $user->id) {
            return $this->unauthorized();
        }

        if ($invoice->status === 'paid') {
            return $this->error('Invoice is already paid');
        }

        // Simulation of Midtrans Snap token generation
        $token = 'snap-token-'.bin2hex(random_bytes(16));
        $redirectUrl = 'https://app.sandbox.midtrans.com/snap/v2/vtweb/'.$token;

        return $this->success([
            'token' => $token,
            'redirect_url' => $redirectUrl,
            'amount' => $invoice->amount,
            'invoice_id' => $invoice->id,
        ], 'Payment transaction initialized');
    }

    /**
     * Handle payment gateway callback (Webhook).
     */
    public function callback(Request $request): \Illuminate\Http\JsonResponse
    {
        // Simulated webhook logic
        $orderId = $request->input('order_id');
        $status = $request->input('transaction_status');

        Log::info('Payment callback received', ['order_id' => $orderId, 'status' => $status]);

        // In real app, verify signature and update invoice status
        if ($status === 'settlement' || $status === 'capture') {
            // $invoice = Invoice::find($orderId);
            // $invoice->update(['status' => 'paid']);
        }

        return response()->json(['status' => 'ok']);
    }
}
