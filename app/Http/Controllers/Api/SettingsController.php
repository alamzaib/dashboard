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
    public function show()
    {
        $logoPath = Setting::where('key', 'logo_path')->value('value');

        return response()->json([
            'logo_path' => $logoPath,
            'logo_url' => $logoPath ? Storage::disk('public')->url($logoPath) : null,
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
}


