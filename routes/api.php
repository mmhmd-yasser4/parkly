<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SocialAuthController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\ParkingLocationController;
use App\Http\Controllers\Api\ParkingSpotController;
use Illuminate\Support\Facades\Route;


Route::post('/register',    [AuthController::class, 'register']);
Route::post('/login',       [AuthController::class, 'login']);
Route::post('/auth/google', [SocialAuthController::class, 'handleGoogle']);


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
});