<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HasilKuisController;
use App\Http\Controllers\Api\KuisController;
use App\Http\Controllers\Api\SoalController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public Quiz Routes
Route::get('/kuis', [KuisController::class, 'index']);
Route::get('/kuis/publik', [KuisController::class, 'publicList']);
Route::get('/kuis/public', [KuisController::class, 'publicList']);
Route::get('/kuis/publik/{id}', [KuisController::class, 'publicShow']);
Route::get('/kuis/public/{id}', [KuisController::class, 'publicShow']);
Route::post('/kuis/join', [KuisController::class, 'joinByCode']);

// Submit jawaban kuis (tidak perlu login — peserta hanya isi nama)
Route::post('/kuis/{kuisId}/submit', [HasilKuisController::class, 'submit']);

// JWT-protected
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Quiz management
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

    // Hasil kuis (hanya guru pemilik kuis)
    Route::get('/kuis/{kuisId}/hasil/export', [HasilKuisController::class, 'exportCsv']);
    Route::get('/kuis/{kuisId}/hasil/{riwayatId}', [HasilKuisController::class, 'show']);
    Route::get('/kuis/{kuisId}/hasil', [HasilKuisController::class, 'index']);
});
