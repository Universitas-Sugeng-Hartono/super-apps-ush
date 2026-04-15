<?php

namespace App\Http\Controllers\Student;

use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Models\SkpiAcademicProfile;
use App\Models\SkpiDocumentSetting;
use App\Models\SkpiRegistration;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\StudyProgram;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SkpiController extends Controller
{
    public function index()
    {
        $student = $this->getStudent();
        $registrationChecklist = $this->buildRegistrationChecklist($student);
        $registrationMeta = $this->buildRegistrationMeta($registrationChecklist);
        $stats = $this->buildStats($student);
        $achievementMeta = $this->buildAchievementMeta($student);
        $skpiRegistration = $student->skpiRegistration;
        $registrationStatus = $this->buildStatusMeta($skpiRegistration?->status);
         // Cek apakah tugas akhir sudah siap (checklist index 1)
        $tugasAkhirReady = $registrationChecklist[1]['ready'];
        $menus = [
            [
                'title' => 'Data Pemegang SKPI',
                'description' => 'Cek identitas utama seperti nama, NIM, tempat lahir, tanggal lahir, dan data diri dasar.',
                'icon' => 'bi bi-person-vcard',
                'badge' => $registrationChecklist[0]['ready'] ? 'Siap' : 'Lengkapi',
                'badge_class' => $registrationChecklist[0]['ready'] ? 'active' : 'warning',
                'href' => route('student.personal.editDataIndex'),
            ],
            [
                'title' => 'Prestasi & Penghargaan',
                'description' => $achievementMeta['description'],
                'icon' => 'bi bi-trophy',
                'badge' => $achievementMeta['badge'],
                'badge_class' => $achievementMeta['badge_class'],
                'href' => route('student.personal.achievements.index'),
            ],
            [
                'title' => ' Tugas Akhir',
                'description' => 'Pastikan Anda sudah menyelesaikan Tugas Akhir/Skripsi .',
                'icon' => 'bi bi-journal-check',
                'badge' => $registrationChecklist[1]['ready'] ? 'Siap' : 'Cek Data',
                'badge_class' => $registrationChecklist[1]['ready'] ? 'active' : 'info',
                'href' => route('student.final-project.index'),
            ],
            [
                'title' => 'Foto & Tanda Tangan',
                'description' => 'Lengkapi foto profil dan tanda tangan agar dokumen pendukung SKPI siap digunakan saat proses akhir.',
                'icon' => 'bi bi-pencil-square',
                'badge' => $registrationChecklist[2]['ready'] ? 'Lengkap' : '2 Dokumen',
                'badge_class' => $registrationChecklist[2]['ready'] ? 'active' : 'warning',
                'href' => route('student.personal.editDataIndex'),
            ],
        ];

        return view('students.skpi.index', compact(
            'student',
            'stats',
            'achievementMeta',
            'menus',
            'registrationChecklist',
            'registrationMeta',
            'skpiRegistration',
            'registrationStatus',
            'tugasAkhirReady'
        ));
    }

    public function daftarIndex()
    {
        $student = $this->getStudent();
        $skpiRegistration = $student->skpiRegistration;
        $registrationChecklist = $this->buildRegistrationChecklist($student);
        $registrationMeta = $this->buildRegistrationMeta($registrationChecklist);
        $holderFields = $this->buildHolderFields($this->buildHolderData($student, $skpiRegistration));
        $holderMeta = $this->buildHolderMeta($holderFields);
        $registrationStatus = $this->buildStatusMeta($skpiRegistration?->status);
        $canEditRegistration = $this->canEditRegistration($skpiRegistration);

        return view('students.skpi.daftar.index', compact(
            'student',
            'skpiRegistration',
            'registrationChecklist',
            'registrationMeta',
            'holderFields',
            'holderMeta',
            'registrationStatus',
            'canEditRegistration'
        ));
    }

    public function daftarCreate(Request $request)
    {
        $student = $this->getStudent();
        $skpiRegistration = $student->skpiRegistration;
        $holderData = $this->buildHolderData($student, $skpiRegistration, $request);
        $holderFields = $this->buildHolderFields($holderData);
        $holderMeta = $this->buildHolderMeta($holderFields);
        $registrationStatus = $this->buildStatusMeta($skpiRegistration?->status);
        $canEditRegistration = $this->canEditRegistration($skpiRegistration);

        return view('students.skpi.daftar.create', compact(
            'student',
            'skpiRegistration',
            'holderData',
            'holderFields',
            'holderMeta',
            'registrationStatus',
            'canEditRegistration'
        ));
    }

    public function daftarStore(Request $request)
    {
        $student = $this->getStudent();
        $skpiRegistration = $student->skpiRegistration;

        // Server-side: cek prasyarat sebelum izinkan submit
        // (Tidak cek Data Pemegang SKPI karena itu data yang sedang dikirim via form ini)
        $hasAcademicProfile = filled($student->ipk) && filled($student->sks);
        $hasFinalProjectData = filled(optional($student->finalProject)->title);
        $hasDocumentSupport = filled($student->foto) && filled($student->ttd);

        if (!$hasAcademicProfile || !$hasFinalProjectData || !$hasDocumentSupport) {
            return redirect()
                ->route('student.skpi.index')
                ->with('error', 'Anda belum memenuhi semua persyaratan untuk mendaftar SKPI. Pastikan tugas akhir sudah selesai dan dokumen (foto/tanda tangan) sudah lengkap.');
        }

        if ($skpiRegistration && !$this->canEditRegistration($skpiRegistration)) {
            return redirect()
                ->route('student.skpi.daftar.show')
                ->with('error', 'Pendaftaran SKPI dengan status saat ini tidak dapat diubah.');
        }

        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:200',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'nim' => 'required|string|max:20',
            'angkatan' => 'required|integer|min:1900|max:2100',
            'nomor_ijazah' => 'required|string|max:100',
            'gelar' => 'required|string|max:100',
        ], [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'tempat_lahir.required' => 'Tempat lahir wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'nim.required' => 'NIM wajib diisi.',
            'angkatan.required' => 'Tahun masuk wajib diisi.',
            'nomor_ijazah.required' => 'Nomor ijazah wajib diisi.',
            'gelar.required' => 'Gelar wajib diisi.',
        ]);

        $registration = SkpiRegistration::updateOrCreate(
            ['student_id' => $student->id],
            array_merge($validated, [
                'status' => 'pending',
                'submitted_at' => now(),
                'approved_by' => null,
                'approved_at' => null,
                'approval_notes' => null,
            ])
        );

        $recipientIds = NotificationHelper::kaprodiAndSuperuserUserIdsForProdi(
            NotificationHelper::prodiFromStudent($student)
        );

        NotificationHelper::notifyUsers(
            $recipientIds,
            'skpi.registration.submitted',
            'Pendaftaran SKPI Baru',
            "{$student->nama_lengkap} mengajukan pendaftaran SKPI dan menunggu review.",
            route('admin.skpi.daftar-skpi.index'),
            ['skpi_registration_id' => $registration->id]
        );

        return redirect()
            ->route('student.skpi.daftar.show')
            ->with('success', 'Pendaftaran SKPI berhasil disimpan dan dikirim untuk direview.');
    }

    public function daftarShow()
    {
        $student = $this->getStudent();
        $skpiRegistration = $student->skpiRegistration;

        if (!$skpiRegistration) {
            return redirect()
                ->route('student.skpi.daftar.create')
                ->with('error', 'Anda belum memiliki draft pendaftaran SKPI. Silakan isi form terlebih dahulu.');
        }

        $holderFields = $this->buildHolderFields($this->buildHolderData($student, $skpiRegistration));
        $holderMeta = $this->buildHolderMeta($holderFields);
        $registrationStatus = $this->buildStatusMeta($skpiRegistration->status);
        $canEditRegistration = $this->canEditRegistration($skpiRegistration);

        return view('students.skpi.daftar.show', compact(
            'student',
            'skpiRegistration',
            'holderFields',
            'holderMeta',
            'registrationStatus',
            'canEditRegistration'
        ));
    }

