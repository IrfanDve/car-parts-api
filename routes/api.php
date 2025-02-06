<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CarPartController;
use App\Http\Controllers\PaymentController;

Route::middleware('auth:sanctum')->group(function () {
    // Car Parts
    Route::apiResource('car-parts', CarPartController::class);

    // Orders
    Route::apiResource('orders', OrderController::class);
    Route::post('orders/{order}/update-status', [OrderController::class, 'updateStatus']);

    // Payments
    Route::post('orders/{order}/create-payment-link', [PaymentController::class, 'createPaymentLink']);
    Route::post('orders/{order}/validate-payment', [PaymentController::class, 'validatePayment']);
    Route::post('stripe/webhook', [StripeWebhookController::class, 'handleWebhook']);
});

// Authentication
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// CSV Export Route
Route::get('car-parts/export', [CarPartController::class, 'export']);