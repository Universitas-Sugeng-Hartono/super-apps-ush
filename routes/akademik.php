<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController\DashboardController;
use App\Http\Controllers\AdminController\AkademikMonitoringController;
use App\Http\Controllers\AdminController\AkademikStudentDirectoryController;

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

    // Direktori Mahasiswa & Monitoring Akademik
    Route::prefix('direktori-mahasiswa')->name('direktori-mahasiswa.')->controller(AkademikStudentDirectoryController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/export/csv', 'exportCsv')->name('export');
        Route::get('/{id}', 'show')->name('show');
    });

    // Monitoring Beasiswa & Student Ambassador
    Route::prefix('monitoring-beasiswa')->name('monitoring-beasiswa.')->controller(AkademikMonitoringController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/export/csv', 'exportCsv')->name('export');
        Route::get('/{id}/evaluation-data', 'getStudentEvaluationData')->name('evaluation-data');
        Route::get('/{id}', 'show')->name('show');
    });

    // Periode Tahun Akademik & Semester (SIAKAD REST API)
    Route::prefix('tahun-akademik')->name('tahun-akademik.')->controller(\App\Http\Controllers\AdminController\AkademikAcademicPeriodController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/sync', 'sync')->name('sync');
    });
});

