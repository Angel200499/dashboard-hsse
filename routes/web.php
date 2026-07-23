<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\SipekaImportController;
use App\Http\Controllers\SipekaFindingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardFunctionController;

// -----------------------------------------------------------------
// Guest Routes — Auth
// -----------------------------------------------------------------
Route::view('/login', 'auth.login')->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// -----------------------------------------------------------------
// Authenticated Routes
// -----------------------------------------------------------------
Route::middleware('auth')->group(function () {

    // Dashboard Global — hanya HSSE roles (Function roles di-redirect di controller)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Dashboard Function — semua role bisa akses (otorisasi di controller)
    Route::get('/dashboard/fungsi/{nama_fungsi?}', [DashboardFunctionController::class, 'index'])
         ->name('dashboard.fungsi');

    // -----------------------------------------------------------------
    // Findings — semua authenticated user bisa lihat & filter
    // Otorisasi Role + Fungsi diterapkan di controller
    // PENTING: export routes harus SEBELUM {id} agar tidak di-treat sebagai ID
    // -----------------------------------------------------------------
    Route::get('/findings', [SipekaFindingController::class, 'index'])
         ->name('findings.index');

    // Export routes ditempatkan SEBELUM {id} wildcard (fix route conflict)
    Route::get('/findings/export', [SipekaFindingController::class, 'export'])
         ->name('findings.export');

    Route::get('/findings/export-pdf', [SipekaFindingController::class, 'exportPdf'])
         ->name('findings.export.pdf');

    Route::get('/findings/{id}', [SipekaFindingController::class, 'show'])
         ->name('findings.show');

    Route::post('/findings/{id}/update', [SipekaFindingController::class, 'update'])
         ->name('findings.update');

    // -----------------------------------------------------------------
    // Admin HSSE Only — Import & User Management
    // -----------------------------------------------------------------
    Route::middleware('role:Admin HSSE')->group(function () {
        Route::post('/sipeka/upload', [SipekaImportController::class, 'upload'])
             ->name('sipeka.upload');

        Route::resource('users', UserController::class)->except(['show']);
    });
});