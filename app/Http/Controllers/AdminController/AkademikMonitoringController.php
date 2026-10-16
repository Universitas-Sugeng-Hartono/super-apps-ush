<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudyProgram;
use App\Models\User;
use App\Services\PokemaEvaluatorService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AkademikMonitoringController extends Controller
{
    /**
     * Scope query pembantu untuk memfilter hanya mahasiswa beasiswa atau student ambassador
     */
    public function applyScholarshipOrAmbassadorScope($query)
    {
        return $query->where(function ($q) {
            $q->where(function ($sub) {
                $sub->whereNotNull('beasiswa')
                    ->where('beasiswa', '!=', '')
                    ->where('beasiswa', '!=', '0%')
                    ->where('beasiswa', '!=', '0')
                    ->where('beasiswa', '!=', 'Non-Beasiswa');
            })->orWhere('status', 'studentambassador');
        });
    }

    /**
     * Tampilkan halaman Monitoring Mahasiswa Beasiswa & Student Ambassador
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $programStudi = trim((string) $request->input('program_studi'));
        $statusAmbassador = $request->input('status_ambassador');
        $beasiswaFilter = $request->input('beasiswa');
        $statusMahasiswa = $request->input('status_mahasiswa');
        $angkatan = trim((string) $request->input('angkatan'));
        $semester = $request->input('semester');

        // Pastikan hanya mahasiswa beasiswa atau ambassador yang tertampil di menu ini
        $query = Student::query();
        $this->applyScholarshipOrAmbassadorScope($query);

        $query->with([
            'dosenPA',
            'achievements' => function ($q) {
                $q->with('approver')->latest();
            },
            'counselings'
        ])
        ->withSum(['achievements as total_skp_approved' => function ($q) {
            $q->where('status', 'approved');
        }], 'skp_points')
        ->withSum('achievements as total_skp_all', 'skp_points');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nim', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('angkatan', 'like', "%{$search}%");
            });
        }

        if ($programStudi !== '') {
            $query->where('program_studi', $programStudi);
        }

        if ($statusAmbassador === 'studentambassador') {
            $query->where('status', 'studentambassador');
        } elseif ($statusAmbassador === 'mahasiswa' || $statusAmbassador === 'non_ambassador') {
            $query->where(function ($q) {
                $q->where('status', '!=', 'studentambassador')
                    ->orWhereNull('status');
            });
        }

        if ($beasiswaFilter === 'has_scholarship') {
            $query->whereNotNull('beasiswa')->whereNotIn('beasiswa', ['', '0%', '0', 'Non-Beasiswa']);
        } elseif (!empty($beasiswaFilter) && $beasiswaFilter !== 'all') {
            $query->where('beasiswa', $beasiswaFilter);
        }

        if ($statusMahasiswa === 'Aktif') {
            $query->where('status_mahasiswa', 'Aktif');
        } elseif ($statusMahasiswa === 'Cuti') {
            $query->where('status_mahasiswa', 'Cuti');
        } elseif ($statusMahasiswa === 'Tidak Aktif') {
            $query->where(function ($q) {
                $q->whereNotIn('status_mahasiswa', ['Aktif', 'Cuti'])
                    ->orWhereNull('status_mahasiswa');
            });
        }

        if ($angkatan !== '') {
            $query->where('angkatan', $angkatan);
        }

        if ($semester !== null && $semester !== '') {
            $query->where('semester', (int) $semester);
        }

        $students = $query->orderBy('nama_lengkap')
            ->paginate(15)
            ->appends($request->query());

        // Jalankan evaluasi POKEMA cerdas untuk setiap mahasiswa beasiswa/ambassador
        $students->getCollection()->transform(function ($student) {
            $student->pokema_evaluation = PokemaEvaluatorService::evaluate($student);
            return $student;
        });

        // Statistik Keseluruhan (Khusus Mahasiswa Beasiswa & Ambassador yang Dipantau)
        $baseScholarshipAmbassadorCountQuery = Student::query();
        $this->applyScholarshipOrAmbassadorScope($baseScholarshipAmbassadorCountQuery);

        $totalStudents = (clone $baseScholarshipAmbassadorCountQuery)->count();
        $totalAmbassador = Student::where('status', 'studentambassador')->count();
        $totalScholarship = Student::whereNotNull('beasiswa')
            ->whereNotIn('beasiswa', ['', '0%', '0', 'Non-Beasiswa'])
            ->count();

        $avgScholarshipIpk = (clone $baseScholarshipAmbassadorCountQuery)
            ->whereNotNull('ipk')
            ->where('ipk', '>', 0)
            ->avg('ipk');

        $scholarship100Count = Student::where('beasiswa', '100%')->count();
        $scholarship75Count = Student::where('beasiswa', '75%')->count();
        $scholarship50Count = Student::where('beasiswa', '50%')->count();
        $scholarship25Count = Student::where('beasiswa', '25%')->count();

        // Opsi Program Studi & Angkatan (Hanya dari data mahasiswa terpantau)
        $studyPrograms = StudyProgram::where('is_active', true)->orderBy('order')->get();
        $programOptions = $studyPrograms->pluck('name')
            ->merge(
                Student::query()
                    ->where(fn ($q) => $this->applyScholarshipOrAmbassadorScope($q))
                    ->whereNotNull('program_studi')
                    ->where('program_studi', '!=', '')
                    ->distinct()
                    ->orderBy('program_studi')
                    ->pluck('program_studi')
            )
            ->map(fn ($n) => trim((string) $n))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $angkatanOptions = Student::query()
            ->where(fn ($q) => $this->applyScholarshipOrAmbassadorScope($q))
            ->whereNotNull('angkatan')
            ->where('angkatan', '!=', '')
            ->distinct()
            ->orderByDesc('angkatan')
            ->pluck('angkatan');

        $activePeriod = \App\Services\SiakadAcademicPeriodService::getActivePeriod();
        $academicPeriods = \App\Services\SiakadAcademicPeriodService::getAllPeriods();

        return view('akademik.monitoring-beasiswa.index', compact(
            'students',
            'activePeriod',
            'academicPeriods',
            'search',
            'programStudi',
            'statusAmbassador',
            'beasiswaFilter',
            'statusMahasiswa',
            'angkatan',
            'semester',
            'totalStudents',
            'totalAmbassador',
            'totalScholarship',
            'avgScholarshipIpk',
            'scholarship100Count',
            'scholarship75Count',
            'scholarship50Count',
            'scholarship25Count',
            'programOptions',
            'angkatanOptions'
        ));
    }

    /**
     * Tampilkan rincian detail profil & status beasiswa mahasiswa (Read-only)
     */
    public function show(Request $request, $id)
    {
        $student = Student::with(['dosenPA'])->findOrFail($id);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $student->id,
                    'nama_lengkap' => $student->nama_lengkap,
                    'nim' => $student->nim,
                    'program_studi' => $student->program_studi ?? '-',
                    'angkatan' => $student->angkatan ?? '-',
                    'tanggal_masuk' => $student->tanggal_masuk ? \Carbon\Carbon::parse($student->tanggal_masuk)->translatedFormat('F Y') : '-',
                    'semester' => $student->semester ? 'Semester ' . $student->semester : '-',
                    'ipk' => filled($student->ipk) ? number_format((float) $student->ipk, 2) : 'Belum Ada IPK',
                    'beasiswa' => $student->beasiswa ?: '0%',
                    'status' => $student->status === 'studentambassador' ? 'Student Ambassador' : 'Mahasiswa Reguler',
                    'status_badge' => $student->status === 'studentambassador' ? 'ambassador' : 'mahasiswa',
                    'status_mahasiswa' => $student->status_mahasiswa ?: 'Aktif',
                    'jenis_kelamin' => $student->jenis_kelamin === 'P' ? 'Perempuan' : 'Laki-laki',
                    'dosen_pa' => $student->dosenPA->name ?? ($student->id_lecturer ? 'ID: ' . $student->id_lecturer : '-'),
                    'no_telepon' => $student->no_telepon ?: '-',
                    'email' => $student->email ?: '-',
                    'alamat' => $student->alamat ?: '-',
                    'notes' => $student->notes ?: 'Belum ada catatan.',
                ]
            ]);
        }

        return view('akademik.monitoring-beasiswa.show', compact('student'));
    }

    /**
     * Ambil data evaluasi POKEMA mahasiswa dalam format JSON (bisa spesifik tahun studi)
     */
    public function getStudentEvaluationData(Request $request, $id)
    {
        $student = Student::with(['achievements' => function ($q) {
            $q->where('status', 'approved');
        }, 'counselings'])->findOrFail($id);

        $yearInput = $request->input('year');
        $year = null;
        if ($yearInput === 'total' || $yearInput === '4') {
            $year = 'total';
        } elseif (in_array((string) $yearInput, ['1', '2', '3'], true)) {
            $year = (int) $yearInput;
        }

        $eval = PokemaEvaluatorService::evaluate($student, $year);

        return response()->json([
            'success' => true,
            'evaluation' => $eval,
        ]);
    }

    /**
     * Export rekap data monitoring beasiswa ke CSV
     */
    public function exportCsv(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $programStudi = trim((string) $request->input('program_studi'));
        $statusAmbassador = $request->input('status_ambassador');
        $beasiswaFilter = $request->input('beasiswa');
        $statusMahasiswa = $request->input('status_mahasiswa');
        $angkatan = trim((string) $request->input('angkatan'));
        $semester = $request->input('semester');

        $query = Student::query();
        $this->applyScholarshipOrAmbassadorScope($query);

        $query->with([
            'dosenPA',
            'achievements' => function ($q) {
                $q->with('approver')->latest();
            },
            'counselings'
        ])
        ->withSum(['achievements as total_skp_approved' => function ($q) {
            $q->where('status', 'approved');
        }], 'skp_points');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nim', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($programStudi !== '') {
            $query->where('program_studi', $programStudi);
        }
        if ($statusAmbassador === 'studentambassador') {
            $query->where('status', 'studentambassador');
        } elseif ($statusAmbassador === 'mahasiswa' || $statusAmbassador === 'non_ambassador') {
            $query->where(function ($q) {
                $q->where('status', '!=', 'studentambassador')
                    ->orWhereNull('status');
            });
        }
        if ($beasiswaFilter === 'has_scholarship') {
            $query->whereNotNull('beasiswa')->whereNotIn('beasiswa', ['', '0%', '0', 'Non-Beasiswa']);
        } elseif (!empty($beasiswaFilter) && $beasiswaFilter !== 'all') {
            $query->where('beasiswa', $beasiswaFilter);
        }
        if ($statusMahasiswa === 'Aktif') {
            $query->where('status_mahasiswa', 'Aktif');
        } elseif ($statusMahasiswa === 'Cuti') {
            $query->where('status_mahasiswa', 'Cuti');
        } elseif ($statusMahasiswa === 'Tidak Aktif') {
            $query->where(function ($q) {
                $q->whereNotIn('status_mahasiswa', ['Aktif', 'Cuti'])
                    ->orWhereNull('status_mahasiswa');
            });
        }
        if ($angkatan !== '') {
            $query->where('angkatan', $angkatan);
        }
        if ($semester !== null && $semester !== '') {
            $query->where('semester', (int) $semester);
        }

        $students = $query->orderBy('nama_lengkap')->get();

        $fileName = 'rekap_monitoring_beasiswa_ambassador_' . date('Ymd_His') . '.csv';

        return new StreamedResponse(function () use ($students) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'No',
                'NIM',
                'Nama Lengkap',
                'Program Studi',
                'Angkatan',
                'Semester',
                'IPK',
                'Beasiswa Saat Ini',
                'Status Penetapan',
                'Total SKP Disetujui',
                'Status Evaluasi POKEMA',
                'Rekomendasi Beasiswa',
                'Status Mahasiswa',
                'Dosen PA',
                'No. Telepon',
                'Email',
            ]);

            $index = 1;
            foreach ($students as $s) {
                $eval = PokemaEvaluatorService::evaluate($s);
                $evalStatus = $eval['action_label'] ?? '-';
                $recBeasiswa = $eval['recommended_beasiswa'] ?? ($s->beasiswa ?: '0%');

                fputcsv($handle, [
                    $index++,
                    $s->nim,
                    $s->nama_lengkap,
                    $s->program_studi ?? '-',
                    $s->angkatan ?? '-',
                    $s->semester ?? '-',
                    filled($s->ipk) ? number_format((float) $s->ipk, 2) : '-',
                    $s->beasiswa ?: '0%',
                    $s->status === 'studentambassador' ? 'Student Ambassador' : 'Mahasiswa Beasiswa',
                    (int) ($s->total_skp_approved ?? 0),
                    $evalStatus,
                    $recBeasiswa,
                    $s->status_mahasiswa ?: 'Aktif',
                    $s->dosenPA->name ?? ($s->id_lecturer ? 'ID: ' . $s->id_lecturer : '-'),
                    $s->no_telepon ?: '-',
                    $s->email ?: '-',
                ]);
            }
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
