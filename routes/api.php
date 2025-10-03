<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\LokasiAssetController;

Route::post('/register', [AuthController::class,'register']);
Route::post('/login', [AuthController::class,'login']);

Route::get('/debug-config', function () {
    return response()->json([
        'session_domain_config' => config('session.domain'),
        'sanctum_stateful_domains_config' => config('sanctum.stateful'),
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class,'me']);
    Route::post('/logout', [AuthController::class,'logout']);

    // Users
    Route::apiResource('users', UserController::class);

    // Roles & permissions
    Route::apiResource('roles', RoleController::class);
    Route::post('roles/{role}/permissions', [RoleController::class,'attachPermissions']);
    Route::post('roles/{role}/assign-permission', [RoleController::class, 'assignPermission']);
    Route::apiResource('permissions', PermissionController::class);

    Route::apiResource('/menus', MenuController::class);

    // Departement
    Route::apiResource('departments', DepartmentController::class);

    // Employee
    Route::apiResource('employees', EmployeeController::class);

    // Lokasi Asset
    Route::apiResource('lokasi-assets', LokasiAssetController::class);
});
