<?php
// routes/api.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ReportController;

// Authentication
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

// Tenant registration (public)
Route::post('tenants', [TenantController::class, 'store']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Tenant settings (Owner only)
    Route::get('tenants/settings', [TenantController::class, 'settings'])
        ->middleware('role:owner');
    Route::put('tenants/settings', [TenantController::class, 'updateSettings'])
        ->middleware('role:owner');

    // Users (Owner/Manager for index/show, Owner for create/update/delete)
    Route::get('users', [UserController::class, 'index'])
        ->middleware('role:owner,manager');
    Route::get('users/{user}', [UserController::class, 'show'])
        ->middleware('role:owner,manager');
    Route::post('users', [UserController::class, 'store'])->middleware('role:owner');
    Route::put('users/{user}', [UserController::class, 'update'])
        ->middleware('role:owner');
    Route::delete('users/{user}', [UserController::class, 'destroy'])
        ->middleware('role:owner');

    // Products (Manager/Cashier for index/show, Manager for create/update/delete/upload)
    Route::get('products', [ProductController::class, 'index'])->middleware('role:manager,cashier');
    Route::get('products/{product}', [ProductController::class, 'show'])->middleware('role:manager,cashier');
    Route::post('products', [ProductController::class, 'store'])->middleware('role:manager');
    Route::put('products/{product}', [ProductController::class, 'update'])->middleware('role:manager');
    Route::delete('products/{product}', [ProductController::class, 'destroy'])->middleware('role:manager');
    Route::post('products/upload-image', [ProductController::class, 'uploadImage'])->middleware('role:manager'); // To be implemented
    Route::get('products/low-stock', [InventoryController::class, 'lowStock'])->middleware('role:manager');

    // Inventory (Manager only)
    Route::get('inventory_movements', [InventoryController::class, 'movements'])->middleware('role:manager');
    Route::post('inventory_movements', [InventoryController::class, 'createMovement'])->middleware('role:manager');

    // Orders (Manager/Cashier)
    Route::get('orders', [OrderController::class, 'index'])->middleware('role:manager,cashier');
    Route::get('orders/{order}', [OrderController::class, 'show'])->middleware('role:manager,cashier');
    Route::post('orders', [OrderController::class, 'store'])->middleware('role:cashier');
    // Additional order endpoints (payment, refund, receipt, items) to be implemented

    // Reports (Owner/Manager)
    Route::get('reports/dashboard', [ReportController::class, 'dashboard'])->middleware('role:owner,manager');

    // Sync endpoints (to be implemented)
    // Route::post('sync/orders', ...);
    // Route::get('sync/status', ...);

    // Webhook endpoints (to be implemented)
    // Route::post('webhooks/stripe', ...);
});
