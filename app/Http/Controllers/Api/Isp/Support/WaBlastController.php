<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Support;

use App\Http\Controllers\Api\Core\BaseApiController;
use App\Models\Isp\WaBlast;
use App\Models\Isp\WaSchedule;
use App\Models\Isp\WaTemplate;
use App\Services\Isp\WhatsApp\WaBlastService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class WaBlastController extends BaseApiController
{
    public function __construct(
        protected WaBlastService $service
    ) {}

    // ============================================================
    // TEMPLATES
    // ============================================================

    /**
     * List templates.
     */
    public function templates(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['search', 'category', 'per_page']);

            return $this->success($this->service->listTemplates($filters), 'Templates retrieved');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to list templates', 'WaBlastController@templates');
        }
    }

    /**
     * Create template.
     */
    public function storeTemplate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'category' => ['required', Rule::in(['billing', 'outage', 'promo', 'reminder', 'custom'])],
                'body' => 'required|string',
                'variables' => 'nullable|array',
                'is_active' => 'nullable|boolean',
            ]);

            $validated['slug'] = Str::slug($validated['name']);
            $validated['created_by'] = $request->user()?->id;

            $template = $this->service->createTemplate($validated);

            return $this->success($template, 'Template created', 201);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to create template', 'WaBlastController@storeTemplate');
        }
    }

    /**
     * Update template.
     */
    public function updateTemplate(Request $request, int $id): JsonResponse
    {
        try {
            /** @var WaTemplate $template */
            $template = WaTemplate::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'category' => ['sometimes', Rule::in(['billing', 'outage', 'promo', 'reminder', 'custom'])],
                'body' => 'sometimes|string',
                'variables' => 'nullable|array',
                'is_active' => 'nullable|boolean',
            ]);

            $template = $this->service->updateTemplate($template, $validated);

            return $this->success($template, 'Template updated');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to update template', 'WaBlastController@updateTemplate');
        }
    }

    /**
     * Delete template.
     */
    public function destroyTemplate(int $id): JsonResponse
    {
        try {
            /** @var WaTemplate $template */
            $template = WaTemplate::findOrFail($id);
            $template->delete();

            return $this->success(null, 'Template deleted');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to delete template', 'WaBlastController@destroyTemplate');
        }
    }

    // ============================================================
    // BLASTS
    // ============================================================

    /**
     * List blasts.
     */
    public function blasts(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'per_page']);

            return $this->success($this->service->listBlasts($filters), 'Blasts retrieved');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to list blasts', 'WaBlastController@blasts');
        }
    }

    /**
     * Show blast detail with logs.
     */
    public function showBlast(int $id): JsonResponse
    {
        try {
            /** @var WaBlast $blast */
            $blast = WaBlast::with(['template', 'logs'])->findOrFail($id);

            return $this->success($blast, 'Blast retrieved');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to get blast', 'WaBlastController@showBlast');
        }
    }

    /**
     * Create blast (draft).
     */
    public function storeBlast(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'template_id' => 'nullable|integer|exists:isp_wa_templates,id',
                'message' => 'nullable|string',
                'target_filter' => ['required', Rule::in(['all', 'unpaid', 'active', 'inactive', 'custom'])],
                'target_numbers' => 'nullable|array',
                'target_numbers.*' => 'string',
                'scheduled_at' => 'nullable|date|after:now',
            ]);

            $validated['created_by'] = $request->user()?->id;

            $blast = $this->service->createBlast($validated);

            return $this->success($blast, 'Blast created', 201);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to create blast', 'WaBlastController@storeBlast');
        }
    }

    /**
     * Send blast immediately.
     */
    public function sendBlast(int $id): JsonResponse
    {
        try {
            /** @var WaBlast $blast */
            $blast = WaBlast::findOrFail($id);

            if ($blast->status !== 'draft') {
                return $this->error('Blast already processed', 422);
            }

            $result = $this->service->sendBlast($blast);

            return $this->success($result, 'Blast sent');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to send blast', 'WaBlastController@sendBlast');
        }
    }

    /**
     * Delete blast.
     */
    public function destroyBlast(int $id): JsonResponse
    {
        try {
            /** @var WaBlast $blast */
            $blast = WaBlast::findOrFail($id);

            if ($blast->status === 'processing') {
                return $this->error('Cannot delete blast in progress', 422);
            }

            $blast->delete();

            return $this->success(null, 'Blast deleted');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to delete blast', 'WaBlastController@destroyBlast');
        }
    }

    // ============================================================
    // SCHEDULES
    // ============================================================

    /**
     * List schedules.
     */
    public function schedules(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['per_page']);

            return $this->success($this->service->listSchedules($filters), 'Schedules retrieved');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to list schedules', 'WaBlastController@schedules');
        }
    }

    /**
     * Create schedule.
     */
    public function storeSchedule(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'template_id' => 'nullable|integer|exists:isp_wa_templates,id',
                'target_filter' => ['required', Rule::in(['all', 'unpaid', 'active', 'inactive', 'due_soon'])],
                'frequency' => ['required', Rule::in(['daily', 'weekly', 'monthly', 'once'])],
                'day_offset' => 'nullable|integer|min:0|max:30',
                'time' => 'nullable|string|regex:/^\d{2}:\d{2}$/',
                'is_active' => 'nullable|boolean',
            ]);

            $validated['created_by'] = $request->user()?->id;

            $schedule = $this->service->createSchedule($validated);

            return $this->success($schedule, 'Schedule created', 201);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to create schedule', 'WaBlastController@storeSchedule');
        }
    }

    /**
     * Update schedule.
     */
    public function updateSchedule(Request $request, int $id): JsonResponse
    {
        try {
            /** @var WaSchedule $schedule */
            $schedule = WaSchedule::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'template_id' => 'nullable|integer|exists:isp_wa_templates,id',
                'target_filter' => ['sometimes', Rule::in(['all', 'unpaid', 'active', 'inactive', 'due_soon'])],
                'frequency' => ['sometimes', Rule::in(['daily', 'weekly', 'monthly', 'once'])],
                'day_offset' => 'nullable|integer|min:0|max:30',
                'time' => 'nullable|string|regex:/^\d{2}:\d{2}$/',
                'is_active' => 'nullable|boolean',
            ]);

            $schedule = $this->service->updateSchedule($schedule, $validated);

            return $this->success($schedule, 'Schedule updated');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to update schedule', 'WaBlastController@updateSchedule');
        }
    }

    /**
     * Delete schedule.
     */
    public function destroySchedule(int $id): JsonResponse
    {
        try {
            /** @var WaSchedule $schedule */
            $schedule = WaSchedule::findOrFail($id);
            $schedule->delete();

            return $this->success(null, 'Schedule deleted');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to delete schedule', 'WaBlastController@destroySchedule');
        }
    }
}
