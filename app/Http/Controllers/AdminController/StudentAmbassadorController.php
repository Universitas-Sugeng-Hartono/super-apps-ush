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
        $studyPrograms = StudyProgram::where('is_active', true)->orderBy('order')->get();

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
            ->latest()
            ->paginate($programStudi || $statusFilter ? 9999 : 15)
            ->appends(['search' => $search, 'program_studi' => $programStudi, 'status' => $statusFilter]);

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
            'studyPrograms',
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
        $validPrograms = StudyProgram::where('is_active', true)->pluck('name')->toArray();

        $validator = Validator::make($request->all(), [
            'full_name'     => 'required|string|max:100',
            'nim'           => 'required|string|max:12|unique:students,nim,' . $student->id,
            'periode_masuk' => 'required|date_format:Y-m',
            'program_studi' => 'required|string|in:' . implode(',', $validPrograms),
            'status'        => 'required|string|in:mahasiswa,studentambassador',
            'ipk'           => 'nullable|numeric|min:0|max:4',
            'beasiswa'      => 'nullable|string|max:100',
            'gender'        => 'nullable|in:L,P',
            'address'       => 'nullable|string|max:500',
            'notes'         => 'nullable|string|max:1000',
            'email'         => 'nullable|email|max:100|unique:students,email,' . $student->id,
            'phone'         => 'nullable|string|max:15',
            'id_lecturer'   => 'nullable|exists:users,id',
        ], [
            'ipk.numeric'   => 'IPK harus berupa angka.',
            'ipk.min'       => 'IPK minimal 0.00.',
            'ipk.max'       => 'IPK maksimal 4.00.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

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

        $student->update([
            'nama_lengkap'     => $request->full_name,
            'nim'              => $request->nim,
            'angkatan'         => (int) substr($request->periode_masuk, 0, 4),
            'program_studi'    => $request->program_studi,
            'status'           => $request->status,
            'ipk'              => $request->filled('ipk') ? $request->ipk : $student->ipk,
            'beasiswa'         => $beasiswa,
            'email'            => $request->email,
            'no_telepon'       => $request->phone,
            'notes'            => $request->notes,
            'jenis_kelamin'    => $request->gender ?? $student->jenis_kelamin,
            'alamat'           => $request->address,
            'id_lecturer'      => $request->id_lecturer ?: $student->id_lecturer,
            'tanggal_masuk'    => $request->periode_masuk . '-01',
        ]);

        return redirect()->route('kemahasiswaan.student-ambassador.index')
            ->with('success', 'Data berhasil diperbarui.');
    }

    /**
     * Hapus data
     */
    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return redirect()->route('kemahasiswaan.student-ambassador.index')
            ->with('success', 'Data berhasil dihapus.');
    }

    /**
     * Reset password ke default (12345678)
     */
    public function resetPassword($id)
    {
        $student = Student::findOrFail($id);
        $student->password = Hash::make('12345678');
        $student->save();

        return redirect()->back()->with('success', 'Password mahasiswa/ambassador berhasil direset ke 12345678.');
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

        $csv = implode(',', ['nama', 'nim', 'angkatan', 'program_studi', 'status', 'ipk', 'beasiswa', 'email', 'password']) . "\n";
        $csv .= implode(',', ['Budi Santoso', '2201234567', '2024', 'Bisnis Digital', 'studentambassador', '3.85', '100%', 'budi@example.com', '']) . "\n";
        $csv .= implode(',', ['Siti Rahma', '2201234568', '2024', 'Ilmu Komputer', 'mahasiswa', '3.70', '50%', 'siti@example.com', '']) . "\n";
        $csv .= implode(',', ['Ahmad Fauzi', '2201234569', '2024', 'Sistem Informasi', 'mahasiswa', '3.50', '0%', 'ahmad@example.com', '']) . "\n";

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
}
