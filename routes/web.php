<?php

use Illuminate\Support\Facades\Route;
// Auth and role controllers
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemsController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\SettingsController;

Route::get('/post/create', [App\Http\Controllers\PostController::class, 'create']);
Route::post('/post', [App\Http\Controllers\PostController::class, 'store']);

// Landing: redirect to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Login / Logout
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Role-based dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/api/dashboard/summary', [DashboardController::class, 'summary'])->name('api.dashboard.summary');

// Superadmin control panel - NO MIDDLEWARE RESTRICTIONS FOR SUPERADMIN
Route::prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/', [SuperAdminController::class, 'index'])->name('index');
    Route::post('/branding', [SuperAdminController::class, 'updateBranding'])->name('branding');
    Route::post('/system', [SuperAdminController::class, 'systemAction'])->name('system');
    Route::get('/database', [SuperAdminController::class, 'showDatabaseSettings'])->name('database');
    Route::post('/database', [SuperAdminController::class, 'updateDatabaseSettings'])->name('database.update');
    Route::get('/database/info', [SuperAdminController::class, 'getDatabaseInfo'])->name('database.info');
    Route::post('/query', [SuperAdminController::class, 'executeQuery'])->name('query');
    Route::get('/logs', [SuperAdminController::class, 'showLogs'])->name('logs');
    Route::post('/logs/clear', [SuperAdminController::class, 'clearLogs'])->name('logs.clear');
    
    // User management for superadmin
    Route::post('/users/reset-password', [SuperAdminController::class, 'resetUserPassword'])->name('users.reset-password');
    Route::post('/users/toggle', [SuperAdminController::class, 'toggleUserStatus'])->name('users.toggle');
    Route::delete('/users/{id}', [SuperAdminController::class, 'deleteUser'])->name('users.delete');
    Route::post('/users/create', [SuperAdminController::class, 'createUser'])->name('users.create');
});

// Superadmin API routes - NO RESTRICTIONS FOR SUPERADMIN
Route::prefix('api/superadmin')->name('api.superadmin.')->group(function () {
    Route::get('/test', function() {
        return response()->json(['success' => true, 'message' => 'API is working']);
    })->name('test');
    Route::get('/metrics', [SuperAdminController::class, 'getMetrics'])->name('metrics');
    Route::get('/database/table-info', [SuperAdminController::class, 'getDatabaseInfo'])->name('database.table-info');
    Route::get('/database/table-details/{table}', [SuperAdminController::class, 'getTableDetails'])->name('database.table-details');
    Route::get('/logs/recent', [SuperAdminController::class, 'getRecentLogsApi'])->name('logs.recent');
    Route::post('/logs/clear', [SuperAdminController::class, 'clearLogsApi'])->name('logs.clear');
    Route::post('/database/optimize', [SuperAdminController::class, 'optimizeDatabaseApi'])->name('database.optimize');
    Route::post('/database/check-integrity', [SuperAdminController::class, 'checkDatabaseIntegrityApi'])->name('database.check-integrity');
    Route::post('/database/repair', [SuperAdminController::class, 'repairDatabaseTablesApi'])->name('database.repair');
    Route::post('/database/backup', [SuperAdminController::class, 'createDatabaseBackupApi'])->name('database.backup');
    
    // User management API
    Route::post('/users/reset-password', [SuperAdminController::class, 'resetUserPasswordApi'])->name('users.reset-password');
    Route::post('/users/toggle', [SuperAdminController::class, 'toggleUserStatusApi'])->name('users.toggle');
    Route::delete('/users/{id}', [SuperAdminController::class, 'deleteUserApi'])->name('users.delete');
    Route::post('/users/create', [SuperAdminController::class, 'createUserApi'])->name('users.create');
    
    // Branding API
    Route::post('/branding/update', [SuperAdminController::class, 'updateBrandingApi'])->name('branding.update');
});

// Admin: user management (authorized_personnel)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
});

