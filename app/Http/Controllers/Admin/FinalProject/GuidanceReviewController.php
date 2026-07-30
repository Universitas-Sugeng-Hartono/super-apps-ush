<?php

namespace App\Http\Controllers\Admin\FinalProject;

use App\Http\Controllers\Controller;
use App\Models\FinalProjectGuidanceLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GuidanceReviewController extends Controller
{
    private function canManageAll(): bool
    {
        $role = User::normalizeRole(auth()->user()?->role);
        return in_array($role, ['superadmin', 'masteradmin'], true);
    }

    public function index()
    {
        $lecturerId = auth()->id();
        $canManageAll = $this->canManageAll();
        
        $logs = FinalProjectGuidanceLog::with(['finalProject.student'])
            ->when(!$canManageAll, function ($q) use ($lecturerId) {
                $q->where('supervisor_id', $lecturerId);
            })
            ->when(request('status'), function($q) {
                $q->where('status', request('status'));
            }, function($q) use ($canManageAll) {
                if (!$canManageAll) {
                    $q->pending(); // Default to pending for supervisor review
                }
            })
            ->orderBy('guidance_date', 'desc')
            ->paginate(20);

        return view('admin.final-project.guidance.index', compact('logs', 'canManageAll'));
    }

    public function approve(Request $request, $id)
    {
        abort_if($this->canManageAll(), 403);

        $lecturerId = auth()->id();
        
        $log = FinalProjectGuidanceLog::when(!$this->canManageAll(), function ($q) use ($lecturerId) {
            $q->where('supervisor_id', $lecturerId);
        })->findOrFail($id);

        $request->validate([
            'supervisor_feedback' => 'nullable|string',
        ]);

        $log->update([
            'status' => 'approved',
            'supervisor_feedback' => $request->supervisor_feedback,
            'approved_at' => now(),
        ]);

        return redirect()->route('admin.final-project.guidance.index')
            ->with('success', 'Log bimbingan berhasil disetujui.');
    }

    public function reject(Request $request, $id)
    {
        abort_if($this->canManageAll(), 403);

        $lecturerId = auth()->id();
        
        $log = FinalProjectGuidanceLog::when(!$this->canManageAll(), function ($q) use ($lecturerId) {
            $q->where('supervisor_id', $lecturerId);
        })->findOrFail($id);

        $request->validate([
            'supervisor_feedback' => 'required|string',
        ]);

        $log->update([
            'status' => 'rejected',
            'supervisor_feedback' => $request->supervisor_feedback,
        ]);

        return redirect()->route('admin.final-project.guidance.index')
            ->with('success', 'Log bimbingan ditolak.');
    }

    public function download($id)
    {
        $lecturerId = auth()->id();
        
        $log = FinalProjectGuidanceLog::when(!$this->canManageAll(), function ($q) use ($lecturerId) {
            $q->where('supervisor_id', $lecturerId);
        })->findOrFail($id);

        if (!$log->file_path || !Storage::disk('public')->exists($log->file_path)) {
            return back()->with('error', 'File tidak ditemukan.');
        }

        return Storage::disk('public')->download($log->file_path);
    }
}
