<?php

declare(strict_types=1);

namespace App\Services\Isp\Customer;

use App\Models\Isp\Customer\Contract;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ContractService
{
    /**
     * Get all contracts with pagination
     *
     * @param  array<string, mixed>  $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, \App\Models\Isp\Customer\Contract>
     */
    public function getContracts(array $filters = [])
    {
        $query = Contract::with('customer');

        if (! empty($filters['search'])) {
            $searchRaw = $filters['search'];
            $search = is_scalar($searchRaw) ? (string) $searchRaw : '';
            $query->where(function ($q) use ($search) {
                $q->where('contract_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate(15);
    }

    /**
     * Create a new contract
     *
     * @param  array<string, mixed>  $data
     */
    public function createContract(array $data, ?UploadedFile $file = null): Contract
    {
        if ($file) {
            $data['file_path'] = $file->store('contracts', 'public');
        }

        return Contract::create($data);
    }

    /**
     * Update an existing contract
     *
     * @param  array<string, mixed>  $data
     */
    public function updateContract(Contract $contract, array $data, ?UploadedFile $file = null): Contract
    {
        if ($file) {
            // Delete old file if exists
            if ($contract->file_path) {
                Storage::disk('public')->delete($contract->file_path);
            }
            $data['file_path'] = $file->store('contracts', 'public');
        }

        $contract->update($data);

        return $contract;
    }

    /**
     * Delete a contract
     */
    public function deleteContract(Contract $contract): bool
    {
        if ($contract->file_path) {
            Storage::disk('public')->delete($contract->file_path);
        }

        return (bool) $contract->delete();
    }
}
