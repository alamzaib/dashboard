<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TaskGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskGroupController extends Controller
{
    public function index()
    {
        $taskGroups = TaskGroup::with('creator')->get();
        return response()->json($taskGroups);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['created_by'] = Auth::id();
        $taskGroup = TaskGroup::create($validated);

        return response()->json($taskGroup->load('creator'), 201);
    }

    public function show(string $id)
    {
        $taskGroup = TaskGroup::with('creator')->findOrFail($id);
        return response()->json($taskGroup);
    }

    public function update(Request $request, string $id)
    {
        $taskGroup = TaskGroup::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $taskGroup->update($validated);

        return response()->json($taskGroup->load('creator'));
    }

    public function destroy(string $id)
    {
        $taskGroup = TaskGroup::findOrFail($id);
        $taskGroup->delete();

        return response()->json(['message' => 'Task Group deleted successfully']);
    }
}
