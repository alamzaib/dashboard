<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index()
    {
        try {
            $tasks = Task::with(['assignedUserGroup', 'creator', 'taskGroup', 'workflow'])->get();
            return response()->json($tasks);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:pending,in_progress,completed,cancelled',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'assigned_to_user_group_id' => 'nullable|exists:user_groups,id',
            'task_group_id' => 'nullable|exists:task_groups,id',
            'workflow_id' => 'nullable|exists:workflows,id',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['created_by'] = Auth::id();
        $task = Task::create($validated);

        return response()->json($task->load(['assignedUserGroup', 'creator', 'taskGroup', 'workflow']), 201);
    }

    public function show(string $id)
    {
        $task = Task::with(['assignedUserGroup', 'creator', 'taskGroup', 'workflow'])->findOrFail($id);
        return response()->json($task);
    }

    public function update(Request $request, string $id)
    {
        $task = Task::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:pending,in_progress,completed,cancelled',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'assigned_to_user_group_id' => 'nullable|exists:user_groups,id',
            'task_group_id' => 'nullable|exists:task_groups,id',
            'workflow_id' => 'nullable|exists:workflows,id',
            'is_active' => 'nullable|boolean',
        ]);

        $task->update($validated);

        return response()->json($task->load(['assignedUserGroup', 'creator', 'taskGroup', 'workflow']));
    }

    public function destroy(string $id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }
}
