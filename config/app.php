<?php

return [

    'name' => env('APP_NAME', 'Aplikasi Konseling Akademik'),

    'env' => env('APP_ENV', 'production'),

    'debug' => (bool) env('APP_DEBUG', false),

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Zona Waktu Indonesia
    |--------------------------------------------------------------------------
    */
    'timezone' => 'Asia/Jakarta',

    /*
    |--------------------------------------------------------------------------
    | Bahasa Default
    |--------------------------------------------------------------------------
    */
    'locale' => env('APP_LOCALE', 'id'),

    // Kalau string terjemahan bahasa Indonesia tidak ada, fallback ke Inggris
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    // Faker locale untuk generate data dummy dengan nama lokal Indonesia
    'faker_locale' => env('APP_FAKER_LOCALE', 'id_ID'),

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Nomor WhatsApp Admin
    |--------------------------------------------------------------------------
    | Format: kode negara + nomor tanpa tanda + (contoh: 6281234567890)
    | Ubah di file .env dengan key ADMIN_WA_NUMBER
    */
    'admin_wa_number' => env('ADMIN_WA_NUMBER', '6289613942890'),

];