<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VendorController extends Controller
{
    public function index()
    {
        try {
            $vendors = Vendor::with('documents')->get();
            return response()->json($vendors);
        } catch (\Exception $e) {
            Log::error('Error fetching vendors: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch vendors.'], 500);
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
                'tax_id' => 'nullable|string|max:100',
                'notes' => 'nullable|string',
                'status' => 'nullable|in:active,inactive,pending',
            ]);

            $vendor = Vendor::create($validated);
            return response()->json($vendor->load('documents'), 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error creating vendor: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create vendor.'], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $vendor = Vendor::with('documents')->findOrFail($id);
            return response()->json($vendor);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Vendor not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching vendor: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch vendor.'], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $vendor = Vendor::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:50',
                'address' => 'nullable|string',
                'contact_person' => 'nullable|string|max:255',
                'tax_id' => 'nullable|string|max:100',
                'notes' => 'nullable|string',
                'status' => 'nullable|in:active,inactive,pending',
            ]);

            $vendor->update($validated);
            return response()->json($vendor->load('documents'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Vendor not found.'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating vendor: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update vendor.'], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $vendor = Vendor::findOrFail($id);
            $vendor->delete();
            return response()->json(['message' => 'Vendor deleted successfully']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Vendor not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Error deleting vendor: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete vendor.'], 500);
        }
    }
}

