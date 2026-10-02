<?php

require __DIR__ . '/../../../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../../../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\StudyProgram;
use Illuminate\Support\Facades\Hash;

$defaultProdi = StudyProgram::where('is_active', true)->orderBy('order')->value('name') ?? 'Bisnis Digital';

// 1. Akun Test Kemahasiswaan
$k = User::updateOrCreate(
    ['email' => 'kemahasiswaan@ush.ac.id'],
    [
        'name' => 'Staf Kemahasiswaan',
        'username' => 'kemahasiswaan',
        'role' => 'kemahasiswaan',
        'program_studi' => null,
        'password' => Hash::make('12345678'),
        'email_verified_at' => now(),
    ]
);

// 2. Akun Test Keuangan
$keu = User::updateOrCreate(
    ['email' => 'keuangan@ush.ac.id'],
    [
        'name' => 'Staf Keuangan',
        'username' => 'keuangan',
        'role' => 'keuangan',
        'program_studi' => null,
        'password' => Hash::make('12345678'),
        'email_verified_at' => now(),
    ]
);

echo "BERHASIL:\n";
echo "- User Kemahasiswaan: ID {$k->id} | Email: {$k->email} | Role: {$k->role}\n";
echo "- User Keuangan: ID {$keu->id} | Email: {$keu->email} | Role: {$keu->role}\n";
echo "Password default kedua akun: 12345678\n";



\App\Models\MenuItem::updateOrCreate(
    ['route_name' => 'admin.skpi.index'],
    [
        'name' => 'Kelulusan & SKPI',
        'icon' => 'bi bi-award-fill',
        'roles' => 'masteradmin,kemahasiswaan,keuangan',
        'order' => 25,
        'description' => 'Verifikasi prestasi, pembayaran wisuda, dan kelulusan',
        'badge_text' => 'Aktif',
        'badge_color' => 'active',
        'is_active' => true,
        'target' => '_self',
    ]
);
echo "- Menu 'Kelulusan & SKPI' diupdate dengan role: masteradmin,kemahasiswaan,keuangan\n";

