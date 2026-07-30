<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => 'Admin Dummy',
                'email' => 'admin@email.com',
                'username' => 'Anwar',
                'role' => 'admin',
                'email_verified_at' => now(),
                'password' => Hash::make('12345678'), // Password di-hash sesuai Laravel
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}