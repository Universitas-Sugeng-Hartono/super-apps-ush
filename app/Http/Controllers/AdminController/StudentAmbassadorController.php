<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Services\SiakadSyncService;
use App\Services\PokemaEvaluatorService;

class StudentAmbassadorController extends Controller
{
    /**
     * Tampilkan daftar Mahasiswa & Student Ambassador
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $programStudi = $request->input('program_studi');
        $statusFilter = $request->input('status');
        $angkatanFilter = $request->input('angkatan');
        $studyPrograms = StudyProgram::where('is_active', true)->orderBy('order')->get();

        $availableAngkatan = Student::whereNotNull('angkatan')
            ->where('angkatan', '>', 0)
            ->distinct()
            ->orderBy('angkatan', 'desc')
            ->pluck('angkatan');

        if ($availableAngkatan->isEmpty()) {
            $availableAngkatan = collect([2026, 2025, 2024, 2023]);
        }

        $students = Student::with(['dosenPA'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nim', 'like', "%{$search}%")
                        ->orWhere('angkatan', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($programStudi, function ($query, $programStudi) {
                $query->where('program_studi', $programStudi);
            })
            ->when($statusFilter, function ($query, $statusFilter) {
                if ($statusFilter === 'studentambassador') {
                    $query->where('status', 'studentambassador');
                } elseif ($statusFilter === 'mahasiswa') {
                    $query->where(function ($q) {
                        $q->where('status', 'mahasiswa')
                          ->orWhereNull('status')
                          ->orWhere('status', '');
                    });
                }
            })
            ->when($angkatanFilter, function ($query, $angkatanFilter) {
                $query->where('angkatan', $angkatanFilter);
            })
            ->latest()
            ->paginate($programStudi || $statusFilter || $angkatanFilter ? 9999 : 15)
            ->appends([
                'search' => $search,
                'program_studi' => $programStudi,
                'status' => $statusFilter,
                'angkatan' => $angkatanFilter,
            ]);

        $totalAmbassador = Student::where('status', 'studentambassador')->count();
        $totalMahasiswa = Student::where(function ($q) {
            $q->where('status', 'mahasiswa')
              ->orWhereNull('status')
              ->orWhere('status', '');
        })->count();

        return view('kemahasiswaan.student-ambassador.index', compact(
            'students',
            'search',
            'programStudi',
            'statusFilter',
            'angkatanFilter',
            'studyPrograms',
            'availableAngkatan',
            'totalAmbassador',
            'totalMahasiswa'
        ));
    }

    /**
     * Form tambah Mahasiswa / Student Ambassador
     */
    public function create()
    {
        $studyPrograms = StudyProgram::where('is_active', true)->orderBy('order')->get();
        $lecturers = User::whereIn('role', ['admin', 'superadmin', 'masteradmin'])
            ->orderBy('name')
            ->get();

        return view('kemahasiswaan.student-ambassador.create', compact('studyPrograms', 'lecturers'));
    }

