<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class ScheduledTaskController extends BaseApiController
{
    public function index()
    {
        $tasks = DB::table('scheduled_tasks')->get();

        return $this->success($tasks, 'Scheduled tasks retrieved successfully');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'command' => 'required|string',
            'schedule' => 'required|string', // Cron expression
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'options' => 'nullable|array',
        ]);

        $task = DB::table('scheduled_tasks')->insertGetId($validated);

        return $this->success(DB::table('scheduled_tasks')->where('id', $task)->first(), 'Scheduled task created successfully', 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'command' => 'sometimes|required|string',
            'schedule' => 'sometimes|required|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'options' => 'nullable|array',
        ]);

        DB::table('scheduled_tasks')->where('id', $id)->update($validated);

        return $this->success(DB::table('scheduled_tasks')->where('id', $id)->first(), 'Scheduled task updated successfully');
    }

    public function run($id)
    {
        $task = DB::table('scheduled_tasks')->where('id', $id)->first();

        if (! $task) {
            return $this->notFound('Task');
        }

        try {
            DB::table('scheduled_tasks')->where('id', $id)->update([
                'status' => 'running',
                'last_run_at' => now(),
            ]);

            Artisan::call($task->command);

            $output = Artisan::output();

            DB::table('scheduled_tasks')->where('id', $id)->update([
                'status' => 'completed',
                'output' => $output,
            ]);

            return $this->success([
                'output' => $output,
            ], 'Task executed successfully');
        } catch (\Exception $e) {
            DB::table('scheduled_tasks')->where('id', $id)->update([
                'status' => 'failed',
                'output' => $e->getMessage(),
            ]);

            return $this->error('Task failed: '.$e->getMessage(), 500, [], 'TASK_EXECUTION_ERROR');
        }
    }

    public function destroy($id)
    {
        DB::table('scheduled_tasks')->where('id', $id)->delete();

        return $this->success(null, 'Task deleted successfully');
    }
}
