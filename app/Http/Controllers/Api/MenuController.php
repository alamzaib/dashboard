<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\PermissionGroup;
use App\Models\UserGroup;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $menus = Menu::with('userGroups')->orderBy('order')->get();
        return response()->json($menus);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'route' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:menus,id',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'user_group_ids' => 'nullable|array',
            'user_group_ids.*' => 'exists:user_groups,id',
        ]);

        $menu = Menu::create($validated);

        if (isset($validated['user_group_ids'])) {
            $menu->userGroups()->attach($validated['user_group_ids']);
        }

        return response()->json($menu->load('userGroups'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $menu = Menu::with('userGroups', 'parent', 'children')->findOrFail($id);
        return response()->json($menu);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $menu = Menu::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'route' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:menus,id',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'user_group_ids' => 'nullable|array',
            'user_group_ids.*' => 'exists:user_groups,id',
        ]);

        $menu->update($validated);

        if (isset($validated['user_group_ids'])) {
            $menu->userGroups()->sync($validated['user_group_ids']);
        }

        return response()->json($menu->load('userGroups'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $menu = Menu::findOrFail($id);
        $menu->delete();

        return response()->json(['message' => 'Menu deleted successfully']);
    }

    /**
     * Get all user groups.
     */
    public function userGroups()
    {
        $userGroups = UserGroup::all();
        return response()->json($userGroups);
    }

    /**
     * Get user groups for a permission group (user groups that have users in the permission group).
     */
    public function getUserGroupsByPermissionGroup($permissionGroupId)
    {
        $permissionGroup = PermissionGroup::with('users.userGroups')->findOrFail($permissionGroupId);
        
        // Get all user groups that have users in this permission group
        $userGroupIds = [];
        foreach ($permissionGroup->users as $user) {
            foreach ($user->userGroups as $userGroup) {
                $userGroupIds[] = $userGroup->id;
            }
        }
        $userGroupIds = array_unique($userGroupIds);
        
        // Get user groups
        $userGroups = UserGroup::whereIn('id', $userGroupIds)->get();
        
        return response()->json($userGroups);
    }

    /**
     * Get menus by permission group.
     */
    public function getMenusByPermissionGroup($permissionGroupId)
    {
        $permissionGroup = PermissionGroup::with('users.userGroups')->findOrFail($permissionGroupId);
        
        // Get all user groups that have users in this permission group
        $userGroupIds = [];
        foreach ($permissionGroup->users as $user) {
            foreach ($user->userGroups as $userGroup) {
                $userGroupIds[] = $userGroup->id;
            }
        }
        $userGroupIds = array_unique($userGroupIds);
        
        // If no user groups found, return empty array
        if (empty($userGroupIds)) {
            return response()->json([]);
        }
        
        // Get menus that are associated with at least one of these user groups
        $menus = Menu::whereHas('userGroups', function ($query) use ($userGroupIds) {
            $query->whereIn('user_groups.id', $userGroupIds);
        })->with('userGroups')->orderBy('order')->get();
        
        // Log for debugging
        \Log::info('Menus for permission group ' . $permissionGroupId . ': ' . $menus->count(), [
            'user_group_ids' => $userGroupIds,
            'menu_ids' => $menus->pluck('id')->toArray()
        ]);
        
        return response()->json($menus);
    }

    /**
     * Update menu order.
     */
    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'menu_ids' => 'required|array',
            'menu_ids.*' => 'exists:menus,id',
        ]);

        foreach ($validated['menu_ids'] as $index => $menuId) {
            Menu::where('id', $menuId)->update(['order' => $index + 1]);
        }

        return response()->json(['message' => 'Menu order updated successfully']);
    }
}
