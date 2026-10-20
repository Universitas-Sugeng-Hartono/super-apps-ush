<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\SiakadSyncService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AkademikStudentDirectoryController extends Controller
{
    /**
     * Tampilkan halaman Direktori Mahasiswa & Monitoring Akademik
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $statusMahasiswa = $request->input('status_mahasiswa');
        $statusAmbassador = $request->input('status_ambassador');
        $beasiswa = $request->input('beasiswa');
        $programStudi = trim((string) $request->input('program_studi'));
        $angkatan = trim((string) $request->input('angkatan'));

        $query = Student::with(['dosenPA']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nim', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
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

        if ($statusAmbassador === 'studentambassador') {
            $query->where('status', 'studentambassador');
        } elseif ($statusAmbassador === 'mahasiswa') {
            $query->where(function ($q) {
                $q->where('status', 'mahasiswa')
                    ->orWhereNull('status')
                    ->orWhere('status', '');
            });
        }

        if ($beasiswa === 'has_scholarship') {
            $query->whereNotNull('beasiswa')->whereNotIn('beasiswa', ['', '0%', '0']);
        } elseif ($beasiswa === '0%') {
            $query->where(function ($q) {
                $q->where('beasiswa', '0%')
                    ->orWhere('beasiswa', '0')
                    ->orWhereNull('beasiswa')
                    ->orWhere('beasiswa', '');
            });
        } elseif (!empty($beasiswa)) {
            $query->where('beasiswa', $beasiswa);
        }

        if ($programStudi !== '') {
            $query->where('program_studi', $programStudi);
        }

        if ($angkatan !== '') {
            $query->where('angkatan', $angkatan);
        }

        $students = $query->orderBy('nama_lengkap')
            ->paginate(15)
            ->appends($request->query());

        // Statistik Direktori
        $totalStudents = Student::count();
        $activeStudentCount = Student::where('status_mahasiswa', 'Aktif')->count();
        $leaveStudentCount = Student::where('status_mahasiswa', 'Cuti')->count();
        $inactiveStudentCount = Student::where(function ($q) {
            $q->whereNotIn('status_mahasiswa', ['Aktif', 'Cuti'])
                ->orWhereNull('status_mahasiswa');
        })->count();
        $ambassadorCount = Student::where('status', 'studentambassador')->count();
        $scholarshipCount = Student::whereNotNull('beasiswa')
            ->whereNotIn('beasiswa', ['', '0%', '0'])
            ->count();

        // Opsi Filter
        $studyPrograms = StudyProgram::where('is_active', true)->orderBy('order')->get();
        $programOptions = $studyPrograms->pluck('name')
            ->merge(
                Student::query()
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
            ->whereNotNull('angkatan')
            ->where('angkatan', '!=', '')
            ->distinct()
            ->orderByDesc('angkatan')
            ->pluck('angkatan');

        return view('akademik.direktori-mahasiswa.index', compact(
            'students',
            'search',
            'statusMahasiswa',
            'statusAmbassador',
            'beasiswa',
            'programStudi',
            'angkatan',
            'totalStudents',
            'activeStudentCount',
            'leaveStudentCount',
            'inactiveStudentCount',
            'ambassadorCount',
            'scholarshipCount',
            'programOptions',
            'angkatanOptions'
        ));
    }

    /**
     * Detail Mahasiswa (Read-only)
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
                    'status_is_ambassador' => $student->status === 'studentambassador',
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

        return view('akademik.direktori-mahasiswa.show', compact('student'));
    }

    /**
     * Export Direktori Mahasiswa ke format CSV
     */
    public function exportCsv(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $statusMahasiswa = $request->input('status_mahasiswa');
        $statusAmbassador = $request->input('status_ambassador');
        $beasiswa = $request->input('beasiswa');
        $programStudi = trim((string) $request->input('program_studi'));
        $angkatan = trim((string) $request->input('angkatan'));

        $query = Student::with(['dosenPA']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nim', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
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
        if ($statusAmbassador === 'studentambassador') {
            $query->where('status', 'studentambassador');
        } elseif ($statusAmbassador === 'mahasiswa') {
            $query->where(function ($q) {
                $q->where('status', 'mahasiswa')
                    ->orWhereNull('status')
                    ->orWhere('status', '');
            });
        }
        if ($beasiswa === 'has_scholarship') {
            $query->whereNotNull('beasiswa')->whereNotIn('beasiswa', ['', '0%', '0']);
        } elseif ($beasiswa === '0%') {
            $query->where(function ($q) {
                $q->where('beasiswa', '0%')
                    ->orWhere('beasiswa', '0')
                    ->orWhereNull('beasiswa')
                    ->orWhere('beasiswa', '');
            });
        } elseif (!empty($beasiswa)) {
            $query->where('beasiswa', $beasiswa);
        }
        if ($programStudi !== '') {
            $query->where('program_studi', $programStudi);
        }
        if ($angkatan !== '') {
            $query->where('angkatan', $angkatan);
        }

        $students = $query->orderBy('nama_lengkap')->get();

        $fileName = 'direktori_mahasiswa_ush_' . date('Ymd_His') . '.csv';

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
                'Beasiswa',
                'Penetapan Status',
                'Status Akademik',
                'Dosen PA',
                'No. Telepon',
                'Email',
            ]);

            $i = 1;
            foreach ($students as $s) {
                fputcsv($handle, [
                    $i++,
                    $s->nim,
                    $s->nama_lengkap,
                    $s->program_studi ?? '-',
                    $s->angkatan ?? '-',
                    $s->semester ?? '-',
                    filled($s->ipk) ? number_format((float) $s->ipk, 2) : '-',
                    $s->beasiswa ?: '0%',
                    $s->status === 'studentambassador' ? 'Student Ambassador' : 'Mahasiswa Reguler',
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

    /**
     * Preview ringkasan data dari Siakad sebelum sinkronisasi
     */
    public function previewSiakadData(Request $request, SiakadSyncService $syncService)
    {
        try {
            $data = $syncService->preview();
            return response()->json(array_merge(['success' => true], $data));
        } catch (\Throwable $e) {
            Log::error('Siakad preview failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal terhubung ke API Siakad: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sinkronisasi data mahasiswa baru dari Siakad
     */
    public function syncFromSiakad(Request $request, SiakadSyncService $syncService)
    {
        try {
            $result = $syncService->syncNewStudents();

            if ($result['total'] === 0) {
                return redirect()->route('akademik.direktori-mahasiswa.index')
                    ->with('error', 'Tidak ada data mahasiswa di Siakad yang dapat ditarik.');
            }

            if ($result['created'] === 0) {
                return redirect()->route('akademik.direktori-mahasiswa.index')
                    ->with('success', "Seluruh data mahasiswa dari Siakad ({$result['total']} mahasiswa) sudah terdaftar di SuperApps. Tidak ada data mahasiswa baru yang perlu ditarik.");
            }

            $detailBeasiswa = $result['created_beasiswa'] > 0 ? "{$result['created_beasiswa']} Beasiswa / Student Ambassador" : null;
            $detailReguler = $result['created_reguler'] > 0 ? "{$result['created_reguler']} Mahasiswa Reguler" : null;
            $parts = array_filter([$detailBeasiswa, $detailReguler]);
            $breakdown = !empty($parts) ? ' (' . implode(', ', $parts) . ')' : '';

            return redirect()->route('akademik.direktori-mahasiswa.index')
                ->with('success', "Berhasil menarik {$result['created']} mahasiswa baru dari SIAKAD{$breakdown}. Periode Masuk & Tanggal Masuk otomatis disesuaikan.");
        } catch (\Throwable $e) {
            Log::error('Siakad sync failed: ' . $e->getMessage());
            return redirect()->route('akademik.direktori-mahasiswa.index')
                ->with('error', 'Gagal menarik data mahasiswa dari Siakad: ' . $e->getMessage());
        }
    }

    /**
     * Refresh data akademik (Semester, IPK & Periode Masuk) dari Siakad
     */
    public function refreshAcademicData(Request $request, SiakadSyncService $syncService)
    {
        try {
            $result = $syncService->refreshScholarshipAcademicData(false);

            if ($result['total'] === 0) {
                return redirect()->route('akademik.direktori-mahasiswa.index')
                    ->with('error', 'Tidak ada data mahasiswa yang terdaftar untuk diperbarui.');
            }

            return redirect()->route('akademik.direktori-mahasiswa.index')
                ->with('success', "Berhasil memperbarui data akademik (Semester, Nilai IPK & Periode Masuk) untuk {$result['updated']} mahasiswa (Beasiswa & Reguler) langsung dari SIAKAD!");
        } catch (\Throwable $e) {
            Log::error('Siakad refresh academic data failed: ' . $e->getMessage());
            return redirect()->route('akademik.direktori-mahasiswa.index')
                ->with('error', 'Gagal memperbarui Semester & IPK dari Siakad: ' . $e->getMessage());
        }
    }
}
