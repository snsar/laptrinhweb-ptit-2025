<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
    });

    // User routes - cần quyền quản lý người dùng
    Route::middleware('permission:manage-users')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::post('/users/{user}/roles', [UserController::class, 'assignRole']);
        Route::delete('/users/{user}/roles', [UserController::class, 'revokeRole']);
        Route::post('/users/{user}/permissions', [UserController::class, 'givePermission']);
        Route::delete('/users/{user}/permissions', [UserController::class, 'revokePermission']);
    });

    // Role routes - chỉ admin mới có quyền quản lý vai trò
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('roles', RoleController::class);
        Route::post('/roles/{role}/permissions', [RoleController::class, 'addPermission']);
        Route::delete('/roles/{role}/permissions', [RoleController::class, 'removePermission']);
    });

    // Permission routes - chỉ admin mới có quyền quản lý quyền
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('permissions', PermissionController::class);
    });

    // Project routes
    Route::apiResource('projects', ProjectController::class);
    Route::post('/projects/{project}/members', [ProjectController::class, 'addMember']);
    Route::delete('/projects/{project}/members', [ProjectController::class, 'removeMember']);

    // Task routes
    Route::apiResource('tasks', TaskController::class);
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus']);

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread', [NotificationController::class, 'unread']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);
});
