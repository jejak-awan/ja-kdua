<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\Isp\Contract;
use App\Services\Isp\ContractService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ContractController extends BaseApiController
{
    protected ContractService $contractService;

    public function __construct(ContractService $contractService)
    {
        $this->contractService = $contractService;
    }

    /**
     * Display a listing of contracts.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status']);
        $contracts = $this->contractService->getContracts($filters);

        return $this->success($contracts);
    }

    /**
     * Store a newly created contract in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'customer_id' => 'required|exists:isp_customers,id',
            'contract_number' => 'required|string|unique:isp_contracts,contract_number',
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:active,expired,terminated',
            'file' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $contract = $this->contractService->createContract(
            $request->except('file'),
            $request->file('file')
        );

        return $this->success($contract, 'Contract created successfully', 201);
    }

    /**
     * Display the specified contract.
     */
    public function show(Contract $contract): JsonResponse
    {
        return $this->success($contract->load('customer'));
    }

    /**
     * Update the specified contract in storage.
     */
    public function update(Request $request, Contract $contract): JsonResponse
    {
        $request->validate([
            'contract_number' => 'required|string|unique:isp_contracts,contract_number,' . $contract->id,
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:active,expired,terminated',
            'file' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $updatedContract = $this->contractService->updateContract(
            $contract,
            $request->except('file'),
            $request->file('file')
        );

        return $this->success($updatedContract, 'Contract updated successfully');
    }

    /**
     * Remove the specified contract from storage.
     */
    public function destroy(Contract $contract): JsonResponse
    {
        $this->contractService->deleteContract($contract);

        return $this->success(null, 'Contract deleted successfully');
    }
}