    /**
     * Simpan data Mahasiswa / Student Ambassador baru
     */
    public function store(Request $request)
    {
        $validPrograms = StudyProgram::where('is_active', true)->pluck('name')->toArray();

        $validator = Validator::make($request->all(), [
            'full_name'     => 'required|string|max:100',
            'nim'           => 'required|string|unique:students,nim|max:12',
            'periode_masuk' => 'required|date_format:Y-m',
            'program_studi' => 'required|string|in:' . implode(',', $validPrograms),
            'status'        => 'required|string|in:mahasiswa,studentambassador',
            'semester'      => 'nullable|integer|min:1|max:14',
            'ipk'           => 'nullable|numeric|min:0|max:4',
            'beasiswa'      => 'nullable|string|max:100',
            'gender'        => 'nullable|in:L,P',
            'address'       => 'nullable|string|max:500',
            'notes'         => 'nullable|string|max:1000',
            'email'         => 'nullable|email|max:100|unique:students,email',
            'phone'         => 'nullable|string|max:15',
            'id_lecturer'   => 'nullable|exists:users,id',
        ], [
            'full_name.required'        => 'Nama Lengkap wajib diisi.',
            'nim.required'              => 'NIM wajib diisi.',
            'nim.unique'                => 'NIM ini sudah terdaftar.',
            'periode_masuk.required'    => 'Periode Masuk wajib diisi.',
            'periode_masuk.date_format' => 'Format Periode Masuk tidak valid.',
            'program_studi.required'    => 'Program Studi harus dipilih.',
            'status.required'           => 'Status harus dipilih (Mahasiswa atau Student Ambassador).',
            'semester.integer'          => 'Semester harus berupa angka bulat.',
            'semester.min'              => 'Semester minimal 1.',
            'semester.max'              => 'Semester maksimal 14.',
            'ipk.numeric'               => 'IPK harus berupa angka.',
            'ipk.min'                   => 'IPK minimal 0.00.',
            'ipk.max'                   => 'IPK maksimal 4.00.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $lecturerId = $request->id_lecturer ?: (User::whereIn('role', ['admin', 'superadmin', 'masteradmin'])->value('id') ?? 1);
        $beasiswa = $request->beasiswa;
        if (filled($beasiswa)) {
            $beasiswa = trim((string) $beasiswa);
            if (is_numeric($beasiswa)) {
                $beasiswa = $beasiswa . '%';
            } elseif (!str_ends_with($beasiswa, '%') && preg_match('/^\d+(\.\d+)?$/', $beasiswa)) {
                $beasiswa = $beasiswa . '%';
            }
        } else {
            $beasiswa = null;
        }

        Student::create([
            'id_lecturer'      => $lecturerId,
            'nama_lengkap'     => $request->full_name,
            'nim'              => $request->nim,
            'password'         => Hash::make('12345678'),
            'angkatan'         => (int) substr($request->periode_masuk, 0, 4),
            'program_studi'    => $request->program_studi,
            'status'           => $request->status,
            'semester'         => $request->filled('semester') ? (int) $request->semester : null,
            'ipk'              => $request->filled('ipk') ? $request->ipk : null,
            'beasiswa'         => $beasiswa,
            'status_mahasiswa' => 'Aktif',
            'email'            => $request->email,
            'no_telepon'       => $request->phone,
            'notes'            => $request->notes,
            'jenis_kelamin'    => $request->gender ?? 'L',
            'alamat'           => $request->address,
            'is_edited'        => 1,
            'tanggal_masuk'    => $request->periode_masuk . '-01',
        ]);

        return redirect()->route('kemahasiswaan.student-ambassador.index')
            ->with('success', 'Data ' . ($request->status === 'studentambassador' ? 'Student Ambassador' : 'Mahasiswa') . ' berhasil ditambahkan.');
    }

    /**
     * Detail Mahasiswa / Student Ambassador
     */
    public function show($id)
    {
        $student = Student::with(['dosenPA'])->findOrFail($id);
        return view('kemahasiswaan.student-ambassador.show', compact('student'));
    }

    /**
     * Form edit data Mahasiswa / Student Ambassador
     */
    public function edit($id)
    {
        $student = Student::findOrFail($id);
        $studyPrograms = StudyProgram::where('is_active', true)->orderBy('order')->get();
        $lecturers = User::whereIn('role', ['admin', 'superadmin', 'masteradmin'])
            ->orderBy('name')
            ->get();

        return view('kemahasiswaan.student-ambassador.edit', compact('student', 'studyPrograms', 'lecturers'));
    }

    /**
     * Update data Mahasiswa / Student Ambassador
     */
    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:mahasiswa,studentambassador',
            'notes'  => 'nullable|string|max:1000',
        ], [
            'status.required' => 'Status penetapan wajib dipilih.',
            'status.in'       => 'Pilihan status penetapan tidak valid.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $student->update([
            'status' => $request->status,
            'notes'  => $request->notes,
        ]);

