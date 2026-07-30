<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'name' => 'Bimbingan PA',
                'icon' => 'bi bi-people-fill',
                'route_name' => 'admin.counseling.index',
                'roles' => 'admin,superadmin,masteradmin',
                'order' => 10,
                'description' => 'Kelola bimbingan mahasiswa bimbingan',
                'badge_text' => 'Aktif',
                'badge_color' => 'active',
            ],
            [
                'name' => 'Tugas Akhir',
                'icon' => 'bi bi-mortarboard',
                'route_name' => 'admin.final-project.index',
                'roles' => 'admin,superadmin,masteradmin',
                'order' => 20,
                'description' => 'Pengajuan judul, proposal, hingga sidang skripsi',
                'badge_text' => 'Aktif',
                'badge_color' => 'active',
            ],
            [
                'name' => 'Pengumuman',
                'icon' => 'bi bi-megaphone-fill',
                'route_name' => 'admin.announcements.index',
                'roles' => 'admin,superadmin,masteradmin',
                'order' => 30,
                'description' => 'Kelola pengumuman untuk landing & dashboard',
                'badge_text' => 'Aktif',
                'badge_color' => 'active',
            ],
            [
                'name' => 'Management Dosen',
                'icon' => 'bi bi-person-badge-fill',
                'route_name' => 'admin.management.lecturers.index',
                'roles' => 'superadmin,masteradmin',
                'order' => 40,
                'description' => 'Kelola data dosen, tambah, edit, dan hapus',
                'badge_text' => 'Aktif',
                'badge_color' => 'active',
            ],
            [
                'name' => 'Management Mahasiswa',
                'icon' => 'bi bi-people-fill',
                'route_name' => 'admin.management.students.index',
                'roles' => 'superadmin,masteradmin',
                'order' => 50,
                'description' => 'Kelola data mahasiswa, tambah, edit, dan hapus',
                'badge_text' => 'Aktif',
                'badge_color' => 'active',
            ],
        ];

        foreach ($items as $item) {
            MenuItem::updateOrCreate(
                ['route_name' => $item['route_name']],
                [
                    'name' => $item['name'],
                    'icon' => $item['icon'],
                    'url' => $item['url'] ?? null,
                    'roles' => $item['roles'],
                    'order' => $item['order'] ?? 0,
                    'is_active' => true,
                    'target' => $item['target'] ?? '_self',
                    'description' => $item['description'] ?? null,
                    'badge_text' => $item['badge_text'] ?? null,
                    'badge_color' => $item['badge_color'] ?? null,
                ]
            );
        }
    }
}


