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
        Route::get('/preview-siakad', 'previewSiakadData')->name('preview-siakad');
        Route::post('/sync-siakad', 'syncFromSiakad')->name('sync-siakad');
        Route::post('/refresh-siakad', 'refreshAcademicData')->name('refresh-siakad');
        Route::get('/{id}', 'show')->name('show');
    });

    // Monitoring Beasiswa & Student Ambassador
    Route::prefix('monitoring-beasiswa')->name('monitoring-beasiswa.')->controller(AkademikMonitoringController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/export/csv', 'exportCsv')->name('export');
        Route::get('/{id}/evaluation-data', 'getStudentEvaluationData')->name('evaluation-data');
        Route::get('/{id}', 'show')->name('show');
    });

});

