<?php

use App\Http\Controllers\Api\HealthCheckController;
use App\Http\Controllers\Api\RequestorController;
use App\Http\Controllers\SuperAdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Health check endpoints (public)
Route::get('/health', [HealthCheckController::class, 'index'])->name('health');
Route::get('/health/detailed', [HealthCheckController::class, 'detailed'])->name('health.detailed');

// Requestor API routes
Route::prefix('requestor')->name('api.requestor.')->middleware(['web', 'api.auth:requestor'])->group(function () {
    // Dashboard metrics
    Route::get('/metrics', [RequestorController::class, 'getMetrics'])->name('metrics');
    Route::get('/statistics', [RequestorController::class, 'getStatistics'])->name('statistics');

    // Purchase Orders
    Route::prefix('purchase-orders')->name('purchase-orders.')->group(function () {
        Route::get('/', [RequestorController::class, 'getPurchaseOrders'])->name('index');
        Route::get('/recent', [RequestorController::class, 'getRecentPurchaseOrders'])->name('recent');
        Route::get('/{poId}', [RequestorController::class, 'getPurchaseOrder'])->name('show');
    });
});

// Test route
Route::get('/test', function () {
    return response()->json(['success' => true, 'message' => 'API is working']);
});

// Note: Purchase order details route moved to web.php for session-based authentication

// Superadmin API routes
Route::prefix('superadmin')->name('api.superadmin.')->middleware('web')->group(function () {

    // System metrics
    Route::get('/metrics', [SuperAdminController::class, 'getMetrics'])->name('metrics');

    // User management
    Route::prefix('users')->name('users.')->group(function () {
        Route::post('/create', [SuperAdminController::class, 'createUserApi'])->name('create');
        Route::post('/reset-password', [SuperAdminController::class, 'resetUserPasswordApi'])->name('reset-password');
        Route::post('/toggle', [SuperAdminController::class, 'toggleUserStatusApi'])->name('toggle');
        Route::delete('/delete', [SuperAdminController::class, 'deleteUserApi'])->name('delete');
    });

    // Security management
    Route::prefix('security')->name('security.')->group(function () {
        Route::get('/stats', [SuperAdminController::class, 'getSecurityStatsApi'])->name('stats');
        Route::get('/alerts', [SuperAdminController::class, 'getSecurityAlertsApi'])->name('alerts');
        Route::post('/update', [SuperAdminController::class, 'updateSecuritySettingsApi'])->name('update');
        Route::post('/force-logout-all', [SuperAdminController::class, 'forceLogoutAllApi'])->name('force-logout-all');
        Route::post('/terminate-session', [SuperAdminController::class, 'terminateSessionApi'])->name('terminate-session');
    });

    // System management
    Route::prefix('system')->name('system.')->group(function () {
        Route::post('/clear-cache', [SuperAdminController::class, 'clearCacheApi'])->name('clear-cache');
        Route::post('/backup', [SuperAdminController::class, 'createBackupApi'])->name('backup');
        Route::post('/update', [SuperAdminController::class, 'updateSystemApi'])->name('update');
        Route::post('/restart-services', [SuperAdminController::class, 'restartServicesApi'])->name('restart-services');
        Route::get('/info', [SuperAdminController::class, 'getSystemInfoApi'])->name('info');
    });

    // Database management
    Route::prefix('database')->name('database.')->group(function () {
        Route::get('/table-info', [SuperAdminController::class, 'getDatabaseInfo'])->name('table-info');
        Route::get('/table-details/{tableName}', [SuperAdminController::class, 'getTableDetailsApi'])->name('table-details');
        Route::post('/optimize', [SuperAdminController::class, 'optimizeDatabaseApi'])->name('optimize');
        Route::post('/check-integrity', [SuperAdminController::class, 'checkDatabaseIntegrityApi'])->name('check-integrity');
        Route::post('/repair', [SuperAdminController::class, 'repairDatabaseTablesApi'])->name('repair');
        Route::post('/backup', [SuperAdminController::class, 'createDatabaseBackupApi'])->name('backup');
    });

    // Logs management
    Route::prefix('logs')->name('logs.')->group(function () {
        Route::get('/recent', [SuperAdminController::class, 'getRecentLogsApi'])->name('recent');
        Route::post('/clear', [SuperAdminController::class, 'clearLogsApi'])->name('clear');
        Route::post('/settings', [SuperAdminController::class, 'updateLogSettingsApi'])->name('settings');
    });

    // Branding management
    Route::prefix('branding')->name('branding.')->group(function () {
        Route::post('/update', [SuperAdminController::class, 'updateBrandingApi'])->name('update');
    });
});
