<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use App\Services\SiakadAcademicPeriodService;
use Illuminate\Http\Request;

class AkademikAcademicPeriodController extends Controller
{
    /**
     * Tampilkan halaman detail Tahun Akademik & Semester dari SIAKAD
     */
    public function index(Request $request)
    {
        $activePeriod = SiakadAcademicPeriodService::getActivePeriod();
        $academicPeriods = SiakadAcademicPeriodService::getAllPeriods();

        return view('akademik.tahun-akademik.index', compact(
            'activePeriod',
            'academicPeriods'
        ));
    }

    /**
     * Paksa refresh sinkronisasi data dari REST API SIAKAD (bypass cache)
     */
    public function sync(Request $request)
    {
        $activePeriod = SiakadAcademicPeriodService::getActivePeriod(true);
        $academicPeriods = SiakadAcademicPeriodService::getAllPeriods(true);

        return redirect()
            ->back()
            ->with('success', 'Data Tahun Akademik & Semester berhasil disinkronkan ulang secara langsung dari REST API SIAKAD.');
    }
}
