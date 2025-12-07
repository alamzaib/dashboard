<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Task;
use App\Models\Workorder;
use App\Models\Vendor;
use App\Models\Prospect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Get users count by status
     * Since users don't have a status field, we'll return total users count
     */
    public function usersByStatus()
    {
        try {
            $totalUsers = User::count();

            // For now, return total users as "active" since there's no status field
            // You can extend this later to add a status field to users table
            return response()->json([
                'total' => $totalUsers,
                'by_status' => [
                    'active' => $totalUsers, // All users are considered active for now
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get tasks and workorders count
     */
    public function tasksAndWorkorders()
    {
        try {
            $tasksCount = Task::count();
            $workordersCount = Workorder::count();

            return response()->json([
                'tasks' => $tasksCount,
                'workorders' => $workordersCount,
                'total' => $tasksCount + $workordersCount,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get tasks by status
     */
    public function tasksByStatus()
    {
        try {
            $tasksByStatus = Task::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status')
                ->toArray();

            return response()->json($tasksByStatus);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get workorders by status
     */
    public function workordersByStatus()
    {
        try {
            $workordersByStatus = Workorder::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status')
                ->toArray();

            return response()->json($workordersByStatus);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get vendors by status
     */
    public function vendorsByStatus()
    {
        try {
            $vendorsByStatus = Vendor::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status')
                ->toArray();

            return response()->json($vendorsByStatus);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get prospects by status
     */
    public function prospectsByStatus()
    {
        try {
            $prospectsByStatus = Prospect::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status')
                ->toArray();

            return response()->json($prospectsByStatus);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

