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
use App\Http\Controllers\Api\GrupAssetController;
use App\Http\Controllers\Api\MasterAssetController;
use App\Http\Controllers\Api\PermintaanAssetController;
use App\Http\Controllers\Api\SerahTerimaController;
use App\Http\Controllers\Api\MasterStatusAssetController;
use App\Http\Controllers\Api\PermintaanScrapController;
use App\Http\Controllers\Api\MutasiAssetController;
use App\Http\Controllers\Api\PermintaanPerbaikanController;
use App\Http\Controllers\Api\AssetReportController;
use App\Http\Controllers\Api\CompanySettingController;
use App\Http\Controllers\Api\AssetCountController;
use App\Http\Controllers\Api\ImportAssetController;
use App\Http\Controllers\Api\EmployeeImportController;

Route::post('/register', [AuthController::class,'register']);
Route::post('/login', [AuthController::class,'login']);
Route::get('/test', [AuthController::class,'test']);

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

    // Grup Asset
    Route::apiResource('grup-assets', GrupAssetController::class);
    // Lokasi Asset
    Route::apiResource('lokasi-assets', LokasiAssetController::class);
    Route::get('lokasi-assets/{lokasiAsset}/assets', [LokasiAssetController::class, 'getAssets']);

    // Master Asset
    Route::apiResource('master-assets', MasterAssetController::class);
    Route::get('master-assets/{asset_id}/stock/{location_id}', [MasterAssetController::class, 'getStock']);
    Route::post('master-assets/{id}/images', [MasterAssetController::class, 'uploadImages']);
    Route::delete('master-assets/{asset_id}/images/{image_id}', [MasterAssetController::class, 'deleteImage']);
    Route::get('master-assets/{kodeAsset}/location-stock-summary', [MasterAssetController::class, 'getLocationStockSummary']);
    Route::get('lokasi-with-stock/{lokasiID?}', [MasterAssetController::class, 'getLokasiWithStock']);


    // Master Status Asset
    Route::apiResource('master-status-assets', MasterStatusAssetController::class);

    // Permintaan Asset
    Route::apiResource('permintaan-assets', PermintaanAssetController::class);
    Route::get('permintaan-assets-bytrx/{noTransaksi}', [PermintaanAssetController::class, 'showbytrx']);


    // Serah Terima Asset
    Route::apiResource('serah-terima', SerahTerimaController::class);
    Route::post('serah-terima/{id}/restore', [SerahTerimaController::class, 'restore']);

    // Permintaan Scrap
    Route::apiResource('permintaan-scrap', PermintaanScrapController::class);
    Route::patch('permintaan-scrap/{id}/approval', [PermintaanScrapController::class, 'updateApproval']);

    // Mutasi Asset
    Route::apiResource('mutasi-assets', MutasiAssetController::class);

    // Permintaan Perbaikan Asset
    Route::apiResource('permintaan-perbaikan-assets', PermintaanPerbaikanController::class);
    Route::patch('permintaan-perbaikan-assets/{id}/approval', [PermintaanPerbaikanController::class, 'updateApproval']);
    Route::get('permintaan-perbaikan-assets/approved/{KodeAsset}', [PermintaanPerbaikanController::class, 'getApprovedRequests']);


    // Reporting
    Route::get('/reports/assets', [AssetReportController::class, 'index']);

    // Dashboard
    Route::get('/dashboard/summary', [App\Http\Controllers\Api\DashboardController::class, 'getAssetSummary']);
    Route::get('/dashboard/summary-by-group', [App\Http\Controllers\Api\DashboardController::class, 'getSummaryByGroup']);
    Route::get('/dashboard/repair-summary-by-month', [App\Http\Controllers\Api\DashboardController::class, 'getRepairSummaryByMonth']);

    // Company Settings
    Route::apiResource('company-settings', CompanySettingController::class);

    // Asset Count
    Route::apiResource('asset-counts', AssetCountController::class);

    // Import
    Route::post('import-master-assets', [ImportAssetController::class, 'importBulk']);
    Route::post('import-employees', [EmployeeImportController::class, 'import']);
});
