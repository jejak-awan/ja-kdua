<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Support;

use App\Http\Controllers\Api\Core\BaseApiController;
use App\Models\Isp\PrintTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrintTemplateController extends BaseApiController
{
    /**
     * List all print templates, filterable by type.
     */
    public function index(Request $request): JsonResponse
    {
        $type = $request->input('type');

        $query = PrintTemplate::query()->orderByDesc('is_default')->orderBy('name');

        if (is_string($type) && in_array($type, ['voucher', 'invoice'], true)) {
            $query->where('type', $type);
        }

        return $this->success($query->get());
    }

    /**
     * Get a single template.
     */
    public function show(int $id): JsonResponse
    {
        $template = PrintTemplate::findOrFail($id);

        return $this->success($template);
    }

    /**
     * Store a new print template.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:voucher,invoice',
            'paper_size' => 'sometimes|string|max:50',
            'orientation' => 'sometimes|in:portrait,landscape',
            'columns_per_row' => 'sometimes|integer|min:1|max:10',
            'html_content' => 'required|string',
            'css_content' => 'nullable|string',
            'is_default' => 'sometimes|boolean',
        ]);

        $validated['created_by'] = $request->user()?->id;

        // If setting as default, unset other defaults of same type
        if (! empty($validated['is_default'])) {
            PrintTemplate::where('type', $validated['type'])->update(['is_default' => false]);
        }

        $template = PrintTemplate::create($validated);

        return $this->success($template, 'Print template created', 201);
    }

    /**
     * Update a print template.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $template = PrintTemplate::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:voucher,invoice',
            'paper_size' => 'sometimes|string|max:50',
            'orientation' => 'sometimes|in:portrait,landscape',
            'columns_per_row' => 'sometimes|integer|min:1|max:10',
            'html_content' => 'sometimes|string',
            'css_content' => 'nullable|string',
            'is_default' => 'sometimes|boolean',
        ]);

        // If setting as default, unset other defaults of same type
        if (! empty($validated['is_default'])) {
            $type = $validated['type'] ?? $template->type;
            PrintTemplate::where('type', $type)
                ->where('id', '!=', $template->id)
                ->update(['is_default' => false]);
        }

        $template->update($validated);

        return $this->success($template, 'Print template updated');
    }

    /**
     * Delete a print template.
     */
    public function destroy(int $id): JsonResponse
    {
        $template = PrintTemplate::findOrFail($id);
        $template->delete();

        return $this->success(null, 'Print template deleted');
    }

    /**
     * Preview a template with sample data.
     */
    public function preview(Request $request, int $id): JsonResponse
    {
        $template = PrintTemplate::findOrFail($id);

        $sampleData = $template->type === 'voucher'
            ? [
                'username' => 'VOUCHER001',
                'password' => 'pass1234',
                'profile' => '10Mbps Unlimited',
                'price' => 'Rp50.000',
                'price_raw' => '50000',
                'code' => 'VOUCHER001',
                'batch_code' => 'BATCH-2026-001',
                'expired' => '2026-03-10',
                'quota' => '10 GB',
                'duration' => '30 Hari',
                'created_at' => '2026-02-10',
                'company_name' => 'ISP Provider',
            ]
            : [
                'invoice_number' => 'INV-2026-001',
                'customer_name' => 'John Doe',
                'customer_address' => 'Jl. Contoh No. 123',
                'customer_phone' => '08123456789',
                'plan_name' => '10Mbps Unlimited',
                'amount' => 'Rp250.000',
                'amount_raw' => '250000',
                'due_date' => '2026-02-28',
                'period' => 'Februari 2026',
                'status' => 'Unpaid',
                'company_name' => 'ISP Provider',
            ];

        return $this->success([
            'html' => $template->renderFull($sampleData),
            'variables' => $template->type === 'voucher'
                ? PrintTemplate::voucherVariables()
                : PrintTemplate::invoiceVariables(),
        ]);
    }

    /**
     * Get available variables for a template type.
     */
    public function variables(Request $request): JsonResponse
    {
        $type = $request->input('type', 'voucher');

        $variables = $type === 'invoice'
            ? PrintTemplate::invoiceVariables()
            : PrintTemplate::voucherVariables();

        return $this->success($variables);
    }
}
