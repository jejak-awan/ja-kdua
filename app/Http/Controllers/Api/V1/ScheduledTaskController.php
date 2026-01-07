<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ScheduledTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ScheduledTaskController extends BaseApiController
{
    /**
     * List all scheduled tasks
     */
    public function index()
    {
        $tasks = ScheduledTask::orderBy('name')->get();

        return $this->success($tasks, 'Scheduled tasks retrieved successfully');
    }

    public function allowedCommands()
    {
        return $this->success(
            ScheduledTask::getAllowedCommands(),
            'Allowed commands retrieved'
        );
    }

    /**
     * Create a new scheduled task
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'command' => 'required|string|max:500',
            'schedule' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'options' => 'nullable|array',
        ]);

        // Validate command against whitelist
        if (! ScheduledTask::isCommandAllowed($validated['command'])) {
            return $this->error(
                'Command not allowed. Use GET /scheduled-tasks/allowed-commands to see available commands.',
                403,
                ['allowed_commands' => ScheduledTask::ALLOWED_COMMANDS],
                'COMMAND_NOT_ALLOWED'
            );
        }

        // Validate cron expression
        if (! ScheduledTask::isValidCronExpression($validated['schedule'])) {
            return $this->validationError([
                'schedule' => ['Invalid cron expression format'],
            ]);
        }

        $task = ScheduledTask::create($validated);

        Log::info('Scheduled task created', [
            'task_id' => $task->id,
            'command' => $task->command,
            'user_id' => auth()->id(),
        ]);

        return $this->success($task, 'Scheduled task created successfully', 201);
    }

    /**
     * Update a scheduled task
     */
    public function update(Request $request, $id)
    {
        $task = ScheduledTask::find($id);

        if (! $task) {
            return $this->notFound('Task');
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'command' => 'sometimes|required|string|max:500',
            'schedule' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'options' => 'nullable|array',
        ]);

        // Validate command if changed
        if (isset($validated['command']) && ! ScheduledTask::isCommandAllowed($validated['command'])) {
            return $this->error(
                'Command not allowed',
                403,
                ['allowed_commands' => ScheduledTask::ALLOWED_COMMANDS],
                'COMMAND_NOT_ALLOWED'
            );
        }

        // Validate cron expression if changed
        if (isset($validated['schedule']) && ! ScheduledTask::isValidCronExpression($validated['schedule'])) {
            return $this->validationError([
                'schedule' => ['Invalid cron expression format'],
            ]);
        }

        $task->update($validated);

        Log::info('Scheduled task updated', [
            'task_id' => $task->id,
            'changes' => $validated,
            'user_id' => auth()->id(),
        ]);

        return $this->success($task->fresh(), 'Scheduled task updated successfully');
    }

    /**
     * Run a scheduled task manually
     */
    public function run($id)
    {
        $task = ScheduledTask::find($id);

        if (! $task) {
            return $this->notFound('Task');
        }

        // Check if user has permission
        if (! auth()->user()->can('manage scheduled tasks')) {
            Log::warning('Unauthorized task execution attempt', [
                'task_id' => $task->id,
                'command' => $task->command,
                'user_id' => auth()->id(),
            ]);

            return $this->error(
                'Insufficient permissions to execute scheduled tasks',
                403,
                [],
                'INSUFFICIENT_PERMISSIONS'
            );
        }

        // Double-check command is still allowed (in case whitelist changed)
        if (! ScheduledTask::isCommandAllowed($task->command)) {
            return $this->error(
                'This command is no longer allowed to be executed',
                403,
                [],
                'COMMAND_NOT_ALLOWED'
            );
        }

        try {
            $task->update([
                'status' => 'running',
                'last_run_at' => now(),
            ]);

            Log::info('Executing scheduled task', [
                'task_id' => $task->id,
                'command' => $task->command,
                'user_id' => auth()->id(),
            ]);

            // Execute the command
            $exitCode = Artisan::call($task->command);
            $output = Artisan::output();

            $status = $exitCode === 0 ? 'completed' : 'failed';

            $task->update([
                'status' => $status,
                'output' => $output,
            ]);

            Log::info('Scheduled task completed', [
                'task_id' => $task->id,
                'status' => $status,
                'exit_code' => $exitCode,
            ]);

            return $this->success([
                'task' => $task->fresh(),
                'output' => $output,
                'exit_code' => $exitCode,
            ], 'Task executed successfully');

        } catch (\Exception $e) {
            $task->update([
                'status' => 'failed',
                'output' => $e->getMessage(),
            ]);

            Log::error('Scheduled task execution failed', [
                'task_id' => $task->id,
                'command' => $task->command,
                'error' => $e->getMessage(),
            ]);

            return $this->error(
                'Task execution failed: '.$e->getMessage(),
                500,
                [],
                'TASK_EXECUTION_ERROR'
            );
        }
    }

    /**
     * Delete a scheduled task
     */
    public function destroy($id)
    {
        $task = ScheduledTask::find($id);

        if (! $task) {
            return $this->notFound('Task');
        }

        Log::info('Scheduled task deleted', [
            'task_id' => $task->id,
            'command' => $task->command,
            'user_id' => auth()->id(),
        ]);

        $task->delete();

        return $this->success(null, 'Task deleted successfully');
    }

    /**
     * Get task execution history
     */
    public function show($id)
    {
        $task = ScheduledTask::find($id);

        if (! $task) {
            return $this->notFound('Task');
        }

        return $this->success([
            'task' => $task,
            'next_run_at' => $task->getNextRunAt()?->format('Y-m-d H:i:s'),
        ], 'Task details retrieved');
    }
}
