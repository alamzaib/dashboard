<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SiteController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Site::with(['forms', 'documents']);
            
            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            
            $sites = $query->get();
            return response()->json($sites);
        } catch (\Exception $e) {
            Log::error('Error fetching sites: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch sites.'], 500);
        }
    }
    
    /**
     * Toggle site status (active/inactive)
     */
    public function toggleStatus(Request $request, string $id)
    {
        try {
            $site = Site::findOrFail($id);
            $newStatus = $site->status === 'active' ? 'inactive' : 'active';
            $site->update(['status' => $newStatus]);
            $site->refresh();
            
            return response()->json([
                'message' => 'Site status updated successfully',
                'site' => $site->load(['forms', 'documents']),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Site not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Error toggling site status: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update site status.'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Prepare data - convert empty strings to null for numeric fields before validation
            $data = $request->all();
            if (isset($data['longitude']) && $data['longitude'] === '') {
                $data['longitude'] = null;
            }
            if (isset($data['latitude']) && $data['latitude'] === '') {
                $data['latitude'] = null;
            }
            
            // Merge prepared data back to request
            $request->merge($data);

            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:sites,name', // This will be the postfix name
                'code' => 'nullable|string|max:255|unique:sites,code',
                'address' => 'nullable|string',
                'city' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'country' => 'nullable|string|max:255',
                'zip_code' => 'nullable|string|max:20',
                'contact_person' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:50',
                'email' => 'nullable|email|max:255',
                'description' => 'nullable|string',
                'longitude' => 'nullable|numeric|between:-180,180',
                'latitude' => 'nullable|numeric|between:-90,90',
                'status' => 'nullable|in:active,inactive,pending,completed',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'project_id' => 'nullable|exists:projects,id',
            ]);

            // Get site prefix from project settings if project_id is provided
            $postfixName = $validated['name'];
            $projectId = $request->input('project_id');
            
            // Remove project_id from validated data as it's not a column in sites table
            unset($validated['project_id']);
            
            if ($projectId) {
                $prefix = Setting::where('key', "site_prefix_project_{$projectId}")->value('value');
                if ($prefix) {
                    $validated['name'] = $prefix . $postfixName;
                }
            }

            // Set default status if not provided
            if (!isset($validated['status'])) {
                $validated['status'] = 'active';
            }

            // Ensure longitude and latitude are null if empty
            $validated['longitude'] = $validated['longitude'] ?? null;
            $validated['latitude'] = $validated['latitude'] ?? null;

            $site = Site::create($validated);
            
            // Try to load relationships, but don't fail if tables don't exist
            try {
                $site->load(['forms', 'documents']);
            } catch (\Exception $e) {
                // Relationships not available, continue without them
                Log::warning('Could not load site relationships: ' . $e->getMessage());
            }
            
            return response()->json($site, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error creating site: ' . json_encode($e->errors()));
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error creating site: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'error' => 'Failed to create site.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $site = Site::with(['forms', 'documents'])->findOrFail($id);
            return response()->json($site);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Site not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching site: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch site.'], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $site = Site::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255|unique:sites,name,' . $id,
                'code' => 'nullable|string|max:255|unique:sites,code,' . $id,
                'address' => 'nullable|string',
                'city' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'country' => 'nullable|string|max:255',
                'zip_code' => 'nullable|string|max:20',
                'contact_person' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:50',
                'email' => 'nullable|email|max:255',
                'description' => 'nullable|string',
                'longitude' => 'nullable|numeric|between:-180,180',
                'latitude' => 'nullable|numeric|between:-90,90',
                'status' => 'nullable|in:active,inactive,pending,completed',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $site->update($validated);
            return response()->json($site->load(['forms', 'documents']));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Site not found.'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating site: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update site.'], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $site = Site::findOrFail($id);
            $site->delete();
            return response()->json(['message' => 'Site deleted successfully']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Site not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Error deleting site: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete site.'], 500);
        }
    }

    /**
     * Import sites from CSV file
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
                'project_id' => 'nullable|exists:projects,id',
            ]);

            $file = $request->file('file');
            $projectId = $request->input('project_id');
            $prefix = '';
            
            if ($projectId) {
                $prefix = Setting::where('key', "site_prefix_project_{$projectId}")->value('value') ?? '';
            }

            $imported = 0;
            $errors = [];
            $rowNumber = 1; // Start from 1 (header row)

            if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
                // Skip header row
                fgetcsv($handle);
                $rowNumber = 2;

                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    $rowNumber++;
                    
                    // Expected format: name, longitude, latitude, description, status
                    if (count($data) < 1) {
                        $errors[] = "Row {$rowNumber}: Insufficient columns";
                        continue;
                    }

                    $name = trim($data[0] ?? '');
                    if (empty($name)) {
                        $errors[] = "Row {$rowNumber}: Site name is required";
                        continue;
                    }

                    // Apply prefix if available
                    $fullName = $prefix ? $prefix . $name : $name;

                    // Check if site name already exists
                    if (Site::where('name', $fullName)->exists()) {
                        $errors[] = "Row {$rowNumber}: Site '{$fullName}' already exists";
                        continue;
                    }

                    $longitude = !empty($data[1]) ? trim($data[1]) : null;
                    $latitude = !empty($data[2]) ? trim($data[2]) : null;
                    $description = !empty($data[3]) ? trim($data[3]) : null;
                    $status = !empty($data[4]) ? trim($data[4]) : 'active';

                    // Validate status
                    if (!in_array($status, ['active', 'inactive', 'pending', 'completed'])) {
                        $status = 'active';
                    }

                    // Validate coordinates
                    if ($longitude !== null && (!is_numeric($longitude) || $longitude < -180 || $longitude > 180)) {
                        $errors[] = "Row {$rowNumber}: Invalid longitude";
                        $longitude = null;
                    }
                    if ($latitude !== null && (!is_numeric($latitude) || $latitude < -90 || $latitude > 90)) {
                        $errors[] = "Row {$rowNumber}: Invalid latitude";
                        $latitude = null;
                    }

                    try {
                        Site::create([
                            'name' => $fullName,
                            'longitude' => $longitude,
                            'latitude' => $latitude,
                            'description' => $description,
                            'status' => $status,
                        ]);
                        $imported++;
                    } catch (\Exception $e) {
                        $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                    }
                }
                fclose($handle);
            }

            return response()->json([
                'message' => 'Import completed',
                'imported' => $imported,
                'errors' => $errors,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error importing sites: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to import sites: ' . $e->getMessage()], 500);
        }
    }
}
