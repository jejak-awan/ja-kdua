<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Business;

use App\Http\Controllers\Api\Core\BaseApiController;
use App\Models\Isp\Invoice;
use App\Models\Isp\PaymentGateway;
use App\Services\Isp\PaymentGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends BaseApiController
{
    protected PaymentGatewayService $paymentService;

    public function __construct(PaymentGatewayService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Create a payment transaction for an invoice.
     */
    public function createTransaction(Request $request, Invoice $invoice): JsonResponse
    {
        $user = Auth::user();
        if (! $user || $invoice->user_id !== $user->id) {
            return $this->unauthorized();
        }

        if ($invoice->status === 'paid') {
            return $this->error('Invoice is already paid');
        }

        $transaction = $this->paymentService->initializePayment($invoice);

        return $this->success($transaction, 'Payment transaction initialized');
    }

    /**
     * Display a listing of payment gateways.
     */
    public function indexGateways(): JsonResponse
    {
        return $this->success(PaymentGateway::all());
    }

    /**
     * Update gateway configuration.
     */
    public function updateGateway(Request $request, PaymentGateway $gateway): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'config' => 'required|array',
            'is_active' => 'required|boolean',
        ]);

        if ($request->input('is_active')) {
            // Deactivate other gateways
            PaymentGateway::where('id', '!=', $gateway->id)->update(['is_active' => false]);
        }

        $gateway->update($request->only(['name', 'config', 'is_active']));

        return $this->success($gateway, 'Gateway updated successfully');
    }

    /**
     * Handle payment gateway callback (Webhook).
     */
    public function callback(Request $request): JsonResponse
    {
        // Simulated webhook logic
        $orderIdRaw = $request->input('order_id');
        $orderId = is_scalar($orderIdRaw) ? (string) $orderIdRaw : '';

        $statusRaw = $request->input('transaction_status');
        $status = is_scalar($statusRaw) ? (string) $statusRaw : '';

        Log::info('Payment callback received', ['order_id' => $orderId, 'status' => $status]);

        // In real app, verify signature and update invoice status
        if ($status === 'settlement' || $status === 'capture') {
            $invoice = Invoice::find($orderId);
            if ($invoice) {
                $invoice->update(['status' => 'paid']);
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
