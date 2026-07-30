<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class MasterAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cek apakah sudah ada masteradmin
        $existingMasterAdmin = User::where('role', 'masteradmin')->first();
        
        if ($existingMasterAdmin) {
            $this->command->info('Masteradmin sudah ada dengan email: ' . $existingMasterAdmin->email);
            return;
        }

        // Buat user masteradmin
        User::create([
            'name' => 'Master Admin',
            'email' => 'masteradmin@mail.com',
            'username' => 'masteradmin',
            'role' => 'masteradmin',
            'program_studi' => 'Bisnis Digital',
            'email_verified_at' => now(),
            'password' => Hash::make('12345678'), // Password default
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('Masteradmin berhasil dibuat!');
        $this->command->info('Email: masteradmin@mail.com');
        $this->command->info('Password: 12345678');
    }
}

