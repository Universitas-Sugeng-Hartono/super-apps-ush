<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/skpi/payment-completed', [\App\Http\Controllers\Api\GraduationController::class, 'getCompletedPayments'])->middleware('api-key');
