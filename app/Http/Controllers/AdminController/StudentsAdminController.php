<?php

namespace App\Http\Controllers\AdminController;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\StudyProgram;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\CardCounseling;
use App\Models\Course;
use Illuminate\Support\Facades\Hash;

class StudentsAdminController extends Controller
{
    /**
     * Display a listing of students by lecturer.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $students = Student::withCount('counselings')
            ->where('id_lecturer', Auth::id())
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nim', 'like', "%{$search}%")
                        ->orWhere('angkatan', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->appends(['search' => $search]);

        return view('admin.students.index', compact('students', 'search'));
    }
    public function unlock()
    {
        Student::where('id_lecturer', Auth::id())->where('is_counseling', 0)->update(['is_counseling' => 1]);
        return back()->with('success', 'Semua kartu konseling telah dibuka.');
    }
    public function lock(){
        Student::where('id_lecturer', Auth::id())->where('is_counseling', 1)->update(['is_counseling' => 0]);
        return back()->with('success', 'Semua kartu konseling telah dikunci.');
    }

    public function changeAdvisor(Request $request)
    {
    $users = User::where('program_studi', auth()->user()->program_studi)->get();
    $search = $request->input('search');

        $students = Student::withCount('counselings')
            ->where('id_lecturer', Auth::id())
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nim', 'like', "%{$search}%")
                        ->orWhere('angkatan', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->appends(['search' => $search]);
    return view('admin.students.advisor', compact('users', 'students', 'search'));
    }

    public function changeAdvisorById($id)
    {
        $student = Student::where('id_lecturer', $id)->get();
        $users = User::where('program_studi', auth()->user()->program_studi)->where('id', '!=', auth()->user()->id)->get();
        return view('admin.students.change_advisor', compact('student', 'users'));
    }

    public function bulkChangeAdvisor(Request $request)
        {

            $request->validate([
                'advisor_id' => 'required|exists:users,id',
                'student_ids' => 'required|array|min:1',
                'student_ids.*' => 'exists:students,id'
            ]);

            Student::whereIn('id', $request->student_ids)
                ->update([
                    'id_lecturer' => $request->advisor_id
                ]);

            return back()->with('success', 'Advisor updated successfully.');
        }
    /**
     * Management Index - List semua mahasiswa untuk superadmin/masteradmin
     */
    public function managementIndex(Request $request)
    {
        $search = $request->input('search');
        $programStudi = $request->input('program_studi');
        $studyPrograms = StudyProgram::where('is_active', true)->orderBy('order')->get();

        $students = Student::with(['dosenPA'])
            ->withCount('counselings')
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
            ->latest()
            ->paginate(15)
            ->appends(['search' => $search, 'program_studi' => $programStudi]);

        return view('admin.management.students.index', compact('students', 'search', 'programStudi', 'studyPrograms'));
    }

