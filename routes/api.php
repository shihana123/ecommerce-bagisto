<?php

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

Route::prefix('api')->group(function () {
    Route::get('/categories', [App\Http\Controllers\Api\CategoryController::class, 'index']);
    Route::get('/products', [App\Http\Controllers\Api\ProductController::class, 'index']);
    // Route::post('/cart', [App\Http\Controllers\Api\CartController::class, 'addToCart']);
    // Route::post('/checkout', [App\Http\Controllers\Api\CheckoutController::class, 'checkout']);
});
