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
| 2026-10-02 16:15 | Pembuatan branch git `studentAmbassador` untuk fitur penambahan role Kemahasiswaan & Keuangan, dashboard terdedikasi, serta rangkaian smoke testing. | Seluruh file terkait | **Branch created & checked out (`studentAmbassador`)** |

---

## 5. Catatan Langkah Selanjutnya (Next Steps)
1. Branch `studentAmbassador` aktif.
2. Seluruh folder, view, controller, routing, migrasi, dan database role Kemahasiswaan & Keuangan telah lengkap dan lulus verifikasi pengujian 14/14.
3. Siap digunakan dalam proses operasional kampus atau di-merge ke branch utama jika diperlukan.

