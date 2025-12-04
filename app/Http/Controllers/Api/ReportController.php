<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index()
    {
        $reports = Report::with('creator')->get();
        return response()->json($reports);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|in:task,workflow,group,member,custom',
            'filters' => 'nullable|array',
            'columns' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['created_by'] = Auth::id();
        $report = Report::create($validated);

        return response()->json($report->load('creator'), 201);
    }

    public function show(string $id)
    {
        $report = Report::with('creator')->findOrFail($id);
        return response()->json($report);
    }

    public function update(Request $request, string $id)
    {
        $report = Report::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|in:task,workflow,group,member,custom',
            'filters' => 'nullable|array',
            'columns' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        $report->update($validated);

        return response()->json($report->load('creator'));
    }

    public function destroy(string $id)
    {
        $report = Report::findOrFail($id);
        $report->delete();

        return response()->json(['message' => 'Report deleted successfully']);
    }
}
