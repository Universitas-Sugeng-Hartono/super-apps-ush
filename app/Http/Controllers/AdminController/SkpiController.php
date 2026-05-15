<?php

namespace App\Http\Controllers\AdminController;

use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Models\SkpiAcademicProfile;
use App\Models\SkpiDocumentSetting;
use App\Models\SkpiLearningOutcome;
use App\Models\SkpiRegistration;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\StudyProgram;
use App\Services\SkpiDocumentEncryption;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SkpiController extends Controller
{
    public function index()
    {


        return view('admin.skpi.index',);
    }

    public function daftarSkpi(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $status = $request->input('status');
        $studyProgramId = $request->input('study_program_id');

        $registrations = SkpiRegistration::with(['student.finalProject', 'approver'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nim', 'like', "%{$search}%")
                        ->orWhereHas('student', function ($studentQuery) use ($search) {
                            $studentQuery->where('program_studi', 'like', "%{$search}%");
                        });
                });
            })
            ->when(in_array($status, ['pending', 'approved', 'needs_revision', 'rejected'], true), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($studyProgramId, function ($query) use ($studyProgramId) {
                $query->whereHas('student', function ($q) use ($studyProgramId) {
                    $studyProgram = \App\Models\StudyProgram::find($studyProgramId);
                    if ($studyProgram) {
                        $q->where('program_studi', $studyProgram->name);
                    }
                });
            })
            ->latest('submitted_at')
            ->latest('created_at')
            ->paginate(15)
            ->appends($request->query());

        $stats = [
            'total' => SkpiRegistration::count(),
            'pending' => SkpiRegistration::where('status', 'pending')->count(),
            'approved' => SkpiRegistration::where('status', 'approved')->count(),
            'needs_revision' => SkpiRegistration::where('status', 'needs_revision')->count(),
            'rejected' => SkpiRegistration::where('status', 'rejected')->count(),
        ];

        $studyPrograms = \App\Models\StudyProgram::all();

        return view('admin.skpi.daftar-skpi.index', compact('registrations', 'stats', 'search', 'status', 'studyPrograms', 'studyProgramId'));
    }

    public function approveDaftarSkpi(Request $request, $id)
    {
        $registration = SkpiRegistration::with('student.finalProject')->findOrFail($id);

        // Server-side: cek prasyarat mahasiswa sebelum approve
        $student = $registration->student;
        $issues = [];

        if (!filled($student->ipk) || !filled($student->sks)) {
            $issues[] = 'IPK dan SKS belum lengkap.';
        }
        if (!filled(optional($student->finalProject)->title)) {
            $issues[] = 'Data Tugas Akhir belum ada.';
        }
        if (!filled($student->foto) || !filled($student->ttd)) {
            $issues[] = 'Foto atau tanda tangan belum diupload.';
        }

        if (count($issues) > 0) {
            return redirect()
                ->route('admin.skpi.daftar-skpi.index')
                ->withErrors($issues);
        }

        $request->validate([
            'nomor_ijazah'   => 'required|string|max:100',
            'approval_notes' => 'nullable|string',
        ]);

        $registration->update([
            'status'         => 'approved',
            'nomor_ijazah'   => $request->nomor_ijazah,
            'approval_notes' => $request->approval_notes,
            'approved_by'    => auth()->id(),
            'approved_at'    => now(),
        ]);

        try {
            NotificationHelper::notifyStudent(
                $registration->student_id,
                'skpi.registration.approved',
                'Pendaftaran SKPI Disetujui',
                'Pendaftaran SKPI Anda telah disetujui. Silakan tunggu Admin men-generate dokumen SKPI Anda.',
                route('student.skpi.index'),
                ['skpi_registration_id' => $registration->id]
            );
        } catch (\Exception $e) {
            \Log::warning('Notifikasi approve SKPI gagal: ' . $e->getMessage());
        }

        return redirect()
            ->route('admin.skpi.daftar-skpi.index')
            ->with('success', 'Pendaftaran SKPI berhasil disetujui. Silakan masuk ke menu Generate SKPI untuk membuat dokumennya agar mahasiswa bisa mengunduh.');
    }

    public function revisionDaftarSkpi(Request $request, $id)
    {
        $registration = SkpiRegistration::with('student')->findOrFail($id);

        $request->validate([
            'approval_notes' => 'required|string',
        ], [
            'approval_notes.required' => 'Catatan revisi wajib diisi.',
        ]);

        $registration->update([
            'status' => 'needs_revision',
            'skpi_document' => null, // Hapus file lama karena data akan berubah
            'approval_notes' => $request->approval_notes,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        NotificationHelper::notifyStudent(
            $registration->student_id,
            'skpi.registration.revision',
            'Pendaftaran SKPI Perlu Revisi',
            'Pendaftaran SKPI Anda perlu revisi. Catatan: ' . $registration->approval_notes,
            route('student.skpi.daftar.show'),
            ['skpi_registration_id' => $registration->id]
        );

        return redirect()
            ->route('admin.skpi.daftar-skpi.index')
            ->with('success', 'Status pendaftaran SKPI diubah menjadi need revision.');
    }

    public function rejectDaftarSkpi(Request $request, $id)
    {
        $registration = SkpiRegistration::with('student')->findOrFail($id);

        $request->validate([
            'approval_notes' => 'required|string',
        ], [
            'approval_notes.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $registration->update([
            'status' => 'rejected',
            'skpi_document' => null, // Hapus file lama
            'approval_notes' => $request->approval_notes,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        NotificationHelper::notifyStudent(
            $registration->student_id,
            'skpi.registration.rejected',
            'Pendaftaran SKPI Ditolak',
            'Pendaftaran SKPI Anda ditolak. Catatan: ' . $registration->approval_notes,
            route('student.skpi.daftar.show'),
            ['skpi_registration_id' => $registration->id]
        );

        return redirect()
            ->route('admin.skpi.daftar-skpi.index')
            ->with('success', 'Pendaftaran SKPI berhasil ditolak.');
    }

    public function updateIjazahOnly(Request $request, $id)
    {
        $registration = SkpiRegistration::findOrFail($id);

        $request->validate([
            'nomor_ijazah' => 'required|string|max:100',
        ]);

        $registration->update([
            'nomor_ijazah' => $request->nomor_ijazah,
            'skpi_document' => null, 
        ]);

        return redirect()
            ->back()
            ->with('success', 'Nomor ijazah mahasiswa ' . $registration->nama_lengkap . ' berhasil diperbarui.');
    }

    public function inputDataAkademi(Request $request)
    {
        $fieldLabels = $this->academicFieldLabels();
        $studyPrograms = StudyProgram::query()
            ->where('is_active', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $profiles = SkpiAcademicProfile::query()
            ->with('studyProgram')
            ->get()
            ->keyBy('study_program_id');

        $selectedStudyProgramId = (int) ($request->integer('study_program_id') ?: old('study_program_id') ?: optional($studyPrograms->first())->id);
        $selectedStudyProgram = $studyPrograms->firstWhere('id', $selectedStudyProgramId) ?? $studyPrograms->first();

        $academicProfile = $selectedStudyProgram
            ? ($profiles->get($selectedStudyProgram->id) ?? new SkpiAcademicProfile(['study_program_id' => $selectedStudyProgram->id]))
            : new SkpiAcademicProfile();

        $studyPrograms = $studyPrograms->map(function ($studyProgram) use ($profiles, $fieldLabels) {
            $profile = $profiles->get($studyProgram->id);
            $completedFields = $this->countCompletedAcademicFields($profile, $fieldLabels);

            $studyProgram->setAttribute('skpi_completed_fields', $completedFields);
            $studyProgram->setAttribute('skpi_total_fields', count($fieldLabels));
            $studyProgram->setAttribute('skpi_ready', $profile && $completedFields === count($fieldLabels));

            return $studyProgram;
        });

        $stats = [
            'total_programs'             => $studyPrograms->count(),
            'configured_programs'        => $profiles->count(),
            'ready_programs'             => $studyPrograms->where('skpi_ready', true)->count(),
            'selected_completed_fields'  => $this->countCompletedAcademicFields($academicProfile, $fieldLabels),
            'selected_total_fields'      => count($fieldLabels),
        ];

        // ── Tambahan baru ──────────────────────────────────────────
        $documentMeta = $this->resolveDocumentMeta($request);

        // Point 4: Learning Outcome per prodi (tabel terpisah)
        $learningOutcome = $selectedStudyProgram
            ? SkpiLearningOutcome::firstOrNew(['study_program_id' => $selectedStudyProgram->id])
            : new SkpiLearningOutcome();

        $approvedRegistrations = collect();
        if ($selectedStudyProgram) {
            $approvedRegistrations = SkpiRegistration::with('student')
                ->where('status', 'approved')
                ->whereHas('student', function ($query) use ($selectedStudyProgram) {
                    $query->where('program_studi', $selectedStudyProgram->name);
                })
                ->orderBy('nama_lengkap')
                ->get();
        }
        // ──────────────────────────────────────────────────────────

        return view('admin.skpi.input-data-akademi.index', compact(
            'academicProfile',
            'documentMeta',
            'fieldLabels',
            'learningOutcome',
            'selectedStudyProgram',
            'selectedStudyProgramId',
            'stats',
            'studyPrograms',
            'approvedRegistrations'
        ));
    }

    public function storeInputDataAkademi(Request $request)
    {
        $fieldLabels = $this->academicFieldLabels();

        $validated = $request->validate([
            'study_program_id' => 'required|exists:study_programs,id',
            'sk_pendirian_perguruan_tinggi' => 'nullable|string|max:255',
            'nama_perguruan_tinggi' => 'nullable|string|max:255',
            'akreditasi_perguruan_tinggi' => 'nullable|string|max:255',
            'akreditasi_program_studi' => 'nullable|string|max:255',
            'jenis_dan_jenjang_pendidikan' => 'nullable|string|max:255',
            'jenjang_kualifikasi_kkni' => 'nullable|string|max:255',
            'persyaratan_penerimaan' => 'nullable|string',
            'bahasa_pengantar_kuliah' => 'nullable|string|max:255',
            'nomor_akreditasi_perguruan_tinggi' => 'nullable|string|max:255',
            'sistem_penilaian' => 'nullable|string',
            'lama_studi' => 'nullable|string|max:255',
            'nomor_akreditasi_program_studi' => 'nullable|string|max:255',
            'status_profesi' => 'nullable|string|max:255',
            'gelar_lulusan' => 'nullable|string|max:100',
        ], [
            'study_program_id.required' => 'Program studi wajib dipilih.',
            'study_program_id.exists' => 'Program studi yang dipilih tidak valid.',
        ]);

        $payload = Arr::only($validated, array_keys($fieldLabels));

        foreach ($payload as $key => $value) {
            $payload[$key] = is_string($value) ? trim($value) : $value;
            if ($payload[$key] === '') {
                $payload[$key] = null;
            }
        }

        SkpiAcademicProfile::query()->updateOrCreate(
            ['study_program_id' => $validated['study_program_id']],
            $payload
        );

        return redirect()
            ->route('admin.skpi.input-data-akademi.index', $request->only('study_program_id'))
            ->with('success', 'Data akademik SKPI berhasil disimpan.');
    }


    public function storeStudyProgram(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:study_programs,name',
        ], [
            'name.required' => 'Nama Program Studi wajib diisi.',
            'name.unique' => 'Program Studi ini sudah ada di sistem.',
        ]);

        $lastOrder = StudyProgram::max('order') ?? 0;

        $newProgram = StudyProgram::create([
            'name' => $validated['name'],
            'order' => $lastOrder + 1,
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.skpi.input-data-akademi.index', ['study_program_id' => $newProgram->id])
            ->with('success', 'Program Studi baru berhasil ditambahkan.');
    }

    public function destroyStudyProgram($id)
    {
        $studyProgram = StudyProgram::findOrFail($id);

        $usedByStudents = Student::query()
            ->where('program_studi', $studyProgram->name)
            ->exists();

        if ($usedByStudents) {
            return redirect()
                ->route('admin.skpi.input-data-akademi.index', ['study_program_id' => $studyProgram->id])
                ->with('error', 'Program Studi tidak bisa dihapus karena masih dipakai oleh data mahasiswa.');
        }

        $studyProgram->delete();

        return redirect()
            ->route('admin.skpi.input-data-akademi.index')
            ->with('success', 'Program Studi berhasil dihapus.');
    }

    public function verifikasiData(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $status = $request->input('status');
        $programStudi = $request->input('program_studi');
        $category = $request->input('category');

        $achievementsFilter = function ($query) use ($status, $category) {
            $validCategories = array_keys(\App\Models\StudentAchievement::manualCategoryOptions());
            $query->whereIn('category', $validCategories);
            
            if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
                $query->where('status', $status);
            }
            if ($category && in_array($category, $validCategories, true)) {
                $query->where('category', $category);
            }
        };

        $students = \App\Models\Student::query()
            ->whereHas('achievements', $achievementsFilter)
            ->with(['achievements' => function ($query) use ($achievementsFilter) {
                $achievementsFilter($query);
                $query->with('approver')->latest();
            }])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nim', 'like', "%{$search}%")
                        ->orWhere('program_studi', 'like', "%{$search}%");
                });
            })
            ->when(filled($programStudi), function ($query) use ($programStudi) {
                $query->where('program_studi', $programStudi);
            })
            ->orderBy('nama_lengkap')
            ->paginate(10)
            ->appends($request->query());

        $stats = [
            'total' => StudentAchievement::count(),
            'pending' => StudentAchievement::where('status', 'pending')->count(),
            'approved' => StudentAchievement::where('status', 'approved')->count(),
            'rejected' => StudentAchievement::where('status', 'rejected')->count(),
        ];

        $studyPrograms = StudyProgram::query()
            ->where('is_active', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get();
        $categoryOptions = StudentAchievement::manualCategoryOptions();

        return view('admin.skpi.verifikasi-data.index', compact(
            'students',
            'category',
            'categoryOptions',
            'programStudi',
            'search',
            'stats',
            'status',
            'studyPrograms'
        ));
    }

    public function approveVerifikasiData(Request $request, $id)
    {
        $achievement = StudentAchievement::with('student')->findOrFail($id);

        $request->validate([
            'approval_notes' => 'nullable|string',
        ]);

        $achievement->update([
            'status' => 'approved',
            'approval_notes' => $request->approval_notes,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        NotificationHelper::notifyStudent(
            $achievement->student_id,
            'skpi.achievement.approved',
            'Data Aktivitas SKPI Disetujui',
            'Data "' . ($achievement->activity_type_label ?? $achievement->activity_type) . '" pada kategori ' . $achievement->category_label . ' telah disetujui dan siap masuk ke SKPI.',
            route('student.personal.achievements.index'),
            ['student_achievement_id' => $achievement->id]
        );

        return redirect()
            ->route('admin.skpi.verifikasi-data.index')
            ->with('success', 'Prestasi mahasiswa berhasil disetujui.');
    }

    public function rejectVerifikasiData(Request $request, $id)
    {
        $achievement = StudentAchievement::with('student')->findOrFail($id);

        $request->validate([
            'approval_notes' => 'required|string',
        ], [
            'approval_notes.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $achievement->update([
            'status' => 'rejected',
            'approval_notes' => $request->approval_notes,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        NotificationHelper::notifyStudent(
            $achievement->student_id,
            'skpi.achievement.rejected',
            'Data Aktivitas SKPI Ditolak',
            'Data "' . ($achievement->activity_type_label ?? $achievement->activity_type) . '" pada kategori ' . $achievement->category_label . ' ditolak. Catatan: ' . $achievement->approval_notes,
            route('student.personal.achievements.index'),
            ['student_achievement_id' => $achievement->id]
        );

        return redirect()
            ->route('admin.skpi.verifikasi-data.index')
            ->with('success', 'Prestasi mahasiswa berhasil ditolak.');
    }

    public function approveAllVerifikasiData(Request $request)
    {
        $request->validate([
            'approval_notes' => 'nullable|string',
        ]);

        $achievements = StudentAchievement::with('student')
            ->where('status', 'pending')
            ->get();

        foreach ($achievements as $achievement) {
            $achievement->update([
                'status'         => 'approved',
                'approval_notes' => $request->approval_notes,
                'approved_by'    => auth()->id(),
                'approved_at'    => now(),
            ]);

            try {
                NotificationHelper::notifyStudent(
                    $achievement->student_id,
                    'skpi.achievement.approved',
                    'Data Aktivitas SKPI Disetujui',
                    'Data "' . ($achievement->activity_type_label ?? $achievement->activity_type) . '" pada kategori ' . $achievement->category_label . ' telah disetujui dan siap masuk ke SKPI.',
                    route('student.personal.achievements.index'),
                    ['student_achievement_id' => $achievement->id]
                );
            } catch (\Exception $e) {
                \Log::warning('Notifikasi approve all gagal: ' . $e->getMessage());
            }
        }

        return redirect()
            ->route('admin.skpi.verifikasi-data.index')
            ->with('success', "Berhasil approve {$achievements->count()} data prestasi mahasiswa.");
    }

    public function generateSkpi(Request $request)
    {
        $generateData = $this->prepareGenerateData($request);

        return view('admin.skpi.generate.index', array_merge(
            $generateData,
            [
                'documentMeta' => $this->resolveDocumentMeta($request),
            ]
        ));
    }

    public function storeGenerateMetadata(Request $request)
    {
        $validated = $request->validate([
            'registration_id' => 'nullable|integer',
            'achievement_ids' => 'nullable|array',
            'achievement_ids.*' => 'integer',
            'nomor_skpi' => 'nullable|string|max:255',
            'authorization_place_date' => 'nullable|string|max:255',
            'vice_rector_name' => 'nullable|string|max:255',
            'vice_rector_title' => 'nullable|string|max:255',
            'signature_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'signature_image.image' => 'File tanda tangan harus berupa gambar.',
            'signature_image.mimes' => 'Format tanda tangan harus JPG, PNG, atau WEBP.',
            'signature_image.max' => 'Ukuran file tanda tangan maksimal 2 MB.',
        ]);

        $setting = SkpiDocumentSetting::query()->first() ?? new SkpiDocumentSetting();

        if ($request->hasFile('signature_image')) {
            $oldPath = $setting->signature_path;

            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            $setting->signature_path = $request->file('signature_image')->store('skpi/signatures', 'public');
        }

        $setting->nomor_skpi = trim((string) ($validated['nomor_skpi'] ?? ''));
        $setting->authorization_place_date = trim((string) ($validated['authorization_place_date'] ?? ''))
            ?: ('Sukoharjo, ' . now()->translatedFormat('d F Y'));
        $setting->vice_rector_name = trim((string) ($validated['vice_rector_name'] ?? ''));
        $setting->vice_rector_title = trim((string) ($validated['vice_rector_title'] ?? 'Wakil Rektor I Universitas Sugeng Hartono')) ?: 'Wakil Rektor I Universitas Sugeng Hartono';
        $setting->updated_by = auth()->id();
        $setting->save();



        return redirect()
            ->route('admin.skpi.input-data-akademi.index')
            ->with('success', 'Informasi pengesahan SKPI berhasil disimpan.');
    }

    private function prepareGenerateData(Request $request): array
    {
        $manualCategories = array_keys(StudentAchievement::manualCategoryOptions());
        $studyPrograms = StudyProgram::query()
            ->with('skpiAcademicProfile')
            ->where('is_active', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $selectedStudyProgramIdFilter = $request->integer('study_program_id');
        $generateStatusFilter = $request->input('generate_status');


        $approvedRegistrationsQuery = SkpiRegistration::query()
            ->with('student')
            ->where('status', 'approved')
            ->whereHas('student')
            ->orderBy('nama_lengkap');

        if ($selectedStudyProgramIdFilter) {
            $studyProgram = StudyProgram::find($selectedStudyProgramIdFilter);
            if ($studyProgram) {
                $approvedRegistrationsQuery->whereHas('student', function ($q) use ($studyProgram) {
                    $q->where('program_studi', $studyProgram->name);
                });
            }
        }

        if ($generateStatusFilter === 'belum') {
            $approvedRegistrationsQuery->whereNull('skpi_document');
        } elseif ($generateStatusFilter === 'sudah') {
            $approvedRegistrationsQuery->whereNotNull('skpi_document');
        }





        $approvedRegistrations = $approvedRegistrationsQuery->paginate(10)->appends($request->query());

        $selectedRegistrationId = (int) ($request->integer('registration_id') ?: optional($approvedRegistrations->first())->id);

        $selectedRegistration = SkpiRegistration::query()
            ->with(['student.finalProject', 'approver'])
            ->where('status', 'approved')
            ->whereKey($selectedRegistrationId)
            ->first();

        $selectedStudent = $selectedRegistration?->student;
        $selectedStudyProgram = null;
        $academicProfile = null;
        $approvedAchievements = collect();
        $selectedAchievementIds = [];
        $selectedAchievements = collect();
        $automaticEntries = collect();

        if ($selectedStudent) {
            $selectedStudyProgram = StudyProgram::query()
                ->where('name', $selectedStudent->program_studi)
                ->first();

            if ($selectedStudyProgram) {
                $academicProfile = SkpiAcademicProfile::query()
                    ->where('study_program_id', $selectedStudyProgram->id)
                    ->first();
            }

            $approvedAchievements = StudentAchievement::query()
                ->where('student_id', $selectedStudent->id)
                ->where('status', 'approved')
                ->whereIn('category', $manualCategories)
                ->latest()
                ->get();

            $selectedAchievementIds = collect($request->input('achievement_ids', []))
                ->map(fn($id) => (int) $id)
                ->filter(fn($id) => $approvedAchievements->contains('id', $id))
                ->values()
                ->all();

            if (count($selectedAchievementIds) === 0) {
                $selectedAchievementIds = $approvedAchievements->pluck('id')->all();
            }

            $selectedAchievements = $approvedAchievements
                ->whereIn('id', $selectedAchievementIds)
                ->values();

            if (filled($selectedStudent->finalProject?->title)) {
                $automaticEntries->push((object) [
                    'id' => 'skripsi-auto-' . $selectedStudent->id,
                    'category' => StudentAchievement::CATEGORY_SKRIPSI,
                    'category_label' => StudentAchievement::categoryOptions()[StudentAchievement::CATEGORY_SKRIPSI],
                    'event' => $selectedStudent->finalProject->title,
                    'achievement' => 'Judul Skripsi / Tugas Akhir',
                    'level' => $selectedStudent->program_studi ?? '-',
                    'source' => 'Tugas Akhir',
                    'status' => 'approved',
                ]);
            }
        }

        $stats = [
            'approved_registrations' => $approvedRegistrations->total(),
            'selected_achievements' => $selectedAchievements->count() + $automaticEntries->count(),
            'approved_achievements' => $approvedAchievements->count(),
            'academic_profile_ready' => $academicProfile
                ? $this->countCompletedAcademicFields($academicProfile, $this->academicFieldLabels())
                : 0,
        ];

        return compact(
            'academicProfile',
            'automaticEntries',
            'approvedAchievements',
            'approvedRegistrations',

            'generateStatusFilter',

            'selectedAchievementIds',
            'selectedAchievements',
            'selectedRegistration',
            'selectedRegistrationId',
            'selectedStudent',
            'selectedStudyProgram',
            'studyPrograms',
            'selectedStudyProgramIdFilter',
            'stats',
        );
    }

    private function academicFieldLabels(): array
    {
        return [
            'study_program_id' => 'Program Studi',
            'sk_pendirian_perguruan_tinggi' => 'SK Pendirian Perguruan Tinggi',
            'nama_perguruan_tinggi' => 'Nama Perguruan Tinggi',
            'akreditasi_perguruan_tinggi' => 'Akreditasi Perguruan Tinggi',
            'akreditasi_program_studi' => 'Akreditasi Program Studi',
            'jenis_dan_jenjang_pendidikan' => 'Jenis dan Jenjang Pendidikan',
            'jenjang_kualifikasi_kkni' => 'Jenjang Kualifikasi Sesuai KKNI',
            'persyaratan_penerimaan' => 'Persyaratan Penerimaan',
            'bahasa_pengantar_kuliah' => 'Bahasa Pengantar Kuliah',
            'nomor_akreditasi_perguruan_tinggi' => 'Nomor Akreditasi Perguruan Tinggi',
            'sistem_penilaian' => 'Sistem Penilaian',
            'lama_studi' => 'Lama Studi',
            'nomor_akreditasi_program_studi' => 'Nomor Akreditasi Program Studi',
            'status_profesi'                  => 'Status Profesi',
            'gelar_lulusan'                   => 'Gelar Lulusan',
        ];
    }

    /**
     * Simpan/update Point 4 – Kualifikasi & Capaian Pembelajaran per prodi.
     * Data disimpan ke tabel skpi_learning_outcomes (terpisah dari skpi_academic_profiles).
     */
    public function storeLearningOutcome(Request $request)
    {
        $validated = $request->validate([
            'study_program_id'          => 'required|exists:study_programs,id',
            'cp_sikap'                  => 'nullable|array',
            'cp_sikap.*'                => 'nullable|string',
            'cp_sikap_en'               => 'nullable|array',
            'cp_sikap_en.*'             => 'nullable|string',
            'cp_pengetahuan'            => 'nullable|array',
            'cp_pengetahuan.*'          => 'nullable|string',
            'cp_pengetahuan_en'         => 'nullable|array',
            'cp_pengetahuan_en.*'       => 'nullable|string',
        ]);

        // Helper function to clean array (remove empty, trim, reindex)
        $cleanArray = function ($arr) {
            if (!is_array($arr)) return null;
            $filtered = array_values(array_filter(array_map('trim', $arr), function ($val) {
                return $val !== '';
            }));
            return empty($filtered) ? null : $filtered;
        };

        SkpiLearningOutcome::updateOrCreate(
            ['study_program_id' => $validated['study_program_id']],
            [
                'cp_sikap'          => $cleanArray($request->input('cp_sikap')),
                'cp_sikap_en'       => $cleanArray($request->input('cp_sikap_en')),
                'cp_pengetahuan'    => $cleanArray($request->input('cp_pengetahuan')),
                'cp_pengetahuan_en' => $cleanArray($request->input('cp_pengetahuan_en')),
            ]
        );

        return redirect()
            ->route('admin.skpi.input-data-akademi.index', ['study_program_id' => $validated['study_program_id']])
            ->with('success', 'Data Kualifikasi & Capaian Pembelajaran (Point 4) berhasil disimpan.');
    }

    private function countCompletedAcademicFields(?SkpiAcademicProfile $profile, array $fieldLabels): int
    {
        return collect(array_keys($fieldLabels))
            ->filter(function ($field) use ($profile) {
                return filled($profile?->{$field});
            })
            ->count();
    }

    private function resolveLogoDataUri(): ?string
    {
        $logoPath = public_path('ush.png');

        if (!File::exists($logoPath)) {
            return null;
        }

        $contents = File::get($logoPath);
        $mime = mime_content_type($logoPath) ?: 'image/png';

        return 'data:' . $mime . ';base64,' . base64_encode($contents);
    }

    private function resolveDocumentMeta(Request $request): array
    {
        $setting = SkpiDocumentSetting::query()->latest('updated_at')->first();
        $defaultPlaceDate = 'Sukoharjo, ' . now()->translatedFormat('d F Y');

        $sigPath = $setting?->signature_path;
        $sigDataUri = $this->resolveStorageDataUri($sigPath);

        return [
            'nomor_skpi' => $setting?->nomor_skpi ?? '',
            'authorization_place_date' => $setting?->authorization_place_date ?: $defaultPlaceDate,
            'vice_rector_name' => $setting?->vice_rector_name ?? '',
            'vice_rector_title' => $setting?->vice_rector_title ?: 'Wakil Rektor I Universitas Sugeng Hartono',
            'signature_path' => $sigPath,
            'signature_url' => !empty($sigPath) ? asset('storage/' . ltrim($sigPath, '/')) : null,
            'signature_data_uri' => $sigDataUri,
        ];
    }

    private function resolveStorageDataUri(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path(ltrim($path, '/'));

        if (!\Illuminate\Support\Facades\File::exists($fullPath)) {
            $fullPath = public_path('storage/' . ltrim($path, '/'));
        }

        if (!\Illuminate\Support\Facades\File::exists($fullPath)) {
            return null;
        }

        $mime = mime_content_type($fullPath) ?: 'image/png';

        return 'data:' . $mime . ';base64,' . base64_encode(File::get($fullPath));
    }
    public function AproveAllDaftarSkpi(Request $request)
    {
        $request->validate([
            'approval_notes' => 'nullable|string',
        ]);

        $registrations = SkpiRegistration::with('student.finalProject')
            ->where('status', 'pending')
            ->get();

        $approved  = 0;
        $skipped   = 0;
        $skipNames = [];

        foreach ($registrations as $registration) {
            $student = $registration->student;

            // ── Validasi prasyarat (sama dengan approveDaftarSkpi) ──
            $issues = [];

            if (!filled($student?->ipk) || !filled($student?->sks)) {
                $issues[] = 'IPK/SKS belum lengkap';
            }
            if (!filled(optional($student?->finalProject)->title)) {
                $issues[] = 'Tugas Akhir belum ada';
            }
            if (!filled($student?->foto) || !filled($student?->ttd)) {
                $issues[] = 'Foto/TTD belum diupload';
            }

            // Jika ada kekurangan, skip mahasiswa ini
            if (count($issues) > 0) {
                $skipped++;
                $skipNames[] = ($registration->nama_lengkap ?? 'Mahasiswa') . ' (' . implode(', ', $issues) . ')';
                continue;
            }

            // Lolos validasi → approve
            $registration->update([
                'status'         => 'approved',
                'approval_notes' => $request->approval_notes,
                'approved_by'    => auth()->id(),
                'approved_at'    => now(),
            ]);

            $approved++;

            try {
                NotificationHelper::notifyStudent(
                    $registration->student_id,
                    'skpi.registration.approved',
                    'Pendaftaran SKPI Disetujui',
                    'Pendaftaran SKPI Anda telah disetujui. SKPI Anda sudah siap untuk diunduh.',
                    route('student.skpi.index'),
                    ['skpi_registration_id' => $registration->id]
                );
            } catch (\Exception $e) {
                \Log::warning('Notifikasi approve SKPI gagal: ' . $e->getMessage());
            }
        }

        // ── Susun pesan hasil ──
        $message = "Berhasil approve {$approved} pendaftaran SKPI.";
        if ($skipped > 0) {
            $message .= " {$skipped} dilewati karena data belum lengkap: " . implode('; ', $skipNames) . '.';
        }

        return redirect()
            ->route('admin.skpi.daftar-skpi.index')
            ->with($approved > 0 ? 'success' : 'error', $message);
    }

    /**
     * Download file SKPI yang sudah tersimpan terenkripsi di database.
     * File akan didekripsi terlebih dahulu sebelum dikirim ke browser.
     */
    public function downloadSavedSkpi(int $id)
    {
        $registration = SkpiRegistration::findOrFail($id);

        if (!$registration->hasGeneratedDocument()) {
            return redirect()->back()->with('error', 'File SKPI untuk mahasiswa ini belum pernah di-generate oleh admin.');
        }

        try {
            $decrypted = SkpiDocumentEncryption::decrypt($registration->skpi_document);
        } catch (\Throwable $e) {
            \Log::error('[SkpiWord] Gagal mendekripsi file SKPI ID=' . $id . ': ' . $e->getMessage());
            return redirect()->back()->with('error', 'File SKPI tidak dapat dibaca. Silakan generate ulang.');
        }

        $safeNim  = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $registration->nim) ?: 'student';
        $fileName = 'SKPI_' . $safeNim . '.docx';

        return response($decrypted, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Content-Length'      => strlen($decrypted),
        ]);
    }
}
