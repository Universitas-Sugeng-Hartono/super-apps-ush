<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Helpers\NotificationHelper;
use App\Models\FinalProject;
use App\Models\FinalProjectProposal;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProposalRegistrationController extends Controller
{
    private array $fileFields = [
        'proposal_file'                => 'Proposal Tugas Akhir',
        'eligibility_form_file'        => 'Form Penilaian Kelayakan Judul',
        'guidance_form_file'           => 'Form Bimbingan Tugas Akhir',
        'seminar_approval_form_file'   => 'Form Persetujuan Seminar Proposal',
        'seminar_attendance_form_file' => 'Form Mengikuti Seminar Proposal TA',
        'krs_file'                     => 'Kartu Rencana Studi Sem 1 - Sem Berjalan',
        'transcript_file'              => 'Transkrip Nilai',
    ];

    public function create()
    {
        $studentId = decrypt(session('student_id'));
        $finalProject = FinalProject::where('student_id', $studentId)
            ->with(['proposal.finalProject.documents'])
            ->firstOrFail();

        if ($finalProject->proposal) {
            // Jika rejected → ke edit
            if ($finalProject->proposal->status === 'rejected') {
                return redirect()->route('student.final-project.proposal.edit', $finalProject->proposal->id);
            }

            // Jika ada dokumen needs_revision → ke edit
            $hasNeedsRevision = $finalProject->documents
                ->where('document_type', 'proposal')
                ->where('review_status', 'needs_revision')
                ->count() > 0;

            if ($hasNeedsRevision) {
                return redirect()->route('student.final-project.proposal.edit', $finalProject->proposal->id);
            }

            return redirect()->route('student.final-project.proposal.show', $finalProject->proposal->id)
                ->with('info', 'Anda sudah pernah mendaftar seminar proposal.');
        }

        if (!$finalProject->title_approved_at) {
            return redirect()->route('student.final-project.index')
                ->with('error', 'Judul Tugas Akhir harus disetujui terlebih dahulu sebelum mendaftar seminar proposal.');
        }

        if (!$finalProject->supervisor_1_id) {
            return redirect()->route('student.final-project.index')
                ->with('error', 'Pembimbing 1 harus ditentukan oleh admin terlebih dahulu sebelum mendaftar seminar proposal.');
        }

        return view('students.final-project.proposal.create', compact('finalProject'));
    }

    public function store(Request $request)
    {
        $studentId = decrypt(session('student_id'));
        $finalProject = FinalProject::where('student_id', $studentId)->firstOrFail();

        $request->validate([
            'proposal_file'                => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'eligibility_form_file'        => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'guidance_form_file'           => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'seminar_approval_form_file'   => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'seminar_attendance_form_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'krs_file'                     => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'transcript_file'              => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        try {
            $proposal = FinalProjectProposal::create([
                'final_project_id' => $finalProject->id,
                'registered_at'    => now(),
                'scheduled_at'     => null,
                'status'           => 'pending',
            ]);

            foreach ($this->fileFields as $field => $title) {
                if ($request->hasFile($field)) {
                    $path = $request->file($field)->store("final-projects/{$studentId}/proposal", 'public');

                    $finalProject->documents()->create([
                        'document_type' => 'proposal',
                        'title'         => $title,
                        'file_path'     => $path,
                        'version'       => 1,
                        'uploaded_by'   => $studentId,
                        'uploaded_at'   => now(),
                        'review_status' => 'pending',
                    ]);
                }
            }

            $student     = Student::find($studentId);
            $prodi       = $student?->program_studi;
            $toUserIds   = NotificationHelper::kaprodiAndSuperuserUserIdsForProdi($prodi);
            if ($finalProject->supervisor_1_id) $toUserIds[] = (int) $finalProject->supervisor_1_id;
            if ($finalProject->supervisor_2_id) $toUserIds[] = (int) $finalProject->supervisor_2_id;

            $studentName = $student?->nama_lengkap ?: 'Mahasiswa';
            NotificationHelper::notifyUsers(
                $toUserIds,
                'sempro.submitted',
                'Pengajuan Sempro Baru',
                "{$studentName} mengajukan pendaftaran Sempro. Silakan lakukan review/approval.",
                route('admin.final-project.proposals.index'),
                ['final_project_id' => $finalProject->id, 'proposal_id' => $proposal->id, 'program_studi' => $prodi]
            );

            return redirect()->route('student.final-project.index')
                ->with('success', 'Pendaftaran seminar proposal berhasil disubmit. Menunggu persetujuan.');

        } catch (\Exception $e) {
            \Log::error('Proposal registration failed: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat mendaftar seminar proposal.');
        }
    }

    public function edit($id)
    {
        $studentId = decrypt(session('student_id'));

        $proposal = FinalProjectProposal::whereHas('finalProject', function ($q) use ($studentId) {
            $q->where('student_id', $studentId);
        })->with([
            'finalProject.documents',
            'finalProject.supervisor1',
            'finalProject.supervisor2',
        ])->findOrFail($id);

        $finalProject = $proposal->finalProject;
        $finalProject->loadMissing('documents');

        $hasNeedsRevision = $finalProject->documents
            ->where('document_type', 'proposal')
            ->where('review_status', 'needs_revision')
            ->count() > 0;

        // Boleh edit jika rejected ATAU ada dokumen needs_revision
        if ($proposal->status !== 'rejected' && !$hasNeedsRevision) {
            return redirect()->route('student.final-project.proposal.show', $proposal->id)
                ->with('info', 'Tidak ada dokumen yang perlu direvisi.');
        }

        $existingDocs = collect($this->fileFields)->mapWithKeys(function ($title, $field) use ($finalProject) {
            $doc = $finalProject->documents
                ->where('document_type', 'proposal')
                ->where('title', $title)
                ->sortByDesc('version')
                ->first();

            return [$field => $doc];
        });

        return view('students.final-project.proposal.edit', compact('proposal', 'finalProject', 'existingDocs'));
    }

public function update(Request $request, $id)
{
    $studentId = decrypt(session('student_id'));

    $proposal = FinalProjectProposal::whereHas('finalProject', function ($q) use ($studentId) {
        $q->where('student_id', $studentId);
    })->with('finalProject')->findOrFail($id);

    $finalProject = $proposal->finalProject;

    $hasNeedsRevision = $finalProject->documents()
        ->where('document_type', 'proposal')
        ->where('review_status', 'needs_revision')
        ->exists();

    if ($proposal->status !== 'rejected' && !$hasNeedsRevision) {
        return redirect()->route('student.final-project.proposal.show', $proposal->id)
            ->with('error', 'Proposal tidak bisa diedit.');
    }

    // Cek apakah semua dokumen sudah approved
    $allDocsApproved = true;
    foreach ($this->fileFields as $field => $title) {
        $doc = $finalProject->documents()
            ->where('document_type', 'proposal')
            ->where('title', $title)
            ->orderByDesc('version')
            ->first();

        if (!$doc || $doc->review_status !== 'approved') {
            $allDocsApproved = false;
            break;
        }
    }

    // Validasi hanya untuk dokumen yang belum approved
    if (!$allDocsApproved) {
        $validationRules = [];
        foreach ($this->fileFields as $field => $title) {
            $doc = $finalProject->documents()
                ->where('document_type', 'proposal')
                ->where('title', $title)
                ->orderByDesc('version')
                ->first();

            // Skip validasi untuk dokumen yang sudah approved
            if ($doc && $doc->review_status === 'approved') continue;

            $validationRules[$field] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240';
        }

        if (!empty($validationRules)) {
            $request->validate($validationRules);
        }
    }

    try {
        // Proses upload file hanya jika tidak semua dokumen approved
        if (!$allDocsApproved) {
            foreach ($this->fileFields as $field => $title) {
                if (!$request->hasFile($field)) continue;

                $oldDoc = $finalProject->documents()
                    ->where('document_type', 'proposal')
                    ->where('title', $title)
                    ->orderByDesc('version')
                    ->first();

                // Skip dokumen yang sudah approved
                if ($oldDoc && $oldDoc->review_status === 'approved') continue;

                $path = $request->file($field)
                    ->store("final-projects/{$studentId}/proposal", 'public');

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
                        'document_type' => 'proposal',
                        'title'         => $title,
                        'file_path'     => $path,
                        'version'       => 1,
                        'uploaded_by'   => $studentId,
                        'uploaded_at'   => now(),
                        'review_status' => 'pending',
                    ]);
                }
            }
        }

        // Reset proposal ke pending
        $proposal->update([
            'status'         => 'pending',
            'approval_notes' => null,
            'approved_by'    => null,
            'approved_at'    => null,
            'registered_at'  => now(),
        ]);

        $student     = Student::find($studentId);
        $prodi       = $student?->program_studi;
        $toUserIds   = NotificationHelper::kaprodiAndSuperuserUserIdsForProdi($prodi);
        if ($finalProject->supervisor_1_id) $toUserIds[] = (int) $finalProject->supervisor_1_id;
        if ($finalProject->supervisor_2_id) $toUserIds[] = (int) $finalProject->supervisor_2_id;

        $studentName = $student?->nama_lengkap ?: 'Mahasiswa';
        NotificationHelper::notifyUsers(
            $toUserIds,
            'sempro.resubmitted',
            'Dokumen Sempro Diajukan Ulang',
            $allDocsApproved
                ? "{$studentName} mengajukan ulang Sempro (dokumen sudah disetujui sebelumnya)."
                : "{$studentName} telah mengupload ulang dokumen Sempro. Silakan review kembali.",
            route('admin.final-project.proposals.index'),
            ['final_project_id' => $finalProject->id, 'proposal_id' => $proposal->id, 'program_studi' => $prodi]
        );

        return redirect()->route('student.final-project.proposal.show', $proposal->id)
            ->with('success', $allDocsApproved
                ? 'Sempro berhasil diajukan ulang. Menunggu persetujuan.'
                : 'Dokumen berhasil diupload ulang. Menunggu review.'
            );

    } catch (\Exception $e) {
        \Log::error('Proposal update failed: ' . $e->getMessage() . ' line ' . $e->getLine());
        return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}

    public function show($id)
    {
        $studentId = decrypt(session('student_id'));
        $proposal = FinalProjectProposal::whereHas('finalProject', function ($q) use ($studentId) {
            $q->where('student_id', $studentId);
        })->with([
            'finalProject.student',
            'finalProject.supervisor1',
            'finalProject.supervisor2',
            'finalProject.documents',
            'approver',
        ])->findOrFail($id);

        return view('students.final-project.proposal.show', compact('proposal'));
    }
}
