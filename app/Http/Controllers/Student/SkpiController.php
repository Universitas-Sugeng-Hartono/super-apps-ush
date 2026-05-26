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
use App\Services\SkpiDocumentEncryption;
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
        // Cek apakah tugas akhir sudah siap langsung dari relasi finalProject
        $tugasAkhirReady = $student->finalProject && filled($student->finalProject->title) && $student->finalProject->defense?->status === 'approved';

        $hasDocumentSupport = filled($student->foto) && filled($student->ttd);

        $menus = [
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
                'description' => $student->finalProject && $student->finalProject->title 
                    ? $student->finalProject->title . ($student->finalProject->title_en ? ' (' . $student->finalProject->title_en . ')' : '')
                    : 'Pastikan Anda sudah menyelesaikan Tugas Akhir/Skripsi.',
                'icon' => 'bi bi-journal-check',
                'badge' => $tugasAkhirReady ? 'Siap' : 'Cek Data',
                'badge_class' => $tugasAkhirReady ? 'active' : 'info',
                'href' => route('student.final-project.index'),
            ],
            [
                'title' => 'Profile',
                'description' => 'Lengkapi foto profil dan tanda tangan agar dokumen pendukung SKPI siap digunakan saat proses akhir.',
                'icon' => 'bi bi-pencil-square',
                'badge' => $hasDocumentSupport ? 'Lengkap' : '2 Dokumen',
                'badge_class' => $hasDocumentSupport ? 'active' : 'warning',
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
        
        $birthIdentityComplete = filled($student->nama_lengkap) 
            && filled($student->tempat_lahir) 
            && filled($student->tanggal_lahir)
            && filled($student->nim)
            && filled($student->angkatan);

        $registrationStatus = $this->buildStatusMeta($skpiRegistration?->status);
        $canEditRegistration = $this->canEditRegistration($skpiRegistration);

        return view('students.skpi.daftar.index', compact(
            'student',
            'skpiRegistration',

            'registrationChecklist',
            'registrationMeta',
            'birthIdentityComplete',
            'registrationStatus',
            'canEditRegistration'
        ));
    }

    public function daftarCreate()
    {
        $student = $this->getStudent();
        $skpiRegistration = $student->skpiRegistration;

        $holderData = $this->buildHolderData($student, $skpiRegistration);
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

        if ($skpiRegistration && !$this->canEditRegistration($skpiRegistration)) {
            return redirect()
                ->route('student.skpi.daftar.index')
                ->with('error', 'Data identitas tidak dapat diubah karena status pengajuan saat ini tidak mengizinkan pengeditan.');
        }

        $validated = $request->validate([
            'ipk'          => 'required|numeric|min:0|max:4',
            'sks'          => 'required|integer|min:0',
            'judul_ta_indo'=> 'required|string|max:500',
            'judul_ta_inggris'=> 'nullable|string|max:500',
            'periode_lulus'=> 'required|date_format:Y-m',
            'lama_studi'   => 'required|string|max:255',
            'doc_ijasah'   => 'nullable|mimes:pdf|max:1024',
            'doc_ktp'      => 'nullable|mimes:pdf|max:1024',
        ]);

        // Ambil gelar otomatis dari profil prodi
        $studyProgram = \App\Models\StudyProgram::where('name', $student->program_studi)->first();
        $gelar = null;
        if ($studyProgram) {
            $academicProfile = \App\Models\SkpiAcademicProfile::where('study_program_id', $studyProgram->id)->first();
            $gelar = $academicProfile?->gelar_lulusan;
        }

        $finalProject = $student->finalProject;

        $lamaStudiStr = $validated['lama_studi'];

        $registrationData = [
            'nama_lengkap' => $student->nama_lengkap,
            'nim'          => $student->nim,
            'tempat_lahir' => $student->tempat_lahir,
            'tanggal_lahir'=> $student->tanggal_lahir,
            'angkatan'     => $student->angkatan,
            'gelar'        => $gelar,
            'status'       => 'draft',
            'ipk'          => $validated['ipk'],
            'sks'          => $validated['sks'],
            'judul_ta_indo'=> $validated['judul_ta_indo'],
            'judul_ta_inggris'=> $validated['judul_ta_inggris'] ?? null,
            'periode_lulus'=> $validated['periode_lulus'],
            'lama_studi'   => $lamaStudiStr,
        ];

        if ($request->hasFile('doc_ijasah')) {
            if ($skpiRegistration && $skpiRegistration->doc_ijasah) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($skpiRegistration->doc_ijasah);
            }
            $registrationData['doc_ijasah'] = $request->file('doc_ijasah')->store('skpi/ijasah', 'public');
        }

        if ($request->hasFile('doc_ktp')) {
            if ($skpiRegistration && $skpiRegistration->doc_ktp) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($skpiRegistration->doc_ktp);
            }
            $registrationData['doc_ktp'] = $request->file('doc_ktp')->store('skpi/ktp', 'public');
        }

        if ($skpiRegistration) {
            $skpiRegistration->update($registrationData);
        } else {
            $student->skpiRegistration()->create($registrationData);
        }

        return redirect()
            ->route('student.skpi.daftar.index')
            ->with('success', 'Data identitas SKPI berhasil disimpan sebagai draft.');
    }

    public function daftarSubmit(Request $request)
    {
        $student = $this->getStudent();
        $skpiRegistration = $student->skpiRegistration;

        $registrationChecklist = $this->buildRegistrationChecklist($student);
        $registrationMeta = $this->buildRegistrationMeta($registrationChecklist);

        if (!$registrationMeta['ready']) {
            return redirect()
                ->route('student.skpi.daftar.index')
                ->with('error', 'Gagal mengirim pengajuan! Anda belum memenuhi semua Prasyarat Sistem.');
        }

        if ($skpiRegistration && !$this->canEditRegistration($skpiRegistration)) {
            return redirect()
                ->route('student.skpi.daftar.index')
                ->with('error', 'Pendaftaran SKPI dengan status saat ini tidak dapat diajukan ulang.');
        }
        if ($skpiRegistration) {
            $skpiRegistration->update([
                'status' => 'pending',
                'submitted_at' => now(),
                'approval_notes' => null,
                'approved_by' => null,
                'approved_at' => null,
            ]);
        } else {
            return redirect()
                ->route('student.skpi.daftar.index')
                ->with('error', 'Gagal mengirim pengajuan! Simpan form identitas SKPI terlebih dahulu.');
        }

        $recipientIds = NotificationHelper::kaprodiAndSuperuserUserIdsForProdi(
            NotificationHelper::prodiFromStudent($student)
        );

        NotificationHelper::notifyUsers(
            $recipientIds,
            'skpi.registration.submitted',
            'Pendaftaran SKPI Baru',
            "{$student->nama_lengkap} mengajukan pendaftaran SKPI dan menunggu review.",
            route('admin.skpi.daftar-skpi.index'),
            ['skpi_registration_id' => $skpiRegistration->id]
        );

        return redirect()
            ->route('student.skpi.daftar.index')
            ->with('success', 'Pendaftaran SKPI berhasil dikirim untuk direview.');
    }

    public function daftarShow()
    {
        $student = $this->getStudent();
        $skpiRegistration = $student->skpiRegistration;

        if (!$skpiRegistration) {
            return redirect()
                ->route('student.skpi.daftar.index')
                ->with('error', 'Anda belum mengajukan pendaftaran SKPI.');
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

    public function downloadWord()
    {
        $student      = $this->getStudent();
        $registration = $student->skpiRegistration;

        if (!$registration) {
            return redirect()->route('student.skpi.daftar.index')
                ->with('error', 'Anda belum memiliki pengajuan SKPI.');
        }

        if ($registration->status !== 'approved') {
            return redirect()->route('student.skpi.daftar.index')
                ->with('error', 'Dokumen SKPI baru bisa diunduh setelah pengajuan Anda disetujui.');
        }

        if (!$registration->hasGeneratedDocument()) {
            return redirect()->route('student.skpi.daftar.index')
                ->with('error', 'Dokumen SKPI Anda belum siap. Mohon hubungi Admin agar dokumen segera di-generate.');
        }

        try {
            $decrypted = SkpiDocumentEncryption::decrypt($registration->skpi_document);
        } catch (\Throwable $e) {
            \Log::error('[StudentSkpi] Gagal dekripsi SKPI NIM=' . $registration->nim . ': ' . $e->getMessage());
            return redirect()->route('student.skpi.daftar.index')
                ->with('error', 'Dokumen SKPI tidak dapat dibaca. Silakan hubungi Admin.');
        }

        $safeNim  = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $registration->nim) ?: 'student';
        $fileName = 'SKPI_' . $safeNim . '.docx';

        return response($decrypted, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Content-Length'      => strlen($decrypted),
        ]);
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
            ->with(['finalProject.defense', 'skpiRegistration'])
            ->findOrFail(decrypt(session('student_id')));
    }

    private function canEditRegistration(?SkpiRegistration $registration): bool
    {
        return !$registration || in_array($registration->status, ['draft', 'needs_revision', 'rejected'], true);
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
        $birthIdentityComplete = filled($student->nama_lengkap) 
            && filled($student->tempat_lahir) 
            && filled($student->tanggal_lahir)
            && filled($student->nim)
            && filled($student->angkatan);
            
        $skpiRegistration = $student->skpiRegistration;
        $formIdentitasLengkap = $skpiRegistration 
            && filled($skpiRegistration->ipk) 
            && filled($skpiRegistration->sks) 
            && filled($skpiRegistration->judul_ta_indo) 
            && filled($skpiRegistration->periode_lulus)
            && filled($skpiRegistration->lama_studi);
        
        $hasDocumentSupport = filled($student->foto) && filled($student->ttd);
        $hasApprovedAchievements = ($student->approved_achievements_count ?? 0) > 0;

        return [
            [
                'title' => 'Data Pemegang SKPI',
                'description' => 'Identitas dasar (nama, tempat/tanggal lahir, NIM) harus lengkap di profil.',
                'ready' => $birthIdentityComplete,
                'required' => true,
            ],
            [
                'title' => 'Form Identitas SKPI',
                'description' => 'Data IPK, SKS, Judul Tugas Akhir, dan Periode Lulus sudah dilengkapi dan disimpan pada form.',
                'ready' => (bool)$formIdentitasLengkap,
                'required' => true,
            ],
            [
                'title' => 'Foto & Tanda Tangan',
                'description' => 'Foto profil dan tanda tangan digital sudah lengkap.',
                'ready' => $hasDocumentSupport,
                'required' => true,
            ],
            [
                'title' => 'Prestasi & Penghargaan',
                'description' => 'Opsional. Hanya prestasi yang sudah approved yang akan masuk ke SKPI.',
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
        // Selalu ambil gelar terbaru dari profil prodi sebagai prioritas utama
        $gelarFromProfile = null;
        $studyProgram = \App\Models\StudyProgram::where('name', $student->program_studi)->first();
        if ($studyProgram) {
            $academicProfile = \App\Models\SkpiAcademicProfile::where('study_program_id', $studyProgram->id)->first();
            $gelarFromProfile = $academicProfile?->gelar_lulusan;
        }

        $finalProject = $student->finalProject;

        $defaults = [
            'nama_lengkap' => $registration?->nama_lengkap ?? $student->nama_lengkap,
            'tempat_lahir' => $registration?->tempat_lahir ?? $student->tempat_lahir,
            'tanggal_lahir' => optional($registration?->tanggal_lahir ?? $student->tanggal_lahir)->format('Y-m-d'),
            'nim' => $registration?->nim ?? $student->nim,
            'angkatan' => $registration?->angkatan ?? $student->angkatan,
            'gelar' => $gelarFromProfile ?? $registration?->gelar,
            'ipk' => $registration?->ipk ?? $student->ipk,
            'sks' => $registration?->sks ?? $student->sks,
            'judul_ta_indo' => $registration?->judul_ta_indo ?? $finalProject?->title,
            'judul_ta_inggris' => $registration?->judul_ta_inggris ?? $finalProject?->title_en,
            'periode_lulus' => $registration?->periode_lulus,
            'lama_studi' => $registration?->lama_studi,
        ];

        return [
            'nama_lengkap' => old('nama_lengkap', $request?->input('nama_lengkap', $defaults['nama_lengkap']) ?? $defaults['nama_lengkap']),
            'tempat_lahir' => old('tempat_lahir', $request?->input('tempat_lahir', $defaults['tempat_lahir']) ?? $defaults['tempat_lahir']),
            'tanggal_lahir' => old('tanggal_lahir', $request?->input('tanggal_lahir', $defaults['tanggal_lahir']) ?? $defaults['tanggal_lahir']),
            'nim' => old('nim', $request?->input('nim', $defaults['nim']) ?? $defaults['nim']),
            'angkatan' => old('angkatan', $request?->input('angkatan', $defaults['angkatan']) ?? $defaults['angkatan']),
            'gelar' => old('gelar', $request?->input('gelar', $defaults['gelar']) ?? $defaults['gelar']),
            'ipk' => old('ipk', $request?->input('ipk', $defaults['ipk']) ?? $defaults['ipk']),
            'sks' => old('sks', $request?->input('sks', $defaults['sks']) ?? $defaults['sks']),
            'judul_ta_indo' => old('judul_ta_indo', $request?->input('judul_ta_indo', $defaults['judul_ta_indo']) ?? $defaults['judul_ta_indo']),
            'judul_ta_inggris' => old('judul_ta_inggris', $request?->input('judul_ta_inggris', $defaults['judul_ta_inggris']) ?? $defaults['judul_ta_inggris']),
            'periode_lulus' => old('periode_lulus', $request?->input('periode_lulus', $defaults['periode_lulus']) ?? $defaults['periode_lulus']),
            'lama_studi' => old('lama_studi', $request?->input('lama_studi', $defaults['lama_studi']) ?? $defaults['lama_studi']),
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
                'key' => 'gelar',
                'label' => 'Gelar',
                'value' => $holderData['gelar'] ?? null,
                'display' => $holderData['gelar'] ?? null,
            ],
            [
                'key' => 'ipk',
                'label' => 'IPK',
                'value' => $holderData['ipk'] ?? null,
                'display' => $holderData['ipk'] ?? null,
            ],
            [
                'key' => 'sks',
                'label' => 'Total SKS',
                'value' => $holderData['sks'] ?? null,
                'display' => $holderData['sks'] ?? null,
            ],
            [
                'key' => 'judul_ta_indo',
                'label' => 'Judul Tugas Akhir (Indonesia)',
                'value' => $holderData['judul_ta_indo'] ?? null,
                'display' => $holderData['judul_ta_indo'] ?? null,
            ],
            [
                'key' => 'judul_ta_inggris',
                'label' => 'Judul Tugas Akhir (Inggris)',
                'value' => $holderData['judul_ta_inggris'] ?? null,
                'display' => $holderData['judul_ta_inggris'] ?? null,
            ],
            [
                'key' => 'periode_lulus',
                'label' => 'Periode Lulus',
                'value' => $holderData['periode_lulus'] ?? null,
                'display' => filled($holderData['periode_lulus'] ?? null)
                    ? \Carbon\Carbon::parse($holderData['periode_lulus'] . '-01')->translatedFormat('F Y')
                    : null,
            ],
            [
                'key' => 'lama_studi',
                'label' => 'Lama Studi',
                'value' => $holderData['lama_studi'] ?? null,
                'display' => $holderData['lama_studi'] ?? null,
            ],
        ];
    }

    private function buildHolderMeta(array $holderFields): array
    {
        $filledCount = collect($holderFields)
            ->filter(fn($field) => filled($field['value']))
            ->count();

        return [
            'filled_count' => $filledCount,
            'total_count' => count($holderFields),
            'complete' => $filledCount === count($holderFields),
            'missing_fields' => collect($holderFields)
                ->filter(fn($field) => blank($field['value']))
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
            'draft' => [
                'value' => 'draft',
                'label' => 'Draft',
                'badge_class' => 'muted',
                'description' => 'Data identitas sudah disimpan, tetapi belum diajukan.',
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
