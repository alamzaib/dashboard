<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormField;
use App\Models\VendorField;
use App\Models\ProspectField;
use App\Models\Vendor;
use App\Models\Prospect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class FormController extends Controller
{
    public function index()
    {
        try {
            $forms = Form::with('formFields')->orderBy('created_at', 'desc')->get();
            return response()->json($forms);
        } catch (\Exception $e) {
            Log::error('Error fetching forms: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch forms.'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'type' => 'required|in:vendor,prospect',
                'is_active' => 'nullable|boolean',
            ]);

            $form = Form::create($validated);
            return response()->json($form, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error creating form: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create form.'], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $form = Form::with('formFields')->findOrFail($id);
            
            // Enrich form fields with metadata from vendor_fields or prospect_fields
            $enrichedFields = $form->formFields->map(function ($formField) {
                $fieldMetadata = null;
                
                if ($formField->field_source === 'vendor') {
                    $fieldMetadata = VendorField::where('field_name', $formField->field_name)->first();
                } elseif ($formField->field_source === 'prospect') {
                    $fieldMetadata = ProspectField::where('field_name', $formField->field_name)->first();
                }
                
                return [
                    'id' => $formField->id,
                    'form_id' => $formField->form_id,
                    'field_name' => $formField->field_name,
                    'field_source' => $formField->field_source,
                    'display_order' => $formField->display_order,
                    'is_required' => $formField->is_required,
                    'is_visible' => $formField->is_visible,
                    'field_label' => $fieldMetadata ? $fieldMetadata->field_label : ucfirst(str_replace('_', ' ', $formField->field_name)),
                    'field_type' => $fieldMetadata ? $fieldMetadata->field_type : null,
                ];
            });
            
            $form->form_fields = $enrichedFields;
            
            return response()->json($form);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Form not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching form: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch form.'], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $form = Form::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'type' => 'sometimes|required|in:vendor,prospect',
                'is_active' => 'nullable|boolean',
            ]);

            $form->update($validated);
            return response()->json($form);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Form not found.'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating form: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update form.'], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $form = Form::findOrFail($id);
            $form->delete();
            return response()->json(['message' => 'Form deleted successfully']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Form not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Error deleting form: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete form.'], 500);
        }
    }

    public function addField(Request $request, string $id)
    {
        try {
            $form = Form::findOrFail($id);

            $validated = $request->validate([
                'field_name' => 'required|string|max:255',
                'field_source' => 'required|in:vendor,prospect',
                'is_required' => 'nullable|boolean',
                'is_visible' => 'nullable|boolean',
            ]);

            // Get the highest display_order for this form
            $maxOrder = FormField::where('form_id', $id)->max('display_order') ?? 0;

            $formField = FormField::create([
                'form_id' => $id,
                'field_name' => $validated['field_name'],
                'field_source' => $validated['field_source'],
                'display_order' => $maxOrder + 1,
                'is_required' => $validated['is_required'] ?? false,
                'is_visible' => $validated['is_visible'] ?? true,
            ]);

            return response()->json($formField, 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Form not found.'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error adding field to form: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to add field to form.'], 500);
        }
    }

    public function updateField(Request $request, string $formId, string $fieldId)
    {
        try {
            $formField = FormField::where('form_id', $formId)->findOrFail($fieldId);

            $validated = $request->validate([
                'is_required' => 'nullable|boolean',
                'is_visible' => 'nullable|boolean',
                'display_order' => 'nullable|integer',
            ]);

            $formField->update($validated);
            return response()->json($formField);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Form field not found.'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating form field: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update form field.'], 500);
        }
    }

    public function deleteField(string $formId, string $fieldId)
    {
        try {
            $formField = FormField::where('form_id', $formId)->findOrFail($fieldId);
            $formField->delete();
            return response()->json(['message' => 'Form field deleted successfully']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Form field not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Error deleting form field: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete form field.'], 500);
        }
    }

    public function updateFieldOrder(Request $request, string $id)
    {
        try {
            $form = Form::findOrFail($id);

            $validated = $request->validate([
                'field_ids' => 'required|array',
                'field_ids.*' => 'required|integer|exists:form_fields,id',
            ]);

            foreach ($validated['field_ids'] as $index => $fieldId) {
                FormField::where('id', $fieldId)
                    ->where('form_id', $id)
                    ->update(['display_order' => $index + 1]);
            }

            return response()->json(['message' => 'Field order updated successfully']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Form not found.'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating form field order: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update field order.'], 500);
        }
    }

    public function generatePublicKey(string $id)
    {
        try {
            $form = Form::findOrFail($id);
            
            // Generate a unique public key
            do {
                $publicKey = Str::random(32);
            } while (Form::where('public_key', $publicKey)->exists());

            $form->update([
                'is_public' => true,
                'public_key' => $publicKey,
            ]);

            // Get frontend URL from env or use default React dev server URL
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            $publicUrl = rtrim($frontendUrl, '/') . "/public/form/{$publicKey}";

            return response()->json([
                'public_key' => $publicKey,
                'public_url' => $publicUrl,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Form not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Error generating public key: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate public key.'], 500);
        }
    }

    public function getPublicForm(string $publicKey)
    {
        try {
            $form = Form::where('public_key', $publicKey)
                ->where('is_public', true)
                ->where('is_active', true)
                ->with(['formFields' => function ($query) {
                    $query->where('is_visible', true)->orderBy('display_order');
                }])
                ->firstOrFail();

            // Enrich form fields with metadata
            $enrichedFields = $form->formFields->map(function ($formField) {
                $fieldMetadata = null;
                
                if ($formField->field_source === 'vendor') {
                    $fieldMetadata = VendorField::where('field_name', $formField->field_name)->first();
                } elseif ($formField->field_source === 'prospect') {
                    $fieldMetadata = ProspectField::where('field_name', $formField->field_name)->first();
                }
                
                return [
                    'id' => $formField->id,
                    'field_name' => $formField->field_name,
                    'field_source' => $formField->field_source,
                    'display_order' => $formField->display_order,
                    'is_required' => $formField->is_required,
                    'is_visible' => $formField->is_visible,
                    'field_label' => $fieldMetadata ? $fieldMetadata->field_label : ucfirst(str_replace('_', ' ', $formField->field_name)),
                    'field_type' => $fieldMetadata ? $fieldMetadata->field_type : 'VARCHAR(255)',
                ];
            });

            return response()->json([
                'form' => [
                    'id' => $form->id,
                    'name' => $form->name,
                    'description' => $form->description,
                    'type' => $form->type,
                ],
                'fields' => $enrichedFields,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Form not found or not available.'], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching public form: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch form.'], 500);
        }
    }

    public function submitPublicForm(Request $request, string $publicKey)
    {
        try {
            $form = Form::where('public_key', $publicKey)
                ->where('is_public', true)
                ->where('is_active', true)
                ->with('formFields')
                ->firstOrFail();

            // Get form fields to validate required fields
            $formFields = $form->formFields->keyBy('field_name');
            $submittedData = $request->all();

            // Validate required fields
            $errors = [];
            foreach ($formFields as $fieldName => $formField) {
                if ($formField->is_required && empty($submittedData[$fieldName])) {
                    $errors[$fieldName] = 'This field is required';
                }
            }

            if (!empty($errors)) {
                return response()->json(['errors' => $errors], 422);
            }

            // Prepare data for saving
            $dataToSave = [];
            foreach ($formFields as $fieldName => $formField) {
                if (isset($submittedData[$fieldName])) {
                    $dataToSave[$fieldName] = $submittedData[$fieldName];
                }
            }

            // Add timestamps
            $dataToSave['created_at'] = now();
            $dataToSave['updated_at'] = now();

            // Save to appropriate table
            if ($form->type === 'vendor') {
                Vendor::create($dataToSave);
            } else {
                Prospect::create($dataToSave);
            }

            return response()->json([
                'message' => 'Form submitted successfully!',
                'success' => true,
            ], 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Form not found or not available.'], 404);
        } catch (\Exception $e) {
            Log::error('Error submitting public form: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to submit form. Please try again.'], 500);
        }
    }
}

