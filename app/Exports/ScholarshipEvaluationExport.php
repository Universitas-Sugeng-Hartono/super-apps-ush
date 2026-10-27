<?php

namespace App\Exports;

use App\Models\AppSetting;
use App\Models\Student;
use App\Services\PokemaEvaluatorService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ScholarshipEvaluationExport implements FromView, ShouldAutoSize, WithStyles, WithTitle
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function title(): string
    {
        return 'Laporan Beasiswa';
    }

    public function view(): View
    {
        $search = trim((string) $this->request->input('search'));
        $programStudi = $this->request->input('program_studi');
        $angkatan = $this->request->input('angkatan');
        $statusMahasiswa = $this->request->input('status_mahasiswa');
        $beasiswaFilter = $this->request->input('beasiswa');

        $query = Student::query();

        // Target: Mahasiswa Penerima Beasiswa & Student Ambassador
        $query->where(function ($q) {
            $q->where(function ($sub) {
                $sub->whereNotNull('beasiswa')
                    ->where('beasiswa', '!=', '')
                    ->where('beasiswa', '!=', '0%')
                    ->where('beasiswa', '!=', '0')
                    ->where('beasiswa', '!=', 'Non-Beasiswa');
            })->orWhere('status', 'studentambassador');
        });

        // Filter Pencarian
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nim', 'like', "%{$search}%")
                    ->orWhere('program_studi', 'like', "%{$search}%")
                    ->orWhere('beasiswa', 'like', "%{$search}%");
            });
        }

        // Filter Program Studi
        if (filled($programStudi)) {
            $query->where('program_studi', $programStudi);
        }

        // Filter Angkatan
        if (filled($angkatan)) {
            $query->where('angkatan', $angkatan);
        }

        // Filter Status Mahasiswa (Student Ambassador vs Mahasiswa)
        if (filled($statusMahasiswa)) {
            if ($statusMahasiswa === 'studentambassador') {
                $query->where('status', 'studentambassador');
            } elseif ($statusMahasiswa === 'mahasiswa') {
                $query->where(function ($q) {
                    $q->where('status', 'mahasiswa')
                        ->orWhereNull('status')
                        ->orWhere('status', '');
                });
            }
        }

        // Filter Beasiswa
        if (filled($beasiswaFilter)) {
            if ($beasiswaFilter === 'none') {
                $query->where(function ($q) {
                    $q->whereNull('beasiswa')
                        ->orWhere('beasiswa', '')
                        ->orWhere('beasiswa', '0%')
                        ->orWhere('beasiswa', '0')
                        ->orWhere('beasiswa', 'Non-Beasiswa');
                });
            } elseif ($beasiswaFilter === 'has_beasiswa') {
                $query->whereNotNull('beasiswa')
                    ->where('beasiswa', '!=', '')
                    ->where('beasiswa', '!=', '0%')
                    ->where('beasiswa', '!=', '0')
                    ->where('beasiswa', '!=', 'Non-Beasiswa');
            } else {
                $query->where('beasiswa', $beasiswaFilter);
            }
        }

        $students = $query->with(['achievements' => function ($q) {
                $q->where('status', 'approved');
            }, 'counselings'])
            ->orderBy('angkatan', 'desc')
            ->orderBy('program_studi', 'asc')
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        $students->transform(function ($student) {
            $student->pokema_evaluation = PokemaEvaluatorService::evaluate($student);
            return $student;
        });

        $exportDate = Carbon::now()->translatedFormat('d F Y H:i');
        $isPortalOpen = AppSetting::isBeasiswaUploadOpen();

        $activeFilters = [];
        if (filled($angkatan)) $activeFilters[] = "Angkatan: {$angkatan}";
        if (filled($programStudi)) $activeFilters[] = "Prodi: {$programStudi}";
        if (filled($beasiswaFilter)) $activeFilters[] = "Beasiswa: {$beasiswaFilter}";
        if (filled($search)) $activeFilters[] = "Pencarian: '{$search}'";
        $filterDescription = !empty($activeFilters) ? implode(' | ', $activeFilters) : 'Semua Penerima Beasiswa & Student Ambassador';

        return view('kemahasiswaan.verifikasi-prestasi.exports.scholarship_report', [
            'students'          => $students,
            'exportDate'        => $exportDate,
            'isPortalOpen'      => $isPortalOpen,
            'filterDescription' => $filterDescription,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'size' => 11]],
            3 => ['font' => ['italic' => true, 'size' => 10]],
            5 => ['font' => ['bold' => true]],
            6 => ['font' => ['bold' => true]],
        ];
    }
}
