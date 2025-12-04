<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TaskGroupController;
use App\Http\Controllers\Api\TaskMemberController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserGroupController;
use App\Http\Controllers\Api\WorkflowController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Users
    Route::apiResource('users', UserController::class);

    // Menus - Custom routes must come before apiResource to avoid route conflicts
    Route::put('/menus/update-order', [MenuController::class, 'updateOrder']);
    Route::get('/menus/permission-group/{permissionGroupId}', [MenuController::class, 'getMenusByPermissionGroup']);
    Route::get('/menus/permission-group/{permissionGroupId}/user-groups', [MenuController::class, 'getUserGroupsByPermissionGroup']);
    Route::get('/user-groups', [MenuController::class, 'userGroups']); // Keep for backward compatibility
    Route::apiResource('menus', MenuController::class);

    // User Groups
    Route::apiResource('user-groups', UserGroupController::class);

    // Projects
    Route::apiResource('projects', ProjectController::class);

    // Permissions
    Route::get('/permissions/projects', [PermissionController::class, 'projects']);
    Route::get('/permissions/projects/{projectId}/groups', [PermissionController::class, 'permissionGroups']);
    Route::post('/permissions/groups', [PermissionController::class, 'storeGroup']);
    Route::get('/permissions/groups/{permissionGroupId}/users', [PermissionController::class, 'getUsers']);
    Route::put('/permissions/groups/{permissionGroupId}/users', [PermissionController::class, 'updateUsers']);
    Route::apiResource('permissions', PermissionController::class);

    // Task Management
    Route::apiResource('tasks', TaskController::class);
    Route::apiResource('reports', ReportController::class);
    Route::apiResource('workflows', WorkflowController::class);
    Route::apiResource('task-groups', TaskGroupController::class);
    Route::apiResource('task-members', TaskMemberController::class);
});