// Purchase Orders (Requestor)
Route::prefix('po')->name('po.')->group(function () {
    Route::get('/', [PurchaseOrderController::class, 'index'])->name('index');
    Route::get('/create', [PurchaseOrderController::class, 'create'])->name('create');
    Route::post('/', [PurchaseOrderController::class, 'store'])->name('store');
    Route::get('/{poNo}', [PurchaseOrderController::class, 'show'])->name('show');
    Route::get('/{poNo}/json', [PurchaseOrderController::class, 'showJson'])->name('show_json');
    Route::get('/{poNo}/print', [PurchaseOrderController::class, 'print'])->name('print');
    Route::get('/{poNo}/edit', [PurchaseOrderController::class, 'edit'])->name('edit');
    Route::put('/{poNo}', [PurchaseOrderController::class, 'update'])->name('update');
    Route::post('/{poNo}/status', [PurchaseOrderController::class, 'updateStatus'])->name('update_status');
    Route::get('/next/number', [PurchaseOrderController::class, 'nextNumber'])->name('next_number');
    Route::get('/getallorder', [PurchaseOrderController::class, 'getAllPO']);
});

// Suppliers (Authorized Personnel & Superadmin)
Route::prefix('suppliers')->name('suppliers.')->group(function () {
    Route::get('/', [SupplierController::class, 'index'])->name('index');
    Route::get('/create', [SupplierController::class, 'create'])->name('create');
    Route::post('/', [SupplierController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [SupplierController::class, 'edit'])->name('edit');
    Route::post('/{id}/edit', [SupplierController::class, 'edit'])->name('edit.post'); // Support POST for edit
    Route::put('/{id}', [SupplierController::class, 'update'])->name('update');
    Route::post('/{id}', [SupplierController::class, 'update'])->name('update.post'); // Support POST for update
    Route::delete('/{id}', [SupplierController::class, 'destroy'])->name('destroy');
});

// Items pages (Requestor & Superadmin)
Route::prefix('items')->name('items.')->group(function () {
    Route::get('/', [ItemsController::class, 'index'])->name('index');
    Route::get('/create', [ItemsController::class, 'create'])->name('create');
    Route::post('/', [ItemsController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [ItemsController::class, 'edit'])->name('edit');
    Route::put('/{id}', [ItemsController::class, 'update'])->name('update');
    Route::delete('/{id}', [ItemsController::class, 'destroy'])->name('destroy');
});

// Approval actions
Route::post('/approvals/{poId}/verify', [ApprovalController::class, 'verify'])->name('approvals.verify');
Route::post('/approvals/{poId}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
Route::post('/approvals/{poID}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
Route::post('/approvals/{poId}/receive', [ApprovalController::class, 'receive'])->name('approvals.receive');

// Item helper APIs for dropdowns and price autofill
Route::get('/api/items/suggestions', [ItemController::class, 'suggestions'])->name('api.items.suggestions');
Route::get('/api/items/latest-price', [ItemController::class, 'latestPrice'])->name('api.items.latest_price');

// Settings routes
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [SettingsController::class, 'index'])->name('index');
    Route::post('/profile', [SettingsController::class, 'updateProfile'])->name('profile.update');
    Route::post('/password', [SettingsController::class, 'updatePassword'])->name('password.update');
    Route::post('/logo/upload', [SettingsController::class, 'uploadLogo'])->name('logo.upload');
    Route::delete('/logo/remove', [SettingsController::class, 'removeLogo'])->name('logo.remove');
});

// Status management routes
Route::prefix('status')->name('status.')->group(function () {
    Route::get('/', [StatusController::class, 'index'])->name('index');
    Route::post('/', [StatusController::class, 'store'])->name('store');
    Route::put('/{id}', [StatusController::class, 'update'])->name('update');
    Route::delete('/{id}', [StatusController::class, 'destroy'])->name('destroy');
});

// Admin Status management routes (for advanced settings)
Route::prefix('admin/status')->name('admin.status.')->group(function () {
    Route::get('/', [StatusController::class, 'adminIndex'])->name('index');
    Route::get('/config', [StatusController::class, 'config'])->name('config');
    Route::post('/config', [StatusController::class, 'updateConfig'])->name('config.update');
    Route::get('/create', [StatusController::class, 'create'])->name('create');
    Route::post('/', [StatusController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [StatusController::class, 'edit'])->name('edit');
    Route::put('/{id}', [StatusController::class, 'update'])->name('update');
    Route::delete('/{id}', [StatusController::class, 'destroy'])->name('destroy');
    Route::post('/reorder', [StatusController::class, 'reorder'])->name('reorder');
    Route::post('/reset', [StatusController::class, 'reset'])->name('reset');
});

// Dynamic CSS route
Route::get('/css/dynamic-status.css', [SettingsController::class, 'dynamicStatusCss'])->name('dynamic.status.css');

