<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\SipekaImportController;
use App\Http\Controllers\SipekaFindingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardFunctionController;

Route::view('/login', 'auth.login')->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/fungsi/{nama_fungsi?}', [DashboardFunctionController::class, 'index'])->name('dashboard.fungsi');
    
    Route::get('/findings', [SipekaFindingController::class, 'index'])->name('findings.index');
    Route::get('/findings/export', [SipekaFindingController::class, 'export'])->name('findings.export');
    Route::get('/findings/export-pdf', [SipekaFindingController::class, 'exportPdf'])->name('findings.export.pdf');
    Route::post('/findings/{id}/update', [SipekaFindingController::class, 'update'])->name('findings.update');
    Route::get('/findings/{id}', [SipekaFindingController::class, 'show'])->name('findings.show');
    
    // Admin HSSE only
    Route::middleware('role:Admin HSSE')->group(function () {
        Route::post('/sipeka/upload', [SipekaImportController::class, 'upload'])->name('sipeka.upload');
        Route::resource('users', UserController::class)->except(['show']);
    });
});