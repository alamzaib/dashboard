<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Return basic application settings (currently only logo).
     */
    public function show(Request $request)
    {
        $logoPath = Setting::where('key', 'logo_path')->value('value');
        
        // Get project-specific site prefix if project_id is provided
        $projectId = $request->query('project_id');
        $sitePrefix = null;
        if ($projectId) {
            $sitePrefix = Setting::where('key', "site_prefix_project_{$projectId}")->value('value');
        }

        return response()->json([
            'logo_path' => $logoPath,
            'logo_url' => $logoPath ? Storage::disk('public')->url($logoPath) : null,
            'site_prefix' => $sitePrefix,
        ]);
    }

    /**
     * Update the global logo.
     */
    public function updateLogo(Request $request)
    {
        $validated = $request->validate([
            'logo' => 'required|image|max:2048',
        ]);

        $path = $request->file('logo')->store('app-logo', 'public');

        Setting::updateOrCreate(
            ['key' => 'logo_path'],
            ['value' => $path]
        );

        return response()->json([
            'message' => 'Logo updated successfully',
            'logo_path' => $path,
            'logo_url' => Storage::disk('public')->url($path),
        ]);
    }

    /**
     * Update site prefix for a project
     */
    public function updateSitePrefix(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'site_prefix' => 'nullable|string|max:50',
        ]);

        Setting::updateOrCreate(
            ['key' => "site_prefix_project_{$validated['project_id']}"],
            ['value' => $validated['site_prefix'] ?? '']
        );

        return response()->json([
            'message' => 'Site prefix updated successfully',
            'site_prefix' => $validated['site_prefix'] ?? '',
        ]);
    }
}


