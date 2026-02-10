<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Business;

use App\Http\Controllers\Api\Core\BaseApiController;
use App\Models\Isp\Partner;
use App\Services\Isp\PartnerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PartnerController extends BaseApiController
{
    public function __construct(
        private readonly PartnerService $partnerService
    ) {}

    /**
     * Get all partners.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'category' => $request->query('category'),
                'status' => $request->query('status'),
                'search' => $request->query('search'),
            ];

            $partners = $this->partnerService->getAll(array_filter($filters));

            return $this->success($partners, 'Partners retrieved successfully');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to retrieve partners', 'PartnerController@index');
        }
    }

    /**
     * Get a single partner.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $partner = Partner::with(['user', 'transactions'])->find($id);

            if ($partner === null) {
                return $this->notFound('Partner');
            }

            $statistics = $this->partnerService->getStatistics($partner);

            return $this->success([
                'partner' => $partner,
                'statistics' => $statistics,
            ], 'Partner retrieved successfully');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to retrieve partner', 'PartnerController@show');
        }
    }

    /**
     * Create a new partner.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'nullable|exists:users,id',
                'name' => 'required|string|max:255',
                'category' => ['required', Rule::in(['reseller', 'biller'])],
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'saldo' => 'nullable|numeric|min:0',
                'limit_hutang' => 'nullable|numeric|min:0',
                'commission_rate' => 'nullable|numeric|min:0|max:100',
                'status' => ['nullable', Rule::in(['active', 'inactive'])],
            ]);

            $partner = $this->partnerService->create($validated);

            return $this->success($partner, 'Partner created successfully', 201);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to create partner', 'PartnerController@store');
        }
    }

    /**
     * Update a partner.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $partner = Partner::find($id);

            if ($partner === null) {
                return $this->notFound('Partner');
            }

            $validated = $request->validate([
                'user_id' => 'nullable|exists:users,id',
                'name' => 'sometimes|required|string|max:255',
                'category' => ['sometimes', Rule::in(['reseller', 'biller'])],
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'limit_hutang' => 'nullable|numeric|min:0',
                'commission_rate' => 'nullable|numeric|min:0|max:100',
                'status' => ['nullable', Rule::in(['active', 'inactive'])],
            ]);

            $partner = $this->partnerService->update($partner, $validated);

            return $this->success($partner, 'Partner updated successfully');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to update partner', 'PartnerController@update');
        }
    }

    /**
     * Delete a partner.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $partner = Partner::find($id);

            if ($partner === null) {
                return $this->notFound('Partner');
            }

            $this->partnerService->delete($partner);

            return $this->success(null, 'Partner deleted successfully');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to delete partner', 'PartnerController@destroy');
        }
    }

    /**
     * Add credit to partner saldo.
     */
    public function addCredit(Request $request, int $id): JsonResponse
    {
        try {
            $partner = Partner::find($id);

            if ($partner === null) {
                return $this->notFound('Partner');
            }

            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'category' => 'required|string|max:100',
                'description' => 'nullable|string|max:255',
            ]);

            $user = $request->user();
            $transaction = $this->partnerService->addCredit(
                $partner,
                (float) $validated['amount'],
                $validated['category'],
                $validated['description'] ?? '',
                $user
            );

            return $this->success([
                'transaction' => $transaction,
                'new_saldo' => $partner->fresh()?->saldo,
            ], 'Credit added successfully');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to add credit', 'PartnerController@addCredit');
        }
    }

    /**
     * Deduct from partner saldo.
     */
    public function addDebit(Request $request, int $id): JsonResponse
    {
        try {
            $partner = Partner::find($id);

            if ($partner === null) {
                return $this->notFound('Partner');
            }

            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'category' => 'required|string|max:100',
                'description' => 'nullable|string|max:255',
            ]);

            $amount = (float) $validated['amount'];
            if (! $partner->hasSufficientSaldo($amount)) {
                return $this->error('Insufficient balance and debt limit exceeded', 400, [], 'INSUFFICIENT_BALANCE');
            }

            $user = $request->user();
            $transaction = $this->partnerService->addDebit(
                $partner,
                $amount,
                $validated['category'],
                $validated['description'] ?? '',
                $user
            );

            return $this->success([
                'transaction' => $transaction,
                'new_saldo' => $partner->fresh()?->saldo,
            ], 'Debit added successfully');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to add debit', 'PartnerController@addDebit');
        }
    }

    /**
     * Get partner transactions.
     */
    public function transactions(Request $request, int $id): JsonResponse
    {
        try {
            $partner = Partner::find($id);

            if ($partner === null) {
                return $this->notFound('Partner');
            }

            $fromDate = $request->query('from_date');
            $toDate = $request->query('to_date');

            $transactions = $this->partnerService->getTransactions(
                $partner,
                is_string($fromDate) ? $fromDate : null,
                is_string($toDate) ? $toDate : null
            );

            return $this->success($transactions, 'Transactions retrieved successfully');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to retrieve transactions', 'PartnerController@transactions');
        }
    }
}
