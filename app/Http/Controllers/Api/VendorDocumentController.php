<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VendorDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class VendorDocumentController extends Controller
{
    public function index()
    {
        try {
            $documents = VendorDocument::with('vendor')->get();
            return response()->json($documents);
        } catch (\Exception $e) {
            Log::error('Error fetching vendor documents: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch vendor documents.'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'vendor_id' => 'nullable|exists:vendors,id',
                'name' => 'required|string|max:255',
                'file' => 'required|file|max:10240', // 10MB max
                'description' => 'nullable|string',
                'document_type' => 'nullable|in:contract,invoice,certificate,license,other',
                'expiry_date' => 'nullable|date',
            ]);

            $file = $request->file('file');
            $path = $file->store('vendor-documents', 'public');

            $document = VendorDocument::create([
                'vendor_id' => $validated['vendor_id'] ?? null,
                'name' => $validated['name'],
                'file_path' => $path,
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'description' => $validated['description'] ?? null,
                'document_type' => $validated['document_type'] ?? 'other',
                'expiry_date' => $validated['expiry_date'] ?? null,
            ]);

            return response()->json($document->load('vendor'), 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error creating vendor document: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create vendor document.'], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $document = VendorDocument::with('vendor')->findOrFail($id);
            return response()->json($document);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Vendor document not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching vendor document: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch vendor document.'], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $document = VendorDocument::findOrFail($id);

            $validated = $request->validate([
                'vendor_id' => 'nullable|exists:vendors,id',
                'name' => 'sometimes|required|string|max:255',
                'file' => 'sometimes|file|max:10240',
                'description' => 'nullable|string',
                'document_type' => 'nullable|in:contract,invoice,certificate,license,other',
                'expiry_date' => 'nullable|date',
            ]);

            if ($request->hasFile('file')) {
                // Delete old file
                if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                    Storage::disk('public')->delete($document->file_path);
                }

                $file = $request->file('file');
                $path = $file->store('vendor-documents', 'public');
                $validated['file_path'] = $path;
                $validated['file_type'] = $file->getClientMimeType();
                $validated['file_size'] = $file->getSize();
            }

            $document->update($validated);
            return response()->json($document->load('vendor'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Vendor document not found.'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating vendor document: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update vendor document.'], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $document = VendorDocument::findOrFail($id);
            
            // Delete file from storage
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            
            $document->delete();
            return response()->json(['message' => 'Vendor document deleted successfully']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Vendor document not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Error deleting vendor document: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete vendor document.'], 500);
        }
    }
}

