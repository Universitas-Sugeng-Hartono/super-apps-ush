# Memory Proyek: Super Apps USH (Universitas Sugeng Hartono)

Dokumentasi persisten konteks sistem, struktur peran, arsitektur, dan riwayat perubahan.

---

## 1. Ikhtisar Proyek & Tech Stack
- **Framework**: Laravel 12.x (PHP 8.2+)
- **Database**: MySQL
- **Frontend / UI**: Blade templates, Bootstrap 5.3, Bootstrap Icons, SweetAlert2
- **Modul Utama**:
  - **SuperApps Dashboard**: Hub aplikasi terpusat untuk berbagai layanan civitas akademika.
  - **Bimbingan PA (Counseling)**: Manajemen perwalian dosen & mahasiswa.
  - **Tugas Akhir / Skripsi**: Pengajuan judul, bimbingan, sempro, dan sidang skripsi.
  - **Kelulusan & SKPI**: Pendaftaran SKPI, verifikasi data prestasi, verifikasi pembayaran wisuda, approval kelulusan, dan generate dokumen SKPI.
  - **Manajemen User & Menu**: CRUD dosen/user, menu dinamis berbasis role.
  - **Pengumuman & Notifikasi**: Pengumuman publik & internal, notifikasi real-time.

---

## 2. Struktur Peran & Hak Akses (Role Permissions)

| Role Key | Label UI | Deskripsi Akses Saat Ini |
|---|---|---|
| `student` | Mahasiswa | Portal mahasiswa (Bimbingan, SKPI, Tugas Akhir, Dokumen). |
| `admin` | Dosen | Portal dosen (Bimbingan PA bimbingan, review bimbingan TA dosen). |
| `superadmin` | Kaprodi | Admin prodi (Manajemen Dosen, Mahasiswa, Approval TA prodi). |
| `masteradmin` | Superuser | Akses penuh (Manajemen Menu, SKPI penuh, Pengaturan WhatsApp, dll). |
| `kemahasiswaan` | Kemahasiswaan | Dashboard SuperApps, Modul SKPI Verifikasi Data Prestasi Mahasiswa (`admin.skpi.verifikasi-data.*`). |
| `keuangan` | Keuangan | Dashboard SuperApps, Modul SKPI Verifikasi Pembayaran Wisuda (`admin.skpi.verifikasi-pembayaran.*`). |

---

## 3. Konvensi Arsitektur & Aturan Kode
- **Role Normalization**: Menggunakan method statis `User::normalizeRole($role)` dan `User::roleLabel($role)` di model `App\Models\User`.
- **Middleware Check**: `CheckRole` middleware dengan sintaks `role:admin,superadmin,masteradmin,kemahasiswaan,keuangan` yang memeriksa kecocokan role ternormalisasi.
- **Dynamic Menus**: Menggunakan tabel `menu_items` dengan kolom `roles` (koma-terpisah). Diambil lewat `MenuHelper` atau `MenuItem::forRole($role)`.
- **UI Consistency**: Form input role di halaman manajemen user menggunakan dropdown dan badge status seragam.

---

## 4. Log Aktivitas & Riwayat Perubahan

