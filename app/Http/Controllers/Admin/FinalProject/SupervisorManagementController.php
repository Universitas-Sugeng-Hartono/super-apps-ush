<?php

namespace App\Http\Controllers\Admin\FinalProject;

use App\Http\Controllers\Controller;
use App\Models\FinalProject;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;

class SupervisorManagementController extends Controller
{
    private function roleKey(): ?string
    {
        return User::normalizeRole(auth()->user()?->role);
    }

    private function scopeToProdiIfNeeded($query)
    {
        $role = $this->roleKey();
        if ($role !== 'superadmin') {
            return $query;
        }

        $prodi = auth()->user()?->program_studi;
        if (!$prodi) {
            return $query;
        }

        return $query->whereHas('student', function ($q) use ($prodi) {
            $q->where('program_studi', $prodi);
        });
    }

    private function canManageSupervisors(): bool
    {
        $role = $this->roleKey();
        return in_array($role, ['superadmin', 'masteradmin'], true);
    }

    public function index()
    {
        abort_unless($this->canManageSupervisors(), 403);

        $search = request('search');
        $status = request('status'); // unassigned|assigned|needs_second

        $query = FinalProject::with(['student', 'supervisor1', 'supervisor2'])
            ->whereNotNull('title'); // mahasiswa sudah mengajukan judul

        // Kaprodi: hanya prodi sendiri. Superuser: bisa semua, optional filter prodi dari query string.
        $role = $this->roleKey();
        if ($role === 'superadmin') {
            $query = $this->scopeToProdiIfNeeded($query);
        } elseif ($role === 'masteradmin' && request('prodi')) {
            $prodi = request('prodi');
            $query->whereHas('student', fn ($q) => $q->where('program_studi', $prodi));
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($qs) use ($search) {
                        $qs->where('nama_lengkap', 'like', "%{$search}%")
                           ->orWhere('nim', 'like', "%{$search}%");
                    });
            });
        }

        if ($status === 'unassigned') {
            $query->whereNull('supervisor_1_id');
        } elseif ($status === 'assigned') {
            $query->whereNotNull('supervisor_1_id');
        } elseif ($status === 'needs_second') {
            $query->whereNotNull('supervisor_1_id')->whereNull('supervisor_2_id');
        }

        $finalProjects = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $prodis = collect();
        if ($role === 'masteradmin') {
            $prodis = Student::query()
                ->whereNotNull('program_studi')
                ->distinct()
                ->orderBy('program_studi')
                ->pluck('program_studi');
        }

        // List dosen untuk dropdown, dikelompokkan per prodi
        $lecturerRoles = ['admin', 'superadmin', 'masteradmin', 'dosen', 'kaprodi', 'superuser'];
        $lecturers = User::query()
            ->whereIn('role', $lecturerRoles)
            ->select(['id', 'name', 'program_studi', 'role'])
            ->orderBy('name')
            ->get();

        $lecturersByProdi = $lecturers
            ->groupBy(fn ($u) => $u->program_studi ?: 'Unknown')
            ->map(fn ($items) => $items->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])->values());

        return view('admin.final-project.supervisors.index', compact(
            'finalProjects',
            'search',
            'status',
            'prodis',
            'lecturersByProdi',
            'role'
        ));
    }

    public function edit($id)
    {
        abort_unless($this->canManageSupervisors(), 403);

        $finalProjectQuery = FinalProject::with(['student', 'supervisor1', 'supervisor2']);
        $finalProjectQuery = $this->scopeToProdiIfNeeded($finalProjectQuery);
        $finalProject = $finalProjectQuery->findOrFail($id);

        $studentProdi = data_get($finalProject, 'student.program_studi');
        $lecturerRoles = ['admin', 'superadmin', 'masteradmin', 'dosen', 'kaprodi', 'superuser'];

        $lecturers = User::whereIn('role', $lecturerRoles)
            ->when($studentProdi, function ($q) use ($studentProdi) {
                $q->where('program_studi', $studentProdi);
            })
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.final-project.supervisors.edit', compact('finalProject', 'lecturers'));
    }

    public function update(Request $request, $id)
    {
        abort_unless($this->canManageSupervisors(), 403);

        $request->validate([
            'supervisor_1_id' => 'required|exists:users,id',
            'supervisor_2_id' => 'nullable|exists:users,id|different:supervisor_1_id',
        ]);

        $finalProjectQuery = FinalProject::with('student');
        $finalProjectQuery = $this->scopeToProdiIfNeeded($finalProjectQuery);
        $finalProject = $finalProjectQuery->findOrFail($id);

        $studentProdi = data_get($finalProject, 'student.program_studi');
        if ($studentProdi) {
            $supervisor1 = User::findOrFail($request->supervisor_1_id);
            if ($supervisor1->program_studi && $supervisor1->program_studi !== $studentProdi) {
                return back()->with('error', 'Pembimbing 1 harus dari program studi yang sama dengan mahasiswa.')->withInput();
            }

            if ($request->filled('supervisor_2_id')) {
                $supervisor2 = User::findOrFail($request->supervisor_2_id);
                if ($supervisor2->program_studi && $supervisor2->program_studi !== $studentProdi) {
                    return back()->with('error', 'Pembimbing 2 harus dari program studi yang sama dengan mahasiswa.')->withInput();
                }
            }
        }
        
        $finalProject->update([
            'supervisor_1_id' => $request->supervisor_1_id,
            'supervisor_2_id' => $request->supervisor_2_id,
        ]);

        return redirect()->route('admin.final-project.supervisors.index')
            ->with('success', 'Pembimbing berhasil diupdate.');
    }
}

