<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Announcement;

class DashboardController extends Controller
{
    /**
     * Dashboard lama (personal)
     */
    public function index()
    {
        return view('admin/dashboard/index', [
            'menu' => 'Dashboard',
            'students' => Student::where('program_studi', auth()->user()->program_studi)->count(),
        ]);
    }

    /**
     * Dashboard SuperApp
     */
    public function dashboard(Request $request)
    {
        $user = auth()->user();
        $userRole = User::normalizeRole($user->role);

        if ($user->canSwitchDashboardRole() && $user->activeDashboardRole() === 'akademik') {
            return redirect()->route('akademik.dashboard');
        }

        // Dashboard khusus role Kemahasiswaan - redirect ke dedicated route
        if ($userRole === 'kemahasiswaan') {
            return redirect()->route('kemahasiswaan.dashboard');
        }

        // Dashboard khusus role Akademik - bukan dashboard dosen PA
        if ($userRole === 'akademik') {
            return redirect()->route('akademik.dashboard');
        }

        // Dashboard khusus role Keuangan - redirect ke dedicated route
        if ($userRole === 'keuangan') {
            return redirect()->route('keuangan.dashboard');
        }

        // Data untuk Chart: Mahasiswa per Angkatan
        $studentsByBatch = Student::where('id_lecturer', $user->id)
            ->select('angkatan', DB::raw('count(*) as total'))
            ->groupBy('angkatan')
            ->orderBy('angkatan', 'asc')
            ->get();

        $batchLabels = $studentsByBatch->pluck('angkatan')->toArray();
        $batchData = $studentsByBatch->pluck('total')->toArray();

        // Data untuk Chart: Mahasiswa per Prodi/Jurusan
        $studentsByProdi = Student::where('id_lecturer', $user->id)
            ->select('program_studi', DB::raw('count(*) as total'))
            ->groupBy('program_studi')
            ->orderBy('total', 'desc')
            ->get();

        $prodiLabels = $studentsByProdi->pluck('program_studi')->toArray();
        $prodiData = $studentsByProdi->pluck('total')->toArray();

        // Data untuk Chart: IPK Rata-rata per Angkatan
        $ipkByBatch = Student::where('id_lecturer', $user->id)
            ->whereNotNull('ipk')
            ->select('angkatan', DB::raw('AVG(ipk) as avg_ipk'))
            ->groupBy('angkatan')
            ->orderBy('angkatan', 'asc')
            ->get();

        $ipkLabels = $ipkByBatch->pluck('angkatan')->toArray();
        $ipkData = $ipkByBatch->pluck('avg_ipk')->map(function ($ipk) {
            return round($ipk, 2);
        })->toArray();

        // Load menu dinamis dari database
        $userRole = $user->role;
        $menus = \App\Models\MenuItem::active()
            ->forRole($userRole)
            ->ordered()
            ->get()
            ->map(function ($menu) {
                $menu->menu_url = $menu->full_url;
                return $menu;
            });

        // Role dosen/admin: batasi menu Final Project hanya Log Bimbingan + Log Dokumen (view-only).
        $normalizedRole = User::normalizeRole($userRole);
        if ($normalizedRole === 'admin') {
            $menus = $menus->filter(function ($menu) {
                $routeName = (string) ($menu->route_name ?? '');
                $url = (string) ($menu->menu_url ?? '');

                $isFinalProject = str_starts_with($routeName, 'admin.final-project.')
                    || str_contains($url, '/admin/final-project');

                if (!$isFinalProject) {
                    return true;
                }

                return $routeName === 'admin.final-project.guidance.index'
                    || $routeName === 'admin.final-project.documents.index'
                    || str_contains($url, '/admin/final-project/guidance')
                    || str_contains($url, '/admin/final-project/documents');
            })->values();

            // Hard-map menu dinamis agar klik selalu mengarah ke halaman yang tepat.
            $menus = $menus->map(function ($menu) {
                $routeName = (string) ($menu->route_name ?? '');
                $url = (string) ($menu->menu_url ?? '');

                if (
                    $routeName === 'admin.final-project.documents.index'
                    || str_contains($url, '/admin/final-project/documents')
                ) {
                    $menu->menu_url = route('admin.final-project.documents.index');
                } elseif (
                    $routeName === 'admin.final-project.guidance.index'
                    || str_contains($url, '/admin/final-project/guidance')
                ) {
                    $menu->menu_url = route('admin.final-project.guidance.index');
                }

                return $menu;
            })->values();

            $hasGuidanceMenu = $menus->contains(function ($menu) {
                $routeName = (string) ($menu->route_name ?? '');
                $url = (string) ($menu->menu_url ?? '');
                return $routeName === 'admin.final-project.guidance.index'
                    || str_contains($url, '/admin/final-project/guidance');
            });

            $hasDocumentMenu = $menus->contains(function ($menu) {
                $routeName = (string) ($menu->route_name ?? '');
                $url = (string) ($menu->menu_url ?? '');
                return $routeName === 'admin.final-project.documents.index'
                    || str_contains($url, '/admin/final-project/documents');
            });

            if (!$hasGuidanceMenu) {
                $fallback = new \stdClass();
                $fallback->name = 'Log Bimbingan TA';
                $fallback->description = 'Review dan persetujuan log bimbingan mahasiswa';
                $fallback->icon = 'bi bi-journal-check';
                $fallback->badge_text = 'Aktif';
                $fallback->badge_color = 'active';
                $fallback->menu_url = route('admin.final-project.guidance.index');
                $fallback->target = '_self';
                $menus->push($fallback);
            }

            if (!$hasDocumentMenu) {
                $fallback = new \stdClass();
                $fallback->name = 'Log Dokumen TA';
                $fallback->description = 'Lihat dokumen tugas akhir mahasiswa bimbingan';
                $fallback->icon = 'bi bi-folder-check';
                $fallback->badge_text = 'Aktif';
                $fallback->badge_color = 'active';
                $fallback->menu_url = route('admin.final-project.documents.index');
                $fallback->target = '_self';
                $menus->push($fallback);
            }
        }

        $announcements = collect();
        if (Schema::hasTable('announcements')) {
            $announcements = Announcement::query()
                ->published()
                ->orderByDesc('published_at')
                ->limit(3)
                ->get();
        }

        return view('admin.dashboard.super-app-home', compact(
            'user',
            'batchLabels',
            'batchData',
            'studentsByProdi',
            'prodiLabels',
            'prodiData',
            'ipkLabels',
            'ipkData',
            'menus',
            'announcements'
        ));
    }

