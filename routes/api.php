<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SocialAuthController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\ParkingLocationController;
use App\Http\Controllers\Api\ParkingSpotController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PaymentMethodController;


Route::post('/register',    [AuthController::class, 'register']);
Route::post('/login',       [AuthController::class, 'login']);
Route::post('/auth/google', [SocialAuthController::class, 'handleGoogle']);

Route::post('/webhook/paymob', [PaymentController::class, 'webhook']);

Route::get('/locations',                [ParkingLocationController::class, 'index']);
Route::get('/locations/{id}',           [ParkingLocationController::class, 'show']);
Route::get('/locations/{id}/spots',     [ParkingSpotController::class, 'index']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);


    Route::get('/vehicles',                [VehicleController::class, 'index']);
    Route::post('/vehicles',               [VehicleController::class, 'store']);
    Route::post('/vehicles/{id}/default',  [VehicleController::class, 'setDefault']);
    Route::delete('/vehicles/{id}',        [VehicleController::class, 'destroy']);

    Route::post('/payment/initiate-card-save',    [PaymentController::class, 'initiateCardSave']);

    Route::get('/payment-methods',                [PaymentMethodController::class, 'index']);
    Route::post('/payment-methods/{id}/default',  [PaymentMethodController::class, 'setDefault']);
    Route::delete('/payment-methods/{id}',        [PaymentMethodController::class, 'destroy']);
});
