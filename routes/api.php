<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KuisController;
use App\Http\Controllers\Api\SoalController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Quiz management
    Route::get('/kuis', [KuisController::class, 'index']);
    Route::post('/kuis', [KuisController::class, 'store']);
    Route::get('/kuis/{id}', [KuisController::class, 'show']);
    Route::put('/kuis/{id}', [KuisController::class, 'update']);
    Route::delete('/kuis/{id}', [KuisController::class, 'destroy']);
    Route::post('/kuis/{id}/publish', [KuisController::class, 'publish']);
    Route::post('/kuis/import-excel', [KuisController::class, 'importExcel']);
    Route::get('/kuis/summary', [KuisController::class, 'summary']);
    
    // Questions management
    Route::get('/kuis/{kuisId}/soal', [SoalController::class, 'index']);
    Route::post('/soal', [SoalController::class, 'store']);
    Route::get('/soal/{id}', [SoalController::class, 'show']);
    Route::put('/soal/{id}', [SoalController::class, 'update']);
    Route::delete('/soal/{id}', [SoalController::class, 'destroy']);
    Route::post('/kuis/{kuisId}/soal/reorder', [SoalController::class, 'reorder']);
});

// JWT-protected routes (allow clients using JWT tokens)
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Quiz management
    Route::get('/kuis', [KuisController::class, 'index']);
    Route::post('/kuis', [KuisController::class, 'store']);
    Route::get('/kuis/{id}', [KuisController::class, 'show']);
    Route::put('/kuis/{id}', [KuisController::class, 'update']);
    Route::delete('/kuis/{id}', [KuisController::class, 'destroy']);
    Route::post('/kuis/{id}/publish', [KuisController::class, 'publish']);
    Route::post('/kuis/import-excel', [KuisController::class, 'importExcel']);
    Route::get('/kuis/summary', [KuisController::class, 'summary']);
    
    // Questions management
    Route::get('/kuis/{kuisId}/soal', [SoalController::class, 'index']);
    Route::post('/soal', [SoalController::class, 'store']);
    Route::get('/soal/{id}', [SoalController::class, 'show']);
    Route::put('/soal/{id}', [SoalController::class, 'update']);
    Route::delete('/soal/{id}', [SoalController::class, 'destroy']);
    Route::post('/kuis/{kuisId}/soal/reorder', [SoalController::class, 'reorder']);
});
