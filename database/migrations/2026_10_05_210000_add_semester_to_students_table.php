<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('students', 'semester')) {
            Schema::table('students', function (Blueprint $table) {
                $table->unsignedTinyInteger('semester')->nullable()->after('sks');
            });
        }

        // Backfill semester mahasiswa dari riwayat card_counselings terbaru
        try {
            DB::statement("
                UPDATE students s
                INNER JOIN (
                    SELECT id_student, MAX(semester) as max_sem
                    FROM card_counselings
                    WHERE semester IS NOT NULL AND semester > 0
                    GROUP BY id_student
                ) c ON s.id = c.id_student
                SET s.semester = c.max_sem
            ");
        } catch (\Throwable $e) {
            // Abaikan jika tabel kosong
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('students', 'semester')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn('semester');
            });
        }
    }
};
