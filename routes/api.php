<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HubController;
use App\Http\Controllers\Api\MerchantController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProducerController;
use App\Http\Controllers\Api\WithdrawalController;
use Illuminate\Support\Facades\Route;

// Public endpoints — no auth required
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/refresh', [AuthController::class, 'refresh']);
Route::get('/hub/active', [HubController::class, 'active']);
Route::get('/hubs', [HubController::class, 'index']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/producers/{id}', [ProducerController::class, 'show']);

// Order submission — open for now (frontend may not send token yet)
Route::post('/orders', [OrderController::class, 'store']);

// Order-specific endpoints — public (frontend may not send token yet)
Route::get('/orders/{orderId}/invoice', [OrderController::class, 'invoice']);
Route::post('/orders/{orderId}/reorder', [OrderController::class, 'reorder']);

// Protected endpoints — require Sanctum auth
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/user/withdrawals', [WithdrawalController::class, 'userWithdrawals']);
    Route::post('/user/push-token', [NotificationController::class, 'registerPushToken']);
    Route::get('/merchant/dashboard', [MerchantController::class, 'dashboard']);
    Route::post('/merchant/verify-qr', [MerchantController::class, 'verifyQr']);
});
