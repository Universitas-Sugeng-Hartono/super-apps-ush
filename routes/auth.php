<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::controller(AuthController::class)
   
    ->name('auth.')
    ->group(function () {
        // Login utama
        Route::get('/login', 'index')->name('login');
        Route::post('/login', 'login')->name('login.post');  

        // Login khusus
        Route::post('/login/dosen', 'loginDosen')->name('login.dosen');
        Route::post('/login/mahasiswa', 'loginMahasiswa')->name('login.mahasiswa');

        // Logout
        Route::get('/logout', 'logout')->name('logout');  
    });
