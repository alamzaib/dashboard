<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VendorField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class VendorFieldController extends Controller
{
    public function index()
    {
        try {
            // Get actual columns from vendors table
            $tableColumns = Schema::getColumnListing('vendors');
            $columnsInfo = [];
            
            foreach ($tableColumns as $column) {
                if (in_array($column, ['id', 'created_at', 'updated_at'])) {
                    continue; // Skip timestamps and id
                }
                
                // Get column type from database
                $columnInfo = DB::select("SHOW COLUMNS FROM vendors WHERE Field = ?", [$column]);
                $columnType = $columnInfo[0]->Type ?? 'varchar(255)';
                
                // Normalize column type for display
                $normalizedType = $this->normalizeColumnType($columnType);
                
                $columnsInfo[] = [
                    'field_name' => $column,
                    'field_type' => $normalizedType,
                    'db_type' => $columnType, // Keep original DB type
                ];
            }
            
            // Get field metadata from vendor_fields table
            $fieldMetadata = VendorField::orderBy('display_order')->orderBy('id')->get()->keyBy('field_name');
            
            // Merge table columns with metadata
            $fields = [];
            foreach ($columnsInfo as $column) {
                $metadata = $fieldMetadata->get($column['field_name']);
                $fields[] = [
                    'id' => $metadata ? $metadata->id : null,
                    'field_name' => $column['field_name'],
                    'field_label' => $metadata ? $metadata->field_label : ucfirst(str_replace('_', ' ', $column['field_name'])),
                    'field_type' => $metadata ? $metadata->field_type : $column['field_type'], // Use normalized type or metadata type
                    'is_required' => $metadata ? $metadata->is_required : false,
                    'is_active' => $metadata ? ($metadata->is_active ?? false) : false, // Default to false (inactive)
                    'display_order' => $metadata ? $metadata->display_order : 0,
                ];
            }
            
            // Sort by display_order
            usort($fields, function($a, $b) {
                return $a['display_order'] <=> $b['display_order'];
            });
            
            return response()->json($fields);
        } catch (\Exception $e) {
            Log::error('Error fetching vendor fields: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch vendor fields.'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'field_name' => 'required|string|max:255',
                'field_label' => 'required|string|max:255',
                'field_type' => 'nullable|string',
                'is_required' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
                'display_order' => 'nullable|integer',
            ]);

            // Check if field already exists, update instead of create
            $field = VendorField::updateOrCreate(
                ['field_name' => $validated['field_name']],
                $validated
            );
            
            return response()->json($field, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error creating vendor field: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create vendor field.'], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $field = VendorField::findOrFail($id);
            return response()->json($field);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Vendor field not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching vendor field: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch vendor field.'], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $field = VendorField::findOrFail($id);

            $validated = $request->validate([
                'field_name' => 'sometimes|required|string|max:255',
                'field_label' => 'sometimes|required|string|max:255',
                'field_type' => 'nullable|string',
                'is_required' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
                'display_order' => 'nullable|integer',
            ]);

            $field->update($validated);
            return response()->json($field);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Vendor field not found.'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating vendor field: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update vendor field.'], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $field = VendorField::findOrFail($id);
            $field->delete();
            return response()->json(['message' => 'Vendor field deleted successfully']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Vendor field not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Error deleting vendor field: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete vendor field.'], 500);
        }
    }

    public function updateOrder(Request $request)
    {
        try {
            $validated = $request->validate([
                'fields' => 'required|array',
                'fields.*.field_name' => 'required|string|max:255',
                'fields.*.display_order' => 'required|integer',
            ]);

            foreach ($validated['fields'] as $fieldData) {
                $field = VendorField::where('field_name', $fieldData['field_name'])->first();
                
                if ($field) {
                    // Update existing field
                    $field->display_order = $fieldData['display_order'];
                    $field->save();
                } else {
                    // Create new field metadata if it doesn't exist
                    VendorField::create([
                        'field_name' => $fieldData['field_name'],
                        'field_label' => ucfirst(str_replace('_', ' ', $fieldData['field_name'])),
                        'display_order' => $fieldData['display_order'],
                        'is_active' => false,
                    ]);
                }
            }

            return response()->json(['message' => 'Field order updated successfully']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating vendor field order: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Failed to update field order.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Normalize database column type to display format
     */
    private function normalizeColumnType($dbType)
    {
        $dbType = strtolower($dbType);
        
        // Map database types to display types
        if (strpos($dbType, 'varchar') !== false) {
            if (strpos($dbType, '255') !== false) {
                return 'VARCHAR (255)';
            } elseif (strpos($dbType, '100') !== false) {
                return 'VARCHAR (100)';
            } elseif (strpos($dbType, '20') !== false) {
                return 'VARCHAR (20)';
            }
            return 'VARCHAR (255)';
        } elseif (strpos($dbType, 'text') !== false) {
            return 'TEXT';
        } elseif (strpos($dbType, 'char') !== false) {
            return 'CHAR';
        } elseif (strpos($dbType, 'tinyint(1)') !== false || strpos($dbType, 'boolean') !== false) {
            return 'BOOLEAN';
        } elseif (strpos($dbType, 'decimal') !== false || strpos($dbType, 'int') !== false || strpos($dbType, 'float') !== false || strpos($dbType, 'double') !== false) {
            return 'NUMBER';
        }
        
        return 'TEXT'; // Default
    }
}

