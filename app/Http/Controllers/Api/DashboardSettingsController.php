<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserDashboardSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardSettingsController extends Controller
{
    /**
     * Get dashboard settings for the authenticated user
     */
    public function show()
    {
        try {
            $user = Auth::user();
            $settings = UserDashboardSetting::where('user_id', $user->id)->first();
            
            if ($settings) {
                return response()->json([
                    'chart_settings' => $settings->chart_settings,
                ]);
            }
            
            // Return default settings if none exist
            return response()->json([
                'chart_settings' => null,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Save dashboard settings for the authenticated user
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'chart_settings' => 'required|array',
            ]);

            $user = Auth::user();
            
            $settings = UserDashboardSetting::updateOrCreate(
                ['user_id' => $user->id],
                ['chart_settings' => $validated['chart_settings']]
            );

            return response()->json([
                'message' => 'Dashboard settings saved successfully',
                'chart_settings' => $settings->chart_settings,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

