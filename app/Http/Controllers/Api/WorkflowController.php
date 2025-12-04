<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Workflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkflowController extends Controller
{
    public function index()
    {
        try {
            $workflows = Workflow::with('creator')->get();
            return response()->json($workflows);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'steps' => 'nullable|array',
            'steps.*.name' => 'nullable|string|max:100',
            'steps.*.description' => 'nullable|string|max:100',
            'steps.*.item_type' => 'nullable|in:text,date,file,boolean',
            'steps.*.visibility' => 'nullable|in:show,hide',
            'status' => 'nullable|in:draft,active,inactive',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['created_by'] = Auth::id();
        $workflow = Workflow::create($validated);

        return response()->json($workflow->load('creator'), 201);
    }

    public function show(string $id)
    {
        $workflow = Workflow::with('creator')->findOrFail($id);
        return response()->json($workflow);
    }

    public function update(Request $request, string $id)
    {
        $workflow = Workflow::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'steps' => 'nullable|array',
            'steps.*.name' => 'nullable|string|max:100',
            'steps.*.description' => 'nullable|string|max:100',
            'steps.*.item_type' => 'nullable|in:text,date,file,boolean',
            'steps.*.visibility' => 'nullable|in:show,hide',
            'status' => 'nullable|in:draft,active,inactive',
            'is_active' => 'nullable|boolean',
        ]);

        $workflow->update($validated);

        return response()->json($workflow->load('creator'));
    }

    public function destroy(string $id)
    {
        $workflow = Workflow::findOrFail($id);
        $workflow->delete();

        return response()->json(['message' => 'Workflow deleted successfully']);
    }
}