    /**
     * Dashboard khusus role Kemahasiswaan (/kemahasiswaan/dashboard)
     */
    public function kemahasiswaanDashboard(Request $request)
    {
        $user = auth()->user();
        $pendingCount = \App\Models\StudentAchievement::where('status', 'pending')->count();
        $approvedCount = \App\Models\StudentAchievement::where('status', 'approved')->count();
        $rejectedCount = \App\Models\StudentAchievement::whereIn('status', ['rejected', 'revision'])->count();
        $totalCount = \App\Models\StudentAchievement::count();

        $recentAchievements = \App\Models\StudentAchievement::with('student')
            ->latest()
            ->limit(5)
            ->get();

        // Load menu dinamis dari database untuk role Kemahasiswaan
        $menus = \App\Models\MenuItem::active()
            ->forRole('kemahasiswaan')
            ->ordered()
            ->get()
            ->map(function ($menu) {
                $menu->menu_url = $menu->full_url;
                return $menu;
            });

        return view('kemahasiswaan.dashboard.index', compact(
            'user',
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'totalCount',
            'recentAchievements',
            'menus'
        ));
    }

    /**
     * Dashboard khusus role Akademik (/akademik/dashboard)
     */
    public function akademikDashboard(Request $request)
    {
        $user = auth()->user();

        if ($user->canSwitchDashboardRole() && $user->activeDashboardRole() !== 'akademik') {
            return redirect()->route('admin.dashboard');
        }

        $studentCount = Student::count();
        $activeStudentCount = Student::where('status_mahasiswa', 'Aktif')->count();
        $leaveStudentCount = Student::where('status_mahasiswa', 'Cuti')->count();
        $inactiveStudentCount = Student::where(function ($query) {
            $query->whereNotIn('status_mahasiswa', ['Aktif', 'Cuti'])
                ->orWhereNull('status_mahasiswa');
        })->count();
        $ambassadorCount = Student::where('status', 'studentambassador')->count();
        $scholarshipCount = Student::whereNotNull('beasiswa')
            ->whereNotIn('beasiswa', ['', '0%', '0'])
            ->count();

        $recentStudents = Student::with(['dosenPA'])
            ->latest()
            ->limit(5)
            ->get();

        $prodiCount = \App\Models\StudyProgram::where('is_active', true)->count() ?: Student::whereNotNull('program_studi')->where('program_studi', '!=', '')->distinct()->count('program_studi');
        $lecturerCount = User::where('role', 'admin')->count();

        $announcementCount = 0;
        $recentAnnouncements = collect();
        if (Schema::hasTable('announcements')) {
            $announcementCount = Announcement::query()->published()->count();
            $recentAnnouncements = Announcement::query()
                ->published()
                ->orderByDesc('published_at')
                ->limit(5)
                ->get();
        }

        $menus = \App\Models\MenuItem::active()
            ->forRole('akademik')
            ->ordered()
            ->get()
            ->map(function ($menu) {
                $menu->menu_url = $menu->full_url;
                return $menu;
            });

        $activePeriod = \App\Services\SiakadAcademicPeriodService::getActivePeriod();
        $academicPeriods = \App\Services\SiakadAcademicPeriodService::getAllPeriods();

        return view('akademik.dashboard.index', compact(
            'user',
            'activePeriod',
            'academicPeriods',
            'studentCount',
            'activeStudentCount',
            'leaveStudentCount',
            'inactiveStudentCount',
            'ambassadorCount',
            'scholarshipCount',
            'prodiCount',
            'recentStudents',
            'lecturerCount',
            'announcementCount',
            'recentAnnouncements',
            'menus'
        ));
    }

