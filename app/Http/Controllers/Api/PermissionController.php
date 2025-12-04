<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * Get all projects for dropdown
     */
    public function projects()
    {
        $projects = Project::where('is_active', true)->get();
        return response()->json($projects);
    }

    /**
     * Get permission groups for a project
     */
    public function permissionGroups($projectId)
    {
        $permissionGroups = PermissionGroup::where('project_id', $projectId)
            ->with('permissions')
            ->get();
        return response()->json($permissionGroups);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Permission::with('permissionGroup.project');

        if ($request->has('project_id')) {
            $query->whereHas('permissionGroup', function ($q) use ($request) {
                $q->where('project_id', $request->project_id);
            });
        }

        if ($request->has('permission_group_id')) {
            $query->where('permission_group_id', $request->permission_group_id);
        }

        $permissions = $query->get();
        return response()->json($permissions);
    }

    /**
     * Store a newly created permission group with permissions.
     */
    public function storeGroup(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'required|array',
            'permissions.*.name' => 'required|string|max:255',
            'permissions.*.read' => 'nullable|boolean',
            'permissions.*.write' => 'nullable|boolean',
            'permissions.*.update' => 'nullable|boolean',
            'permissions.*.delete' => 'nullable|boolean',
        ]);

        $permissionGroup = PermissionGroup::create([
            'project_id' => $validated['project_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        foreach ($validated['permissions'] as $permissionData) {
            Permission::create([
                'permission_group_id' => $permissionGroup->id,
                'name' => $permissionData['name'],
                'read' => $permissionData['read'] ?? false,
                'write' => $permissionData['write'] ?? false,
                'update' => $permissionData['update'] ?? false,
                'delete' => $permissionData['delete'] ?? false,
            ]);
        }

        return response()->json($permissionGroup->load('permissions'), 201);
    }

    /**
     * Update a permission group with permissions.
     */
    public function updateGroup(Request $request, $permissionGroupId)
    {
        $permissionGroup = PermissionGroup::findOrFail($permissionGroupId);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'sometimes|required|array',
            'permissions.*.id' => 'nullable|exists:permissions,id',
            'permissions.*.name' => 'required|string|max:255',
            'permissions.*.read' => 'nullable|boolean',
            'permissions.*.write' => 'nullable|boolean',
            'permissions.*.update' => 'nullable|boolean',
            'permissions.*.delete' => 'nullable|boolean',
        ]);

        // Update permission group
        if (isset($validated['name'])) {
            $permissionGroup->name = $validated['name'];
        }
        if (isset($validated['description'])) {
            $permissionGroup->description = $validated['description'];
        }
        $permissionGroup->save();

        // Update permissions if provided
        if (isset($validated['permissions'])) {
            $existingPermissionIds = [];
            
            foreach ($validated['permissions'] as $permissionData) {
                if (isset($permissionData['id'])) {
                    // Update existing permission
                    $permission = Permission::findOrFail($permissionData['id']);
                    $permission->update([
                        'name' => $permissionData['name'],
                        'read' => $permissionData['read'] ?? false,
                        'write' => $permissionData['write'] ?? false,
                        'update' => $permissionData['update'] ?? false,
                        'delete' => $permissionData['delete'] ?? false,
                    ]);
                    $existingPermissionIds[] = $permission->id;
                } else {
                    // Create new permission
                    $permission = Permission::create([
                        'permission_group_id' => $permissionGroup->id,
                        'name' => $permissionData['name'],
                        'read' => $permissionData['read'] ?? false,
                        'write' => $permissionData['write'] ?? false,
                        'update' => $permissionData['update'] ?? false,
                        'delete' => $permissionData['delete'] ?? false,
                    ]);
                    $existingPermissionIds[] = $permission->id;
                }
            }

            // Delete permissions that are no longer in the list
            Permission::where('permission_group_id', $permissionGroup->id)
                ->whereNotIn('id', $existingPermissionIds)
                ->delete();
        }

        return response()->json($permissionGroup->load('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'permission_group_id' => 'required|exists:permission_groups,id',
            'name' => 'required|string|max:255',
            'read' => 'nullable|boolean',
            'write' => 'nullable|boolean',
            'update' => 'nullable|boolean',
            'delete' => 'nullable|boolean',
        ]);

        $permission = Permission::create($validated);

        return response()->json($permission, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $permission = Permission::with('permissionGroup.project')->findOrFail($id);
        return response()->json($permission);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $permission = Permission::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'read' => 'nullable|boolean',
            'write' => 'nullable|boolean',
            'update' => 'nullable|boolean',
            'delete' => 'nullable|boolean',
        ]);

        $permission->update($validated);

        return response()->json($permission);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        return response()->json(['message' => 'Permission deleted successfully']);
    }

    /**
     * Get users for a permission group
     */
    public function getUsers($permissionGroupId)
    {
        $permissionGroup = PermissionGroup::with('users')->findOrFail($permissionGroupId);
        $allUsers = User::all();
        
        // Mark which users are subscribed
        $subscribedUserIds = $permissionGroup->users->pluck('id')->toArray();
        
        $users = $allUsers->map(function ($user) use ($subscribedUserIds) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'reference_id' => $user->reference_id,
                'subscribed' => in_array($user->id, $subscribedUserIds),
            ];
        });

        return response()->json($users);
    }

    /**
     * Subscribe/unsubscribe users to a permission group
     */
    public function updateUsers(Request $request, $permissionGroupId)
    {
        $permissionGroup = PermissionGroup::findOrFail($permissionGroupId);

        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $permissionGroup->users()->sync($validated['user_ids']);

        return response()->json([
            'message' => 'Users updated successfully',
            'permission_group' => $permissionGroup->load('users'),
        ]);
    }
}
