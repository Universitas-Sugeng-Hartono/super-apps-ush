<?php

namespace App\Http\Controllers\Admin\FinalProject;

use App\Http\Controllers\Controller;
use App\Helpers\NotificationHelper;
use App\Models\FinalProjectDocument;
use App\Models\FinalProjectDefense;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DefenseApprovalController extends Controller
{
    private function canManageAll(): bool
    {
        $role = User::normalizeRole(auth()->user()?->role);
        return in_array($role, ['superadmin', 'masteradmin'], true);
    }

    public function index()
    {
        $lecturerId = auth()->id();
        $role = User::normalizeRole(auth()->user()?->role);
        
        $prodiFilter = request('prodi');
        $availableProdis = \App\Models\User::whereNotNull('program_studi')->distinct()->pluck('program_studi');

        $defenses = FinalProjectDefense::with(['finalProject.student', 'finalProject.documents'])
            ->when($prodiFilter, function ($q) use ($prodiFilter) {
                $q->whereHas('finalProject.student', function ($sq) use ($prodiFilter) {
                    $sq->where('program_studi', $prodiFilter);
                });
            })
            ->when(!$this->canManageAll(), function ($q) use ($lecturerId) {
                $q->whereHas('finalProject', function ($qq) use ($lecturerId) {
                    $qq->bySupervisor($lecturerId);
                });
            })
            ->pending()
            ->orderBy('registered_at', 'asc')
            ->get();

        return view('admin.final-project.defenses.index', compact('defenses', 'role', 'availableProdis', 'prodiFilter'));
    }

    public function show($id)
    {
        $lecturerId = auth()->id();
        
        $defense = FinalProjectDefense::with([
            'finalProject.student', 
            'finalProject.supervisor1', 
            'finalProject.supervisor2',
            'finalProject.documents',
            'finalProject.guidanceLogs' => function($q) use ($lecturerId) {
                $q->where('supervisor_id', $lecturerId)->approved();
            }
        ])->when(!$this->canManageAll(), function ($q) use ($lecturerId) {
            $q->whereHas('finalProject', function ($qq) use ($lecturerId) {
                $qq->bySupervisor($lecturerId);
            });
        })->findOrFail($id);

        return view('admin.final-project.defenses.show', compact('defense'));
    }

    public function approve(Request $request, $id)
    {
        $lecturerId = auth()->id();
        
        $defense = FinalProjectDefense::when(!$this->canManageAll(), function ($q) use ($lecturerId) {
            $q->whereHas('finalProject', function ($qq) use ($lecturerId) {
                $qq->bySupervisor($lecturerId);
            });
        })->findOrFail($id);

        $rules = [
            'approval_notes' => 'nullable|string',
        ];

        // Jadwal hanya ditentukan oleh Kaprodi/Superuser (bukan dosen pembimbing).
        if ($this->canManageAll()) {
            $rules['scheduled_at'] = 'nullable|date_format:Y-m-d\TH:i';
        }

        $request->validate($rules);

        $scheduledAt = $defense->scheduled_at;
        if ($this->canManageAll() && $request->filled('scheduled_at')) {
            $scheduledAt = Carbon::parse($request->scheduled_at);
        }

        $defense->update([
            'status' => 'approved',
            'approved_by' => $lecturerId,
            'approved_at' => now(),
            'approval_notes' => $request->approval_notes,
            'scheduled_at' => $scheduledAt,
        ]);

        // Jika pengajuan sidang disetujui, dokumen-dokumen sidang yang terkait ikut disetujui juga.
        FinalProjectDocument::query()
            ->where('final_project_id', $defense->final_project_id)
            ->whereIn('document_type', ['final', 'presentation'])
            ->whereIn('review_status', ['pending', 'needs_revision'])
            ->update([
                'review_status' => 'approved',
                'reviewer_id' => $lecturerId,
                'review_notes' => 'Auto-approved saat pengajuan Sidang disetujui.',
                'reviewed_at' => now(),
            ]);

        $studentId = (int) data_get($defense, 'finalProject.student_id');
        if ($studentId > 0) {
            $when = $defense->scheduled_at ? Carbon::parse($defense->scheduled_at)->translatedFormat('d M Y H:i') : null;
            $msg = $when
                ? "Sidang Anda sudah disetujui. Jadwal: {$when}."
                : "Sidang Anda sudah disetujui. Jadwal akan diinformasikan oleh Kaprodi.";

            NotificationHelper::notifyStudent(
                $studentId,
                'sidang.approved',
                'Sidang Disetujui',
                $msg,
                route('student.final-project.index'),
                ['scheduled_at' => $defense->scheduled_at]
            );
        }

        return redirect()->route('admin.final-project.defenses.index')
            ->with('success', 'Pendaftaran sidang TA berhasil disetujui.');
    }

    public function reject(Request $request, $id)
    {
        $lecturerId = auth()->id();
        
        $defense = FinalProjectDefense::when(!$this->canManageAll(), function ($q) use ($lecturerId) {
            $q->whereHas('finalProject', function ($qq) use ($lecturerId) {
                $qq->bySupervisor($lecturerId);
            });
        })->findOrFail($id);

        $request->validate([
            'approval_notes' => 'required|string',
        ]);

        $defense->update([
            'status' => 'rejected',
            'approved_by' => $lecturerId,
            'approved_at' => now(),
            'approval_notes' => $request->approval_notes,
        ]);

        $studentId = (int) data_get($defense, 'finalProject.student_id');
        if ($studentId > 0) {
            $msg = "Sidang Anda ditolak. Catatan: " . (string) $defense->approval_notes;
            NotificationHelper::notifyStudent(
                $studentId,
                'sidang.rejected',
                'Sidang Ditolak',
                $msg,
                route('student.final-project.index')
            );
        }

        return redirect()->route('admin.final-project.defenses.index')
            ->with('success', 'Pendaftaran sidang TA ditolak.');
    }

    public function approveAll(Request $request)
    {
        $lecturerId = auth()->id();
        $prodi = $request->input('prodi');
        $canManageAll = $this->canManageAll();

        $selectedIds = $request->input('selected_ids', []);
        $scheduledDates = $request->input('scheduled_dates', []);

        $rules = [
            'approval_notes' => 'nullable|string',
            'selected_ids' => 'required|array|min:1',
            'selected_ids.*' => 'integer',
        ];

        if ($canManageAll) {
            $rules['scheduled_dates'] = 'nullable|array';
            $rules['scheduled_dates.*'] = 'nullable|date_format:Y-m-d\TH:i';
        }

        $request->validate($rules, [
            'selected_ids.required' => 'Pilih minimal satu mahasiswa untuk disetujui.'
        ]);

        $defenses = FinalProjectDefense::with(['finalProject.student'])
            ->whereIn('id', $selectedIds)
            ->when($prodi, function ($q) use ($prodi) {
                $q->whereHas('finalProject.student', function ($sq) use ($prodi) {
                    $sq->where('program_studi', $prodi);
                });
            })
            ->when(!$canManageAll, function ($q) use ($lecturerId) {
                $q->whereHas('finalProject', function ($qq) use ($lecturerId) {
                    $qq->bySupervisor($lecturerId);
                });
            })
            ->pending()
            ->get();

        if ($defenses->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data pengajuan Sidang valid yang dipilih untuk disetujui.');
        }

        foreach ($defenses as $defense) {
            $scheduledAt = null;
            if ($canManageAll && isset($scheduledDates[$defense->id]) && !empty($scheduledDates[$defense->id])) {
                $scheduledAt = \Carbon\Carbon::parse($scheduledDates[$defense->id]);
            } else {
                $scheduledAt = $defense->scheduled_at;
            }

            $notes = $request->filled('approval_notes') ? $request->approval_notes : 'Auto-approved via Bulk Approve.';

            $defense->update([
                'status' => 'approved',
                'approved_by' => $lecturerId,
                'approved_at' => now(),
                'approval_notes' => $notes,
                'scheduled_at' => $scheduledAt,
            ]);

            $defense->finalProject()->update(['status' => 'completed']);

            \App\Models\FinalProjectDocument::query()
                ->where('final_project_id', $defense->final_project_id)
                ->whereIn('document_type', ['final', 'presentation'])
                ->whereIn('review_status', ['pending', 'needs_revision'])
                ->update([
                    'review_status' => 'approved',
                    'reviewer_id' => $lecturerId,
                    'review_notes' => 'Auto-approved via Bulk Approve Sidang.',
                    'reviewed_at' => now(),
                ]);

            $studentId = (int) data_get($defense, 'finalProject.student_id');
            if ($studentId > 0) {
                $when = $scheduledAt ? \Carbon\Carbon::parse($scheduledAt)->translatedFormat('d M Y H:i') : null;
                $msg = $when
                    ? "Sidang Anda sudah disetujui secara masal. Jadwal: {$when}."
                    : "Sidang Anda sudah disetujui secara masal. Jadwal akan diinformasikan kemudian.";

                \App\Helpers\NotificationHelper::notifyStudent(
                    $studentId,
                    'sidang.approved',
                    'Sidang TA Disetujui',
                    $msg,
                    route('student.final-project.index'),
                    ['scheduled_at' => $scheduledAt]
                );
            }
        }

        return redirect()->route('admin.final-project.defenses.index')
            ->with('success', $defenses->count() . ' pendaftaran sidang TA berhasil disetujui secara masal.');
    }
}