    /**
     * Download template CSV untuk import mahasiswa (Management Mahasiswa).
     */
    public function downloadImportTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_mahasiswa.csv"',
        ];

        // Email tidak diperlukan di template (opsional di sistem).
        $csv = implode(',', ['nama', 'nim', 'angkatan', 'program_studi', 'password']) . "\n";
        // Kosongkan password untuk default = NIM (bcrypt)
        $csv .= implode(',', ['Budi Santoso', '2201234567', '2022', 'Bisnis Digital', '']) . "\n";

        // BOM untuk Excel
        $csv = "\xEF\xBB\xBF" . $csv;

        return response($csv, 200, $headers);
    }

    /**
     * Import mahasiswa dari CSV (Management Mahasiswa).
     * - Password default: nim (bcrypt), jika kolom password diisi maka gunakan itu.
     * - Field wajib tabel yang tidak ada di template akan diisi default.
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
            return back()->with('error', 'Gagal membaca file.');
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            return back()->with('error', 'File kosong.');
        }
        $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

        // rewind & read header
        rewind($handle);
        $header = fgetcsv($handle, 0, $delimiter);
        if (!$header) {
            fclose($handle);
            return back()->with('error', 'Header CSV tidak ditemukan.');
        }

        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $header);
        $idx = fn (array $keys) => collect($keys)->map(fn ($k) => array_search($k, $header, true))->first(fn ($v) => $v !== false);

        $iNama = $idx(['nama', 'name', 'nama_lengkap']);
        $iNim = $idx(['nim']);
        $iAngkatan = $idx(['angkatan', 'batch']);
        $iProdi = $idx(['program_studi', 'prodi', 'program studi']);
        $iEmail = $idx(['email']);
        $iPassword = $idx(['password']);

        if ($iNama === null || $iNim === null || $iAngkatan === null || $iProdi === null) {
            fclose($handle);
            return back()->with('error', 'Kolom wajib tidak lengkap. Wajib: nama, nim, angkatan, program_studi.');
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            $rowNo = 1; // header
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                $rowNo++;
                if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                    continue;
                }

                $nama = trim((string) ($row[$iNama] ?? ''));
                $nim = trim((string) ($row[$iNim] ?? ''));
                $angkatan = trim((string) ($row[$iAngkatan] ?? ''));
                $prodi = trim((string) ($row[$iProdi] ?? ''));
                $email = $iEmail !== null ? trim((string) ($row[$iEmail] ?? '')) : null;
                $passwordPlain = $iPassword !== null ? trim((string) ($row[$iPassword] ?? '')) : '';

                if ($nama === '' || $nim === '' || $angkatan === '' || $prodi === '') {
                    $skipped++;
                    $errors[] = "Baris {$rowNo}: data wajib kosong.";
                    continue;
                }

                if (!preg_match('/^\d{4}$/', $angkatan)) {
                    $skipped++;
                    $errors[] = "Baris {$rowNo}: angkatan tidak valid ({$angkatan}).";
                    continue;
                }

                $tanggalMasuk = "{$angkatan}-09-01";

                // Cari dosen PA default untuk prodi ini (role admin), fallback ke dosen admin pertama, terakhir fallback ke user login.
                $lecturerId = User::query()
                    ->where('role', 'admin')
                    ->where('program_studi', $prodi)
                    ->value('id')
                    ?? User::query()->where('role', 'admin')->value('id')
                    ?? (int) auth()->id();

                $student = Student::query()->where('nim', $nim)->first();

                if ($student) {
                    $updateData = [
                        'nama_lengkap' => $nama,
                        'angkatan' => (int) $angkatan,
                        'program_studi' => $prodi,
                        'email' => $email ?: $student->email,
                        'id_lecturer' => $student->id_lecturer ?: $lecturerId,
                    ];

                    if ($passwordPlain !== '') {
                        $updateData['password'] = Hash::make($passwordPlain);
                    }

                    $student->update($updateData);
                    $updated++;
                    continue;
                }

                $passwordToUse = $passwordPlain !== '' ? $passwordPlain : '12345678';

                Student::create([
                    'id_lecturer' => $lecturerId,
                    'nama_lengkap' => $nama,
                    'nim' => $nim,
                    'password' => Hash::make($passwordToUse),
                    'angkatan' => (int) $angkatan,
                    'program_studi' => $prodi,
                    'email' => $email ?: null,
                    'jenis_kelamin' => 'L',
                    'status_mahasiswa' => 'Aktif',
                    'tanggal_masuk' => $tanggalMasuk,
                ]);
                $created++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);
            \Log::error('Student import failed: ' . $e->getMessage());
            return back()->with('error', 'Import gagal: ' . $e->getMessage());
        }

        fclose($handle);

        $msg = "Import selesai. Created: {$created}, Updated: {$updated}, Skipped: {$skipped}.";
        if (count($errors) > 0) {
            $msg .= ' Contoh error: ' . implode(' | ', array_slice($errors, 0, 3));
        }

        return back()->with('success', $msg);
    }

    /**
     * Show student counseling card by lecturer.
     */
    public function showCardByLecture($student_id)
    {
        $student = Student::with(['dosenPA', 'counselings'])->findOrFail($student_id);


        $history = $student->counselings()
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($item) {
                $ids = is_array($item->failed_courses)
                    ? $item->failed_courses
                    : json_decode($item->failed_courses, true);

                $item->failed_courses_objects = Course::whereIn('id', $ids ?: [])->get();
                return $item;
            });

        return view('admin.counseling.add_form_student', compact('student', 'history'));
    }

    /**
     * Check students grouped by batch under lecturer.
     */
    public function CheckStudentByLecturer($id)
    {
        $dosen = User::find($id);

        if (!$dosen || !in_array($dosen->role, ['admin', 'superadmin', 'masteradmin'])) {
            return redirect()->back()->with('error', 'Dosen tidak ditemukan atau bukan dosen pembimbing.');
        }

        $angkatan = Student::where('id_lecturer', $id)
            ->select('angkatan', DB::raw('count(*) as total'))
            ->groupBy('angkatan')
            ->orderBy('angkatan', 'asc')
            ->get();

        return view('admin.counseling.index_master', compact('angkatan', 'dosen'));
    }

    /**
     * Get students by batch and lecturer.
     */
    public function getStudentsByBatchLecturer(Request $request, $batch, $id)
    {
        $dosen = User::findOrFail($id);
        $search = $request->input('search');


       $students = Student::withCount('counselings')
            ->where('id_lecturer', $dosen->id)
            ->where('angkatan', $batch)
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nim', 'like', "%{$search}%")
                        ->orWhere('angkatan', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->appends(['search' => $search]);

        return view('admin.students.index_master', compact('students', 'dosen', 'batch'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $menu = 'Add Student';
        $studyPrograms = StudyProgram::where('is_active', true)->orderBy('order')->get();

        // Tentukan view berdasarkan route
        if (request()->routeIs('admin.management.students.*')) {
            $lecturers = User::whereIn('role', ['admin', 'superadmin', 'masteradmin'])->orderBy('name', 'asc')->get();
            return view('admin.management.students.create', compact('lecturers', 'studyPrograms'));
        }

        return view('admin.students.create', compact('menu', 'studyPrograms'));
    }

    /**
     * Store new student.
     */
    public function store(Request $request)
    {
        $validPrograms = StudyProgram::where('is_active', true)->pluck('name')->toArray();

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:100',
            'nim'       => 'required|string|unique:students,nim|max:12',
            'batch'     => 'required|integer|min:1900|max:2100',
            'program_studi' => 'required|string|in:' . implode(',', $validPrograms),
            'gender'    => 'nullable|in:L,P',
            'address'   => 'nullable|string|max:500',
            'notes'     => 'nullable|string|max:1000',
            'email'     => 'nullable|email|max:100|unique:students,email',
            'phone'     => 'nullable|string|max:15',
            'id_lecturer' => 'nullable|exists:users,id',
        ], [
            'full_name.required' => 'Full name cannot be empty.',
            'nim.required'       => 'NIM cannot be empty.',
            'nim.unique'         => 'This NIM is already registered, please use another one.',
            'batch.required'     => 'Batch cannot be empty.',
            'program_studi.required' => 'Program Studi harus dipilih.',
            'program_studi.in'    => 'Program Studi tidak valid.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Tentukan id_lecturer berdasarkan route
        $lecturerId = request()->routeIs('admin.management.students.*')
            ? ($request->id_lecturer ?? auth()->id())
            : auth()->id();

        // Default program studi dari master
        $defaultProdi = StudyProgram::where('is_active', true)->orderBy('order')->first();

        $studentData = [
            'id_lecturer'      => $lecturerId,
            'nama_lengkap'     => $request->full_name,
            'nim'              => $request->nim,
            'password'         => Hash::make('12345678'),
            'angkatan'         => $request->batch,
            'program_studi'    => $request->program_studi ?? ($defaultProdi ? $defaultProdi->name : 'Bisnis Digital'),
            'email'            => $request->email,
            'no_telepon'       => $request->phone,
            'notes'            => $request->notes,
            'jenis_kelamin'    => $request->gender,
            'alamat'           => $request->address,
            'is_edited'        => 1,
            'status_mahasiswa' => 'Aktif',
            'tanggal_masuk'    => now(),
        ];

        Student::create($studentData);

        // Redirect berdasarkan route yang dipanggil
        if (request()->routeIs('admin.management.students.*')) {
            return redirect()->route('admin.management.students.index')->with('success', 'Mahasiswa berhasil ditambahkan.');
        }

        return redirect()->route('admin.students.index')
            ->with('success', 'Mahasiswa berhasil ditambahkan.');
    }

    /**
     * Show a specific student.
     */
    public function show($id)
    {
        $student = Student::with('dosenPA')->findOrFail($id);

        if (request()->routeIs('admin.management.students.*')) {
            return view('admin.management.students.show', compact('student'));
        }

        return view('admin.students.show', compact('student'));
    }


    /**
     * Show edit form.
     */
    public function edit($id)
    {
        $student = Student::findOrFail($id);
        $menu = 'Edit ' . $student->nama_lengkap;
        $studyPrograms = StudyProgram::where('is_active', true)->orderBy('order')->get();

        // Tentukan view berdasarkan route
        if (request()->routeIs('admin.management.students.*')) {
            $lecturers = User::whereIn('role', ['admin', 'superadmin', 'masteradmin'])->orderBy('name', 'asc')->get();
            return view('admin.management.students.edit', compact('student', 'menu', 'studyPrograms', 'lecturers'));
        }

        return view('admin.students.edit', compact('student', 'menu', 'studyPrograms'));
    }

    /**
     * Update a student.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:100',
            'nim'          => 'required|string|max:12|unique:students,nim,' . $id,
            'angkatan'     => 'required|integer|min:1900|max:2100',
            'program_studi'=> 'required|string|max:50',
            'fakultas'     => 'nullable|string|max:100',
            'jenis_kelamin'=> 'required|in:L,P',
            'alamat'       => 'required|string|max:500',
            'email'        => 'nullable|email|max:100|unique:students,email,' . $id,
            'no_telepon'   => 'nullable|string|max:15',
            'notes'        => 'nullable|string|max:1000',
            'id_lecturer'  => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $studentData = $validator->validated();
        $studentData['status_mahasiswa'] = 'Aktif';

        // Update id_lecturer jika dari management route
        if (request()->routeIs('admin.management.students.*') && isset($request->id_lecturer)) {
            $studentData['id_lecturer'] = $request->id_lecturer;
        }

        Student::where('id', $id)->update($studentData);

        // Redirect berdasarkan route yang dipanggil
        if (request()->routeIs('admin.management.students.*')) {
            return redirect()->route('admin.management.students.index')
                ->with('success', 'Data mahasiswa berhasil diperbarui!');
        }

        return redirect()->back()
            ->with('success', 'Data mahasiswa berhasil diperbarui!');
    }
    // Show edit counseling card form
     public function editcard($id)
    {
        $row = CardCounseling::findOrFail($id);
        $student = $row->student;
        $allCourses = Course::orderBy('code_prefix')->orderBy('code_number')->get();

        return view('admin.counseling.card_counseling_edit', compact('row', 'student', 'allCourses'));
    }
    // Update counseling card
    public function updatecard(Request $request, $id)
    {
        $row = CardCounseling::findOrFail($id);

       $validator = Validator::make(
        $request->all(),
        [
            'semester'   => 'required|string|max:20',
            'sks'        => 'required|integer|min:0',
            'ip'         => 'required|numeric|between:0,4',
            'tanggal'    => 'required|date',
            'komentar'   => 'nullable|string|max:500',
            'failed_courses'     => 'array',
            'failed_courses.*'   => 'exists:courses,id',
            'retaken_courses'    => 'array',
            'retaken_courses.*'  => 'exists:courses,id',
        ],
        [
            'semester.required'  => 'Semester wajib diisi.',
            'semester.max'       => 'Semester maksimal 20 karakter.',

            'sks.required'       => 'Jumlah SKS tidak boleh kosong.',
            'sks.integer'        => 'SKS harus berupa angka bulat.',
            'sks.min'            => 'SKS minimal 0.',

            'ip.required'        => 'IP Semester Lalu wajib diisi.',
            'ip.numeric'         => 'IP harus berupa angka.',
            'ip.between'         => 'IP harus antara 0.00 sampai 4.00.',

            'tanggal.required'   => 'Tanggal konsultasi wajib diisi.',
            'tanggal.date'       => 'Format tanggal tidak valid.',

            'komentar.string'    => 'Komentar harus berupa teks.',
            'komentar.max'       => 'Komentar maksimal 500 karakter.',

            'failed_courses.array'     => 'Format mata kuliah tidak lulus tidak valid.',
            'failed_courses.*.exists'  => 'Mata kuliah yang dipilih tidak ditemukan di database.',

            'retaken_courses.array'     => 'Format mata kuliah diulang tidak valid.',
            'retaken_courses.*.exists'  => 'Mata kuliah yang dipilih tidak ditemukan di database.',
        ]
    );


        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::transaction(function () use ($request, $row) {
            $row->semester = $request->semester;
            $row->sks      = $request->sks;
            $row->ip       = $request->ip;
            $row->tanggal  = $request->tanggal;
            $row->komentar = $request->komentar;

            // simpan langsung array ke kolom JSON
            $row->failed_courses  = $request->failed_courses ?? [];
            $row->retaken_courses = $request->retaken_courses ?? [];

            $row->save();
        });


        return redirect()
            ->route('admin.students.showCardByLecture', $row->id_student)
            ->with('success', 'Data konsultasi berhasil diperbarui.');
    }
    /**
     * Reset a student's password to default.
     */
    public function resetpassword($id)
    {
        $student = Student::findOrFail($id);
        $student->password = bcrypt('12345678');
        $student->save();

        return redirect()->back()->with('success', "Password untuk {$student->nama_lengkap} telah direset ke '12345678'.");
    }

    /**
     * Toggle is_edited untuk satu mahasiswa
     */
    public function toggleEdit(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        // Jika request body ada is_edited, gunakan itu, jika tidak toggle
        if ($request->has('is_edited')) {
            $student->is_edited = $request->is_edited ? 1 : 0;
        } else {
            $student->is_edited = $student->is_edited ? 0 : 1;
        }

        $student->save();

        $status = $student->is_edited ? 'dibuka' : 'dikunci';
        return response()->json([
            'success' => true,
            'message' => "Akses edit profil untuk {$student->nama_lengkap} telah {$status}.",
            'is_edited' => $student->is_edited
        ]);
    }

    /**
     * Toggle is_edited untuk semua mahasiswa
     */
    public function toggleAllEdit(Request $request)
    {
        $request->validate([
            'is_edited' => 'required|boolean'
        ]);

        $count = Student::query()->update(['is_edited' => $request->is_edited ? 1 : 0]);
        $status = $request->is_edited ? 'dibuka' : 'dikunci';

        return response()->json([
            'success' => true,
            'message' => "Akses edit profil untuk {$count} mahasiswa telah {$status}.",
            'is_edited' => $request->is_edited ? 1 : 0
        ]);
    }
    /**
     * Remove a student.
     */
    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        // Redirect berdasarkan route yang dipanggil
        if (request()->routeIs('admin.management.students.*')) {
            return redirect()->route('admin.management.students.index')->with('success', 'Mahasiswa berhasil dihapus!');
        }

        return redirect()->route('admin.students.index')
            ->with('success', 'Mahasiswa berhasil dihapus!');
    }

    public function destroycard($id)
    {
        $card = CardCounseling::findOrFail($id);
        $card->delete();

        return redirect()->back()
            ->with('success', 'Kartu konsultasi berhasil dihapus!');
    }
}
