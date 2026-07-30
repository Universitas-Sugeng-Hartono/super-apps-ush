<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();

            /**
             * 🔤 Kode Mata Kuliah (dipisah)
             */
            $table->string('code_prefix', 5);  // FK / AD / AB
            $table->string('code_number', 10); // 1101, 2202, dst

            /**
             * 🔤 Kolom Teks
             */
            $table->string('name');                   // nama matkul
            $table->string('type')->nullable();       // wajib/pilihan
            $table->string('program_study')->nullable(); // jurusan/prodi
            $table->string('lecturer')->nullable();   // dosen pengampu
            $table->string('room')->nullable();       // ruang kuliah
            $table->string('day')->nullable();        // hari kuliah
            $table->text('description')->nullable();  // deskripsi
            $table->text('note')->nullable();         // catatan tambahan

            /**
             * 🔢 Kolom Numerik
             */
            $table->unsignedTinyInteger('semester');  // semester
            $table->unsignedTinyInteger('sks');       // SKS

            /**
             * ⏰ Kolom Waktu
             */
            $table->time('start_time')->nullable();   // jam mulai
            $table->time('end_time')->nullable();     // jam selesai

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};