<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController\DashboardController;
use App\Http\Controllers\AdminController\SkpiController;
use App\Http\Controllers\AdminController\StudentAmbassadorController;
use App\Http\Controllers\AdminController\AchievementVerificationController;

/*
|--------------------------------------------------------------------------
| Kemahasiswaan Routes
|--------------------------------------------------------------------------
|
| Rute terdedikasi untuk Portal Biro Kemahasiswaan (dan masteradmin, superadmin).
|
*/

Route::middleware(['auth', 'role:masteradmin,superadmin,kemahasiswaan'])->prefix('kemahasiswaan')->name('kemahasiswaan.')->group(function () {
    // Dashboard Utama Kemahasiswaan
    Route::get('/dashboard', [DashboardController::class, 'kemahasiswaanDashboard'])->name('dashboard');

    // Management Student Ambassador
    Route::prefix('student-ambassador')->name('student-ambassador.')->controller(StudentAmbassadorController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/template', 'downloadImportTemplate')->name('template');
        Route::post('/import', 'import')->name('import');
        Route::post('/sync-siakad', 'syncFromSiakad')->name('sync-siakad');
        Route::post('/refresh-siakad', 'refreshAcademicData')->name('refresh-siakad');
        Route::get('/preview-siakad', 'previewSiakadData')->name('preview-siakad');
        Route::post('/send-all-siakad', 'sendAllRecommendations')->name('send-all-siakad');
        Route::post('/{id}/sync-siakad', 'syncSingleStudent')->name('sync-single');
        Route::post('/{id}/send-siakad', 'sendRecommendation')->name('send-single-siakad');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
    });

    // Verifikasi Data Prestasi Mahasiswa (Khusus Kemahasiswaan: IPK, Total Point, Status Mahasiswa, Beasiswa)
    Route::prefix('verifikasi-prestasi')->name('verifikasi-prestasi.')->controller(AchievementVerificationController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/{id}/approve', 'approve')->name('approve');
        Route::post('/{id}/unapprove', 'unapprove')->name('unapprove');
        Route::post('/{id}/reject', 'reject')->name('reject');
        Route::post('/{id}/update', 'update')->name('update');
        Route::post('/approve-all', 'approveAll')->name('approve-all');
        Route::post('/students/{id}/update-beasiswa', 'updateStudentBeasiswa')->name('update-beasiswa');
        Route::post('/students/{id}/send-siakad', 'sendRecommendationToSiakad')->name('send-siakad');
        Route::post('/send-all-siakad', 'sendAllRecommendationsToSiakad')->name('send-all-siakad');
        Route::post('/apply-all-recommendations', 'applyAllRecommendations')->name('apply-all-recommendations');
        Route::get('/students/{id}/evaluation-data', 'getStudentEvaluationData')->name('student-evaluation-data');
        Route::post('/toggle-upload-portal', 'toggleUploadPortal')->name('toggle-upload-portal');
        Route::get('/export-beasiswa', 'exportScholarshipReport')->name('export-beasiswa');
    });

    // Akses Verifikasi Data Prestasi SKPI (Tetap dipertahankan untuk kompatibilitas)
    Route::prefix('skpi/verifikasi-data')->name('skpi.verifikasi-data.')->controller(SkpiController::class)->group(function () {
        Route::get('/', 'verifikasiData')->name('index');
        Route::post('/{id}/approve', 'approveVerifikasiData')->name('approve');
        Route::post('/{id}/unapprove', 'unapproveVerifikasiData')->name('unapprove');
        Route::post('/{id}/reject', 'rejectVerifikasiData')->name('reject');
        Route::post('/{id}/update', 'updateVerifikasiData')->name('update');
        Route::post('/approve-all', 'approveAllVerifikasiData')->name('approve-all');
    });

    // Alur Kelulusan & SKPI
    Route::get('/skpi', [SkpiController::class, 'index'])->name('skpi.index');
});
