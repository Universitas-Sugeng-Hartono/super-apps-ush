<?php

namespace App\Http\Controllers\AdminController;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\StudyProgram;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class UserManageController extends Controller
{
    /**
     * Tampilkan profil user yang sedang login
     */
    public function index()
    {
        $user = Auth::user();
        $studyPrograms = StudyProgram::where('is_active', true)->orderBy('order')->get();
        return view('admin.user.index', compact('user', 'studyPrograms'));
    }

    /**
     * Tampilkan semua user
     */
    public function indexMain()
    {
 
        $users = User::where('program_studi', Auth::user()->program_studi)->get();
        
        return view('admin.user.main', compact('users'));
    }

    /**
     * Management Index - List semua dosen untuk superadmin/masteradmin
     */
    public function managementIndex(Request $request)
    {
        $search = $request->input('search');
        $programStudi = $request->input('program_studi');
        $studyPrograms = StudyProgram::where('is_active', true)->orderBy('order')->get();

        $lecturers = User::whereIn('role', ['admin', 'superadmin', 'masteradmin'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('program_studi', 'like', "%{$search}%");
                });
            })
            ->when($programStudi, function ($query, $programStudi) {
                $query->where('program_studi', $programStudi);
            })
            ->orderBy('name', 'asc')
            ->paginate(15)
            ->appends(['search' => $search, 'program_studi' => $programStudi]);

        return view('admin.management.lecturers.index', compact('lecturers', 'search', 'programStudi', 'studyPrograms'));
    }

    /**
     * Download template CSV untuk import dosen (Management Dosen).
     * Password default: "password" (bcrypt).
     */
    public function downloadImportTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_dosen.csv"',
        ];

        $csv = implode(',', ['nama', 'email', 'program_studi']) . "\n";
        $csv .= implode(',', ['Dosen Contoh', 'dosen@example.com', 'Bisnis Digital']) . "\n";

        // BOM untuk Excel
        $csv = "\xEF\xBB\xBF" . $csv;

        return response($csv, 200, $headers);
    }

    /**
     * Import dosen dari CSV (Management Dosen).
     * - role selalu 'admin' (label: Dosen)
     * - password default: "password"
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

        rewind($handle);
        $header = fgetcsv($handle, 0, $delimiter);
        if (!$header) {
            fclose($handle);
            return back()->with('error', 'Header CSV tidak ditemukan.');
        }

        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $header);
        $idx = fn (array $keys) => collect($keys)->map(fn ($k) => array_search($k, $header, true))->first(fn ($v) => $v !== false);

        $iNama = $idx(['nama', 'name']);
        $iEmail = $idx(['email']);
        $iProdi = $idx(['program_studi', 'prodi', 'program studi']);

        if ($iNama === null || $iEmail === null || $iProdi === null) {
            fclose($handle);
            return back()->with('error', 'Kolom wajib tidak lengkap. Wajib: nama, email, program_studi.');
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            $rowNo = 1;
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                $rowNo++;
                if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                    continue;
                }

                $nama = trim((string) ($row[$iNama] ?? ''));
                $email = trim((string) ($row[$iEmail] ?? ''));
                $prodi = trim((string) ($row[$iProdi] ?? ''));

                if ($nama === '' || $email === '' || $prodi === '') {
                    $skipped++;
                    $errors[] = "Baris {$rowNo}: data wajib kosong.";
                    continue;
                }

                $existing = User::query()->where('email', $email)->first();
                if ($existing) {
                    // Jangan override akun non-admin
                    if ($existing->role !== 'admin') {
                        $skipped++;
                        $errors[] = "Baris {$rowNo}: email {$email} sudah dipakai role {$existing->role}.";
                        continue;
                    }

                    $existing->update([
                        'name' => $nama,
                        'program_studi' => $prodi,
                        'role' => 'admin',
                    ]);
                    $updated++;
                    continue;
                }

                $u = new User([
                    'name' => $nama,
                    'email' => $email,
                    'program_studi' => $prodi,
                    'role' => 'admin',
                ]);
                $u->password = bcrypt('12345678');
                $u->save();
                $created++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);
            \Log::error('Lecturer import failed: ' . $e->getMessage());
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
     * Reset password dosen ke default.
     */
    public function resetPassword($id)
    {
        $user = User::findOrFail($id);

        // Jangan reset password akun yang sedang login
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'Tidak dapat mereset password akun yang sedang digunakan.');
        }

        $user->password = bcrypt('12345678');
        $user->save();

        return redirect()->back()->with(
            'success',
            "Password untuk {$user->name} berhasil direset ke '12345678'."
        );
    }

    /**
     * Destroy user
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Jangan hapus user yang sedang login
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus akun yang sedang digunakan.');
        }

        // Hapus foto dan ttd jika ada
        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
        }
        if ($user->ttd) {
            Storage::disk('public')->delete($user->ttd);
        }

        $user->delete();

        return redirect()->route('admin.management.lecturers.index')
            ->with('success', 'Dosen berhasil dihapus.');
    }

    /**
     * Form tambah user
     */
    public function create()
    {
        $user = new User();
        $studyPrograms = StudyProgram::where('is_active', true)->orderBy('order')->get();
        
        // Tentukan view berdasarkan route
        if (request()->routeIs('admin.management.lecturers.*')) {
            return view('admin.management.lecturers.create', compact('user', 'studyPrograms'));
        }
        
        return view('admin.user.create', compact('user', 'studyPrograms'));
    }

    /**
     * Form edit user
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $studyPrograms = StudyProgram::where('is_active', true)->orderBy('order')->get();
        
        // Tentukan view berdasarkan route
        if (request()->routeIs('admin.management.lecturers.*')) {
            return view('admin.management.lecturers.edit', compact('user', 'studyPrograms'));
        }
        
        return view('admin.user.edit', compact('user', 'studyPrograms'));
    }

    /**
     * Simpan user baru
     */
    public function store(Request $request)
    {
        $request->validate($this->rules());

        $user = new User($request->only(['name', 'email', 'username', 'NIDNorNUPTK', 'role', 'program_studi']));
        $user->role = $request->role ?? 'admin';
        
        // Default program studi dari master
        $defaultProdi = StudyProgram::where('is_active', true)->orderBy('order')->first();
        $user->program_studi = $request->program_studi ?? ($defaultProdi ? $defaultProdi->name : 'Bisnis Digital');
        $user->password = bcrypt('12345678'); // password default

        $user->photo = $this->handleUpload($request, 'photo', null, 'users/photo');
        $user->ttd   = $this->handleUpload($request, 'ttd', null, 'users/ttd');

        $user->save();

        // Redirect berdasarkan route yang dipanggil
        if (request()->routeIs('admin.management.lecturers.*')) {
            return redirect()->route('admin.management.lecturers.index')->with('success', 'Dosen berhasil ditambahkan');
        }
        
        return redirect()->route('user.admin.main')->with('success', 'User berhasil ditambahkan');
    }

    /**
     * Update data user
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate($this->rules($user->id));

        $user->fill($request->only('name', 'email', 'username', 'NIDNorNUPTK', 'role', 'program_studi'));

        // Update password jika diisi
        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        $user->photo = $this->handleUpload($request, 'photo', $user->photo, 'users/photo');
        $user->ttd   = $this->handleUpload($request, 'ttd', $user->ttd, 'users/ttd');

        $user->save();

        // Redirect berdasarkan route yang dipanggil
        if (request()->routeIs('admin.management.lecturers.*')) {
            return redirect()->route('admin.management.lecturers.index')->with('success', 'Dosen berhasil diperbarui');
        }
        
        return redirect()->route('user.admin.index')->with('success', 'User berhasil diperbarui');
    }

    /**
     * Validasi rules (bisa dipakai store & update)
     */
    private function rules($id = null): array
    {
        $validPrograms = StudyProgram::where('is_active', true)->pluck('name')->toArray();
        
        return [
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email,' . ($id ?? 'NULL') . ',id',
            'username'      => 'nullable|string|unique:users,username,' . ($id ?? 'NULL') . ',id',
            'program_studi' => 'required|string|in:' . implode(',', $validPrograms),
            'role'          => 'required|string|in:admin,superadmin,masteradmin',
            'password'      => 'nullable|string|min:8',
            'photo'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'ttd'           => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    /**
     * Handle upload file dengan hapus file lama (kalau ada)
     */
    private function handleUpload(Request $request, string $field, ?string $oldFile, string $path): ?string
    {
        if ($request->hasFile($field)) {
            if ($oldFile) {
                Storage::disk('public')->delete($oldFile);
            }
            return $request->file($field)->store($path, 'public');
        }
        return $oldFile;
    }
}