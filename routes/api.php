<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProductImportController;
use App\Http\Controllers\Api\V1\SearchController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    // Public routes
    Route::get('search/products', [SearchController::class, 'products']);
    Route::get('/documentation', '\L5Swagger\Http\Controllers\SwaggerController@api')->name('l5swagger.api');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);

        // Products - only vendors and admins can manage
        Route::apiResource('products', ProductController::class)->middleware(['vendor']);
        Route::get('products/low-stock/alerts', [ProductController::class, 'lowStock'])->middleware(['vendor']);

        // Customers can only view products
        Route::get('products', [ProductController::class, 'index'])->withoutMiddleware(['vendor']);
        Route::get('products/{product}', [ProductController::class, 'show'])->withoutMiddleware(['vendor']);

        // Product Import
        Route::post('products/import', [ProductImportController::class, 'import'])->middleware(['vendor']);

        // Orders
        Route::apiResource('orders', OrderController::class);
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus']);
        Route::post('orders/{order}/cancel', [OrderController::class, 'cancel']);
        Route::get('orders/{order}/invoice', [OrderController::class, 'downloadInvoice']);
    });
});
