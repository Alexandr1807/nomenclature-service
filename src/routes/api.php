<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AuditLogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::post('register', [AuthController::class, 'register']);
Route::post('login',    [AuthController::class, 'login']);
Route::middleware('auth:api')->post('logout', [AuthController::class, 'logout']);
Route::middleware('auth:api')->get('users', [UserController::class, 'index']);


Route::middleware('auth:api')->group(function () {
    // Products
    Route::get('products/export', [ProductController::class, 'export']);
    Route::post('products/import', [ProductController::class, 'import']);
    Route::apiResource('products', ProductController::class);

    // Categories
    Route::apiResource('categories', CategoryController::class);

    // Suppliers
    Route::apiResource('suppliers', SupplierController::class);

    Route::get('audit-logs', [AuditLogController::class, 'index']);
});

