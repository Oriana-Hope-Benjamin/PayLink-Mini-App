<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TestController;
use PHPUnit\Metadata\Test;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/payments/initiate', [PaymentController::class, 'store']);
Route::post('/webhooks/momo', [PaymentController::class, 'webhook']);

//Test Routes
Route::post('/test/momo-token', [TestController::class, 'getAccessToken']);
Route::post('/test/momo-initiate', [TestController::class, 'requestPayment']);
Route::get('/test/momo-status/{payment_id}', [TestController::class, 'verifyPaymentStatus']);