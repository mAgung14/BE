<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KuisController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/kuis', [KuisController::class, 'store']);
    Route::get('/kuis/summary', [KuisController::class, 'summary']);
});
