<?php

namespace App\Http\Controllers\Admin\FinalProject;

use App\Http\Controllers\Controller;
use App\Models\FinalProject;
use App\Models\FinalProjectProposal;
use App\Models\FinalProjectDefense;
use App\Models\FinalProjectGuidanceLog;
use App\Models\FinalProjectDocument;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private function canManageAll(): bool
    {
        $role = User::normalizeRole(auth()->user()?->role);
        return in_array($role, ['superadmin', 'masteradmin'], true);
    }

    public function index(Request $request)
    {
        $lecturerId = auth()->id();
        $manageAll = $this->canManageAll();

        $finalProjects = FinalProject::with(['student', 'proposal', 'defense', 'guidanceLogs'])
            ->when(!$manageAll, fn ($q) => $q->bySupervisor($lecturerId))
            ->when($request->status, function($q) use ($request) {
                $q->byStatus($request->status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $statsQuery = FinalProject::query()->when(!$manageAll, fn ($q) => $q->bySupervisor($lecturerId));
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'proposal' => (clone $statsQuery)->byStatus('proposal')->count(),
            'research' => (clone $statsQuery)->byStatus('research')->count(),
            'defense' => (clone $statsQuery)->byStatus('defense')->count(),
            'completed' => (clone $statsQuery)->byStatus('completed')->count(),
        ];

        $pendingProposals = FinalProjectProposal::when(!$manageAll, function ($q) use ($lecturerId) {
            $q->whereHas('finalProject', fn ($qq) => $qq->bySupervisor($lecturerId));
        })->pending()->count();

        $pendingDefenses = FinalProjectDefense::when(!$manageAll, function ($q) use ($lecturerId) {
            $q->whereHas('finalProject', fn ($qq) => $qq->bySupervisor($lecturerId));
        })->pending()->count();

        $pendingGuidance = FinalProjectGuidanceLog::when(!$manageAll, function ($q) use ($lecturerId) {
            $q->where('supervisor_id', $lecturerId);
        })->pending()->count();

        $pendingDocuments = FinalProjectDocument::when(!$manageAll, function ($q) use ($lecturerId) {
            $q->whereHas('finalProject', fn ($qq) => $qq->bySupervisor($lecturerId));
        })->pendingReview()->count();

        // Pending titles (all admins can see)
        $pendingTitles = FinalProject::whereNotNull('title')
            ->whereNull('title_approved_at')
            ->count();

        $pendingItems = [
            'titles' => $pendingTitles,
            'proposals' => $pendingProposals,
            'defenses' => $pendingDefenses,
            'guidance' => $pendingGuidance,
            'documents' => $pendingDocuments,
        ];

        return view('admin.final-project.index', compact('finalProjects', 'stats', 'pendingItems'));
    }

    public function show($id)
    {
        $lecturerId = auth()->id();
        $finalProject = FinalProject::with([
            'student', 
            'supervisor1', 
            'supervisor2', 
            'proposal', 
            'defense',
            'guidanceLogs' => function($q) {
                $q->orderBy('guidance_date', 'desc');
            },
            'documents'
        ])->when(!$this->canManageAll(), fn ($q) => $q->bySupervisor($lecturerId))->findOrFail($id);

        return view('admin.final-project.show', compact('finalProject'));
    }
}
