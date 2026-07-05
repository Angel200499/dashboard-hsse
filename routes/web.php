<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect('/login');
});

Route::view('/login', 'auth.login')->name('login');


/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

Route::view('/dashboard', 'dashboard.index')->name('dashboard');

Route::view('/dashboard/operation', 'dashboard.operation');

Route::view('/dashboard/maintenance', 'dashboard.maintenance');

Route::view('/dashboard/hsse', 'dashboard.hsse');

Route::view('/dashboard/business-support', 'dashboard.business-support');


/*
|--------------------------------------------------------------------------
| Monitoring
|--------------------------------------------------------------------------
*/

Route::view('/monitoring', 'monitoring.index');

Route::view('/monitoring/detail', 'monitoring.detail');


/*
|--------------------------------------------------------------------------
| User Management
|--------------------------------------------------------------------------
*/

Route::view('/users', 'management.users');


/*
|--------------------------------------------------------------------------
| Import Excel
|--------------------------------------------------------------------------
*/

Route::view('/import', 'import.index');


/*
|--------------------------------------------------------------------------
| Report
|--------------------------------------------------------------------------
*/

Route::view('/report', 'report.index');