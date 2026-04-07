<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\FinalProject;
use App\Models\FinalProjectDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index()
    {
        $studentId = decrypt(session('student_id'));
        $finalProject = FinalProject::where('student_id', $studentId)->firstOrFail();

        $documents = FinalProjectDocument::where('final_project_id', $finalProject->id)
            ->with('reviewer')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('document_type');

        return view('students.final-project.documents.index', compact('documents', 'finalProject'));
    }

    public function create()
    {
        $studentId = decrypt(session('student_id'));
        $finalProject = FinalProject::where('student_id', $studentId)->firstOrFail();

        return view('students.final-project.documents.create', compact('finalProject'));
    }

    public function store(Request $request)
    {
        $studentId = decrypt(session('student_id'));
        $finalProject = FinalProject::where('student_id', $studentId)->firstOrFail();

        $request->validate([
            'document_type' => 'required|in:proposal,chapter,full_draft,final,presentation,other',
            'title' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,doc,docx,ppt,pptx|max:10240',
        ]);

        try {
            // Check for existing document with same type and title to increment version
            $existingDoc = FinalProjectDocument::where('final_project_id', $finalProject->id)
                ->where('document_type', $request->document_type)
                ->where('title', $request->title)
                ->orderBy('version', 'desc')
                ->first();

            $version = $existingDoc ? $existingDoc->version + 1 : 1;

            $path = $request->file('file')->store("final-projects/{$studentId}/documents", 'public');

            FinalProjectDocument::create([
                'final_project_id' => $finalProject->id,
                'document_type' => $request->document_type,
                'title' => $request->title,
                'file_path' => $path,
                'version' => $version,
                'uploaded_by' => $studentId,
                'uploaded_at' => now(),
                'review_status' => 'pending',
            ]);

            return redirect()->route('student.final-project.documents.index')
                ->with('success', 'Dokumen berhasil diupload.');

        } catch (\Exception $e) {
            \Log::error('Document upload failed: '.$e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat mengupload dokumen.');
        }
    }

    public function download($id)
    {
        $studentId = decrypt(session('student_id'));
        $document = FinalProjectDocument::whereHas('finalProject', function($q) use ($studentId) {
            $q->where('student_id', $studentId);
        })->findOrFail($id);

        if (!Storage::disk('public')->exists($document->file_path)) {
            return back()->with('error', 'File tidak ditemukan.');
        }

        return Storage::disk('public')->download($document->file_path);
    }

    public function destroy($id)
    {
        $studentId = decrypt(session('student_id'));
        $document = FinalProjectDocument::whereHas('finalProject', function($q) use ($studentId) {
            $q->where('student_id', $studentId);
        })->where('review_status', 'pending')->findOrFail($id);

        try {
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            $document->delete();

            return redirect()->route('student.final-project.documents.index')
                ->with('success', 'Dokumen berhasil dihapus.');

        } catch (\Exception $e) {
            \Log::error('Document deletion failed'.$e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menghapus dokumen.');
        }
    }
}
