<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('skpi_registrations', function (Blueprint $table) {
            // Hapus kolom nomor_skpi yang sudah tidak diperlukan
            $table->dropColumn('nomor_skpi');

            // Tambah kolom penyimpanan file SKPI terenkripsi (AES-256-CBC, base64)
            // Menggunakan longText karena file .docx bisa berukuran besar
            $table->longText('skpi_document')->nullable()->after('submitted_at')
                  ->comment('Isi file SKPI .docx yang terenkripsi (AES-256-CBC, base64)');

            $table->timestamp('skpi_generated_at')->nullable()->after('skpi_document')
                  ->comment('Waktu terakhir file SKPI di-generate oleh admin');
        });
    }

    public function down(): void
    {
        Schema::table('skpi_registrations', function (Blueprint $table) {
            $table->dropColumn(['skpi_document', 'skpi_generated_at']);
            $table->string('nomor_skpi')->nullable()->after('student_id');
        });
    }
};
