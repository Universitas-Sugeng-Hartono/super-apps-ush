<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Student;
use App\Models\CardCounseling;
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
           
            'menu'=>'Dashboard',
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
        $ipkData = $ipkByBatch->pluck('avg_ipk')->map(function($ipk) {
            return round($ipk, 2);
        })->toArray();
        
        // Load menu dinamis dari database
        $userRole = $user->role;
        $menus = \App\Models\MenuItem::active()
            ->forRole($userRole)
            ->ordered()
            ->get()
            ->map(function($menu) {
                $menu->menu_url = $menu->full_url;
                return $menu;
            });

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