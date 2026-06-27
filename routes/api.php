<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HasilKuisController;
use App\Http\Controllers\Api\KuisController;
use App\Http\Controllers\Api\NotifikasiController;
use App\Http\Controllers\Api\SoalController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Email Verification Routes
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed'])
    ->name('verification.verify');

Route::post('/email/verification-notification', [AuthController::class, 'resendVerification'])
    ->middleware('throttle:6,1')
    ->name('verification.send');

// Public Quiz Routes
Route::get('/kuis', [KuisController::class, 'index']);
Route::get('/kuis/publik', [KuisController::class, 'publicList']);
Route::get('/kuis/public', [KuisController::class, 'publicList']);
Route::get('/kuis/publik/{id}', [KuisController::class, 'publicShow']);
Route::get('/kuis/public/{id}', [KuisController::class, 'publicShow']);
Route::post('/kuis/join', [KuisController::class, 'joinByCode']);

// Submit jawaban kuis (tidak perlu login — peserta hanya isi nama)
Route::post('/kuis/{kuisId}/submit', [HasilKuisController::class, 'submit']);

// Download template Excel (tidak perlu login — kemudahan akses)
Route::get('/kuis/template-excel', [KuisController::class, 'downloadTemplate']);

// JWT-protected
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Quiz management (Hanya bisa diakses jika email sudah diverifikasi)
    Route::middleware('verified')->group(function () {
        // Quiz management — route statis HARUS sebelum route {id}
        Route::post('/kuis', [KuisController::class, 'store']);
    Route::get('/kuis/summary', [KuisController::class, 'summary']);
    Route::post('/kuis/import-excel', [KuisController::class, 'importExcel']);

    Route::get('/kuis/{id}', [KuisController::class, 'show']);
    Route::put('/kuis/{id}', [KuisController::class, 'update']);
    Route::delete('/kuis/{id}', [KuisController::class, 'destroy']);
    Route::post('/kuis/{id}/publish', [KuisController::class, 'publish']);
    
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
    
    }); // End of verified middleware group

    // ── Pusher broadcasting auth (untuk private channel) ─────────────────
    // Frontend mengirim request ke sini saat subscribe ke private-guru.{id}
    Route::post('/broadcasting/auth', function (\Illuminate\Http\Request $request) {
        return Broadcast::auth($request);
    });
});
