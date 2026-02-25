<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\CardCounseling;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CardCounselingController extends Controller
{
    public function show()
    { 
        $courses = Course::where('program_study', session('student_prodi'))->get();
        $student = Student::with('dosenPA')->findOrFail(decrypt(session('student_id')));

        if ($student->id !== decrypt(session('student_id'))) {
            return redirect()->back()->with('error', 'You do not have access to this page.');
        }

        if ($student->ttd == null || $student->foto == null) {
            return redirect()
                ->route('student.personal.editDataIndex', encrypt(session('student_id')))
                ->with('error', 'Please complete your personal data (photo and signature) before accessing counseling services.');
        }

        $history = $student->counselings()
            ->orderBy('created_at', 'desc')
            ->take(1)
            ->get()
            ->map(function ($item) {
                $ids = is_array($item->failed_courses)
                    ? $item->failed_courses
                    : json_decode($item->failed_courses, true);

                $item->failed_courses_objects = Course::whereIn('id', $ids ?: [])->get();
                return $item;
            });

        return view('students.counseling.add_form_student', compact('student', 'history', 'courses'));
    }

    public function store(Request $request, $id_student)
    {
        $student = Student::findOrFail(decrypt(session('student_id')));

        $validated = $request->validate([
            'semester'          => 'required|integer|min:1|max:14',
            'sks'               => 'required|integer|min:1|max:30',
            'ip'                => [
                'nullable',
                'numeric',
                'min:0',
                'max:4.00',
                'regex:/^\d(\.\d{1,2})?$/'
            ],
            'tanggal'           => 'required|date|before_or_equal:today',
            'komentar'          => 'nullable|string|max:500',
            'failed_courses'    => 'nullable|array',
            'failed_courses.*'  => 'string|max:100',
            'retaken_courses'   => 'nullable|array',
            'retaken_courses.*' => 'string|max:100',
        ], [
            'semester.required' => 'Semester is required.',
            'semester.integer'  => 'Semester must be a valid number.',
            'semester.min'      => 'Semester must be at least :min.',
            'semester.max'      => 'Semester cannot be greater than :max.',

            'sks.required' => 'Total SKS is required.',
            'sks.integer'  => 'SKS must be a valid number.',
            'sks.min'      => 'SKS must be at least :min.',
            'sks.max'      => 'SKS cannot exceed :max per semester.',

            'ip.numeric' => 'GPA (IP) must be a valid number.',
            'ip.min'     => 'GPA cannot be less than :min.',
            'ip.max'     => 'GPA cannot be greater than :max.',
            'ip.regex'   => 'GPA must be in format x.xx (e.g., 3.43, max 1 digit before and 2 digits after the decimal).',

            'tanggal.required'        => 'Date is required.',
            'tanggal.date'            => 'Date must be a valid date.',
            'tanggal.before_or_equal' => 'Date cannot be in the future.',

            'komentar.string' => 'Comment must be a valid text.',
            'komentar.max'    => 'Comment cannot exceed :max characters.',

            'failed_courses.array'     => 'Failed courses must be an array.',
            'failed_courses.*.string'  => 'Each failed course must be text.',
            'failed_courses.*.max'     => 'Failed course name cannot exceed :max characters.',

            'retaken_courses.array'    => 'Retaken courses must be an array.',
            'retaken_courses.*.string' => 'Each retaken course must be text.',
            'retaken_courses.*.max'    => 'Retaken course name cannot exceed :max characters.',
        ]);

        DB::transaction(function () use ($student, $validated, $id_student) {
            CardCounseling::create([
                'id_student'      => $id_student,
                'semester'        => $validated['semester'],
                'sks'             => $validated['sks'],
                'ip'              => $validated['ip'] ?? null,
                'tanggal'         => $validated['tanggal'],
                'komentar'        => $validated['komentar'] ?? null,
                'failed_courses'  => $validated['failed_courses'] ?? [],
                'retaken_courses' => $validated['retaken_courses'] ?? [],
            ]);

            $student->update(['is_counseling' => 0]);
        });

        return redirect()
            ->route('student.counseling.show', encrypt(session('student_id')))
            ->with('success', 'Counseling record has been successfully added.');
    }
}