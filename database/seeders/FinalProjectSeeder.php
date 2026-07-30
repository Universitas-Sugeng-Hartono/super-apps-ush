<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\User;
use App\Models\FinalProject;
use App\Models\FinalProjectProposal;
use App\Models\FinalProjectDefense;
use App\Models\FinalProjectGuidanceLog;
use App\Models\FinalProjectDocument;

class FinalProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get sample students and lecturers
        $students = Student::limit(5)->get();
        $lecturers = User::whereIn('role', ['admin', 'superadmin'])->limit(3)->get();

        if ($students->isEmpty() || $lecturers->isEmpty()) {
            $this->command->warn('Please seed students and users first!');
            return;
        }

        foreach ($students as $index => $student) {
            // Create Final Project
            $finalProject = FinalProject::create([
                'student_id' => $student->id,
                'title' => 'Sistem Informasi Manajemen ' . ['Akademik', 'Keuangan', 'Perpustakaan', 'Kepegawaian', 'Inventory'][$index % 5] . ' Berbasis Web',
                'title_approved_at' => now()->subMonths(6),
                'supervisor_1_id' => $lecturers[0]->id ?? $student->id_lecturer,
                'supervisor_2_id' => $lecturers[1]->id ?? null,
                'status' => ['proposal', 'research', 'defense', 'completed'][$index % 4],
                'progress_percentage' => [25, 50, 75, 100][$index % 4],
                'started_at' => now()->subMonths(6),
                'completed_at' => $index % 4 === 3 ? now() : null,
            ]);

            // Create Proposal
            $proposalStatus = $index % 3 === 0 ? 'pending' : ($index % 3 === 1 ? 'approved' : 'rejected');
            $proposal = FinalProjectProposal::create([
                'final_project_id' => $finalProject->id,
                'registered_at' => now()->subMonths(5),
                'scheduled_at' => $proposalStatus === 'approved' ? now()->subMonths(4) : null,
                'status' => $proposalStatus,
                'approval_notes' => $proposalStatus !== 'pending' ? 'Proposal sudah cukup baik untuk dilanjutkan ke tahap penelitian.' : null,
                'approved_by' => $proposalStatus !== 'pending' ? ($lecturers[0]->id ?? $student->id_lecturer) : null,
                'approved_at' => $proposalStatus !== 'pending' ? now()->subMonths(4) : null,
                'grade' => $proposalStatus === 'approved' ? rand(75, 90) : null,
            ]);

            // Create Defense if approved
            if ($finalProject->status === 'defense' || $finalProject->status === 'completed') {
                $defenseStatus = $finalProject->status === 'completed' ? 'approved' : 'pending';
                FinalProjectDefense::create([
                    'final_project_id' => $finalProject->id,
                    'registered_at' => now()->subMonth(),
                    'scheduled_at' => $defenseStatus === 'approved' ? now()->subDays(7) : null,
                    'status' => $defenseStatus,
                    'approval_notes' => $defenseStatus === 'approved' ? 'TA sudah layak untuk disidangkan. Lanjutkan!' : null,
                    'approved_by' => $defenseStatus === 'approved' ? ($lecturers[0]->id ?? $student->id_lecturer) : null,
                    'approved_at' => $defenseStatus === 'approved' ? now()->subDays(10) : null,
                    'final_grade' => $defenseStatus === 'approved' ? rand(80, 95) : null,
                ]);
            }

            // Create Guidance Logs
            for ($i = 1; $i <= rand(5, 12); $i++) {
                $status = $i > 8 ? 'pending' : ($i % 10 === 0 ? 'rejected' : 'approved');
                FinalProjectGuidanceLog::create([
                    'final_project_id' => $finalProject->id,
                    'supervisor_id' => $i % 2 === 0 ? ($lecturers[0]->id ?? $student->id_lecturer) : ($lecturers[1]->id ?? $student->id_lecturer),
                    'guidance_date' => now()->subDays(60 - ($i * 5)),
                    'materials_discussed' => [
                        'Pembahasan outline proposal dan tinjauan pustaka',
                        'Review metodologi penelitian yang akan digunakan',
                        'Diskusi tentang desain sistem dan ERD database',
                        'Review implementasi modul login dan dashboard',
                        'Pembahasan hasil testing dan bug fixing',
                        'Persiapan untuk seminar proposal',
                        'Review BAB 1-3 untuk laporan akhir',
                        'Diskusi tentang kesimpulan dan saran penelitian',
                    ][$i % 8],
                    'student_notes' => $i % 3 === 0 ? 'Perlu revisi di bagian metodologi' : null,
                    'supervisor_feedback' => $status !== 'pending' ? [
                        'Sudah bagus, lanjutkan!',
                        'Tingkatkan lagi untuk BAB berikutnya',
                        'Perlu perbaikan di bagian analisis',
                        'Good progress, keep it up!',
                    ][$i % 4] : null,
                    'status' => $status,
                    'approved_at' => $status === 'approved' ? now()->subDays(60 - ($i * 5) - 1) : null,
                ]);
            }

            // Create Documents
            $docTypes = ['proposal', 'chapter', 'full_draft', 'final', 'presentation'];
            foreach ($docTypes as $typeIndex => $type) {
                if ($typeIndex < ($index % 5 + 1)) { // Varying number of docs per student
                    $reviewStatus = $typeIndex % 3 === 0 ? 'pending' : ($typeIndex % 3 === 1 ? 'approved' : 'needs_revision');
                    FinalProjectDocument::create([
                        'final_project_id' => $finalProject->id,
                        'document_type' => $type,
                        'title' => [
                            'Dokumen Proposal TA',
                            'BAB 1 - Pendahuluan',
                            'Draft Lengkap TA',
                            'Final TA (Revisi Akhir)',
                            'Slide Presentasi Sidang',
                        ][$typeIndex],
                        'file_path' => "final-projects/{$student->id}/{$type}/document_v1.pdf",
                        'version' => 1,
                        'uploaded_by' => $student->id,
                        'uploaded_at' => now()->subDays(50 - ($typeIndex * 10)),
                        'review_status' => $reviewStatus,
                        'reviewer_id' => $reviewStatus !== 'pending' ? ($lecturers[0]->id ?? $student->id_lecturer) : null,
                        'review_notes' => $reviewStatus === 'needs_revision' ? 'Perlu perbaikan di bagian metodologi dan pembahasan.' : ($reviewStatus === 'approved' ? 'Dokumen sudah baik, approved.' : null),
                        'reviewed_at' => $reviewStatus !== 'pending' ? now()->subDays(50 - ($typeIndex * 10) - 2) : null,
                    ]);
                }
            }
        }

        $this->command->info('Final Project seeder completed successfully!');
        $this->command->info('Created ' . $students->count() . ' final projects with proposals, guidance logs, and documents.');
    }
}
