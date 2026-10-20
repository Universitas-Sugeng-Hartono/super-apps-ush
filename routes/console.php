<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Automated SIAKAD Academic Sync Scheduler
|--------------------------------------------------------------------------
| Menjalankan sinkronisasi semester, nilai IPK, dan mahasiswa baru dari SIAKAD
| secara otomatis setiap hari pukul 02:00 dini hari (WIB) di jam sepi server.
*/
Schedule::command('siakad:sync-academic')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->runInBackground();

