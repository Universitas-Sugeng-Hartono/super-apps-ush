<?php

namespace App\Http\Controllers\Admin\FinalProject;

use App\Http\Controllers\Controller;
use App\Models\FinalProjectDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentReviewController extends Controller
{
    private function canManageAll(): bool
    {
        $role = User::normalizeRole(auth()->user()?->role);
        return in_array($role, ['superadmin', 'masteradmin'], true);
    }

    public function index(Request $request)
    {
        $lecturerId = auth()->id();
        $canManageAll = $this->canManageAll();
        $prodiFilter = $request->input('prodi');
        $availableProdis = \App\Models\User::whereNotNull('program_studi')->distinct()->pluck('program_studi');

        $documents = FinalProjectDocument::with(['finalProject.student', 'uploader'])
            ->when($prodiFilter, function ($q) use ($prodiFilter) {
                $q->whereHas('finalProject.student', function ($sq) use ($prodiFilter) {
                    $sq->where('program_studi', $prodiFilter);
                });
            })
            ->when(!$canManageAll, function ($q) use ($lecturerId) {
                $q->whereHas('finalProject', function ($qq) use ($lecturerId) {
                    $qq->bySupervisor($lecturerId);
                });
            })
            ->when(request('status'), function($q) {
                $q->where('review_status', request('status'));
            }, function($q) use ($canManageAll) {
                if ($canManageAll) {
                    $q->pendingReview(); // Default to pending for reviewer
                }
            })
            ->orderBy('uploaded_at', 'desc')
            ->paginate(20);

        return view('admin.final-project.documents.index', compact('documents', 'canManageAll', 'prodiFilter', 'availableProdis'));
    }

    public function download($id)
    {
        $lecturerId = auth()->id();

        $document = FinalProjectDocument::when(!$this->canManageAll(), function ($q) use ($lecturerId) {
            $q->whereHas('finalProject', function ($qq) use ($lecturerId) {
                $qq->bySupervisor($lecturerId);
            });
        })->findOrFail($id);

        if (!Storage::disk('public')->exists($document->file_path)) {
            return back()->with('error', 'File tidak ditemukan.');
        }

        return Storage::disk('public')->download($document->file_path);
    }

    public function downloadZip(Request $request, $id, $type)
    {
        $lecturerId = auth()->id();

        $project = \App\Models\FinalProject::with('student', 'documents')
            ->when(!$this->canManageAll(), function ($q) use ($lecturerId) {
                $q->bySupervisor($lecturerId);
            })->findOrFail($id);

        $student = $project->student;
        
        $documentTypes = [];
        $prefix = '';
        if ($type === 'proposal') {
            $documentTypes = ['proposal'];
            $prefix = 'SEMPRO';
        } elseif ($type === 'final') {
            $documentTypes = ['final', 'presentation'];
            $prefix = 'SIDANG';
        } else {
            return back()->with('error', 'Tipe dokumen tidak valid.');
        }

        $docs = $project->documents->whereIn('document_type', $documentTypes);

        if ($docs->isEmpty()) {
            return back()->with('error', 'Tidak ada dokumen untuk diunduh.');
        }

        $zip = new \ZipArchive();
        $safeName = preg_replace('/[^A-Za-z0-9\- ]/', '', $student->nama_lengkap);
        $safeProdi = preg_replace('/[^A-Za-z0-9\- ]/', '', $student->program_studi);
        $zipFileName = "{$prefix} - {$safeName} - {$student->nim} - {$safeProdi}.zip";
        $zipFilePath = storage_path('app/public/' . $zipFileName);

        if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            $folderName = "{$prefix} - {$safeName} - {$student->nim} - {$safeProdi}";
            $zip->addEmptyDir($folderName);

            foreach ($docs as $doc) {
                if (Storage::disk('public')->exists($doc->file_path)) {
                    $filePath = Storage::disk('public')->path($doc->file_path);
                    $fileName = $folderName . '/' . $doc->title . '_' . basename($doc->file_path);
                    $zip->addFile($filePath, $fileName);
                }
            }
            $zip->close();
        } else {
            return back()->with('error', 'Gagal membuat file ZIP.');
        }

        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }

    public function approve(Request $request, $id)
    {
        abort_unless($this->canManageAll(), 403);

        $lecturerId = auth()->id();

        $document = FinalProjectDocument::when(!$this->canManageAll(), function ($q) use ($lecturerId) {
            $q->whereHas('finalProject', function ($qq) use ($lecturerId) {
                $qq->bySupervisor($lecturerId);
            });
        })->findOrFail($id);

        $request->validate([
            'review_notes' => 'nullable|string',
        ]);

        $document->update([
            'review_status' => 'approved',
            'reviewer_id' => $lecturerId,
            'review_notes' => $request->review_notes,
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.final-project.documents.index')
            ->with('success', 'Dokumen berhasil disetujui.');
    }

    public function revision(Request $request, $id)
    {
        abort_unless($this->canManageAll(), 403);

        $lecturerId = auth()->id();

        $document = FinalProjectDocument::when(!$this->canManageAll(), function ($q) use ($lecturerId) {
            $q->whereHas('finalProject', function ($qq) use ($lecturerId) {
                $qq->bySupervisor($lecturerId);
            });
        })->findOrFail($id);

        $request->validate([
            'review_notes' => 'required|string',
        ]);

        $document->update([
            'review_status' => 'needs_revision',
            'reviewer_id' => $lecturerId,
            'review_notes' => $request->review_notes,
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.final-project.documents.index')
            ->with('success', 'Dokumen perlu revisi. Catatan telah dikirim ke mahasiswa.');
    }

    public function reject(Request $request, $id)
    {
        abort_unless($this->canManageAll(), 403);

        $lecturerId = auth()->id();

        $document = FinalProjectDocument::when(!$this->canManageAll(), function ($q) use ($lecturerId) {
            $q->whereHas('finalProject', function ($qq) use ($lecturerId) {
                $qq->bySupervisor($lecturerId);
            });
        })->findOrFail($id);

        $request->validate([
            'review_notes' => 'required|string',
        ]);

        $document->update([
            'review_status' => 'rejected',
            'reviewer_id' => $lecturerId,
            'review_notes' => $request->review_notes,
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.final-project.documents.index')
            ->with('success', 'Dokumen ditolak.');
    }
}
