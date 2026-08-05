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
            ->where('payment_status', 'approved');

        if ($request->has('has_document')) {
            if ($request->has_document === 'true' || $request->has_document == '1') {
                $query->whereNotNull('skpi_document');
            } elseif ($request->has_document === 'false' || $request->has_document == '0') {
                $query->whereNull('skpi_document');
            }
        }

        if ($request->has('angkatan')) {
            $query->where('angkatan', $request->angkatan);
        }

        if ($request->has('periode_lulus')) {
            $query->where('periode_lulus', $request->periode_lulus);
        }

        if ($request->has('nim')) {
            $query->where('nim', $request->nim);
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
                'nomor_ijazah' => $reg->nomor_ijazah,
                'judul_skripsi_indo' => $reg->judul_ta_indo,
                'judul_skripsi_inggris' => $reg->judul_ta_inggris,
                'tanggal_lulus' => $student && $student->tanggal_lulus ? $student->tanggal_lulus->format('Y-m-d') : null,
                'foto_mahasiswa' => $student ? $student->foto : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Data mahasiswa yang sudah selesai pembayaran wisuda dan SKPI disetujui berhasil diambil',
            'data' => $data
        ]);
    }

    public function checkStudentSkpi($nim)
    {
        $registration = SkpiRegistration::where('nim', $nim)
            ->whereNotNull('skpi_document')
            ->first();

        if ($registration) {
            // Generate a signed URL valid for 24 hours
            $pdfUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'api.skpi.download.signed',
                now()->addHours(24),
                ['nim' => $nim]
            );

            return response()->json([
                'status' => 'success',
                'data' => [
                    'exists' => true,
                    'pdf_url' => $pdfUrl,
                ]
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'SKPI not found'], 404);
    }

    public function downloadSkpiDocument($nim)
    {
        $registration = SkpiRegistration::where('nim', $nim)
            ->whereNotNull('skpi_document') // Ensure it is already generated
            ->first();

        if (!$registration) {
            return response()->json(['message' => 'Dokumen SKPI belum digenerate atau tidak ditemukan'], 404);
        }

        try {
            // Decrypt from database
            $decrypted = \App\Services\SkpiDocumentEncryption::decrypt($registration->skpi_document);
            
            $safeNim = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $registration->nim);
            $fileName = 'SKPI_' . $safeNim . '.docx';

            // Return file stream as docx download
            return response($decrypted, 200, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Content-Length'      => strlen($decrypted),
            ]);

        } catch (\Throwable $e) {
            \Log::error('Gagal dekripsi SKPI NIM=' . $registration->nim . ': ' . $e->getMessage());
            return response()->json(['message' => 'Gagal membaca dokumen SKPI'], 500);
        }
    }
}
