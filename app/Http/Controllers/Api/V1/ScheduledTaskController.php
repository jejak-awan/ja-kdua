<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class ScheduledTaskController extends Controller
{
    public function index()
    {
        $tasks = DB::table('scheduled_tasks')->get();

        return response()->json($tasks);
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

        return response()->json(DB::table('scheduled_tasks')->where('id', $task)->first(), 201);
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

        return response()->json(DB::table('scheduled_tasks')->where('id', $id)->first());
    }

    public function run($id)
    {
        $task = DB::table('scheduled_tasks')->where('id', $id)->first();

        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
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

            return response()->json(['message' => 'Task executed successfully', 'output' => $output]);
        } catch (\Exception $e) {
            DB::table('scheduled_tasks')->where('id', $id)->update([
                'status' => 'failed',
                'output' => $e->getMessage(),
            ]);

            return response()->json(['message' => 'Task failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        DB::table('scheduled_tasks')->where('id', $id)->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }
}