    /**
     * Dashboard khusus role Keuangan (/keuangan/dashboard)
     */
    public function keuanganDashboard(Request $request)
    {
        $user = auth()->user();
        $pendingCount = \App\Models\SkpiRegistration::where('payment_status', 'pending')->count();
        $approvedCount = \App\Models\SkpiRegistration::where('payment_status', 'approved')->count();
        $rejectedCount = \App\Models\SkpiRegistration::whereIn('payment_status', ['rejected', 'revision'])->count();
        $totalCount = \App\Models\SkpiRegistration::count();

        $search = trim((string) $request->input('search'));
        $status = $request->input('status');
        $studyProgramId = $request->input('study_program_id');

        $registrations = \App\Models\SkpiRegistration::with(['student'])
            ->whereNotNull('doc_pembayaran_wisuda')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nim', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['pending', 'approved', 'revision', 'rejected'], true), function ($query) use ($status) {
                $query->where('payment_status', $status);
            })
            ->when($studyProgramId, function ($query) use ($studyProgramId) {
                $query->whereHas('student', function ($q) use ($studyProgramId) {
                    $studyProgram = \App\Models\StudyProgram::find($studyProgramId);
                    if ($studyProgram) {
                        $q->where('program_studi', $studyProgram->name);
                    }
                });
            })
            ->latest('updated_at')
            ->paginate(15)
            ->appends($request->query());

        $studyPrograms = \App\Models\StudyProgram::all();
        $listRoute = route('keuangan.dashboard');

        return view('keuangan.dashboard.index', compact(
            'user',
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'totalCount',
            'registrations',
            'search',
            'status',
            'studyPrograms',
            'studyProgramId',
            'listRoute'
        ));
    }
}

