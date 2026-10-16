<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController\DashboardController;
use App\Http\Controllers\AdminController\SkpiController;

/*
|--------------------------------------------------------------------------
| Keuangan Routes
|--------------------------------------------------------------------------
|
| Rute terdedikasi untuk Portal Biro Keuangan.
|
*/

Route::middleware(['auth', 'role:masteradmin,superadmin,keuangan'])->prefix('keuangan')->name('keuangan.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'keuanganDashboard'])->name('dashboard');

    // Verifikasi Pembayaran Wisuda & Naskah Publikasi
    Route::prefix('verifikasi-pembayaran')->name('verifikasi-pembayaran.')->controller(SkpiController::class)->group(function () {
        Route::patch('/{id}/approve', 'approvePembayaran')->name('approve');
        Route::patch('/{id}/revision', 'revisionPembayaran')->name('revision');
        Route::patch('/{id}/reject', 'rejectPembayaran')->name('reject');
    });
});
