<?php

declare(strict_types=1);

namespace App\Services\Isp\ThirdParty;

use App\Models\Isp\Customer\Customer;
use App\Models\Isp\ThirdParty\WhatsAppBlast;
use App\Models\Isp\ThirdParty\WhatsAppBlastLog;
use App\Models\Isp\ThirdParty\WhatsAppSchedule;
use App\Models\Isp\ThirdParty\WhatsAppTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class WhatsAppBlastService
{
    public function __construct(
        protected WhatsAppNotificationService $whatsApp
    ) {}

    // ============================================================
    // TEMPLATE MANAGEMENT
    // ============================================================

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, WhatsAppTemplate>
     */
    public function listTemplates(array $filters = []): LengthAwarePaginator
    {
        $query = WhatsAppTemplate::query();

        if (isset($filters['search']) && is_string($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if (isset($filters['category']) && is_string($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        $perPage = isset($filters['per_page']) && is_numeric($filters['per_page']) ? (int) $filters['per_page'] : 15;

        return $query->latest()->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createTemplate(array $data): WhatsAppTemplate
    {
        return WhatsAppTemplate::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateTemplate(WhatsAppTemplate $template, array $data): WhatsAppTemplate
    {
        $template->update($data);

        return $template->fresh() ?? $template;
    }

    // ============================================================
    // BLAST MANAGEMENT
    // ============================================================

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, WhatsAppBlast>
     */
    public function listBlasts(array $filters = []): LengthAwarePaginator
    {
        $query = WhatsAppBlast::with('template');

        if (isset($filters['status']) && is_string($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $perPage = isset($filters['per_page']) && is_numeric($filters['per_page']) ? (int) $filters['per_page'] : 15;

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a blast job (draft).
     *
     * @param  array<string, mixed>  $data
     */
    public function createBlast(array $data): WhatsAppBlast
    {
        $targetFilter = isset($data['target_filter']) && is_string($data['target_filter']) ? $data['target_filter'] : 'all';
        $targetNumbers = null;
        if (isset($data['target_numbers']) && is_array($data['target_numbers'])) {
            $filtered = array_filter($data['target_numbers'], 'is_scalar');
            $targetNumbers = array_values(array_map(static fn (bool|float|int|string $v): string => (string) $v, $filtered));
        }
        $targets = $this->resolveTargets($targetFilter, $targetNumbers);

        $message = isset($data['message']) && is_string($data['message']) ? $data['message'] : '';

        // If template_id, resolve template
        if (isset($data['template_id']) && is_numeric($data['template_id'])) {
            /** @var WhatsAppTemplate|null $template */
            $template = WhatsAppTemplate::find((int) $data['template_id']);
            if ($template !== null && $message === '') {
                $message = $template->body;
            }
        }

        return WhatsAppBlast::create([
            'name' => $data['name'] ?? 'Blast '.now()->format('d/m/Y H:i'),
            'template_id' => $data['template_id'] ?? null,
            'message' => $message,
            'target_filter' => $data['target_filter'] ?? 'all',
            'target_numbers' => $data['target_numbers'] ?? null,
            'total_targets' => count($targets),
            'status' => 'draft',
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'created_by' => $data['created_by'] ?? null,
        ]);
    }

    /**
     * Send a blast - queue messages to all targets.
     */
    public function sendBlast(WhatsAppBlast $blast): WhatsAppBlast
    {
        $blast->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);

        $targets = $this->resolveTargets($blast->target_filter, $blast->target_numbers);

        $sentCount = 0;
        $failedCount = 0;

        foreach ($targets as $target) {
            $phone = $target['phone'];
            $name = $target['name'];

            $message = str_replace('{name}', $name, $blast->message);

            $success = $this->whatsApp->sendMessage($phone, $message);

            WhatsAppBlastLog::create([
                'blast_id' => $blast->id,
                'phone' => $phone,
                'name' => $name,
                'status' => $success ? 'sent' : 'failed',
                'error_message' => $success ? null : 'Failed to send',
                'sent_at' => $success ? now() : null,
            ]);

            if ($success) {
                $sentCount++;
            } else {
                $failedCount++;
            }
        }

        $blast->update([
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return $blast->fresh() ?? $blast;
    }

    /**
     * Resolve target phone numbers based on filter.
     *
     * @param  array<int, string>|null  $customNumbers
     * @return array<int, array{phone: string, name: string}>
     */
    protected function resolveTargets(string $filter, mixed $customNumbers = null): array
    {
        $targets = [];

        if ($filter === 'custom' && is_array($customNumbers)) {
            foreach ($customNumbers as $number) {
                $targets[] = ['phone' => (string) $number, 'name' => ''];
            }

            return $targets;
        }

        $query = Customer::query()->whereNotNull('id');

        // Join with users to get phone
        $query->join('users', 'isp_customers.user_id', '=', 'users.id');

        switch ($filter) {
            case 'unpaid':
                $query->whereHas('plan', function ($q): void {
                    $q->whereNotNull('id');
                })->where('isp_customers.status', 'active');
                break;
            case 'active':
                $query->where('isp_customers.status', 'active');
                break;
            case 'inactive':
                $query->where('isp_customers.status', 'inactive');
                break;
            default: // all
                break;
        }

        $customers = $query->select('users.name', 'users.phone', 'users.email')->get();

        foreach ($customers as $customer) {
            $phone = (string) ($customer->phone ?? '');
            if ($phone !== '') {
                $targets[] = ['phone' => $phone, 'name' => (string) ($customer->name ?? '')];
            }
        }

        return $targets;
    }

    // ============================================================
    // SCHEDULE MANAGEMENT
    // ============================================================

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, WhatsAppSchedule>
     */
    public function listSchedules(array $filters = []): LengthAwarePaginator
    {
        $query = WhatsAppSchedule::with('template');

        $perPage = isset($filters['per_page']) && is_numeric($filters['per_page']) ? (int) $filters['per_page'] : 15;

        return $query->latest()->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createSchedule(array $data): WhatsAppSchedule
    {
        $schedule = WhatsAppSchedule::create($data);
        $this->calculateNextRun($schedule);

        return $schedule;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateSchedule(WhatsAppSchedule $schedule, array $data): WhatsAppSchedule
    {
        $schedule->update($data);
        $this->calculateNextRun($schedule);

        return $schedule->fresh() ?? $schedule;
    }

    /**
     * Calculate and set next run time for a schedule.
     */
    protected function calculateNextRun(WhatsAppSchedule $schedule): void
    {
        if (! $schedule->is_active) {
            $schedule->update(['next_run_at' => null]);

            return;
        }

        $timeParts = explode(':', $schedule->time);
        $hour = (int) $timeParts[0];
        $minute = isset($timeParts[1]) ? (int) $timeParts[1] : 0;

        $next = now()->setTime($hour, $minute, 0);

        // If time already passed today, move to next occurrence
        if ($next->lte(now())) {
            switch ($schedule->frequency) {
                case 'daily':
                    $next->addDay();
                    break;
                case 'weekly':
                    $next->addWeek();
                    break;
                case 'monthly':
                    $next->addMonth();
                    break;
                default:
                    $next->addDay();
                    break;
            }
        }

        $schedule->update(['next_run_at' => $next]);
    }

    /**
     * Process due scheduled messages.
     */
    public function processDueSchedules(): int
    {
        $dueSchedules = WhatsAppSchedule::where('is_active', true)
            ->where('next_run_at', '<=', now())
            ->get();

        $processed = 0;

        foreach ($dueSchedules as $schedule) {
            try {
                $blast = $this->createBlast([
                    'name' => 'Scheduled: '.$schedule->name,
                    'template_id' => $schedule->template_id,
                    'message' => $schedule->template !== null ? $schedule->template->body : '',
                    'target_filter' => $schedule->target_filter,
                    'created_by' => $schedule->created_by,
                ]);

                $this->sendBlast($blast);

                $schedule->update(['last_run_at' => now()]);
                $this->calculateNextRun($schedule);

                $processed++;
            } catch (\Exception $e) {
                Log::error('WA Schedule Error: '.$e->getMessage(), [
                    'schedule_id' => $schedule->id,
                ]);
            }
        }

        return $processed;
    }
}
