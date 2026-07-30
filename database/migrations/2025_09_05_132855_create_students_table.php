<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id(); // Kolom auto-incrementing primary key
            $table->integer('id_lecturer')->unsigned(); // ID user (foreign key)
            $table->string('nama_lengkap', 200); // Nama lengkap
            $table->string('nama_orangtua', 250)->nullable(); // NIM unik (max 12 karakter)
            $table->string('password');
            $table->text('foto')->nullable(); // Foto profil (path atau URL)
            $table->text('ttd')->nullable(); // Tanda tangan digital (path atau URL)
            $table->string('nim', 12)->unique(); // NIM unik (max 12 karakter)
            $table->integer('angkatan'); // Tahun masuk (e.g., 2023)
            $table->string('program_studi', 50); // Program studi
            $table->string('fakultas', 50)->nullable(); // Fakultas
            $table->enum('jenis_kelamin', ['L', 'P']); // L (Laki-laki), P (Perempuan)
            $table->date('tanggal_lahir')->nullable(); // Tanggal lahir
            $table->text('alamat')->nullable(); // Alamat lengkap
            $table->string('no_telepon', 15)->nullable(); // Nomor telepon (dengan kode area)
            $table->string('email', 100)->nullable(); // Email (bisa unik)
            $table->enum('status_mahasiswa', ['Aktif', 'Cuti', 'Lulus', 'Mengundurkan Diri'])->default('Aktif');// prodi yang punya wewenang
            $table->date('tanggal_masuk'); // Tanggal resmi masuk kuliah
            $table->date('tanggal_lulus')->nullable(); // Bisa NULL jika belum lulus
            $table->integer('is_counseling')->default(0); // 0 = Belum, 1 = Sudah
            $table->boolean('is_edited')->default(false); // false = belum, true = sudah
            $table->date('tanggal_counseling')->nullable(); // Tanggal terakhir konseling
            $table->text('notes')->nullable(); // Catatan tambahan
            // Timestamps otomatis: created_at, updated_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};