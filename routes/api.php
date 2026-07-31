<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ScanController;
use App\Http\Controllers\Api\CowController;

// Route Publik (Tidak butuh token)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Route Terlindungi (Butuh token di header Authorization)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Rute untuk Profil
    Route::get('/user', [AuthController::class, 'profile']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    
    // Route untuk CRUD Sapi
    Route::apiResource('cows', CowController::class);
    Route::post('/cows/{id}/vaccines', [CowController::class, 'addVaccine']);
    Route::delete('/vaccines/{id}', [CowController::class, 'removeVaccine']);
    
    // Rute untuk melakukan scan NLP
    Route::post('/scans/analyze', [ScanController::class, 'analyze']);
    
    // Rute untuk melihat riwayat scan
    Route::get('/scans/history', [ScanController::class, 'history']);
    Route::delete('/scans/{id}', [ScanController::class, 'destroy']);
});
