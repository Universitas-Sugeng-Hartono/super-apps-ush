<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Counseling;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CounselingController extends Controller
{
    /**
     * Menampilkan daftar angkatan mahasiswa bimbingan dosen.
     */
    public function index()
    {
        $angkatan = Student::where('id_lecturer', Auth::id())
            ->select('angkatan', DB::raw('count(*) as total'))
            ->groupBy('angkatan')
            ->orderBy('angkatan', 'asc')
            ->get();

        return view('admin.counseling.index', compact('angkatan'));
    }

    /**
     * Menampilkan mahasiswa per angkatan.
     */
    public function getStudentsByBatch($batch, Request $request)
    {
        $search = $request->input('search');

        $students = Student::withCount('counselings')
            ->where('id_lecturer', Auth::id())
            ->where('angkatan', $batch)
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nim', 'like', "%{$search}%")
                        ->orWhere('angkatan', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->appends(['search' => $search]);

        return view('admin.students.index', compact('students', 'batch'));
    }

    public function getStudentsByBatchByCourse(Request $request, $batch)
    {
        $students = Student::with(['counselings'])
        ->where('id_lecturer', Auth::id())
        ->where('angkatan', $batch)
        ->get();

        $students->map(function ($student) {
            $student->counselings->map(function ($counseling) {
                $counseling->failed_courses_detail  = $counseling->failed_courses_objects;
                $counseling->retaken_courses_detail = $counseling->retaken_courses_objects;
            });
            return $student;
        });
       
        return view('admin.counseling.course-failorretake', compact('students'));
    }
    /**
     * Form tambah counseling mahasiswa.
     */
    public function counselingAdd()
    {
        $students = Student::where('is_counseling', 0)->get();

        return view('students.counseling.add_form_student', compact('students'));
    }

    /**
     * Toggle status counseling mahasiswa.
     */
    public function openClose($id)
    {
        $student = Student::findOrFail($id);
        $student->is_counseling = $student->is_counseling ? 0 : 1;
        $student->save();

        return redirect()->back()->with('success', 'Status counseling berhasil diubah.');
    }

    /**
     * Toggle status edit data mahasiswa.
     */
    public function openCloseEdit($id)
    {
        $student = Student::findOrFail($id);
        $student->is_edited = $student->is_edited ? 0 : 1;
        $student->save();

        return redirect()->back()->with('success', 'Status Edit Data berhasil diubah.');
    }
}