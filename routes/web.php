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
use App\Http\Controllers\AccountSettingsController;

// Removed old tutorial PostController routes (controller no longer exists)

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

// Superadmin control panel (redirect index to dashboard – features consolidated under Dashboard)
Route::get('/superadmin', function (\Illuminate\Http\Request $request) {
    return redirect()->route('dashboard', $request->only('tab'));
})->name('superadmin.index');
Route::post('/superadmin/branding', [SuperAdminController::class, 'updateBranding'])->name('superadmin.branding');
Route::post('/superadmin/system', [SuperAdminController::class, 'systemAction'])->name('superadmin.system');
Route::get('/superadmin/database', [SuperAdminController::class, 'showDatabaseSettings'])->name('superadmin.database');
Route::post('/superadmin/database', [SuperAdminController::class, 'updateDatabaseSettings'])->name('superadmin.database.update');
Route::get('/superadmin/database/info', [SuperAdminController::class, 'getDatabaseInfo'])->name('superadmin.database.info');
Route::post('/superadmin/query', [SuperAdminController::class, 'executeQuery'])->name('superadmin.query');
Route::get('/superadmin/logs', [SuperAdminController::class, 'showLogs'])->name('superadmin.logs');
Route::post('/superadmin/logs/clear', [SuperAdminController::class, 'clearLogs'])->name('superadmin.logs.clear');

// Account Settings (All authenticated users)
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [AccountSettingsController::class, 'index'])->name('index');
    Route::post('/profile', [AccountSettingsController::class, 'updateProfile'])->name('profile.update');
    Route::post('/password', [AccountSettingsController::class, 'updatePassword'])->name('password.update');
    Route::post('/preferences', [AccountSettingsController::class, 'updatePreferences'])->name('preferences.update');
    Route::post('/role-settings', [AccountSettingsController::class, 'updateRoleSettings'])->name('role.update');
    Route::post('/avatar', [AccountSettingsController::class, 'uploadAvatar'])->name('avatar.upload');
    Route::post('/delete-account', [AccountSettingsController::class, 'deleteAccount'])->name('account.delete');
    Route::post('/export-data', [AccountSettingsController::class, 'exportData'])->name('data.export');
    // JSON endpoints consumed by account-settings.js
    Route::get('/login-activity', [AccountSettingsController::class, 'loginActivity'])->name('login.activity');
    Route::get('/activity-log', [AccountSettingsController::class, 'activityLog'])->name('activity.log');
});

// Admin: user management (authorized_personnel)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::post('/users/{userId}/toggle', [UserController::class, 'toggleActive'])->name('users.toggle');
});

// Purchase Orders (Requestor)
Route::prefix('po')->name('po.')->group(function () {
    Route::get('/', [PurchaseOrderController::class, 'index'])->name('index');
    Route::get('/create', [PurchaseOrderController::class, 'create'])->name('create');
    Route::post('/', [PurchaseOrderController::class, 'store'])->name('store');
    Route::get('/{poNo}/edit', [PurchaseOrderController::class, 'edit'])->name('edit');
    Route::post('/{poNo}/edit', [PurchaseOrderController::class, 'update'])->name('update');
    Route::get('/{poNo}', [PurchaseOrderController::class, 'show'])->name('show');
    Route::get('/{poNo}/json', [PurchaseOrderController::class, 'showJson'])->name('show_json');
    Route::get('/{poNo}/print', [PurchaseOrderController::class, 'print'])->name('print');
    Route::post('/{poNo}/status', [PurchaseOrderController::class, 'updateStatus'])->name('update_status');
    Route::get('/next/number', [PurchaseOrderController::class, 'nextNumber'])->name('next_number');
    Route::get('/getallorder', [PurchaseOrderController::class, 'getAllPO']);
});

// Suppliers (Authorized Personnel)
Route::prefix('suppliers')->name('suppliers.')->group(function () {
    Route::get('/', [SupplierController::class, 'index'])->name('index');
    Route::get('/create', [SupplierController::class, 'create'])->name('create');
    Route::post('/', [SupplierController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [SupplierController::class, 'edit'])->name('edit');
    Route::post('/{id}/edit', [SupplierController::class, 'update'])->name('update');
});

// Items Management (All Users)
Route::prefix('items')->name('items.')->group(function () {
    Route::get('/', [ItemsController::class, 'index'])->name('index');
    Route::get('/create', [ItemsController::class, 'create'])->name('create');
    Route::post('/', [ItemsController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [ItemsController::class, 'edit'])->name('edit');
    Route::put('/{id}', [ItemsController::class, 'update'])->name('update');
    Route::post('/{id}/edit', [ItemsController::class, 'update'])->name('update.post'); // Keep POST for compatibility
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

