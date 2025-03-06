<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController; 
use App\Http\Controllers\StorageController;
use App\Http\Controllers\ShipmentSupplierController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DeliverySupplierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderProductController;



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
// 🔓 Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// 🔒 Protected Routes (Require Authentication)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/users', [UserController::class, 'index']); // ✅ Get all users
    Route::post('/users', [UserController::class, 'store']); // ✅ Only Admins can create users
    Route::delete('/users/{id}', [UserController::class, 'destroy']); // ✅ Only Admins can delete users

    Route::get('/dashboard', function () {
        return response()->json(['message' => 'Welcome to the dashboard']);
    });

    Route::post('/logout', [AuthController::class, 'logout']);
});

//Storage Routes//

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/storages', [StorageController::class, 'index']); // ✅ View all storage locations
    Route::get('/storages/{id}', [StorageController::class, 'show']); // ✅ View a single storage location
    Route::post('/storages', [StorageController::class, 'store']); // ❌ Only Admin can add storage
    Route::put('/storages/{id}', [StorageController::class, 'update']); // ❌ Only Admin can update
    Route::delete('/storages/{id}', [StorageController::class, 'destroy']); // ❌ Only Admin can delete
});


//Shipment Supplier Routes//
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/shipment-suppliers', [ShipmentSupplierController::class, 'index']); // ✅ View all suppliers
    Route::get('/shipment-suppliers/{id}', [ShipmentSupplierController::class, 'show']); // ✅ View a single supplier
    Route::post('/shipment-suppliers', [ShipmentSupplierController::class, 'store']); // ❌ Only Admin can add
    Route::put('/shipment-suppliers/{id}', [ShipmentSupplierController::class, 'update']); // ❌ Only Admin can update
    Route::delete('/shipment-suppliers/{id}', [ShipmentSupplierController::class, 'destroy']); // ❌ Only Admin can delete
});

//Shipment Routes//
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/shipments', [ShipmentController::class, 'index']); // ✅ View all shipments
    Route::get('/shipments/{id}', [ShipmentController::class, 'show']); // ✅ View a single shipment
    Route::post('/shipments', [ShipmentController::class, 'store']); // ❌ Only Admin can add
    Route::put('/shipments/{id}', [ShipmentController::class, 'update']); // ❌ Only Admin can update
    Route::delete('/shipments/{id}', [ShipmentController::class, 'destroy']); // ❌ Only Admin can delete
});

//Product Routes anyone can crud product//
Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('products', ProductController::class);
});


//Delivery Supplier Routes//
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/delivery-suppliers', [DeliverySupplierController::class, 'index']); // ✅ View all suppliers
    Route::get('/delivery-suppliers/{id}', [DeliverySupplierController::class, 'show']); // ✅ View a single supplier
    Route::post('/delivery-suppliers', [DeliverySupplierController::class, 'store']); // ❌ Only Admin can add
    Route::put('/delivery-suppliers/{id}', [DeliverySupplierController::class, 'update']); // ❌ Only Admin can update
    Route::delete('/delivery-suppliers/{id}', [DeliverySupplierController::class, 'destroy']); // ❌ Only Admin can delete
});

//customer routes//
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/customers', [CustomerController::class, 'index']); // ✅ View all customers
    Route::get('/customers/{id}', [CustomerController::class, 'show']); // ✅ View a single customer
    Route::post('/customers', [CustomerController::class, 'store']); // ❌ Only Admin can add
    Route::put('/customers/{id}', [CustomerController::class, 'update']); // ❌ Only Admin can update
    Route::delete('/customers/{id}', [CustomerController::class, 'destroy']); // ❌ Only Admin can delete
});

//order routes//
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/orders', [OrderController::class, 'index']); // ✅ View all orders
    Route::get('/orders/{id}', [OrderController::class, 'show']); // ✅ View a single order
    Route::post('/orders', [OrderController::class, 'store']); // ❌ Only Admin can create
    Route::put('/orders/{id}', [OrderController::class, 'update']); // ❌ Only Admin can update
    Route::delete('/orders/{id}', [OrderController::class, 'destroy']); // ❌ Only Admin can delete
});

//order product routes//
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/order-products', [OrderProductController::class, 'index']); // ✅ View all order products
    Route::get('/order-products/{order_id}', [OrderProductController::class, 'show']); // ✅ View products in an order
    Route::post('/order-products', [OrderProductController::class, 'store']); // ❌ Only Admin can add products to an order
    Route::delete('/order-products/{id}', [OrderProductController::class, 'destroy']); // ❌ Only Admin can remove products
});
