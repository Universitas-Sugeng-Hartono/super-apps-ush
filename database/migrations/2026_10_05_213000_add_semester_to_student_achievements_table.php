<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('student_achievements', 'semester')) {
            Schema::table('student_achievements', function (Blueprint $table) {
                $table->unsignedTinyInteger('semester')->nullable()->after('category');
            });
        }

        // Backfill semester untuk data prestasi yang sudah ada
        try {
            $achievements = DB::table('student_achievements')
                ->join('students', 'student_achievements.student_id', '=', 'students.id')
                ->select(
                    'student_achievements.id as achievement_id',
                    'student_achievements.created_at as ach_created_at',
                    'students.tanggal_masuk',
                    'students.angkatan',
                    'students.semester as student_semester'
                )
                ->get();

            foreach ($achievements as $ach) {
                $entryDate = null;
                if (!empty($ach->tanggal_masuk)) {
                    $entryDate = Carbon::parse($ach->tanggal_masuk);
                } elseif (!empty($ach->angkatan) && (int)$ach->angkatan >= 2000) {
                    $entryDate = Carbon::create((int)$ach->angkatan, 9, 1);
                } else {
                    $entryDate = Carbon::create(2024, 9, 1);
                }

                $achDate = !empty($ach->ach_created_at) ? Carbon::parse($ach_created_at ?? $ach->ach_created_at) : now();

                // Hitung selisih bulan
                $monthDiff = ($achDate->year - $entryDate->year) * 12 + ($achDate->month - $entryDate->month);
                $calculatedSem = max(1, (int) floor($monthDiff / 6) + 1);

                // Jika mahasiswa memiliki semester aktif, batasi semester tidak melebihi semester saat ini
                if (!empty($ach->student_semester) && $calculatedSem > (int)$ach->student_semester) {
                    $calculatedSem = (int) $ach->student_semester;
                }

                DB::table('student_achievements')
                    ->where('id', $ach->achievement_id)
                    ->update(['semester' => $calculatedSem]);
            }
        } catch (\Throwable $e) {
            // Lanjutkan jika ada kendala
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('student_achievements', 'semester')) {
            Schema::table('student_achievements', function (Blueprint $table) {
                $table->dropColumn('semester');
            });
        }
    }
};
