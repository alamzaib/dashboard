<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Workorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WorkorderController extends Controller
{
    public function index()
    {
        try {
            $workorders = Workorder::with(['assignedUserGroup', 'creator', 'taskGroup', 'workflow'])->get();
            return response()->json($workorders);
        } catch (\Exception $e) {
            Log::error('Error fetching workorders: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch workorders.'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
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
            
            // Generate workorder number
            $validated['workorder_number'] = $this->generateWorkorderNumber();

            $workorder = Workorder::create($validated);

            return response()->json($workorder->load(['assignedUserGroup', 'creator', 'taskGroup', 'workflow']), 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error creating workorder: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create workorder.'], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $workorder = Workorder::with(['assignedUserGroup', 'creator', 'taskGroup', 'workflow'])->findOrFail($id);
            return response()->json($workorder);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Workorder not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching workorder: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch workorder.'], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $workorder = Workorder::findOrFail($id);

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

            $workorder->update($validated);

            return response()->json($workorder->load(['assignedUserGroup', 'creator', 'taskGroup', 'workflow']));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Workorder not found.'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating workorder: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update workorder.'], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $workorder = Workorder::findOrFail($id);
            $workorder->delete();

            return response()->json(['message' => 'Workorder deleted successfully']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Workorder not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Error deleting workorder: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete workorder.'], 500);
        }
    }

    private function generateWorkorderNumber()
    {
        $prefix = 'WO';
        $year = date('Y');
        $month = date('m');
        
        // Get the last workorder number for this month
        $lastWorkorder = Workorder::where('workorder_number', 'like', "{$prefix}-{$year}{$month}%")
            ->orderBy('workorder_number', 'desc')
            ->first();
        
        if ($lastWorkorder) {
            // Extract the sequence number
            $lastNumber = (int) substr($lastWorkorder->workorder_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $nextNumber);
    }
}
