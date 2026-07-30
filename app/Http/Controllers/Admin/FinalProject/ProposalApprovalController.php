<?php

namespace App\Http\Controllers\Admin\FinalProject;

use App\Http\Controllers\Controller;
use App\Helpers\NotificationHelper;
use App\Models\FinalProjectDocument;
use App\Models\FinalProjectProposal;
use App\Models\FinalProjectDefense;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProposalApprovalController extends Controller
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

        $proposals = FinalProjectProposal::with(['finalProject.student', 'finalProject.documents'])
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

        return view('admin.final-project.proposals.index', compact('proposals', 'role', 'availableProdis', 'prodiFilter'));
    }

    public function show($id)
    {
        $lecturerId = auth()->id();
        
        $proposal = FinalProjectProposal::with([
            'finalProject.student', 
            'finalProject.supervisor1', 
            'finalProject.supervisor2',
            'finalProject.documents' => function($q) {
                $q->where('document_type', 'proposal');
            },
            'finalProject.guidanceLogs' => function($q) use ($lecturerId) {
                $q->where('supervisor_id', $lecturerId)->approved();
            }
        ])->when(!$this->canManageAll(), function ($q) use ($lecturerId) {
            $q->whereHas('finalProject', function ($qq) use ($lecturerId) {
                $qq->bySupervisor($lecturerId);
            });
        })->findOrFail($id);

        return view('admin.final-project.proposals.show', compact('proposal'));
    }

    public function bulkZip(Request $request)
    {
        $prodi = $request->query('prodi');
        $type = $request->query('type', 'all'); // 'proposal' or 'final'
        $lecturerId = auth()->id();
        $canManageAll = $this->canManageAll();

        $proposals = collect();
        $defenses = collect();

        if ($type === 'proposal' || $type === 'all') {
            $proposals = FinalProjectProposal::with(['finalProject.student', 'finalProject.documents'])
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
        }

        if ($type === 'final' || $type === 'all') {
            $defenses = FinalProjectDefense::with(['finalProject.student', 'finalProject.documents'])
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
        }

        if ($proposals->isEmpty() && $defenses->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data pending untuk prodi tersebut.');
        }

        $zip = new \ZipArchive();
        $safeProdi = $prodi ? preg_replace('/[^A-Za-z0-9\- ]/', '', $prodi) : 'Semua_Prodi';
        $safeProdi = str_replace(' ', '_', $safeProdi);
        
        if ($type === 'proposal') {
            $zipFileName = 'Berkas_Pending_SEMPRO_' . $safeProdi . '.zip';
        } elseif ($type === 'final') {
            $zipFileName = 'Berkas_Pending_SIDANG_' . $safeProdi . '.zip';
        } else {
            $zipFileName = 'Berkas_Pending_Sempro_Sidang_' . $safeProdi . '.zip';
        }
        
        $zipFilePath = storage_path('app/public/' . $zipFileName);

        if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            
            // Add Proposal Documents
            foreach ($proposals as $proposal) {
                $student = $proposal->finalProject->student;
                $safeName = preg_replace('/[^A-Za-z0-9\- ]/', '', $student->nama_lengkap);
                $safeProdiFolder = preg_replace('/[^A-Za-z0-9\- ]/', '', $student->program_studi);
                $studentFolder = "SEMPRO - {$safeName} - {$student->nim} - {$safeProdiFolder}";
                
                $docs = $proposal->finalProject->documents->where('document_type', 'proposal');
                foreach ($docs as $doc) {
                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($doc->file_path)) {
                        $filePath = \Illuminate\Support\Facades\Storage::disk('public')->path($doc->file_path);
                        $fileName = $studentFolder . '/' . $doc->title . '_' . basename($doc->file_path);
                        $zip->addFile($filePath, $fileName);
                    }
                }
            }

            // Add Defense Documents
            foreach ($defenses as $defense) {
                $student = $defense->finalProject->student;
                $safeName = preg_replace('/[^A-Za-z0-9\- ]/', '', $student->nama_lengkap);
                $safeProdiFolder = preg_replace('/[^A-Za-z0-9\- ]/', '', $student->program_studi);
                $studentFolder = "SIDANG - {$safeName} - {$student->nim} - {$safeProdiFolder}";
                
                $docs = $defense->finalProject->documents->whereIn('document_type', ['final', 'presentation']);
                foreach ($docs as $doc) {
                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($doc->file_path)) {
                        $filePath = \Illuminate\Support\Facades\Storage::disk('public')->path($doc->file_path);
                        $fileName = $studentFolder . '/' . $doc->title . '_' . basename($doc->file_path);
                        $zip->addFile($filePath, $fileName);
                    }
                }
            }
            
            $zip->close();
        } else {
            return redirect()->back()->with('error', 'Gagal membuat file ZIP.');
        }

        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }

    public function approve(Request $request, $id)
    {
        $lecturerId = auth()->id();
        
        $proposal = FinalProjectProposal::when(!$this->canManageAll(), function ($q) use ($lecturerId) {
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

        $scheduledAt = $proposal->scheduled_at;
        if ($this->canManageAll() && $request->filled('scheduled_at')) {
            $scheduledAt = Carbon::parse($request->scheduled_at);
        }

        $proposal->update([
            'status' => 'approved',
            'approved_by' => $lecturerId,
            'approved_at' => now(),
            'approval_notes' => $request->approval_notes,
            'scheduled_at' => $scheduledAt,
        ]);

        $proposal->finalProject()->update(['status' => 'research']);

        // Jika pengajuan sempro disetujui, dokumen-dokumen sempro yang terkait ikut disetujui juga.
        FinalProjectDocument::query()
            ->where('final_project_id', $proposal->final_project_id)
            ->where('document_type', 'proposal')
            ->whereIn('review_status', ['pending', 'needs_revision'])
            ->update([
                'review_status' => 'approved',
                'reviewer_id' => $lecturerId,
                'review_notes' => 'Auto-approved saat pengajuan Sempro disetujui.',
                'reviewed_at' => now(),
            ]);

        $studentId = (int) data_get($proposal, 'finalProject.student_id');
        if ($studentId > 0) {
            $when = $proposal->scheduled_at ? Carbon::parse($proposal->scheduled_at)->translatedFormat('d M Y H:i') : null;
            $msg = $when
                ? "Sempro Anda sudah disetujui. Jadwal: {$when}."
                : "Sempro Anda sudah disetujui. Jadwal akan diinformasikan oleh Kaprodi.";

            NotificationHelper::notifyStudent(
                $studentId,
                'sempro.approved',
                'Seminar Proposal Disetujui',
                $msg,
                route('student.final-project.index'),
                ['scheduled_at' => $proposal->scheduled_at]
            );
        }

        return redirect()->route('admin.final-project.proposals.index')
            ->with('success', 'Pendaftaran seminar proposal berhasil disetujui.');
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

        $proposals = FinalProjectProposal::with(['finalProject.student'])
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

        if ($proposals->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data pengajuan Sempro valid yang dipilih untuk disetujui.');
        }

        foreach ($proposals as $proposal) {
            $scheduledAt = null;
            if ($canManageAll && isset($scheduledDates[$proposal->id]) && !empty($scheduledDates[$proposal->id])) {
                $scheduledAt = \Carbon\Carbon::parse($scheduledDates[$proposal->id]);
            } else {
                $scheduledAt = $proposal->scheduled_at;
            }
            
            $notes = $request->filled('approval_notes') ? $request->approval_notes : 'Auto-approved via Bulk Approve.';

            $proposal->update([
                'status' => 'approved',
                'approved_by' => $lecturerId,
                'approved_at' => now(),
                'approval_notes' => $notes,
                'scheduled_at' => $scheduledAt,
            ]);

            $proposal->finalProject()->update(['status' => 'research']);

            FinalProjectDocument::query()
                ->where('final_project_id', $proposal->final_project_id)
                ->where('document_type', 'proposal')
                ->whereIn('review_status', ['pending', 'needs_revision'])
                ->update([
                    'review_status' => 'approved',
                    'reviewer_id' => $lecturerId,
                    'review_notes' => 'Auto-approved via Bulk Approve Sempro.',
                    'reviewed_at' => now(),
                ]);

            $studentId = (int) data_get($proposal, 'finalProject.student_id');
            if ($studentId > 0) {
                $when = $scheduledAt ? \Carbon\Carbon::parse($scheduledAt)->translatedFormat('d M Y H:i') : null;
                $msg = $when
                    ? "Sempro Anda sudah disetujui secara masal. Jadwal: {$when}."
                    : "Sempro Anda sudah disetujui secara masal. Jadwal akan diinformasikan kemudian.";

                \App\Helpers\NotificationHelper::notifyStudent(
                    $studentId,
                    'sempro.approved',
                    'Seminar Proposal Disetujui',
                    $msg,
                    route('student.final-project.index'),
                    ['scheduled_at' => $scheduledAt]
                );
            }
        }

        return redirect()->route('admin.final-project.proposals.index')
            ->with('success', $proposals->count() . ' pengajuan seminar proposal berhasil disetujui secara masal.');
    }

    public function reject(Request $request, $id)
    {
        $lecturerId = auth()->id();
        
        $proposal = FinalProjectProposal::when(!$this->canManageAll(), function ($q) use ($lecturerId) {
            $q->whereHas('finalProject', function ($qq) use ($lecturerId) {
                $qq->bySupervisor($lecturerId);
            });
        })->findOrFail($id);

        $request->validate([
            'approval_notes' => 'required|string',
        ]);

        $proposal->update([
            'status' => 'rejected',
            'approved_by' => $lecturerId,
            'approved_at' => now(),
            'approval_notes' => $request->approval_notes,
        ]);

        $studentId = (int) data_get($proposal, 'finalProject.student_id');
        if ($studentId > 0) {
            $msg = "Sempro Anda ditolak. Catatan: " . (string) $proposal->approval_notes;
            NotificationHelper::notifyStudent(
                $studentId,
                'sempro.rejected',
                'Seminar Proposal Ditolak',
                $msg,
                route('student.final-project.index')
            );
        }

        return redirect()->route('admin.final-project.proposals.index')
            ->with('success', 'Pendaftaran seminar proposal ditolak.');
    }
}