public function downloadPdf()
{
    $student = $this->getStudent();
    $registration = $student->skpiRegistration;

    if (!$registration) {
        return redirect()->route('student.skpi.daftar.index')
            ->with('error', 'Anda belum memiliki pengajuan SKPI.');
    }

    if ($registration->status !== 'approved') {
        return redirect()->route('student.skpi.daftar.show')
            ->with('error', 'PDF SKPI baru bisa diunduh setelah pengajuan Anda disetujui.');
    }

    $studyProgram = StudyProgram::query()
        ->where('name', $student->program_studi)
        ->first();

    $academicProfile = $studyProgram
        ? SkpiAcademicProfile::query()->where('study_program_id', $studyProgram->id)->first()
        : null;

    // Otomatis ambil semua achievement approved milik mahasiswa ini
    $manualCategories = array_keys(StudentAchievement::manualCategoryOptions());
    $selectedAchievements = StudentAchievement::query()
        ->where('student_id', $student->id)
        ->where('status', 'approved')
        ->whereIn('category', $manualCategories)
        ->latest()
        ->get();

    $automaticEntries = collect();
    if (filled($student->finalProject?->title)) {
        $automaticEntries->push((object) [
            'id'             => 'skripsi-auto-' . $student->id,
            'category'       => StudentAchievement::CATEGORY_SKRIPSI,
            'category_label' => StudentAchievement::categoryOptions()[StudentAchievement::CATEGORY_SKRIPSI],
            'event'          => $student->finalProject->title,
            'achievement'    => 'Judul Skripsi / Tugas Akhir',
            'level'          => $student->program_studi ?? '-',
            'source'         => 'Tugas Akhir',
            'status'         => 'approved',
        ]);
    }

    $documentMeta = $this->resolveDocumentMeta();
    $documentMeta['nomor_skpi'] = $registration->nomor_skpi ?? $documentMeta['nomor_skpi'];
    $signatureDataUri = $this->resolveStorageDataUri($documentMeta['signature_path'] ?? null);
    $logoDataUri = $this->resolveLogoDataUri();

    $safeNim = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $registration->nim) ?: 'student';
    $downloadName = 'SKPI-' . $safeNim . '.pdf';

    $pdf = Pdf::loadView('admin.skpi.generate.pdf', [
        'selectedRegistration' => $registration,
        'selectedStudent'      => $student,
        'academicProfile'      => $academicProfile,
        'selectedAchievements' => $selectedAchievements,
        'automaticEntries'     => $automaticEntries,
        'documentMeta'         => $documentMeta,
        'signatureDataUri'     => $signatureDataUri,
        'logoDataUri'          => $logoDataUri,
    ])->setPaper('a4');

    return $pdf->download($downloadName);
}

    private function getStudent(): Student
    {
        return Student::withCount([
                'achievements',
                'achievements as approved_achievements_count' => function ($query) {
                    $query->where('status', 'approved');
                },
                'achievements as pending_achievements_count' => function ($query) {
                    $query->where('status', 'pending');
                },
                'achievements as rejected_achievements_count' => function ($query) {
                    $query->where('status', 'rejected');
                },
            ])
            ->with(['finalProject', 'skpiRegistration'])
            ->findOrFail(decrypt(session('student_id')));
    }

    private function canEditRegistration(?SkpiRegistration $registration): bool
    {
        return !$registration || in_array($registration->status, ['needs_revision', 'rejected'], true);
    }

    private function buildStats(Student $student): array
    {
        return [
            'prestasi_total' => $student->achievements_count ?? 0,
            'prestasi_approved' => $student->approved_achievements_count ?? 0,
            'prestasi_pending' => $student->pending_achievements_count ?? 0,
            'prestasi_rejected' => $student->rejected_achievements_count ?? 0,
            'ipk' => $student->ipk ?? '0.00',
            'sks' => $student->sks ?? 0,
            'dokumen' => ($student->foto ? 1 : 0) + ($student->ttd ? 1 : 0),
        ];
    }

    private function buildRegistrationChecklist(Student $student): array
    {
        $registration = $student->skpiRegistration;
        $holderData = $this->buildHolderData($student, $registration);
        $birthIdentityComplete = filled($holderData['tempat_lahir']) && filled($holderData['tanggal_lahir']);
        $hasAcademicProfile = filled($student->ipk) && filled($student->sks);
        $hasFinalProjectData = filled(optional($student->finalProject)->title);
        $hasDocumentSupport = filled($student->foto) && filled($student->ttd);
        $hasApprovedAchievements = ($student->approved_achievements_count ?? 0) > 0;

        return [
            [
                'title' => 'Data Pemegang SKPI',
                'description' => 'Identitas dasar seperti nama, tempat lahir, tanggal lahir, dan NIM perlu sudah siap.',
                'ready' => $birthIdentityComplete,
                'required' => true,
            ],
            [
                'title' => 'Tugas Akhir',
                'description' => 'Data Tugas akhir yang sudah di selesaikan !.',
                'ready' => $hasAcademicProfile && $hasFinalProjectData,
                'required' => true,
            ],
            [
                'title' => 'Foto & Tanda Tangan',
                'description' => 'Dokumen pendukung visual sudah lengkap untuk proses akhir.',
                'ready' => $hasDocumentSupport,
                'required' => true,
            ],
            [
                'title' => 'Prestasi & Penghargaan',
                'description' => 'Bagian ini opsional. Hanya prestasi yang sudah approved yang akan masuk ke SKPI.',
                'ready' => $hasApprovedAchievements,
                'required' => false,
            ],
        ];
    }

    private function buildAchievementMeta(Student $student): array
    {
        $total = (int) ($student->achievements_count ?? 0);
        $approved = (int) ($student->approved_achievements_count ?? 0);
        $pending = (int) ($student->pending_achievements_count ?? 0);
        $rejected = (int) ($student->rejected_achievements_count ?? 0);

        if ($approved > 0) {
            return [
                'badge' => $approved . ' Approved',
                'badge_class' => 'active',
                'description' => "Hanya prestasi yang sudah approved yang masuk ke SKPI. Saat ini {$approved} approved, {$pending} pending, dan {$rejected} rejected.",
            ];
        }

        if ($pending > 0) {
            return [
                'badge' => $pending . ' Pending',
                'badge_class' => 'warning',
                'description' => "Anda punya {$pending} prestasi yang sedang direview. Data ini belum masuk ke SKPI sampai statusnya approved.",
            ];
        }

        if ($rejected > 0) {
            return [
                'badge' => $rejected . ' Rejected',
                'badge_class' => 'danger',
                'description' => "Ada {$rejected} prestasi yang ditolak. Perbarui data prestasi agar bisa diajukan ulang untuk SKPI.",
            ];
        }

        return [
            'badge' => $total > 0 ? $total . ' Draft' : 'Belum Ada',
            'badge_class' => $total > 0 ? 'info' : 'warning',
            'description' => 'Tambahkan data prestasi, organisasi, magang, atau skill certificate. Skripsi akan diambil otomatis dari Tugas Akhir. Hanya data yang approved yang akan dipakai saat generate SKPI.',
        ];
    }

    private function buildRegistrationMeta(array $registrationChecklist): array
    {
        $requiredChecklistCount = collect($registrationChecklist)->where('required', true)->count();
        $completedPreparation = collect($registrationChecklist)
            ->where('required', true)
            ->where('ready', true)
            ->count();

        return [
            'required_count' => $requiredChecklistCount,
            'completed_count' => $completedPreparation,
            'ready' => $completedPreparation === $requiredChecklistCount,
        ];
    }

    private function buildHolderData(
        Student $student,
        ?SkpiRegistration $registration = null,
        ?Request $request = null
    ): array {
        $defaults = [
            'nama_lengkap' => $registration?->nama_lengkap ?? $student->nama_lengkap,
            'tempat_lahir' => $registration?->tempat_lahir ?? $student->tempat_lahir,
            'tanggal_lahir' => optional($registration?->tanggal_lahir ?? $student->tanggal_lahir)->format('Y-m-d'),
            'nim' => $registration?->nim ?? $student->nim,
            'angkatan' => $registration?->angkatan ?? $student->angkatan,
            'nomor_ijazah' => $registration?->nomor_ijazah,
            'gelar' => $registration?->gelar,
        ];

        return [
            'nama_lengkap' => old('nama_lengkap', $request?->input('nama_lengkap', $defaults['nama_lengkap']) ?? $defaults['nama_lengkap']),
            'tempat_lahir' => old('tempat_lahir', $request?->input('tempat_lahir', $defaults['tempat_lahir']) ?? $defaults['tempat_lahir']),
            'tanggal_lahir' => old('tanggal_lahir', $request?->input('tanggal_lahir', $defaults['tanggal_lahir']) ?? $defaults['tanggal_lahir']),
            'nim' => old('nim', $request?->input('nim', $defaults['nim']) ?? $defaults['nim']),
            'angkatan' => old('angkatan', $request?->input('angkatan', $defaults['angkatan']) ?? $defaults['angkatan']),
            'nomor_ijazah' => old('nomor_ijazah', $request?->input('nomor_ijazah', $defaults['nomor_ijazah']) ?? $defaults['nomor_ijazah']),
            'gelar' => old('gelar', $request?->input('gelar', $defaults['gelar']) ?? $defaults['gelar']),
        ];
    }

    private function buildHolderFields(array $holderData): array
    {
        return [
            [
                'key' => 'nama_lengkap',
                'label' => 'Nama Lengkap',
                'value' => $holderData['nama_lengkap'] ?? null,
                'display' => $holderData['nama_lengkap'] ?? null,
            ],
            [
                'key' => 'tempat_lahir',
                'label' => 'Tempat Lahir',
                'value' => $holderData['tempat_lahir'] ?? null,
                'display' => $holderData['tempat_lahir'] ?? null,
            ],
            [
                'key' => 'tanggal_lahir',
                'label' => 'Tanggal Lahir',
                'value' => $holderData['tanggal_lahir'] ?? null,
                'display' => filled($holderData['tanggal_lahir'] ?? null)
                    ? \Carbon\Carbon::parse($holderData['tanggal_lahir'])->translatedFormat('d F Y')
                    : null,
            ],
            [
                'key' => 'nim',
                'label' => 'Nomor Induk Mahasiswa',
                'value' => $holderData['nim'] ?? null,
                'display' => $holderData['nim'] ?? null,
            ],
            [
                'key' => 'angkatan',
                'label' => 'Tahun Masuk',
                'value' => $holderData['angkatan'] ?? null,
                'display' => $holderData['angkatan'] ?? null,
            ],
            [
                'key' => 'nomor_ijazah',
                'label' => 'Nomor Ijazah',
                'value' => $holderData['nomor_ijazah'] ?? null,
                'display' => $holderData['nomor_ijazah'] ?? null,
            ],
            [
                'key' => 'gelar',
                'label' => 'Gelar',
                'value' => $holderData['gelar'] ?? null,
                'display' => $holderData['gelar'] ?? null,
            ],
        ];
    }

    private function buildHolderMeta(array $holderFields): array
    {
        $filledCount = collect($holderFields)
            ->filter(fn ($field) => filled($field['value']))
            ->count();

        return [
            'filled_count' => $filledCount,
            'total_count' => count($holderFields),
            'complete' => $filledCount === count($holderFields),
            'missing_fields' => collect($holderFields)
                ->filter(fn ($field) => blank($field['value']))
                ->pluck('label')
                ->values(),
        ];
    }

    private function buildStatusMeta(?string $status): array
    {
        return match ($status) {
            'approved' => [
                'value' => 'approved',
                'label' => 'Approved',
                'badge_class' => 'active',
                'description' => 'Pendaftaran SKPI Anda sudah disetujui.',
            ],
            'needs_revision' => [
                'value' => 'needs_revision',
                'label' => 'Need Revision',
                'badge_class' => 'info',
                'description' => 'Pendaftaran SKPI perlu diperbaiki sebelum diajukan kembali.',
            ],
            'rejected' => [
                'value' => 'rejected',
                'label' => 'Rejected',
                'badge_class' => 'danger',
                'description' => 'Pendaftaran SKPI ditolak dan perlu disesuaikan ulang.',
            ],
            'pending' => [
                'value' => 'pending',
                'label' => 'Pending',
                'badge_class' => 'warning',
                'description' => 'Pendaftaran SKPI sedang menunggu review.',
            ],
            default => [
                'value' => null,
                'label' => 'Belum Diajukan',
                'badge_class' => 'muted',
                'description' => 'Anda belum mengajukan pendaftaran SKPI.',
            ],
        };
    }

    private function resolveDocumentMeta(): array
    {
        $setting = SkpiDocumentSetting::query()->latest('updated_at')->first();
        $defaultPlaceDate = 'Sukoharjo, ' . now()->translatedFormat('d F Y');

        return [
            'nomor_skpi' => $setting?->nomor_skpi ?? '',
            'authorization_place_date' => $setting?->authorization_place_date ?: $defaultPlaceDate,
            'vice_rector_name' => $setting?->vice_rector_name ?? '',
            'vice_rector_title' => $setting?->vice_rector_title ?: 'Wakil Rektor I Universitas Sugeng Hartono',
            'signature_path' => $setting?->signature_path,
        ];
    }

    private function resolveLogoDataUri(): ?string
    {
        $logoPath = public_path('ush.png');
        
        if (!File::exists($logoPath)) {
            $logoPath = public_path('img/logo-ush.png'); // Fallback ke path standar lain jika ada
        }

        if (!File::exists($logoPath)) {
            return null;
        }

        $contents = File::get($logoPath);
        $mime = mime_content_type($logoPath) ?: 'image/png';

        return 'data:' . $mime . ';base64,' . base64_encode($contents);
    }

    private function resolveStorageDataUri(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $fullPath = storage_path('app/public/' . $path);

        if (!File::exists($fullPath)) {
            return null;
        }

        $mime = mime_content_type($fullPath) ?: 'image/png';

        return 'data:' . $mime . ';base64,' . base64_encode(File::get($fullPath));
    }
}
