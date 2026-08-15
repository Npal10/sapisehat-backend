<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminDashboardController;

// Redirect Halaman Utama Web ke Admin Login
Route::get('/', function () {
    return redirect()->route('admin.login');
});

// ─── RUTE WEB ADMIN ───
Route::prefix('admin')->group(function () {
    // Auth Routes Admin
    Route::get('/login', [AdminDashboardController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminDashboardController::class, 'login'])->name('admin.login.post');
    Route::post('/logout', [AdminDashboardController::class, 'logout'])->name('admin.logout');

    // Protected Routes Admin (Khusus Role Admin)
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/scans', [AdminDashboardController::class, 'scans'])->name('admin.scans');
        Route::get('/scans/{id}', [AdminDashboardController::class, 'showScan'])->name('admin.scans.show');
        Route::get('/users', [AdminDashboardController::class, 'users'])->name('admin.users');
    });
});
