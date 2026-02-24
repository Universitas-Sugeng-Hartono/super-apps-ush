<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\CardCounseling;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class StudentsController extends Controller
{
    /**
     * Display a listing of the admin.students.
     */
    public function index(Request $request)
    {
        $counseling = CardCounseling::where('id_student', decrypt(session('student_id')))->count();
        return view('students.dashboard.index', compact('counseling'));
    }

    /**
     * Display dashboard with template-it design
     */
    public function dashboard(Request $request)
    {
        $student = Student::with('dosenPA')
            ->withCount('achievements')
            ->findOrFail(decrypt(session('student_id')));
        $counseling = CardCounseling::where('id_student', decrypt(session('student_id')))->count();
        
        // Load menu dinamis untuk role student
        $menus = \App\Models\MenuItem::active()
            ->forRole('student')
            ->ordered()
            ->get()
            ->map(function($menu) {
                $menu->menu_url = $menu->full_url;
                return $menu;
            });
        
        // Hindari menu yang mengarah ke area admin saat login sebagai mahasiswa.
        // Kalau ada menu "Bimbingan PA" / "Tugas Akhir" versi admin, kita map ke route student yang benar.
        $menus = $menus
            ->map(function ($menu) {
                $routeName = $menu->route_name ?? '';
                $name = strtolower(trim((string) $menu->name));
                $url = (string) ($menu->menu_url ?? '');

                if ($name === 'bimbingan pa' || $routeName === 'admin.counseling.index' || str_starts_with($url, '/admin/counseling')) {
                    $menu->menu_url = route('student.counseling.show');
                    $menu->target = '_self';
                    return $menu;
                }

                if ($name === 'tugas akhir' || $routeName === 'admin.final-project.index' || str_starts_with($url, '/admin/final-project')) {
                    $menu->menu_url = route('student.final-project.index');
                    $menu->target = '_self';
                    return $menu;
                }

                return $menu;
            })
            ->filter(function ($menu) {
                $routeName = $menu->route_name ?? '';
                $url = (string) ($menu->menu_url ?? $menu->url ?? '');

                if (str_starts_with($routeName, 'admin.') || str_starts_with($url, '/admin/')) {
                    return false;
                }
                return true;
            })
            ->values();

        // Fallback: pastikan 2 menu utama mahasiswa selalu ada
        if (!$menus->contains(fn ($m) => ($m->route_name ?? '') === 'student.counseling.show' || ($m->menu_url ?? '') === route('student.counseling.show'))) {
            $fallback = new \App\Models\MenuItem([
                'name' => 'Bimbingan PA',
                'icon' => 'bi bi-person-video3',
                'description' => 'Konsultasi dengan dosen pembimbing akademik',
                'badge_text' => 'Aktif',
                'badge_color' => 'active',
                'target' => '_self',
            ]);
            $fallback->menu_url = route('student.counseling.show');
            $menus->push($fallback);
        }

        if (!$menus->contains(fn ($m) => ($m->route_name ?? '') === 'student.final-project.index' || ($m->menu_url ?? '') === route('student.final-project.index'))) {
            $fallback = new \App\Models\MenuItem([
                'name' => 'Tugas Akhir',
                'icon' => 'bi bi-mortarboard',
                'description' => 'Pengajuan judul, proposal, hingga sidang skripsi',
                'badge_text' => 'Aktif',
                'badge_color' => 'active',
                'target' => '_self',
            ]);
            $fallback->menu_url = route('student.final-project.index');
            $menus->push($fallback);
        }

        $announcements = Announcement::query()
            ->published()
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        // Ambil jadwal sempro/sidang yang sudah disetujui dan belum berlalu
        $studentId = decrypt(session('student_id'));
        $upcomingSchedules = collect();

        $finalProject = \App\Models\FinalProject::with(['proposal', 'defense'])
            ->where('student_id', $studentId)
            ->first();
        
        if ($finalProject) {
            // Cek proposal yang sudah disetujui dan jadwalnya belum berlalu
            if ($finalProject->proposal && $finalProject->proposal->status === 'approved' && $finalProject->proposal->scheduled_at) {
                $scheduledAt = \Carbon\Carbon::parse($finalProject->proposal->scheduled_at);
                if ($scheduledAt->greaterThanOrEqualTo(now()->startOfMinute())) {
                    $upcomingSchedules->push([
                        'type' => 'Sempro',
                        'type_label' => 'Seminar Proposal',
                        'datetime' => $scheduledAt,
                        'title' => $finalProject->title ?? '',
                        'approval_notes' => $finalProject->proposal->approval_notes ?? '',
                        'url' => route('student.final-project.proposal.show', $finalProject->proposal->id),
                    ]);
                }
            }

            // Cek defense yang sudah disetujui dan jadwalnya belum berlalu
            if ($finalProject->defense && $finalProject->defense->status === 'approved' && $finalProject->defense->scheduled_at) {
                $scheduledAt = \Carbon\Carbon::parse($finalProject->defense->scheduled_at);
                if ($scheduledAt->greaterThanOrEqualTo(now()->startOfMinute())) {
                    $upcomingSchedules->push([
                        'type' => 'Sidang',
                        'type_label' => 'Sidang Skripsi',
                        'datetime' => $scheduledAt,
                        'title' => $finalProject->title ?? '',
                        'approval_notes' => $finalProject->defense->approval_notes ?? '',
                        'url' => route('student.final-project.defense.show', $finalProject->defense->id),
                    ]);
                }
            }
        }

        // Urutkan berdasarkan tanggal terdekat
        $upcomingSchedules = $upcomingSchedules->sortBy('datetime')->values();
        
        return view('students.dashboard.super-app-home', compact('student', 'counseling', 'menus', 'announcements', 'upcomingSchedules'));
    }

    public function editDataIndex(Request $request)
    {
        $student = Student::with(['dosenPA', 'achievements'])->findOrFail(decrypt(session('student_id')));
        $isDefaultPassword = \Hash::check('12345678', $student->password);
        return view('students.personal.edit', compact('student', 'isDefaultPassword'));
    }

   public function updateData(Request $request)
{
    $student = Student::findOrFail(decrypt(session('student_id')));

    // Validation rules
    $request->validate([
        'nama_orangtua'        => 'nullable|string|max:255',
        'jenis_kelamin'        => 'nullable|in:L,P',
        'tanggal_lahir'        => 'nullable|date',
        'password'             => 'nullable|string|min:8|max:20|confirmed',
        'password_confirmation' => 'nullable|string|min:8|max:20',
        'alamat'               => 'nullable|string',
        'alamat_lat'           => 'nullable|numeric',
        'alamat_lng'           => 'nullable|numeric',
        'no_telepon'           => 'nullable|string|max:20',
        'no_telepon_orangtua'  => 'nullable|string|max:20',
        'email'                => 'nullable|email|max:255',
        'ipk'                  => 'nullable|numeric|min:0|max:4',
        'sks'                  => 'nullable|integer|min:0|max:200',
        'foto'                 => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'ttd'                  => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ], [
        'nama_orangtua.string' => 'Parent’s name must be text.',
        'nama_orangtua.max'    => 'Parent’s name cannot be longer than 255 characters.',

        'jenis_kelamin.in'     => 'Please select a valid gender (L or P).',

        'tanggal_lahir.date'   => 'Please enter a valid birth date.',

        'password.min'         => 'Password must be at least 8 characters.',
        'password.max'         => 'Password cannot be longer than 20 characters.',
        'password.confirmed'   => 'Password dan konfirmasi password tidak cocok.',

        'alamat.string'        => 'Address must be text.',
        'alamat_lat.numeric'   => 'Latitude must be a number.',
        'alamat_lng.numeric'   => 'Longitude must be a number.',

        'no_telepon.string'    => 'Phone number must be text.',
        'no_telepon.max'       => 'Phone number cannot be longer than 20 characters.',
        'no_telepon_orangtua.string' => 'Parent’s phone number must be text.',
        'no_telepon_orangtua.max'    => 'Parent’s phone number cannot be longer than 20 characters.',

        'email.email'          => 'Please enter a valid email address.',
        'email.max'            => 'Email cannot be longer than 255 characters.',

        'ipk.numeric'          => 'IPK must be a number.',
        'ipk.min'              => 'IPK must be at least 0.',
        'ipk.max'              => 'IPK cannot be greater than 4.00.',
        
        'sks.integer'          => 'SKS must be a whole number.',
        'sks.min'              => 'SKS must be at least 0.',
        'sks.max'              => 'SKS cannot be greater than 200.',

        'foto.image'           => 'Photo must be an image.',
        'foto.mimes'           => 'Photo must be a file of type: jpeg, png, jpg.',
        'foto.max'             => 'Photo size must not exceed 2 MB.',

        'ttd.image'            => 'Signature must be an image.',
        'ttd.mimes'            => 'Signature must be a file of type: jpeg, png, jpg.',
        'ttd.max'              => 'Signature size must not exceed 2 MB.',
    ]);

    try {
        $formType = $request->input('form_type', 'text');

        DB::transaction(function () use ($request, $student, $formType) {
            if ($formType === 'foto' && $request->hasFile('foto')) {
                if ($student->foto && Storage::disk('public')->exists($student->foto)) {
                    Storage::disk('public')->delete($student->foto);
                }
                $student->foto = $request->file('foto')->store('students/foto/'.$student->id, 'public');
            } elseif ($formType === 'ttd' && $request->hasFile('ttd')) {
                if ($student->ttd && Storage::disk('public')->exists($student->ttd)) {
                    Storage::disk('public')->delete($student->ttd);
                }
                $student->ttd = $request->file('ttd')->store('students/ttd/'.$student->id, 'public');
            } else {
                // Update field text
                if ($request->filled('nama_orangtua')) $student->nama_orangtua = $request->nama_orangtua;
                if ($request->filled('jenis_kelamin')) $student->jenis_kelamin = $request->jenis_kelamin;
                if ($request->filled('tanggal_lahir')) $student->tanggal_lahir = $request->tanggal_lahir;
                if ($request->filled('alamat')) $student->alamat = $request->alamat;
                if ($request->filled('alamat_lat') && $request->filled('alamat_lng')) {
                    $student->alamat_lat = $request->alamat_lat;
                    $student->alamat_lng = $request->alamat_lng;
                }
                if ($request->filled('no_telepon')) $student->no_telepon = $request->no_telepon;
                if ($request->filled('no_telepon_orangtua')) $student->no_telepon_orangtua = $request->no_telepon_orangtua;
                if ($request->filled('email')) $student->email = $request->email;
                if ($request->filled('ipk')) $student->ipk = $request->ipk;
                if ($request->filled('sks')) $student->sks = $request->sks;
                if ($request->filled('password')) $student->password = bcrypt($request->password);
            }

            // Simpan perubahan dulu
            $student->save();

            // === Cek kelengkapan data ===
            $requiredFields = [
                'nama_orangtua',
                'jenis_kelamin',
                'tanggal_lahir',
                'alamat',
                'alamat_lat',
                'alamat_lng',
                'no_telepon',
                'no_telepon_orangtua',
                'email',
                'foto',
                'ttd',
                'password'
            ];

            $isComplete = true;
            foreach ($requiredFields as $field) {
                if (empty($student->$field)) {
                    $isComplete = false;
                    break;
                }
            }

            // Kalau semua lengkap → kunci data
            if ($isComplete) {
                $student->is_edited = 0;
                $student->save();
            }
        });

    if ($student->foto && $student->ttd) {
            return redirect()
                ->route('student.counseling.show', encrypt(session('student_id')))
                ->with('success', 'Data berhasil disimpan! Perubahan Anda telah tersimpan dengan baik. Anda sekarang dapat mengakses layanan bimbingan akademik.');
        }else{
             return redirect()
            ->route('student.personal.editDataIndex', ['id' => $student->id])
            ->with('success', 'Data berhasil disimpan! Perubahan Anda telah tersimpan dengan baik.');
        }
        

    } catch (\Exception $e) {
        \Log::error("Failed to update student data: " . $e->getMessage());
        return back()->with('error', 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.');
    }
}

    /**
     * Store achievement
     */
    public function storeAchievement(Request $request)
    {
        $student = Student::findOrFail(decrypt(session('student_id')));

        $request->validate([
            'event' => 'required|string|max:255',
            'achievement' => 'required|string|max:255',
            'level' => 'required|string|max:255',
            'certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'event.required' => 'Nama event harus diisi.',
            'achievement.required' => 'Prestasi yang diraih harus diisi.',
            'level.required' => 'Tingkat harus diisi.',
            'certificate.file' => 'Piagam harus berupa file.',
            'certificate.mimes' => 'Piagam harus berupa file PDF, JPG, JPEG, atau PNG.',
            'certificate.max' => 'Ukuran file piagam maksimal 5 MB.',
        ]);

        try {
            $data = $request->only(['event', 'achievement', 'level']);
            $data['student_id'] = $student->id;

            if ($request->hasFile('certificate')) {
                $data['certificate'] = $request->file('certificate')->store('students/achievements/' . $student->id, 'public');
            }

            StudentAchievement::create($data);

            return back()->with('success', 'Prestasi berhasil ditambahkan!');
        } catch (\Exception $e) {
            \Log::error("Failed to store achievement: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menambahkan prestasi.');
        }
    }

    /**
     * Update achievement
     */
    public function updateAchievement(Request $request, $id)
    {
        $achievement = StudentAchievement::where('id', $id)
            ->where('student_id', decrypt(session('student_id')))
            ->firstOrFail();

        $request->validate([
            'event' => 'required|string|max:255',
            'achievement' => 'required|string|max:255',
            'level' => 'required|string|max:255',
            'certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        try {
            $achievement->event = $request->event;
            $achievement->achievement = $request->achievement;
            $achievement->level = $request->level;

            if ($request->hasFile('certificate')) {
                // Delete old certificate
                if ($achievement->certificate && Storage::disk('public')->exists($achievement->certificate)) {
                    Storage::disk('public')->delete($achievement->certificate);
                }
                $achievement->certificate = $request->file('certificate')->store('students/achievements/' . $achievement->student_id, 'public');
            }

            $achievement->save();

            return back()->with('success', 'Prestasi berhasil diperbarui!');
        } catch (\Exception $e) {
            \Log::error("Failed to update achievement: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memperbarui prestasi.');
        }
    }

    /**
     * Delete achievement
     */
    public function deleteAchievement($id)
    {
        try {
            $achievement = StudentAchievement::where('id', $id)
                ->where('student_id', decrypt(session('student_id')))
                ->firstOrFail();

            // Delete certificate file if exists
            if ($achievement->certificate && Storage::disk('public')->exists($achievement->certificate)) {
                Storage::disk('public')->delete($achievement->certificate);
            }

            $achievement->delete();

            return back()->with('success', 'Prestasi berhasil dihapus!');
        } catch (\Exception $e) {
            \Log::error("Failed to delete achievement: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menghapus prestasi.');
        }
    }

}