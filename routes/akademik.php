<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController\DashboardController;

/*
|--------------------------------------------------------------------------
| Akademik Routes
|--------------------------------------------------------------------------
|
| Rute terdedikasi untuk Portal Biro Akademik.
|
*/

Route::middleware(['auth', 'role:masteradmin,superadmin,akademik'])->prefix('akademik')->name('akademik.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'akademikDashboard'])->name('dashboard');
});
