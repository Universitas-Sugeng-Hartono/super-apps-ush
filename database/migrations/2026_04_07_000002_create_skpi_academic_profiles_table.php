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
        Schema::create('skpi_academic_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_program_id')->constrained('study_programs')->cascadeOnDelete()->unique();
            $table->string('sk_pendirian_perguruan_tinggi')->nullable();
            $table->string('nama_perguruan_tinggi')->nullable();
            $table->string('akreditasi_perguruan_tinggi')->nullable();
            $table->string('akreditasi_program_studi')->nullable();
            $table->string('jenis_dan_jenjang_pendidikan')->nullable();
            $table->string('jenjang_kualifikasi_kkni')->nullable();
            $table->text('persyaratan_penerimaan')->nullable();
            $table->string('bahasa_pengantar_kuliah')->nullable();
            $table->string('nomor_akreditasi_perguruan_tinggi')->nullable();
            $table->text('sistem_penilaian')->nullable();
            $table->string('lama_studi')->nullable();
            $table->string('nomor_akreditasi_program_studi')->nullable();
            $table->string('status_profesi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skpi_academic_profiles');
    }
};
