<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Billing;

use App\Http\Controllers\Api\Core\BaseApiController;
use App\Models\Isp\Customer\Customer;
use App\Services\Isp\Billing\SaldoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaldoController extends BaseApiController
{
    public function __construct(
        protected SaldoService $saldoService
    ) {}

    /**
     * Get customer saldo and recent transactions.
     */
    public function show(int $customerId): JsonResponse
    {
        try {
            /** @var Customer $customer */
            $customer = Customer::findOrFail($customerId);

            return $this->success([
                'customer_id' => $customer->id,
                'saldo' => (float) $customer->saldo,
            ], 'Customer saldo retrieved');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to get saldo', 'SaldoController@show');
        }
    }

    /**
     * Add credit (top up) to customer saldo.
     */
    public function addCredit(Request $request, int $customerId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:1',
                'category' => 'required|string|in:topup,refund,bonus,adjustment',
                'description' => 'nullable|string|max:255',
            ]);

            /** @var Customer $customer */
            $customer = Customer::findOrFail($customerId);

            $transaction = $this->saldoService->addCredit(
                $customer,
                (float) $validated['amount'],
                (string) $validated['category'],
                (string) ($validated['description'] ?? ''),
                null,
                null,
                $request->user()
            );

            return $this->success($transaction, 'Credit added successfully');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to add credit', 'SaldoController@addCredit');
        }
    }

    /**
     * Debit from customer saldo.
     */
    public function addDebit(Request $request, int $customerId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:1',
                'category' => 'required|string|in:invoice_payment,withdrawal,adjustment,penalty',
                'description' => 'nullable|string|max:255',
            ]);

            /** @var Customer $customer */
            $customer = Customer::findOrFail($customerId);

            $transaction = $this->saldoService->addDebit(
                $customer,
                (float) $validated['amount'],
                (string) $validated['category'],
                (string) ($validated['description'] ?? ''),
                null,
                null,
                $request->user()
            );

            return $this->success($transaction, 'Debit processed successfully');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to process debit', 'SaldoController@addDebit');
        }
    }

    /**
     * Pay invoice with customer saldo.
     */
    public function payInvoice(Request $request, int $customerId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'invoice_id' => 'required|integer|exists:isp_invoices,id',
                'amount' => 'required|numeric|min:1',
            ]);

            /** @var Customer $customer */
            $customer = Customer::findOrFail($customerId);

            $result = $this->saldoService->payWithSaldo(
                $customer,
                (float) $validated['amount'],
                (int) $validated['invoice_id'],
                $request->user()
            );

            if (! $result['success']) {
                return $this->error($result['message'], 422);
            }

            return $this->success($result, 'Invoice paid with saldo');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to pay invoice', 'SaldoController@payInvoice');
        }
    }

    /**
     * Get transaction history for customer.
     */
    public function transactions(Request $request, int $customerId): JsonResponse
    {
        try {
            $filters = $request->only(['type', 'category', 'per_page']);

            $transactions = $this->saldoService->getTransactions($customerId, $filters);

            return $this->success($transactions, 'Transactions retrieved');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to get transactions', 'SaldoController@transactions');
        }
    }
}
