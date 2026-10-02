<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\StudyProgram;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleTestAccountSeeder extends Seeder
{
    public function run(): void
    {
        $defaultProdi = StudyProgram::where('is_active', true)->orderBy('order')->value('name') ?? 'Bisnis Digital';

        // 1. Akun Test Kemahasiswaan
        User::updateOrCreate(
            ['email' => 'kemahasiswaan@ush.ac.id'],
            [
                'name' => 'Staf Kemahasiswaan',
                'username' => 'kemahasiswaan',
                'role' => 'kemahasiswaan',
                'program_studi' => $defaultProdi,
                'password' => Hash::make('12345678'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Akun Test Keuangan
        User::updateOrCreate(
            ['email' => 'keuangan@ush.ac.id'],
            [
                'name' => 'Staf Keuangan',
                'username' => 'keuangan',
                'role' => 'keuangan',
                'program_studi' => $defaultProdi,
                'password' => Hash::make('12345678'),
                'email_verified_at' => now(),
            ]
        );
    }
}
