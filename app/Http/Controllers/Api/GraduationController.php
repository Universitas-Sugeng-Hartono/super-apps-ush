<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SkpiRegistration;
use App\Models\StudyProgram;
use App\Models\Student;

class GraduationController extends Controller
{
    public function getCompletedPayments(Request $request)
    {
 
        $query = SkpiRegistration::with('student')
            ->where('status', 'approved');

 
        if ($request->has('angkatan')) {
            $query->where('angkatan', $request->angkatan);
        }

        if ($request->has('periode_lulus')) {
            $query->where('periode_lulus', $request->periode_lulus);
        }

        $registrations = $query->get();

        $programNames = $registrations->pluck('student.program_studi')->filter()->unique();
        $studyPrograms = StudyProgram::whereIn('name', $programNames)
            ->with('skpiAcademicProfile')
            ->get()
            ->keyBy('name');

        // Memetakan data sesuai kebutuhan
        $data = $registrations->map(function ($reg) use ($studyPrograms) {
            $student = $reg->student;
            $prodiName = $student ? $student->program_studi : null;
            $studyProgram = $prodiName ? $studyPrograms->get($prodiName) : null;
            $academicProfile = $studyProgram ? $studyProgram->skpiAcademicProfile : null;
            $nik = $student ? $student->nik : null;

            return [
                'nim' => $reg->nim,
                'nik' => $nik,
                'nama_mahasiswa' => $reg->nama_lengkap,
                'tempat_lahir' => $reg->tempat_lahir,
                'tanggal_lahir' => $reg->tanggal_lahir ? $reg->tanggal_lahir->format('Y-m-d') : null,
                'angkatan' => $reg->angkatan,
                'periode_lulus' => $reg->periode_lulus,
                'nama_fakultas' => $student ? $student->fakultas : null,
                'program_studi' => $prodiName,
                'jenis_dan_jenjang_pendidikan' => $academicProfile ? $academicProfile->jenis_dan_jenjang_pendidikan : null,
                'gelar_lulusan' => $academicProfile ? $academicProfile->gelar_lulusan : $reg->gelar,
                'tanggal_pembayaran_disetujui' => $reg->updated_at ? $reg->updated_at->format('Y-m-d H:i:s') : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Data mahasiswa yang sudah selesai pembayaran wisuda berhasil diambil',
            'data' => $data
        ]);
    }
}
