<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PMForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PMFormController extends Controller
{
    public function index()
    {
        try {
            $forms = PMForm::with('site')->get();
            return response()->json($forms);
        } catch (\Exception $e) {
            Log::error('Error fetching PM forms: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch forms.'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'site_id' => 'nullable|exists:sites,id',
                'form_fields' => 'nullable|array',
                'is_active' => 'nullable|boolean',
            ]);

            $form = PMForm::create($validated);
            return response()->json($form->load('site'), 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error creating PM form: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create form.'], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $form = PMForm::with('site')->findOrFail($id);
            return response()->json($form);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Form not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching PM form: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch form.'], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $form = PMForm::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'site_id' => 'nullable|exists:sites,id',
                'form_fields' => 'nullable|array',
                'is_active' => 'nullable|boolean',
            ]);

            $form->update($validated);
            return response()->json($form->load('site'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Form not found.'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating PM form: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update form.'], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $form = PMForm::findOrFail($id);
            $form->delete();
            return response()->json(['message' => 'Form deleted successfully']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Form not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Error deleting PM form: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete form.'], 500);
        }
    }
}
