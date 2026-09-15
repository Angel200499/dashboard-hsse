<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\SipekaImportController;
use App\Http\Controllers\SipekaFindingController;
use App\Http\Controllers\GmFindingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardFunctionController;
use App\Http\Controllers\MasterManpowerController;
use App\Http\Controllers\MasterFunctionMappingController;

// -----------------------------------------------------------------
// Guest Routes — Public & Auth
// -----------------------------------------------------------------
Route::view('/', 'welcome')->name('home');

Route::view('/login', 'auth.login')->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// -----------------------------------------------------------------
// Authenticated Routes
// -----------------------------------------------------------------
Route::middleware('auth')->group(function () {

    // Dashboard Global — hanya HSSE roles (Function roles di-redirect di controller)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Export PDF Rekap Pelapor Business Support
    // Route ini HARUS didefinisikan SEBELUM route fungsi wildcard agar tidak tertangkap sebagai {nama_fungsi}
    Route::get('/dashboard/fungsi/business-support/rekap-pelapor/pdf', [DashboardFunctionController::class, 'exportBusinessSupportReporterPdf'])
         ->name('dashboard.business-support.rekap-pelapor.pdf');

    // Halaman Rekap Pelapor Business Support — halaman tersendiri
    // Accessible: Admin HSSE, Manager HSSE, Admin Function BS, Manager Function BS
    // Otorisasi detail ditangani di controller
    Route::get('/dashboard/fungsi/business-support/rekap-pelapor', [DashboardFunctionController::class, 'rekapPelapor'])
         ->name('dashboard.business-support.rekap-pelapor');

    // Dashboard Function — semua role bisa akses (otorisasi di controller)
    Route::get('/dashboard/fungsi/{nama_fungsi?}', [DashboardFunctionController::class, 'index'])
         ->name('dashboard.fungsi');

    // Temuan GM — khusus Superadmin
    Route::get('/dashboard/temuan-gm', [GmFindingController::class, 'index'])
         ->name('dashboard.temuan-gm');



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

        // -----------------------------------------------------------------
        // Master Data — hanya Admin HSSE
        // -----------------------------------------------------------------
        Route::get('/master/manpower', [MasterManpowerController::class, 'index'])
             ->name('master.manpower.index');
        Route::post('/master/manpower', [MasterManpowerController::class, 'store'])
             ->name('master.manpower.store');
        Route::put('/master/manpower/{manpower}', [MasterManpowerController::class, 'update'])
             ->name('master.manpower.update');
        Route::delete('/master/manpower/{manpower}', [MasterManpowerController::class, 'destroy'])
             ->name('master.manpower.destroy');

        // -----------------------------------------------------------------
        // Master Mapping Fungsi — hanya Admin HSSE
        // -----------------------------------------------------------------
        Route::get('/master/function-mapping', [MasterFunctionMappingController::class, 'index'])
             ->name('master.function-mapping.index');
        Route::post('/master/function-mapping', [MasterFunctionMappingController::class, 'store'])
             ->name('master.function-mapping.store');
        Route::put('/master/function-mapping/{mapping}', [MasterFunctionMappingController::class, 'update'])
             ->name('master.function-mapping.update');
        Route::delete('/master/function-mapping/{mapping}', [MasterFunctionMappingController::class, 'destroy'])
             ->name('master.function-mapping.destroy');
    });
});