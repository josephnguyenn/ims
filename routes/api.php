<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DeliverySupplierController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderProductController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\ShipmentSupplierController;
use App\Http\Controllers\StorageController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// 🔓 Public Routes with Rate Limiting
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// 🔒 Protected Routes (Require Authentication)
Route::middleware(['auth:sanctum'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return response()->json(['message' => 'Welcome to the dashboard']);
    });

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // User Management - Admin Only
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
    });
});

// Storage Routes - Admin & Manager & Staff
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/storages', [StorageController::class, 'index']);
    Route::get('/storages/{id}', [StorageController::class, 'show']);

    // Create/Update/Delete - Admin & Manager only
    Route::middleware(['role:admin,manager'])->group(function () {
        Route::post('/storages', [StorageController::class, 'store']);
        Route::put('/storages/{id}', [StorageController::class, 'update']);
        Route::delete('/storages/{id}', [StorageController::class, 'destroy']);
    });
});

// Shipment Supplier Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/shipment-suppliers', [ShipmentSupplierController::class, 'index']);
    Route::get('/shipment-suppliers/{id}', [ShipmentSupplierController::class, 'show']);

    // Create/Update/Delete - Admin & Manager only
    Route::middleware(['role:admin,manager'])->group(function () {
        Route::post('/shipment-suppliers', [ShipmentSupplierController::class, 'store']);
        Route::put('/shipment-suppliers/{id}', [ShipmentSupplierController::class, 'update']);
        Route::delete('/shipment-suppliers/{id}', [ShipmentSupplierController::class, 'destroy']);
    });
});

// Shipment Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/shipments', [ShipmentController::class, 'index']);
    Route::get('/shipments/{id}', [ShipmentController::class, 'show']);

    Route::middleware(['role:admin,manager'])->group(function () {
        Route::post('/shipments', [ShipmentController::class, 'store']);
        Route::put('/shipments/{id}', [ShipmentController::class, 'update']);
        Route::delete('/shipments/{id}', [ShipmentController::class, 'destroy']);
    });
});

// Product Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('products/search', [ProductController::class, 'searchByCode']);
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{id}', [ProductController::class, 'show']);

    // Create/Update/Delete - Admin & Manager only
    Route::middleware(['role:admin,manager'])->group(function () {
        Route::post('products', [ProductController::class, 'store']);
        Route::put('products/{id}', [ProductController::class, 'update']);
        Route::delete('products/{id}', [ProductController::class, 'destroy']);
        
        // Bulk operations
        Route::post('products/bulk/update', [ProductController::class, 'bulkUpdate']);
        Route::post('products/bulk/delete', [ProductController::class, 'bulkDelete']);
    });
});

// Delivery Supplier Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/delivery-suppliers', [DeliverySupplierController::class, 'index']);
    Route::get('/delivery-suppliers/{id}', [DeliverySupplierController::class, 'show']);

    Route::middleware(['role:admin,manager'])->group(function () {
        Route::post('/delivery-suppliers', [DeliverySupplierController::class, 'store']);
        Route::put('/delivery-suppliers/{id}', [DeliverySupplierController::class, 'update']);
        Route::delete('/delivery-suppliers/{id}', [DeliverySupplierController::class, 'destroy']);
    });
});

// Customer Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/customers', [CustomerController::class, 'index']);
    Route::get('/customers/{id}', [CustomerController::class, 'show']);

    Route::middleware(['role:admin,manager'])->group(function () {
        Route::post('/customers', [CustomerController::class, 'store']);
        Route::put('/customers/{id}', [CustomerController::class, 'update']);
        Route::delete('/customers/{id}', [CustomerController::class, 'destroy']);
    });
});

// Order Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']); // All authenticated users can create orders
    
    Route::middleware(['role:admin,manager'])->group(function () {
        Route::put('/orders/{id}', [OrderController::class, 'update']);
        Route::delete('/orders/{id}', [OrderController::class, 'destroy']);
    });
});

// Order Product Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/order-products', [OrderProductController::class, 'index']);
    Route::get('/order-products/{id}', [OrderProductController::class, 'show']);
    Route::post('/order-products', [OrderProductController::class, 'store']);
    Route::put('/order-products/{id}', [OrderProductController::class, 'update']);
    Route::delete('/order-products/{id}', [OrderProductController::class, 'destroy']);
});

// Report Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/reports/sales', [ReportController::class, 'salesReport']);
    Route::get('/reports/top-products', [ReportController::class, 'topSellingProducts']);
    Route::get('/reports/monthly-sales', [ReportController::class, 'monthlySalesReport']);
    Route::get('/reports/pos', [ReportController::class, 'posReport']);
});

// 📊 Analytics & AI Routes - Rate limited for expensive operations
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::get('/analytics/sales-trends', [AnalyticsController::class, 'salesTrends']);
    Route::get('/analytics/top-products', [AnalyticsController::class, 'topProducts']);
    Route::get('/analytics/revenue', [AnalyticsController::class, 'revenueAnalysis']);
    Route::get('/analytics/inventory-turnover', [AnalyticsController::class, 'inventoryTurnover']);
    Route::get('/analytics/sales-forecast', [AnalyticsController::class, 'salesForecast']);
    
    // AI endpoint with stricter rate limit
    Route::middleware('throttle:10,1')->group(function () {
        Route::get('/analytics/ai-insights', [AnalyticsController::class, 'aiInsights']);
    });
});

// Category Routes - Admin only
Route::middleware(['auth:sanctum', 'role:admin,manager'])->group(function () {
    Route::apiResource('categories', CategoryController::class);
});

