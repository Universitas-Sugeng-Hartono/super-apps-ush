<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PublicAnnouncementController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\NotificationController;

Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/announcements', [PublicAnnouncementController::class, 'index'])->name('announcements.index');
Route::get('/announcements/{id}', [PublicAnnouncementController::class, 'show'])->name('announcements.show');
Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');

Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');
Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/student.php';