<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SocialAuthController;
use Illuminate\Support\Facades\Route;

// Anyone can hit these
Route::post('/register',    [AuthController::class, 'register']);
Route::post('/login',       [AuthController::class, 'login']);
Route::post('/auth/google', [SocialAuthController::class, 'handleGoogle']);

// Must be logged in to hit these
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);
});