        return redirect()->route('kemahasiswaan.student-ambassador.index')
            ->with('success', "Data {$student->nama_lengkap} berhasil diperbarui (Status Penetapan & Catatan).");
    }

    /**
     * Download template CSV untuk import Mahasiswa / Student Ambassador
     */
    public function downloadImportTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_student_ambassador.csv"',
        ];

        $csv = implode(',', ['nama', 'nim', 'angkatan', 'program_studi', 'status', 'semester', 'ipk', 'beasiswa', 'email', 'password']) . "\n";
        $csv .= implode(',', ['Budi Santoso', '2201234567', '2024', 'Bisnis Digital', 'studentambassador', '5', '3.85', '100%', 'budi@example.com', '']) . "\n";
        $csv .= implode(',', ['Siti Rahma', '2201234568', '2024', 'Ilmu Komputer', 'mahasiswa', '3', '3.70', '50%', 'siti@example.com', '']) . "\n";
        $csv .= implode(',', ['Ahmad Fauzi', '2201234569', '2024', 'Sistem Informasi', 'mahasiswa', '1', '3.50', '0%', 'ahmad@example.com', '']) . "\n";

        // BOM untuk kompatibilitas Excel
        $csv = "\xEF\xBB\xBF" . $csv;

        return response($csv, 200, $headers);
    }

    /**
     * Import data Mahasiswa / Student Ambassador dari file CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $path = $request->file('import_file')->getRealPath();
        if (!$path) {
            return back()->with('error', 'File tidak valid.');
        }

        $handle = fopen($path, 'r');
        if (!$handle) {
            return back()->with('error', 'Gagal membuka file CSV.');
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            return back()->with('error', 'File CSV kosong.');
        }
        $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

        rewind($handle);
        $header = fgetcsv($handle, 0, $delimiter, '"', '\\');
        if (!$header) {
            fclose($handle);
            return back()->with('error', 'Header CSV tidak ditemukan.');
        }

        $header = array_map(fn ($h) => strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $h))), $header);
        $idx = fn (array $keys) => collect($keys)->map(fn ($k) => array_search($k, $header, true))->first(fn ($v) => $v !== false);

        $iNama = $idx(['nama', 'name', 'nama_lengkap', 'full_name']);
        $iNim = $idx(['nim']);
        $iAngkatan = $idx(['angkatan', 'batch', 'periode_masuk']);
        $iProdi = $idx(['program_studi', 'prodi', 'program studi']);
        $iStatus = $idx(['status', 'tipe', 'role_mahasiswa']);
        $iSemester = $idx(['semester', 'sem']);
        $iIpk = $idx(['ipk', 'gpa']);
        $iBeasiswa = $idx(['beasiswa', 'scholarship', 'jenis_beasiswa']);
        $iEmail = $idx(['email']);
        $iPassword = $idx(['password']);

        if ($iNama === null || $iNim === null || $iAngkatan === null || $iProdi === null) {
            fclose($handle);
            return back()->with('error', 'Kolom wajib tidak lengkap. Minimal: nama, nim, angkatan, program_studi.');
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            $rowNo = 1;
            while (($row = fgetcsv($handle, 0, $delimiter, '"', '\\')) !== false) {
                $rowNo++;
                if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                    continue;
                }

                $nama = trim((string) ($row[$iNama] ?? ''));
                $nim = trim((string) ($row[$iNim] ?? ''));
                $angkatanRaw = trim((string) ($row[$iAngkatan] ?? ''));
                $prodi = trim((string) ($row[$iProdi] ?? ''));
                $statusInput = $iStatus !== null ? strtolower(trim((string) ($row[$iStatus] ?? ''))) : 'studentambassador';
                $semRaw = $iSemester !== null ? trim((string) ($row[$iSemester] ?? '')) : '';
                $semVal = (is_numeric($semRaw) && (int)$semRaw >= 1 && (int)$semRaw <= 14) ? (int) $semRaw : null;
                $ipkRaw = $iIpk !== null ? trim((string) ($row[$iIpk] ?? '')) : '';
                $ipkVal = (is_numeric($ipkRaw) && (float)$ipkRaw >= 0 && (float)$ipkRaw <= 4) ? (float) $ipkRaw : null;
                $beasiswaRaw = $iBeasiswa !== null ? trim((string) ($row[$iBeasiswa] ?? '')) : '';
                $beasiswa = null;
                if ($beasiswaRaw !== '') {
                    if (is_numeric($beasiswaRaw)) {
                        $beasiswa = $beasiswaRaw . '%';
                    } elseif (!str_ends_with($beasiswaRaw, '%') && preg_match('/^\d+(\.\d+)?$/', $beasiswaRaw)) {
                        $beasiswa = $beasiswaRaw . '%';
                    } else {
                        $beasiswa = $beasiswaRaw;
                    }
                }
                $email = $iEmail !== null ? trim((string) ($row[$iEmail] ?? '')) : null;
                $passwordPlain = $iPassword !== null ? trim((string) ($row[$iPassword] ?? '')) : '';

                if ($nama === '' || $nim === '' || $angkatanRaw === '' || $prodi === '') {
                    $skipped++;
                    $errors[] = "Baris {$rowNo}: Data wajib tidak lengkap.";
                    continue;
                }

                // Normalisasi status
                $status = in_array($statusInput, ['studentambassador', 'ambassador', 'sa'], true) ? 'studentambassador' : 'mahasiswa';

                // Normalisasi angkatan / tanggal_masuk
                $angkatan = (int) substr($angkatanRaw, 0, 4);
                if ($angkatan < 2000 || $angkatan > 2099) {
                    $skipped++;
                    $errors[] = "Baris {$rowNo}: Format angkatan/tahun tidak valid ({$angkatanRaw}).";
                    continue;
                }
                $tanggalMasuk = "{$angkatan}-09-01";

                $lecturerId = User::where('role', 'admin')
                    ->where('program_studi', $prodi)
                    ->value('id')
                    ?? User::whereIn('role', ['admin', 'superadmin', 'masteradmin'])->value('id')
                    ?? (int) auth()->id();

                $student = Student::where('nim', $nim)->first();

                if ($student) {
                    $updateData = [
                        'nama_lengkap' => $nama,
                        'angkatan' => $angkatan,
                        'tanggal_masuk' => $tanggalMasuk,
                        'program_studi' => $prodi,
                        'status' => $status,
                        'email' => $email ?: $student->email,
                    ];
                    if ($beasiswa !== null) {
                        $updateData['beasiswa'] = $beasiswa;
                    }
                    if ($semVal !== null) {
                        $updateData['semester'] = $semVal;
                    }
                    if ($ipkVal !== null) {
                        $updateData['ipk'] = $ipkVal;
                    }
                    if ($passwordPlain !== '') {
                        $updateData['password'] = Hash::make($passwordPlain);
                    }
                    $student->update($updateData);
                    $updated++;
                } else {
                    Student::create([
                        'id_lecturer' => $lecturerId,
                        'nama_lengkap' => $nama,
                        'nim' => $nim,
                        'password' => Hash::make($passwordPlain !== '' ? $passwordPlain : '12345678'),
                        'angkatan' => $angkatan,
                        'program_studi' => $prodi,
                        'status' => $status,
                        'semester' => $semVal,
                        'ipk' => $ipkVal,
                        'beasiswa' => $beasiswa ?: null,
                        'status_mahasiswa' => 'Aktif',
                        'email' => $email ?: null,
                        'jenis_kelamin' => 'L',
                        'is_edited' => 1,
                        'tanggal_masuk' => $tanggalMasuk,
                    ]);
                    $created++;
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);
            Log::error('Student Ambassador import failed: ' . $e->getMessage());
            return back()->with('error', 'Gagal memproses import: ' . $e->getMessage());
        }

        fclose($handle);

        $msg = "Import selesai. Berhasil ditambahkan: {$created}, Diperbarui: {$updated}, Dilewati: {$skipped}.";
        if (count($errors) > 0) {
            $msg .= ' Catatan: ' . implode(' ', array_slice($errors, 0, 5));
        }

        return redirect()->route('kemahasiswaan.student-ambassador.index')->with('success', $msg);
    }

    /**
     * Preview ringkasan data dari Siakad (Rekomendasi Beasiswa Termin) sebelum sinkronisasi
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
     * Sinkronisasi data mahasiswa baru dari Siakad (Menu Rekomendasi Beasiswa & Mahasiswa Reguler)
     * Hanya memasukkan mahasiswa baru yang BELUM terdaftar di SuperApps (tidak menimpa/mengupdate data mahasiswa yang sudah ada).
     */
    public function syncFromSiakad(Request $request, SiakadSyncService $syncService)
    {
        try {
            $result = $syncService->syncNewStudents();

            if ($result['total'] === 0) {
                return redirect()->route('kemahasiswaan.student-ambassador.index')
                    ->with('error', 'Tidak ada data mahasiswa di Siakad yang dapat ditarik.');
            }

            if ($result['created'] === 0) {
                return redirect()->route('kemahasiswaan.student-ambassador.index')
                    ->with('success', "Seluruh data mahasiswa dari Siakad ({$result['total']} mahasiswa) sudah terdaftar di SuperApps. Tidak ada data mahasiswa baru yang perlu ditarik.");
            }

            $detailBeasiswa = $result['created_beasiswa'] > 0 ? "{$result['created_beasiswa']} Beasiswa / Student Ambassador" : null;
            $detailReguler = $result['created_reguler'] > 0 ? "{$result['created_reguler']} Mahasiswa Reguler" : null;
            $parts = array_filter([$detailBeasiswa, $detailReguler]);
            $breakdown = !empty($parts) ? ' (' . implode(', ', $parts) . ')' : '';

            return redirect()->route('kemahasiswaan.student-ambassador.index')
                ->with('success', "Berhasil menarik {$result['created']} mahasiswa baru dari SIAKAD{$breakdown}. Data mahasiswa yang sudah terdaftar tetap aman dan tidak diubah.");
        } catch (\Throwable $e) {
            Log::error('Siakad sync failed: ' . $e->getMessage());
            return redirect()->route('kemahasiswaan.student-ambassador.index')
                ->with('error', 'Gagal menarik data mahasiswa dari Siakad: ' . $e->getMessage());
        }
    }

    /**
     * Refresh data akademik (Semester & IPK) dari Siakad khusus untuk mahasiswa penerima beasiswa / Student Ambassador
     * Hanya memperbarui Semester, IPK, dan riwayatnya tanpa menambah mahasiswa baru.
     */
    public function refreshAcademicData(Request $request, SiakadSyncService $syncService)
    {
        try {
            $result = $syncService->refreshScholarshipAcademicData();

            if ($result['total'] === 0) {
                return redirect()->route('kemahasiswaan.student-ambassador.index')
                    ->with('error', 'Tidak ada data mahasiswa penerima beasiswa yang terdaftar untuk diperbarui.');
            }

            return redirect()->route('kemahasiswaan.student-ambassador.index')
                ->with('success', "Berhasil memperbarui Semester & Nilai IPK untuk {$result['updated']} mahasiswa penerima beasiswa langsung dari SIAKAD!");
        } catch (\Throwable $e) {
            Log::error('Siakad refresh academic data failed: ' . $e->getMessage());
            return redirect()->route('kemahasiswaan.student-ambassador.index')
                ->with('error', 'Gagal memperbarui Semester & IPK dari Siakad: ' . $e->getMessage());
        }
    }

    /**
     * Sinkronisasi data 1 mahasiswa langsung dari Siakad berdasarkan NIM
     */
    public function syncSingleStudent(Request $request, $id, SiakadSyncService $syncService)
    {
        $student = Student::findOrFail($id);
        try {
            $updated = $syncService->syncSingleStudent($student);
            if ($updated) {
                $student->refresh();
                $semInfo = $student->semester ? "Semester {$student->semester}" : "-";
                $ipkInfo = $student->ipk !== null ? "IPK " . number_format((float)$student->ipk, 2) : "IPK -";
                return back()->with('success', "Data {$student->nama_lengkap} (NIM: {$student->nim}) berhasil diperbarui langsung dari Siakad! ({$semInfo}, {$ipkInfo}).");
            }

            return back()->with('error', "NIM {$student->nim} tidak ditemukan dalam daftar rekomendasi beasiswa termin di Siakad.");
        } catch (\Throwable $e) {
            Log::error("Single sync failed for student #{$id}: " . $e->getMessage());
            return back()->with('error', 'Gagal menyinkronkan data dari Siakad: ' . $e->getMessage());
        }
    }

    /**
     * Kirim rekomendasi beasiswa 1 mahasiswa ke Siakad (menu rekomendasi-beasiswa-termin)
     */
    public function sendRecommendation(Request $request, $id, SiakadSyncService $syncService)
    {
        $student = Student::findOrFail($id);

        if (empty($student->nim) || $student->nim === '-') {
            return back()->with('error', "Mahasiswa {$student->nama_lengkap} belum memiliki NIM.");
        }

        $eval = PokemaEvaluatorService::evaluate($student);
        $persentase = $request->input('beasiswa') 
            ?: ($eval['recommended_beasiswa'] ?? ($student->beasiswa ?: '0%'));

        $catatan = $request->input('notes') 
            ?: ("Rekomendasi POKEMA SuperApps: " . ($eval['reason'] ?? "Hasil evaluasi capaian beasiswa {$persentase}"));

        try {
            $res = $syncService->sendRecommendation($student, $persentase, $catatan, 'draft');
            return back()->with('success', "Rekomendasi beasiswa ({$persentase}) untuk {$student->nama_lengkap} (NIM: {$student->nim}) berhasil dikirim ke kolom Rekomendasi di SIAKAD!");
        } catch (\Throwable $e) {
            return back()->with('error', "Gagal mengirim rekomendasi ke SIAKAD: " . $e->getMessage());
        }
    }

    /**
     * Kirim rekomendasi beasiswa massal ke Siakad
     */
    public function sendAllRecommendations(Request $request, SiakadSyncService $syncService)
    {
        $students = Student::where('angkatan', '>=', 2026)
            ->whereNotNull('nim')
            ->where('nim', '!=', '-')
            ->where(function ($q) {
                $q->where('status', 'studentambassador')
                    ->orWhereNotNull('beasiswa');
            })
            ->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'Tidak ada data mahasiswa angkatan 2026+ yang memenuhi kriteria untuk dikirim ke Siakad.');
        }

        $items = [];
        foreach ($students as $stu) {
            $eval = PokemaEvaluatorService::evaluate($stu);
            $recPct = $eval['recommended_beasiswa'] ?? ($stu->beasiswa ?: '0%');
            $catatan = "Rekomendasi POKEMA SuperApps: " . ($eval['reason'] ?? "Beasiswa {$recPct}");

            $items[] = [
                'nim'        => $stu->nim,
                'persentase' => $recPct,
                'catatan'    => $catatan,
                'status'     => 'draft',
            ];
        }

        try {
            $res = $syncService->sendBulkRecommendations($items);
            $successCount = $res['success_count'] ?? count($items);
            return back()->with('success', "Berhasil mengirim {$successCount} rekomendasi beasiswa ke SIAKAD (Menu Rekomendasi Beasiswa Termin). Rekomendasi kini dapat ditinjau oleh Bagian Akademik & Keuangan di Siakad.");
        } catch (\Throwable $e) {
            return back()->with('error', "Gagal mengirim rekomendasi massal ke SIAKAD: " . $e->getMessage());
        }
    }
}
