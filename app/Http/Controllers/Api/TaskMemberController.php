<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TaskMember;
use Illuminate\Http\Request;

class TaskMemberController extends Controller
{
    public function index()
    {
        $taskMembers = TaskMember::with('taskGroup')->get();
        return response()->json($taskMembers);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:task_members',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'role' => 'nullable|in:member,lead,admin',
            'task_group_id' => 'nullable|exists:task_groups,id',
            'is_active' => 'nullable|boolean',
        ]);

        $taskMember = TaskMember::create($validated);

        return response()->json($taskMember->load('taskGroup'), 201);
    }

    public function show(string $id)
    {
        $taskMember = TaskMember::with('taskGroup')->findOrFail($id);
        return response()->json($taskMember);
    }

    public function update(Request $request, string $id)
    {
        $taskMember = TaskMember::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:task_members,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'role' => 'nullable|in:member,lead,admin',
            'task_group_id' => 'nullable|exists:task_groups,id',
            'is_active' => 'nullable|boolean',
        ]);

        $taskMember->update($validated);

        return response()->json($taskMember->load('taskGroup'));
    }

    public function destroy(string $id)
    {
        $taskMember = TaskMember::findOrFail($id);
        $taskMember->delete();

        return response()->json(['message' => 'Task Member deleted successfully']);
    }
}
