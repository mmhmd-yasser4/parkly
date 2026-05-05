<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SocialAuthController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\ParkingLocationController;
use App\Http\Controllers\Api\ParkingSpotController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register',    [AuthController::class, 'register']);
Route::post('/login',       [AuthController::class, 'login']);
Route::post('/auth/google', [SocialAuthController::class, 'handleGoogle']);

// PayMob webhook — no Sanctum auth
Route::post('/webhook/paymob', [PaymentController::class, 'webhook']);

// Public — browsing locations and spots
Route::get('/locations',            [ParkingLocationController::class, 'index']);
Route::get('/locations/{id}',       [ParkingLocationController::class, 'show']);
Route::get('/locations/{id}/spots', [ParkingSpotController::class, 'index']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // Vehicles
    Route::get('/vehicles',               [VehicleController::class, 'index']);
    Route::post('/vehicles',              [VehicleController::class, 'store']);
    Route::post('/vehicles/{id}/default', [VehicleController::class, 'setDefault']);
    Route::delete('/vehicles/{id}',       [VehicleController::class, 'destroy']);

    // Payment
    Route::post('/payment/initiate-card-save', [PaymentController::class, 'initiateCardSave']);

    // Payment methods
    Route::get('/payment-methods',               [PaymentMethodController::class, 'index']);
    Route::post('/payment-methods/{id}/default', [PaymentMethodController::class, 'setDefault']);
    Route::delete('/payment-methods/{id}',       [PaymentMethodController::class, 'destroy']);

    // Reservations
    Route::get('/reservations',                    [ReservationController::class, 'index']);
    Route::post('/reservations',                   [ReservationController::class, 'store']);
    Route::get('/reservations/{id}',               [ReservationController::class, 'show']);
    Route::post('/reservations/{id}/checkin',      [ReservationController::class, 'checkIn']);
    Route::post('/reservations/{id}/extend',       [ReservationController::class, 'extend']);
    Route::post('/reservations/{id}/end',          [ReservationController::class, 'end']);
    Route::post('/reservations/{id}/cancel',       [ReservationController::class, 'cancel']);

    // Notifications
    Route::get('/notifications',              [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read',   [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all',    [NotificationController::class, 'markAllRead']);
});