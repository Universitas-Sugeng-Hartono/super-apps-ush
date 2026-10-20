<?php

use App\Models\Student;
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
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'periode_masuk')) {
                $table->string('periode_masuk', 50)->nullable()->after('angkatan')->comment('Format semester akademik masuk, misal: 2026/2027 Ganjil');
            }
        });

        // Backfill data periode_masuk untuk mahasiswa yang sudah ada di database
        foreach (Student::cursor() as $student) {
            $year = (int) ($student->angkatan ?: ($student->tanggal_masuk ? $student->tanggal_masuk->year : 2026));
            $academicYear = $year . '/' . ($year + 1);
            $month = $student->tanggal_masuk ? (int) $student->tanggal_masuk->month : 9;
            $term = ($month >= 1 && $month <= 6) ? 'Genap' : 'Ganjil';

            $student->update([
                'periode_masuk' => "{$academicYear} {$term}",
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'periode_masuk')) {
                $table->dropColumn('periode_masuk');
            }
        });
    }
};
