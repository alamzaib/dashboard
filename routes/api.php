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
use App\Http\Controllers\Api\WorkorderController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\ProspectController;
use App\Http\Controllers\Api\VendorDocumentController;
use App\Http\Controllers\Api\VendorFieldController;
use App\Http\Controllers\Api\ProspectFieldController;
use App\Http\Controllers\Api\TaskFieldController;
use App\Http\Controllers\Api\WorkorderFieldController;
use App\Http\Controllers\Api\FormController;
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
    Route::put('/permissions/groups/{permissionGroupId}', [PermissionController::class, 'updateGroup']);
    Route::get('/permissions/groups/{permissionGroupId}/users', [PermissionController::class, 'getUsers']);
    Route::put('/permissions/groups/{permissionGroupId}/users', [PermissionController::class, 'updateUsers']);
    Route::apiResource('permissions', PermissionController::class);

    // Task Management
    Route::apiResource('tasks', TaskController::class);
    Route::apiResource('workorders', WorkorderController::class);
    Route::apiResource('reports', ReportController::class);
    Route::apiResource('workflows', WorkflowController::class);
    Route::apiResource('task-groups', TaskGroupController::class);
    
    // Task/Workorder Fields - Custom routes must come before apiResource to avoid route conflicts
    Route::put('/task-fields/update-order', [TaskFieldController::class, 'updateOrder']);
    Route::put('/workorder-fields/update-order', [WorkorderFieldController::class, 'updateOrder']);
    Route::apiResource('task-fields', TaskFieldController::class);
    Route::apiResource('workorder-fields', WorkorderFieldController::class);
    
    // Task Members - Custom routes for managing users in task groups
    Route::get('/task-members/task-groups', [TaskMemberController::class, 'getTaskGroups']);
    Route::get('/task-members/task-groups/{taskGroupId}/users', [TaskMemberController::class, 'getUsersByTaskGroup']);
    Route::get('/task-members/users', [TaskMemberController::class, 'getAllUsers']);
    Route::post('/task-members/task-groups/{taskGroupId}/users', [TaskMemberController::class, 'addUserToTaskGroup']);
    Route::delete('/task-members/task-groups/{taskGroupId}/users/{userId}', [TaskMemberController::class, 'removeUserFromTaskGroup']);

    // Settings
    Route::get('/settings', [SettingsController::class, 'show']);
    Route::post('/settings/logo', [SettingsController::class, 'updateLogo']);

    // Vendor Management
    Route::apiResource('vendors', VendorController::class);
    Route::apiResource('prospects', ProspectController::class);
    Route::apiResource('vendor-documents', VendorDocumentController::class);
    
    // Vendor/Prospect Fields - Custom routes must come before apiResource to avoid route conflicts
    Route::put('/vendor-fields/update-order', [VendorFieldController::class, 'updateOrder']);
    Route::put('/prospect-fields/update-order', [ProspectFieldController::class, 'updateOrder']);
    Route::apiResource('vendor-fields', VendorFieldController::class);
    Route::apiResource('prospect-fields', ProspectFieldController::class);
    
    // Forms
    Route::post('/forms/{id}/fields', [FormController::class, 'addField']);
    Route::put('/forms/{formId}/fields/{fieldId}', [FormController::class, 'updateField']);
    Route::delete('/forms/{formId}/fields/{fieldId}', [FormController::class, 'deleteField']);
    Route::put('/forms/{id}/fields/update-order', [FormController::class, 'updateFieldOrder']);
    Route::post('/forms/{id}/generate-public-key', [FormController::class, 'generatePublicKey']);
    Route::apiResource('forms', FormController::class);
});

// Public routes (no authentication required)
Route::prefix('public')->group(function () {
    Route::get('/form/{publicKey}', [FormController::class, 'getPublicForm']);
    Route::post('/form/{publicKey}/submit', [FormController::class, 'submitPublicForm']);
});