| Tanggal & Waktu | Aktivitas & Perubahan | File Terkait | Status / Hasil |
|---|---|---|---|
| 2026-10-02 15:20 | Inisialisasi `memory.md` & analisis penambahan role baru (`kemahasiswaan` dan `keuangan`). | `memory.md` | Selesai diinisialisasi. |
| 2026-10-02 16:07 | Penyesuaian Program Studi untuk Role Institusi (Kemahasiswaan, Keuangan, Masteradmin): <br>1. Membuat migrasi `make_program_studi_nullable_on_users_table` agar kolom `program_studi` di tabel `users` menjadi nullable.<br>2. Validasi bersyarat di `UserManageController`: `program_studi` wajib hanya untuk `admin` & `superadmin`, dan bersifat opsional/nullable untuk `kemahasiswaan`, `keuangan`, dan `masteradmin`.<br>3. Form `create.blade.php` & `edit.blade.php`: otomatis menghilangkan kewajiban prodi dan menampilkan keterangan ketika memilih role institusi.<br>4. Header SuperApp (`super-app.blade.php`): menampilkan role sebenarnya ("Kemahasiswaan" / "Keuangan") alih-alih "Lecturer Bisnis Digital".<br>5. Tabel Manajemen User (`index.blade.php`): menampilkan label "Semua Prodi" untuk role institusi yang tidak memilih prodi spesifik.<br>6. Menjalankan ulang smoke test: 12/12 Lulus. | `database/migrations/2026_10_02_160506_make_program_studi_nullable_on_users_table.php`<br>`app/Http/Controllers/AdminController/UserManageController.php`<br>`resources/views/admin/management/lecturers/create.blade.php`<br>`resources/views/admin/management/lecturers/edit.blade.php`<br>`resources/views/admin/management/lecturers/index.blade.php`<br>`resources/views/admin/layouts/super-app.blade.php`<br>`.agents/skills/smoke-test/scripts/run_smoke_test.php` | **100% Lulus (12/12 Smoke Tests Pass)** |
| 2026-10-04 20:58 | Integrasi Dynamic Menu Management untuk Dashboard Kemahasiswaan & Keuangan:<br>1. Mengaktifkan pembacaan dynamic `$menus` di `DashboardController@dashboard` untuk role Kemahasiswaan dan Keuangan.<br>2. Menyesuaikan `actions-grid` di `kemahasiswaan/dashboard/index.blade.php` dan `keuangan/dashboard/index.blade.php` agar merender menu dinamis dari database `menu_items` secara otomatis.<br>3. Menambahkan menu default institusi dan `Management Menu` ke `MenuItemSeeder` dan menjalankannya sehingga Superadmin dapat langsung mengelola menu Kemahasiswaan dari UI Menu Management.<br>4. Verifikasi smoke test: 14/14 Lulus. | `app/Http/Controllers/AdminController/DashboardController.php`<br>`resources/views/kemahasiswaan/dashboard/index.blade.php`<br>`resources/views/keuangan/dashboard/index.blade.php`<br>`database/seeders/MenuItemSeeder.php` | **100% Lulus (14/14 Smoke Tests Pass)** |
| 2026-10-04 21:04 | Pemisahan Route Terdedikasi untuk Kemahasiswaan (`/kemahasiswaan/*`):<br>1. Membuat file rute modular `routes/kemahasiswaan.php` dengan prefix `/kemahasiswaan` dan name prefix `kemahasiswaan.`.<br>2. Dashboard Kemahasiswaan kini memiliki URL terdedikasi: `/kemahasiswaan/dashboard` (`kemahasiswaan.dashboard`).<br>3. Akses dari login maupun akses URL `/admin/dashboard` oleh role `kemahasiswaan` otomatis di-redirect ke `/kemahasiswaan/dashboard`.<br>4. Header, sidebar desktop, dan bottom nav mobile menyesuaikan link "Home" ke `/kemahasiswaan/dashboard` jika login sebagai kemahasiswaan.<br>5. Verifikasi smoke test: 14/14 Lulus. | `routes/kemahasiswaan.php`<br>`routes/web.php`<br>`app/Http/Controllers/AdminController/DashboardController.php`<br>`app/Http/Controllers/AuthController.php`<br>`resources/views/admin/layouts/super-app.blade.php`<br>`resources/views/kemahasiswaan/dashboard/index.blade.php` | **100% Lulus (14/14 Smoke Tests Pass)** |
| 2026-10-04 21:15 | Implementasi Fitur & Menu "Student Ambassador" pada Management Menu Superuser:<br>1. Membuat migrasi `add_status_to_students_table` untuk menambahkan kolom `status` (`mahasiswa` / `studentambassador`) pada tabel `students`.<br>2. Menambahkan model scope `scopeAmbassador`, `scopeRegularStudent`, dan accessor `status_label` pada `Student.php`.<br>3. Membuat controller `StudentAmbassadorController` (CRUD lengkap: index, create, store, edit, update, show, destroy, resetPassword).<br>4. Membuat views UI/UX `admin.management.student-ambassador` (index, create, edit, show) mengadopsi tampilan Management Mahasiswa, dengan menghapus kolom Dosen PA, Counseling, Edit Profile, dan Akses SKPI, serta menambahkan field Status pilihan `Mahasiswa` atau `Student Ambassador`.<br>5. Mendaftarkan route `admin.management.student-ambassador.*` dengan izin akses role `superadmin,masteradmin,kemahasiswaan`.<br>6. Menambahkan entri menu `Student Ambassador` pada database `menu_items` (order: 55, icon: `bi bi-award-fill`).<br>7. Verifikasi smoke test: 14/14 Lulus. | `database/migrations/2026_10_04_211500_add_status_to_students_table.php`<br>`app/Models/Student.php`<br>`app/Http/Controllers/AdminController/StudentAmbassadorController.php`<br>`resources/views/admin/management/student-ambassador/*`<br>`routes/admin.php`<br>`database/seeders/MenuItemSeeder.php` | **100% Lulus (14/14 Smoke Tests Pass)** |
| 2026-10-04 21:28 | Pembersihan Dashboard Superuser & Pengaktifan Menu Dinamis Dashboard Kemahasiswaan:<br>1. Mengembalikan dashboard Superuser (`masteradmin`) ke kondisi semula: menghapus menu "Verifikasi Data Prestasi", "Alur Kelulusan & SKPI", "Kalender Akademik", dan duplikasi "Management Menu". Superuser kini bersih dengan 7 kartu original: Manajement Kelulusan, Bimbingan PA, Tugas Akhir, Pengumuman, Management Dosen, Management Mahasiswa, dan Management Menu.<br>2. Menghapus 4 kartu statis/hardcoded di bagian "Layanan & Modul Utama" pada Dashboard Kemahasiswaan (`resources/views/kemahasiswaan/dashboard/index.blade.php`). Bagian ini kini murni memuat menu dinamis hasil penambahan dari Management Menu Superuser.<br>3. Mengatur menu "Student Ambassador" agar memiliki role khusus `kemahasiswaan`, sehingga otomatis tampil di dashboard Kemahasiswaan dan tidak mengotori dashboard Superuser.<br>4. Memperbarui `MenuItemSeeder.php` dan database `menu_items` agar tetap bersih dan sinkron.<br>5. Verifikasi smoke test: 14/14 Lulus. | `database/seeders/MenuItemSeeder.php`<br>`resources/views/kemahasiswaan/dashboard/index.blade.php`<br>`memory.md` | **100% Lulus (14/14 Smoke Tests Pass)** |
| 2026-10-04 21:36 | Migrasi Rute Student Ambassador ke Kemahasiswaan (`/kemahasiswaan/student-ambassador`):<br>1. Memindahkan rute dari `admin/management/student-ambassador` ke `routes/kemahasiswaan.php` dengan URL `/kemahasiswaan/student-ambassador` (named `kemahasiswaan.student-ambassador.*`).<br>2. Memindahkan views ke `resources/views/kemahasiswaan/student-ambassador/` dan memperbarui semua action/form/link internal ke `kemahasiswaan.student-ambassador.*`.<br>3. Memperbarui `StudentAmbassadorController` untuk me-render view `kemahasiswaan.student-ambassador.*` dan redirect ke `kemahasiswaan.student-ambassador.index`.<br>4. Memperbarui database `menu_items` dan `MenuItemSeeder.php` dengan `route_name` = `kemahasiswaan.student-ambassador.index`.<br>5. Verifikasi smoke test: 14/14 Lulus. | `routes/kemahasiswaan.php`<br>`routes/admin.php`<br>`resources/views/kemahasiswaan/student-ambassador/*`<br>`app/Http/Controllers/AdminController/StudentAmbassadorController.php`<br>`database/seeders/MenuItemSeeder.php`<br>`memory.md` | **100% Lulus (14/14 Smoke Tests Pass)** |
| 2026-10-04 21:44 | Penambahan Tombol Kembali dan Fitur Import CSV pada Student Ambassador:<br>1. Menambahkan tombol "Kembali ke Dashboard" (`btn-secondary`) di card header `kemahasiswaan/student-ambassador/index.blade.php`.<br>2. Mengimplementasikan fitur Import CSV dan Download Template CSV di `StudentAmbassadorController` (`downloadImportTemplate` & `import`) dengan dukungan kolom `status` (`studentambassador` atau `mahasiswa`).<br>3. Menambahkan rute `/kemahasiswaan/student-ambassador/template` dan `/kemahasiswaan/student-ambassador/import` pada `routes/kemahasiswaan.php`.<br>4. Menambahkan card UI "Import Mahasiswa / Student Ambassador (CSV)" di `index.blade.php`.<br>5. Verifikasi smoke test: 14/14 Lulus. | `app/Http/Controllers/AdminController/StudentAmbassadorController.php`<br>`routes/kemahasiswaan.php`<br>`resources/views/kemahasiswaan/student-ambassador/index.blade.php`<br>`memory.md` | **100% Lulus (14/14 Smoke Tests Pass)** |

---

## 5. Catatan Langkah Selanjutnya (Next Steps)
1. Branch `studentAmbassador` aktif.
2. Modul **Management Student Ambassador** kini memiliki tombol navigasi kembali ke dashboard dan fitur import CSV lengkap dengan unduhan template.


