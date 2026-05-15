<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Helpers\NotificationHelper;
use App\Models\FinalProject;
use App\Models\FinalProjectDefense;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DefenseRegistrationController extends Controller
{
    public function create()
    {
        $studentId = decrypt(session('student_id'));
        $finalProject = FinalProject::with('proposal', 'defense')->where('student_id', $studentId)->firstOrFail();

        if (!$finalProject->proposal || $finalProject->proposal->status !== 'approved') {
            return redirect()->route('student.final-project.index')
                ->with('error', 'Anda harus menyelesaikan seminar proposal terlebih dahulu.');
        }

        if ($finalProject->defense) {
            $defense = $finalProject->defense;

            if ($defense->status === 'rejected') {
                return redirect()->route('student.final-project.defense.edit', $defense->id);
            }

            // Cek dokumen needs_revision
            $hasNeedsRevision = $finalProject->documents()
                ->where('document_type', 'final')
                ->where('title', 'Draft Final TA')
                ->whereIn('review_status', ['needs_revision', 'rejected'])
                ->exists();

            if ($hasNeedsRevision) {
                return redirect()->route('student.final-project.defense.edit', $defense->id);
            }

            return redirect()->route('student.final-project.defense.show', $defense->id)
                ->with('info', 'Anda sudah pernah mendaftar sidang TA.');
        }

        return view('students.final-project.defense.create', compact('finalProject'));
    }

    public function store(Request $request)
    {
        $studentId = decrypt(session('student_id'));
        $finalProject = FinalProject::where('student_id', $studentId)->firstOrFail();

        // Prevent double submission
        if (FinalProjectDefense::where('final_project_id', $finalProject->id)->where('status', 'pending')->exists()) {
            return redirect()->route('student.final-project.index')
                ->with('info', 'Anda sudah mendaftar sidang TA. Silakan tunggu persetujuan.');
        }

        $request->validate([
            'final_draft_file'             => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'ukt_semester_8_file'          => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'bebas_perpustakaan_file'      => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'persetujuan_dospem_file'      => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'lembar_konsultasi_file'       => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'transkrip_nilai_file'         => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'turnitin_file'                => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'sertifikat_pkkmb_file'        => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'dokumen_pendukung_prodi_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'nik'               => ['required', 'digits:16'],
            'nisn'              => ['required', 'string', 'max:20'],
            'tempat_lahir'      => ['required', 'string', 'max:100'],
            'tanggal_lahir'     => ['required', 'date'],
            'nama_ibu_kandung'  => ['required', 'string', 'max:200'],
            'no_telepon'        => ['required', 'string', 'max:15'],
        ]);

        try {
            $student = Student::findOrFail($studentId);
            $student->update([
                'nik'              => $request->nik,
                'nisn'             => $request->nisn,
                'tempat_lahir'     => $request->tempat_lahir,
                'tanggal_lahir'    => $request->tanggal_lahir,
                'nama_ibu_kandung' => $request->nama_ibu_kandung,
                'no_telepon'       => $request->no_telepon,
            ]);

            $defense = FinalProjectDefense::create([
                'final_project_id' => $finalProject->id,
                'registered_at'    => now(),
                'scheduled_at'     => null,
                'status'           => 'pending',
            ]);

            $defenseDocs = [
                'final_draft_file'             => 'Draft Final TA',
                'ukt_semester_8_file'          => 'Bebas UKT Semester 8',
                'bebas_perpustakaan_file'      => 'Bebas Peminjaman Buku Perpustakaan',
                'persetujuan_dospem_file'      => 'Form Persetujuan Dosen Pembimbing',
                'lembar_konsultasi_file'       => 'Lembar Konsultasi TA',
                'transkrip_nilai_file'         => 'Transkrip Nilai Sementara',
                'turnitin_file'                => 'Hasil Turnitin',
                'sertifikat_pkkmb_file'        => 'Sertifikat PKKMB',
                'dokumen_pendukung_prodi_file' => 'Dokumen Pendukung Prodi'
            ];

            foreach ($defenseDocs as $inputName => $docTitle) {
                if ($request->hasFile($inputName)) {
                    $path = $request->file($inputName)->store("final-projects/{$studentId}/defense", 'public');
                    
                    $existingDoc = $finalProject->documents()
                        ->where('document_type', 'final')
                        ->where('title', $docTitle)
                        ->orderByDesc('version')
                        ->first();

                    if ($existingDoc) {
                        if (Storage::disk('public')->exists($existingDoc->file_path)) {
                            Storage::disk('public')->delete($existingDoc->file_path);
                        }
                        $existingDoc->update([
                            'file_path'     => $path,
                            'version'       => $existingDoc->version + 1,
                            'uploaded_at'   => now(),
                            'review_status' => 'pending',
                            'review_notes'  => null,
                            'reviewed_at'   => null,
                            'reviewer_id'   => null,
                        ]);
                    } else {
                        $finalProject->documents()->create([
                            'document_type' => 'final',
                            'title'         => $docTitle,
                            'file_path'     => $path,
                            'version'       => 1,
                            'uploaded_by'   => $studentId,
                            'uploaded_at'   => now(),
                            'review_status' => 'pending',
                        ]);
                    }
                }
            }

            $finalProject->update(['status' => 'defense']);

            $prodi     = $student?->program_studi;
            $toUserIds = NotificationHelper::kaprodiAndSuperuserUserIdsForProdi($prodi);
            if ($finalProject->supervisor_1_id) $toUserIds[] = (int) $finalProject->supervisor_1_id;
            if ($finalProject->supervisor_2_id) $toUserIds[] = (int) $finalProject->supervisor_2_id;

            $studentName = $student?->nama_lengkap ?: 'Mahasiswa';
            NotificationHelper::notifyUsers(
                $toUserIds,
                'sidang.submitted',
                'Pengajuan Sidang Baru',
                "{$studentName} mengajukan pendaftaran Sidang. Silakan lakukan review/approval.",
                route('admin.final-project.defenses.index'),
                ['final_project_id' => $finalProject->id, 'defense_id' => $defense->id, 'program_studi' => $prodi]
            );

            return redirect()->route('student.final-project.index')
                ->with('success', 'Pendaftaran sidang TA berhasil disubmit. Menunggu persetujuan.');

        } catch (\Exception $e) {
            \Log::error('Defense registration failed: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat mendaftar sidang.');
        }
    }

    public function edit($id)
    {
        $studentId = decrypt(session('student_id'));

        $defense = FinalProjectDefense::whereHas('finalProject', function ($q) use ($studentId) {
            $q->where('student_id', $studentId);
        })->with(['finalProject.documents', 'finalProject.supervisor1', 'finalProject.supervisor2'])
        ->findOrFail($id);

        $finalProject = $defense->finalProject;
        $finalProject->loadMissing('documents');

        // Cek needs_revision
        $hasNeedsRevision = $finalProject->documents()
            ->where('document_type', 'final')
            ->whereIn('review_status', ['needs_revision', 'rejected'])
            ->exists();

        // Boleh edit jika rejected ATAU dokumen needs_revision
        if ($defense->status !== 'rejected' && !$hasNeedsRevision) {
            return redirect()->route('student.final-project.defense.show', $defense->id)
                ->with('info', 'Tidak ada yang perlu diperbaiki.');
        }

        $defenseDocsKeys = [
            'ukt_semester_8_file'          => 'Bebas UKT Semester 8',
            'bebas_perpustakaan_file'      => 'Bebas Peminjaman Buku Perpustakaan',
            'persetujuan_dospem_file'      => 'Form Persetujuan Dosen Pembimbing',
            'lembar_konsultasi_file'       => 'Lembar Konsultasi TA',
            'transkrip_nilai_file'         => 'Transkrip Nilai Sementara',
            'turnitin_file'                => 'Hasil Turnitin',
            'sertifikat_pkkmb_file'        => 'Sertifikat PKKMB',
            'final_draft_file'             => 'Draft Final TA',
            'dokumen_pendukung_prodi_file' => 'Dokumen Pendukung Prodi'
        ];

        $existingDocs = [];
        foreach ($defenseDocsKeys as $input => $title) {
            $existingDocs[$input] = $finalProject->documents()
                ->where('document_type', 'final')
                ->where('title', $title)
                ->orderByDesc('version')
                ->first();
        }

        return view('students.final-project.defense.edit', compact('defense', 'finalProject', 'existingDocs', 'defenseDocsKeys', 'hasNeedsRevision'));
    }

    public function update(Request $request, $id)
    {
        $studentId = decrypt(session('student_id'));

        $defense = FinalProjectDefense::whereHas('finalProject', function ($q) use ($studentId) {
            $q->where('student_id', $studentId);
        })->with('finalProject')->findOrFail($id);

        $finalProject = $defense->finalProject;

        $hasNeedsRevision = $finalProject->documents()
            ->where('document_type', 'final')
            ->whereIn('review_status', ['needs_revision', 'rejected'])
            ->exists();

        if ($defense->status !== 'rejected' && !$hasNeedsRevision) {
            $message = $defense->status === 'pending' 
                ? 'Pendaftaran sidang Anda sedang menunggu persetujuan.' 
                : 'Pendaftaran sidang tidak bisa diedit.';
                
            return redirect()->route('student.final-project.defense.show', $defense->id)
                ->with('info', $message);
        }

        $request->validate([
            'final_draft_file'             => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'ukt_semester_8_file'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'bebas_perpustakaan_file'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'persetujuan_dospem_file'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'lembar_konsultasi_file'       => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'transkrip_nilai_file'         => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'turnitin_file'                => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'sertifikat_pkkmb_file'        => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'dokumen_pendukung_prodi_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'nik'               => ['required', 'digits:16'],
            'nisn'              => ['required', 'string', 'max:20'],
            'tempat_lahir'      => ['required', 'string', 'max:100'],
            'tanggal_lahir'     => ['required', 'date'],
            'nama_ibu_kandung'  => ['required', 'string', 'max:200'],
            'no_telepon'        => ['required', 'string', 'max:15'],
        ]);

        try {
            $student = Student::findOrFail($studentId);
            $student->update([
                'nik'              => $request->nik,
                'nisn'             => $request->nisn,
                'tempat_lahir'     => $request->tempat_lahir,
                'tanggal_lahir'    => $request->tanggal_lahir,
                'nama_ibu_kandung' => $request->nama_ibu_kandung,
                'no_telepon'       => $request->no_telepon,
            ]);

            $defenseDocs = [
                'final_draft_file'             => 'Draft Final TA',
                'ukt_semester_8_file'          => 'Bebas UKT Semester 8',
                'bebas_perpustakaan_file'      => 'Bebas Peminjaman Buku Perpustakaan',
                'persetujuan_dospem_file'      => 'Form Persetujuan Dosen Pembimbing',
                'lembar_konsultasi_file'       => 'Lembar Konsultasi TA',
                'transkrip_nilai_file'         => 'Transkrip Nilai Sementara',
                'turnitin_file'                => 'Hasil Turnitin',
                'sertifikat_pkkmb_file'        => 'Sertifikat PKKMB',
                'dokumen_pendukung_prodi_file' => 'Dokumen Pendukung Prodi'
            ];

            foreach ($defenseDocs as $inputName => $docTitle) {
                if ($request->hasFile($inputName)) {
                    $path = $request->file($inputName)
                        ->store("final-projects/{$studentId}/defense", 'public');

                    $oldDoc = $finalProject->documents()
                        ->where('document_type', 'final')
                        ->where('title', $docTitle)
                        ->orderByDesc('version')
                        ->first();

                    if ($oldDoc) {
                        if (Storage::disk('public')->exists($oldDoc->file_path)) {
                            Storage::disk('public')->delete($oldDoc->file_path);
                        }
                        $oldDoc->update([
                            'file_path'     => $path,
                            'version'       => $oldDoc->version + 1,
                            'uploaded_at'   => now(),
                            'review_status' => 'pending',
                            'review_notes'  => null,
                            'reviewed_at'   => null,
                            'reviewer_id'   => null,
                        ]);
                    } else {
                        $finalProject->documents()->create([
                            'document_type' => 'final',
                            'title'         => $docTitle,
                            'file_path'     => $path,
                            'version'       => 1,
                            'uploaded_by'   => $studentId,
                            'uploaded_at'   => now(),
                            'review_status' => 'pending',
                        ]);
                    }
                }
            }

            $defense->update([
                'status'         => 'pending',
                'approval_notes' => null,
                'approved_by'    => null,
                'approved_at'    => null,
                'registered_at'  => now(),
            ]);

            $prodi     = $student?->program_studi;
            $toUserIds = NotificationHelper::kaprodiAndSuperuserUserIdsForProdi($prodi);
            if ($finalProject->supervisor_1_id) $toUserIds[] = (int) $finalProject->supervisor_1_id;
            if ($finalProject->supervisor_2_id) $toUserIds[] = (int) $finalProject->supervisor_2_id;

            $studentName = $student?->nama_lengkap ?: 'Mahasiswa';
            NotificationHelper::notifyUsers(
                $toUserIds,
                'sidang.resubmitted',
                'Pengajuan Sidang Diajukan Ulang',
                "{$studentName} mengajukan ulang pendaftaran Sidang. Silakan review kembali.",
                route('admin.final-project.defenses.index'),
                ['final_project_id' => $finalProject->id, 'defense_id' => $defense->id, 'program_studi' => $prodi]
            );

            return redirect()->route('student.final-project.defense.show', $defense->id)
                ->with('success', 'Pendaftaran sidang berhasil diajukan ulang. Menunggu persetujuan.');

        } catch (\Exception $e) {
            \Log::error('Defense update failed: ' . $e->getMessage() . ' line ' . $e->getLine());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
        public function show($id)
        {
            $studentId = decrypt(session('student_id'));
            $defense = FinalProjectDefense::whereHas('finalProject', function ($q) use ($studentId) {
                $q->where('student_id', $studentId);
            })->with(['finalProject', 'finalProject.documents', 'approver'])->findOrFail($id);

            return view('students.final-project.defense.show', compact('defense'));
        }
    }
