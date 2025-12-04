<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prospect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProspectController extends Controller
{
    public function index()
    {
        try {
            $prospects = Prospect::all();
            return response()->json($prospects);
        } catch (\Exception $e) {
            Log::error('Error fetching prospects: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch prospects.'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:50',
                'address' => 'nullable|string',
                'contact_person' => 'nullable|string|max:255',
                'company_name' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
                'status' => 'nullable|in:new,contacted,qualified,converted,lost',
            ]);

            $prospect = Prospect::create($validated);
            return response()->json($prospect, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error creating prospect: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create prospect.'], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $prospect = Prospect::findOrFail($id);
            return response()->json($prospect);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Prospect not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching prospect: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch prospect.'], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $prospect = Prospect::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:50',
                'address' => 'nullable|string',
                'contact_person' => 'nullable|string|max:255',
                'company_name' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
                'status' => 'nullable|in:new,contacted,qualified,converted,lost',
            ]);

            $prospect->update($validated);
            return response()->json($prospect);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Prospect not found.'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating prospect: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update prospect.'], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $prospect = Prospect::findOrFail($id);
            $prospect->delete();
            return response()->json(['message' => 'Prospect deleted successfully']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Prospect not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Error deleting prospect: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete prospect.'], 500);
        }
    }
}

