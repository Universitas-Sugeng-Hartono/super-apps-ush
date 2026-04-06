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

    public function index()
    {
        $lecturerId = auth()->id();

        $documents = FinalProjectDocument::with(['finalProject.student', 'uploader'])
            ->when(!$this->canManageAll(), function ($q) use ($lecturerId) {
                $q->whereHas('finalProject', function ($qq) use ($lecturerId) {
                    $qq->bySupervisor($lecturerId);
                });
            })
            ->when(request('status'), function($q) {
                $q->where('review_status', request('status'));
            }, function($q) {
                $q->pendingReview(); // Default to pending
            })
            ->orderBy('uploaded_at', 'desc')
            ->paginate(20);

        return view('admin.final-project.documents.index', compact('documents'));
    }

    public function show($id)
    {
        $lecturerId = auth()->id();

        $document = FinalProjectDocument::with(['finalProject.student', 'uploader', 'reviewer'])
            ->when(!$this->canManageAll(), function ($q) use ($lecturerId) {
                $q->whereHas('finalProject', function ($qq) use ($lecturerId) {
                    $qq->bySupervisor($lecturerId);
                });
            })
            ->findOrFail($id);

        return view('admin.final-project.documents.show', compact('document'));
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

    public function approve(Request $request, $id)
    {
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
