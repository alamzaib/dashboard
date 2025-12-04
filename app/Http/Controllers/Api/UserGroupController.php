<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserGroup;
use Illuminate\Http\Request;

class UserGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userGroups = UserGroup::with('users')->get();
        return response()->json($userGroups);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:user_groups,name',
            'description' => 'nullable|string',
            'header_color' => 'nullable|string|max:20',
            'logo' => 'nullable|image|max:2048',
        ]);

        // Handle logo upload if provided
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('user-group-logos', 'public');
            $validated['logo_path'] = $path;
        }

        $userGroup = UserGroup::create($validated);

        return response()->json($userGroup->load('users'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $userGroup = UserGroup::with('users', 'menus')->findOrFail($id);
        return response()->json($userGroup);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $userGroup = UserGroup::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:user_groups,name,' . $id,
            'description' => 'nullable|string',
            'header_color' => 'nullable|string|max:20',
            'logo' => 'nullable|image|max:2048',
        ]);

        // Handle logo upload if provided
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('user-group-logos', 'public');
            $validated['logo_path'] = $path;
        }

        $userGroup->update($validated);

        return response()->json($userGroup->load('users'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $userGroup = UserGroup::findOrFail($id);
        $userGroup->delete();

        return response()->json(['message' => 'User Group deleted successfully']);
    }
}
