<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TaskGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TaskMemberController extends Controller
{
    /**
     * Get all task groups
     */
    public function getTaskGroups()
    {
        try {
            $taskGroups = TaskGroup::withCount('users')->get();
            return response()->json($taskGroups);
        } catch (\Exception $e) {
            Log::error('Error fetching task groups: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch task groups.'], 500);
        }
    }

    /**
     * Get users for a specific task group
     */
    public function getUsersByTaskGroup($taskGroupId)
    {
        try {
            $taskGroup = TaskGroup::with('users')->findOrFail($taskGroupId);
            return response()->json($taskGroup->users);
        } catch (\Exception $e) {
            Log::error('Error fetching users for task group: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch users.'], 500);
        }
    }

    /**
     * Get all users (for the right section)
     */
    public function getAllUsers()
    {
        try {
            $users = User::select('id', 'name', 'email', 'reference_id')->get();
            return response()->json($users);
        } catch (\Exception $e) {
            Log::error('Error fetching users: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch users.'], 500);
        }
    }

    /**
     * Add a user to a task group
     */
    public function addUserToTaskGroup(Request $request, $taskGroupId)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);

            $taskGroup = TaskGroup::findOrFail($taskGroupId);
            $user = User::findOrFail($validated['user_id']);

            // Check if user is already in the group
            if ($taskGroup->users()->where('user_id', $user->id)->exists()) {
                return response()->json(['error' => 'User is already a member of this task group.'], 422);
            }

            $taskGroup->users()->attach($user->id);

            return response()->json(['message' => 'User added to task group successfully.'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error adding user to task group: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to add user to task group.'], 500);
        }
    }

    /**
     * Remove a user from a task group
     */
    public function removeUserFromTaskGroup($taskGroupId, $userId)
    {
        try {
            $taskGroup = TaskGroup::findOrFail($taskGroupId);
            $user = User::findOrFail($userId);

            $taskGroup->users()->detach($user->id);

            return response()->json(['message' => 'User removed from task group successfully.'], 200);
        } catch (\Exception $e) {
            Log::error('Error removing user from task group: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to remove user from task group.'], 500);
        }
    }
}
