<?php

namespace App\Http\Controllers\Admin\FinalProject;

use App\Http\Controllers\Controller;
use App\Helpers\NotificationHelper;
use App\Models\FinalProject;
use Illuminate\Http\Request;

class TitleApprovalController extends Controller
{
    public function index()
    {
        $pendingTitles = FinalProject::with(['student', 'supervisor1', 'supervisor2'])
            ->whereNotNull('title')
            ->whereNull('title_approved_at')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.final-project.titles.index', compact('pendingTitles'));
    }

    public function show($id)
    {
        $finalProject = FinalProject::with(['student', 'supervisor1', 'supervisor2'])
            ->findOrFail($id);

        return view('admin.final-project.titles.show', compact('finalProject'));
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'approval_notes' => 'nullable|string',
        ]);

        $finalProject = FinalProject::findOrFail($id);
        
        $finalProject->update([
            'title_approved_at' => now(),
        ]);

        $studentId = (int) $finalProject->student_id;
        if ($studentId > 0) {
            NotificationHelper::notifyStudent(
                $studentId,
                'judul.approved',
                'Judul Tugas Akhir Disetujui',
                'Judul Tugas Akhir Anda sudah disetujui. Silakan lanjutkan ke pendaftaran Sempro.',
                route('student.final-project.index')
            );
        }

        return redirect()->route('admin.final-project.titles.index')
            ->with('success', 'Judul Tugas Akhir berhasil disetujui.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_notes' => 'required|string',
        ]);

        $finalProject = FinalProject::findOrFail($id);
        
        $finalProject->update([
            'title' => null,
            'title_en' => null,
        ]);

        $studentId = (int) $finalProject->student_id;
        if ($studentId > 0) {
            NotificationHelper::notifyStudent(
                $studentId,
                'judul.rejected',
                'Judul Tugas Akhir Ditolak',
                'Judul Tugas Akhir Anda ditolak. Catatan: ' . (string) $request->rejection_notes,
                route('student.final-project.index')
            );
        }

        return redirect()->route('admin.final-project.titles.index')
            ->with('success', 'Judul Tugas Akhir ditolak. Mahasiswa dapat mengajukan judul baru.');
    }
}

