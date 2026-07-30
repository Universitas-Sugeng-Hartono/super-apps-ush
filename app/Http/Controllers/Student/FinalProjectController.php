<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\FinalProject;
use App\Models\Notification;
use Illuminate\Http\Request;

class FinalProjectController extends Controller
{
    public function index()
    {
        $student = auth()->guard('student')->user();
        $studentId = decrypt(session('student_id'));
        
        $finalProject = FinalProject::with([
            'supervisor1', 
            'supervisor2', 
            'proposal', 
            'defense',
            'guidanceLogs' => function($q) {
                $q->where('status', 'approved')->orderBy('guidance_date', 'desc');
            },
            'documents'
        ])->where('student_id', $studentId)->first();

        // Create final project if doesn't exist
        if (!$finalProject) {
            $finalProject = FinalProject::create([
                'student_id' => $studentId,
                'supervisor_1_id' => $student->id_lecturer, // Default PA as supervisor 1
                'status' => 'proposal',
                'progress_percentage' => 0,
                'started_at' => now(),
            ]);
            $finalProject->load(['supervisor1', 'supervisor2', 'proposal', 'defense', 'guidanceLogs', 'documents']);
        }

        $stats = [
            'approved_guidance_count' => $finalProject->guidanceLogs()->approved()->count(),
            'total_guidance_count' => $finalProject->guidanceLogs()->count(),
            'documents_count' => $finalProject->documents()->count(),
            'pending_documents' => $finalProject->documents()->pendingReview()->count(),
        ];

        $celebrationNotif = Notification::query()
            ->forRecipient('student', (int) $studentId)
            ->unread()
            ->whereIn('type', ['sempro.approved', 'sidang.approved'])
            ->orderByDesc('created_at')
            ->first();

        return view('students.final-project.index', compact('finalProject', 'stats', 'celebrationNotif'));
    }
}
