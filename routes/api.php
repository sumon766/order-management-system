<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);

        // Products - only vendors and admins can manage
        Route::apiResource('products', ProductController::class)->middleware(['vendor']);
        Route::get('products/low-stock/alerts', [ProductController::class, 'lowStock'])->middleware(['vendor']);

        // Customers can only view products
        Route::get('products', [ProductController::class, 'index'])->withoutMiddleware(['vendor']);
        Route::get('products/{product}', [ProductController::class, 'show'])->withoutMiddleware(['vendor']);
    });
});
