<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\FinalProject;
use Illuminate\Http\Request;

class TitleRequestController extends Controller
{
    public function create()
    {
        $studentId = decrypt(session('student_id'));
        $finalProject = FinalProject::where('student_id', $studentId)->first();
        
        if (!$finalProject) {
            $finalProject = FinalProject::create([
                'student_id' => $studentId,
                'supervisor_1_id' => auth()->guard('student')->user()->id_lecturer,
                'status' => 'proposal',
                'progress_percentage' => 0,
                'started_at' => now(),
            ]);
        }

        return view('students.final-project.title.create', compact('finalProject'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|min:10|max:500',
            'title_en' => 'required|string|min:10|max:500',
        ]);

        $studentId = decrypt(session('student_id'));
        $finalProject = FinalProject::where('student_id', $studentId)->firstOrFail();

        // Jika sudah ada judul yang approved, tidak bisa ubah
        if ($finalProject->title_approved_at) {
            return back()->with('error', 'Judul Tugas Akhir Anda sudah disetujui. Hubungi admin untuk perubahan.');
        }

        $finalProject->update([
            'title' => $request->title,
            'title_en' => $request->title_en,
            'status' => 'proposal',
        ]);

        return redirect()->route('student.final-project.index')
            ->with('success', 'Judul Tugas Akhir berhasil diajukan. Menunggu persetujuan admin.');
    }

    public function edit()
    {
        $studentId = decrypt(session('student_id'));
        $finalProject = FinalProject::where('student_id', $studentId)->firstOrFail();

        // Jika sudah approved, tidak bisa edit
        if ($finalProject->title_approved_at) {
            return redirect()->route('student.final-project.index')
                ->with('error', 'Judul sudah disetujui. Hubungi admin untuk perubahan.');
        }

        return view('students.final-project.title.edit', compact('finalProject'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'title' => 'required|string|min:10|max:500',
            'title_en' => 'required|string|min:10|max:500',
        ]);

        $studentId = decrypt(session('student_id'));
        $finalProject = FinalProject::where('student_id', $studentId)->firstOrFail();

        if ($finalProject->title_approved_at) {
            return back()->with('error', 'Judul sudah disetujui. Hubungi admin untuk perubahan.');
        }

        $finalProject->update([
            'title' => $request->title,
            'title_en' => $request->title_en,
        ]);

        return redirect()->route('student.final-project.index')
            ->with('success', 'Judul Tugas Akhir berhasil diubah. Menunggu persetujuan admin.');
    }
}

