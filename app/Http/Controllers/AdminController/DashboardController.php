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
}
