<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Student;
use App\Models\SkpiRegistration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Perbaiki tanggal_masuk mahasiswa di server jika year > angkatan
        $students = Student::all();
        foreach ($students as $student) {
            if ($student->tanggal_masuk && $student->angkatan && $student->tanggal_masuk->year > $student->angkatan) {
                $student->update([
                    'tanggal_masuk' => "{$student->angkatan}-09-01",
                ]);
            }
        }

        // 2. Rekalkulasi dan perbaiki seluruh lama_studi di skpi_registrations
        $regs = SkpiRegistration::with('student')->get();
        foreach ($regs as $reg) {
            $student = $reg->student;
            $tglMasuk = $student?->tanggal_masuk ? $student->tanggal_masuk->format('Y-m-d') : null;
            $angkatan = $reg->angkatan ?? $student?->angkatan;

            $calculated = SkpiRegistration::calculateLamaStudi($tglMasuk, $angkatan, $reg->periode_lulus);
            if ($calculated) {
                $reg->update(['lama_studi' => $calculated]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Perbaikan data tidak perlu di-rollback
    }
};
