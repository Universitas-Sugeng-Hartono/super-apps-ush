<?php

namespace App\Http\Controllers\AdminController;

use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\StudyProgram;
use App\Services\PokemaEvaluatorService;
use App\Services\SkpPointCalculator;
use App\Services\SiakadSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AchievementVerificationController extends Controller
{
    /**
     * Tampilkan halaman Verifikasi Data Prestasi Mahasiswa (Kemahasiswaan).
     * Dilengkapi kolom IPK, Total Point SKP, Status Mahasiswa, dan Beasiswa.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $status = $request->input('status');
        $programStudi = $request->input('program_studi');
        $category = $request->input('category');
        $statusMahasiswa = $request->input('status_mahasiswa');
        $beasiswaFilter = $request->input('beasiswa');
        $angkatan = $request->input('angkatan');

        $availableAngkatan = Student::whereNotNull('angkatan')
            ->where('angkatan', '>', 0)
            ->distinct()
            ->orderBy('angkatan', 'desc')
            ->pluck('angkatan');

        if ($availableAngkatan->isEmpty()) {
            $availableAngkatan = collect([2026, 2025, 2024, 2023]);
        }

        $achievementsFilter = function ($query) use ($status, $category) {
            $validCategories = array_keys(StudentAchievement::manualCategoryOptions());
            $query->whereIn('category', $validCategories);

            if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
                $query->where('status', $status);
            }
            if ($category && in_array($category, $validCategories, true)) {
                $query->where('category', $category);
            }
        };

        $tab = $request->input('tab', 'all');
        if (!in_array($tab, ['all', 'beasiswa', 'non_beasiswa'], true)) {
            $tab = 'all';
        }

        $beasiswaCondition = fn ($q) => $this->applyBeasiswaCondition($q);
        $nonBeasiswaCondition = fn ($q) => $this->applyNonBeasiswaCondition($q);

        $countsQuery = Student::query()
            ->whereHas('achievements', $achievementsFilter)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nim', 'like', "%{$search}%")
                        ->orWhere('program_studi', 'like', "%{$search}%")
                        ->orWhere('beasiswa', 'like', "%{$search}%");
                });
            })
            ->when(filled($programStudi), function ($query) use ($programStudi) {
                $query->where('program_studi', $programStudi);
            })
            ->when(filled($angkatan), function ($query) use ($angkatan) {
                $query->where('angkatan', $angkatan);
            });

        $tabCounts = [
            'all'          => (clone $countsQuery)->count(),
            'beasiswa'     => (clone $countsQuery)->where($beasiswaCondition)->count(),
            'non_beasiswa' => (clone $countsQuery)->where($nonBeasiswaCondition)->count(),
        ];

        // Hitung jumlah prestasi pending khusus per kategori tab (Beasiswa vs Reguler vs Semua)
        $pendingCounts = [
            'all'          => StudentAchievement::where('status', 'pending')->count(),
            'beasiswa'     => StudentAchievement::where('status', 'pending')->whereHas('student', $beasiswaCondition)->count(),
            'non_beasiswa' => StudentAchievement::where('status', 'pending')->whereHas('student', $nonBeasiswaCondition)->count(),
        ];
        $activeTabPendingCount = $pendingCounts[$tab] ?? $pendingCounts['all'];

        $students = Student::query()
            ->whereHas('achievements', $achievementsFilter)
            ->with(['achievements' => function ($query) use ($achievementsFilter) {
                $achievementsFilter($query);
                $query->with('approver')->latest();
            }, 'skpiRegistration', 'finalProject', 'counselings'])
            ->withSum(['achievements as total_skp_approved' => function ($q) {
                $q->where('status', 'approved');
            }], 'skp_points')
            ->withSum('achievements as total_skp_all', 'skp_points')
            ->when($tab === 'beasiswa', function ($query) use ($beasiswaCondition) {
                $query->where($beasiswaCondition);
            })
            ->when($tab === 'non_beasiswa', function ($query) use ($nonBeasiswaCondition) {
                $query->where($nonBeasiswaCondition);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nim', 'like', "%{$search}%")
                        ->orWhere('program_studi', 'like', "%{$search}%")
                        ->orWhere('beasiswa', 'like', "%{$search}%");
                });
            })
            ->when(filled($programStudi), function ($query) use ($programStudi) {
                $query->where('program_studi', $programStudi);
            })
            ->when(filled($statusMahasiswa), function ($query) use ($statusMahasiswa) {
                if ($statusMahasiswa === 'studentambassador') {
                    $query->where('status', 'studentambassador');
                } elseif ($statusMahasiswa === 'mahasiswa') {
                    $query->where(function ($q) {
                        $q->where('status', 'mahasiswa')
                            ->orWhereNull('status')
                            ->orWhere('status', '');
                    });
                }
            })
            ->when(filled($beasiswaFilter), function ($query) use ($beasiswaFilter) {
                if ($beasiswaFilter === 'none') {
                    $query->where(function ($q) {
                        $q->whereNull('beasiswa')
                            ->orWhere('beasiswa', '')
                            ->orWhere('beasiswa', '0%')
                            ->orWhere('beasiswa', '0')
                            ->orWhere('beasiswa', 'Non-Beasiswa');
                    });
                } elseif ($beasiswaFilter === 'has_beasiswa') {
                    $query->whereNotNull('beasiswa')
                        ->where('beasiswa', '!=', '')
                        ->where('beasiswa', '!=', '0%')
                        ->where('beasiswa', '!=', '0')
                        ->where('beasiswa', '!=', 'Non-Beasiswa');
                } else {
                    $query->where('beasiswa', $beasiswaFilter);
                }
            })
            ->when(filled($angkatan), function ($query) use ($angkatan) {
                $query->where('angkatan', $angkatan);
            })
            ->orderBy('nama_lengkap')
            ->paginate(12)
            ->appends($request->query());

        $students->getCollection()->transform(function ($student) {
            $student->pokema_evaluation = PokemaEvaluatorService::evaluate($student);
            return $student;
        });

        $stats = [
            'total'          => StudentAchievement::count(),
            'pending'        => StudentAchievement::where('status', 'pending')->count(),
            'approved'       => StudentAchievement::where('status', 'approved')->count(),
            'rejected'       => StudentAchievement::where('status', 'rejected')->count(),
            'ambassador'     => Student::where('status', 'studentambassador')->count(),
            'total_students' => Student::whereHas('achievements')->count(),
        ];

        $studyPrograms = StudyProgram::query()
            ->where('is_active', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $categoryOptions = StudentAchievement::manualCategoryOptions();
        $skpDictionary = SkpPointCalculator::getDictionary();

        // Opsi beasiswa persentase untuk filter (standar SK Rektor POKEMA & dari database)
        $dbBeasiswa = Student::whereNotNull('beasiswa')
            ->where('beasiswa', '!=', '')
            ->where('beasiswa', '!=', '0%')
            ->where('beasiswa', '!=', '0')
            ->where('beasiswa', '!=', 'Non-Beasiswa')
            ->distinct()
            ->orderBy('beasiswa')
            ->pluck('beasiswa')
            ->toArray();
        $availableBeasiswa = array_values(array_unique(array_merge(['100%', '75%', '50%', '25%'], $dbBeasiswa)));
        $isPortalOpen = AppSetting::isBeasiswaUploadOpen();

        return view('kemahasiswaan.verifikasi-prestasi.index', compact(
            'students',
            'category',
            'categoryOptions',
            'programStudi',
            'search',
            'stats',
            'status',
            'statusMahasiswa',
            'beasiswaFilter',
            'availableBeasiswa',
            'angkatan',
            'availableAngkatan',
            'studyPrograms',
            'skpDictionary',
            'tab',
            'tabCounts',
            'pendingCounts',
            'activeTabPendingCount',
            'isPortalOpen'
        ));
    }

    /**
     * Setujui satu data prestasi mahasiswa
     */
    public function approve(Request $request, $id)
    {
        $achievement = StudentAchievement::with('student')->findOrFail($id);

        $request->validate([
            'approval_notes' => 'nullable|string',
        ]);

        $achievement->update([
            'status'         => 'approved',
            'approval_notes' => $request->approval_notes,
            'approved_by'    => auth()->id(),
            'approved_at'    => now(),
        ]);

        NotificationHelper::notifyStudent(
            $achievement->student_id,
            'skpi.achievement.approved',
            'Data Aktivitas SKPI Disetujui',
            'Data "' . ($achievement->activity_type_label ?? $achievement->activity_type) . '" pada kategori ' . $achievement->category_label . ' telah disetujui dan siap masuk ke SKPI.',
            route('student.personal.achievements.index'),
            ['student_achievement_id' => $achievement->id]
        );

        return redirect()->back()->with('success', 'Prestasi mahasiswa berhasil disetujui.');
    }

    /**
     * Batalkan status approval (kembali ke pending)
     */
    public function unapprove(Request $request, $id)
    {
        $achievement = StudentAchievement::findOrFail($id);

        $achievement->update([
            'status'         => 'pending',
            'approval_notes' => null,
            'approved_by'    => null,
            'approved_at'    => null,
        ]);

        return redirect()->back()->with('success', 'Status approval berhasil dibatalkan. Data kembali ke antrian pending.');
    }

    /**
     * Tolak satu data prestasi mahasiswa
     */
    public function reject(Request $request, $id)
    {
        $achievement = StudentAchievement::with('student')->findOrFail($id);

        $request->validate([
            'approval_notes' => 'required|string',
        ], [
            'approval_notes.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $achievement->update([
            'status'         => 'rejected',
            'approval_notes' => $request->approval_notes,
            'approved_by'    => auth()->id(),
            'approved_at'    => now(),
        ]);

        NotificationHelper::notifyStudent(
            $achievement->student_id,
            'skpi.achievement.rejected',
            'Data Aktivitas SKPI Ditolak',
            'Data "' . ($achievement->activity_type_label ?? $achievement->activity_type) . '" pada kategori ' . $achievement->category_label . ' ditolak. Catatan: ' . $achievement->approval_notes,
            route('student.personal.achievements.index'),
            ['student_achievement_id' => $achievement->id]
        );

        return redirect()->back()->with('success', 'Prestasi mahasiswa berhasil ditolak.');
    }

    /**
     * Perbarui data sertifikat/prestasi mahasiswa dari form admin
     */
    public function update(Request $request, $id)
    {
        $achievement = StudentAchievement::with('student')->findOrFail($id);
        $categoryKeys = implode(',', array_keys(StudentAchievement::manualCategoryOptions()));

        $isPmb = SkpPointCalculator::isRangeType($request->activity_type);
        $rules = [
            'category'           => "required|string|in:{$categoryKeys}",
            'semester'           => 'nullable|integer|min:1|max:14',
            'activity_type'      => 'required|string|max:100',
            'event'              => 'required|string|max:255',
            'organizer'          => 'required|string|max:255',
            'event_year'         => 'required|string|max:10',
            'level'              => 'required|string|max:100',
            'participation_role' => 'required|string|max:100',
            'certificate'        => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5048',
            'status'             => 'required|string|in:pending,approved,rejected',
            'approval_notes'     => 'nullable|string',
        ];

        if ($isPmb) {
            $rules['skp_points'] = 'required|integer|min:15|max:75';
        }

        $request->validate($rules, [
            'skp_points.required' => 'Nilai poin POKEMA untuk Penerimaan Mahasiswa Baru wajib diisi.',
            'skp_points.integer'  => 'Nilai poin POKEMA harus berupa angka bilangan bulat.',
            'skp_points.min'      => 'Nilai poin POKEMA minimal 15 poin.',
            'skp_points.max'      => 'Nilai poin POKEMA maksimal 75 poin.',
        ]);

        $customPoints = ($isPmb && $request->filled('skp_points')) ? (int) $request->skp_points : null;
        $skpPoints = SkpPointCalculator::calculate(
            $request->category,
            $request->activity_type,
            $request->level,
            $request->participation_role,
            $customPoints
        );

        $updateData = [
            'category'           => $request->category,
            'activity_type'      => $request->activity_type,
            'event'              => $request->event,
            'organizer'          => $request->organizer,
            'event_year'         => $request->event_year,
            'level'              => $request->level,
            'participation_role' => $request->participation_role,
            'skp_points'         => $skpPoints,
            'status'             => $request->status,
            'approval_notes'     => $request->approval_notes,
        ];

        if ($request->filled('semester')) {
            $updateData['semester'] = (int) $request->semester;
        }

        if ($request->status === 'approved') {
            $updateData['approved_by'] = auth()->id();
            $updateData['approved_at'] = now();
        } elseif ($request->status === 'pending') {
            $updateData['approved_by'] = null;
            $updateData['approved_at'] = null;
        }

        if ($request->hasFile('certificate')) {
            if ($achievement->certificate && Storage::disk('public')->exists($achievement->certificate)) {
                Storage::disk('public')->delete($achievement->certificate);
            }
            $updateData['certificate'] = $request->file('certificate')
                ->store('students/achievements/' . $achievement->student_id, 'public');
        }

        $achievement->update($updateData);

        try {
            NotificationHelper::notifyStudent(
                $achievement->student_id,
                'skpi.achievement.updated_by_admin',
                'Data Sertifikat/Aktivitas Disunting Admin',
                'Data "' . ($achievement->activity_type_label ?? $achievement->activity_type) . '" telah disunting/dilengkapi oleh Staf Kemahasiswaan.',
                route('student.personal.achievements.index'),
                ['student_achievement_id' => $achievement->id]
            );
        } catch (\Exception $e) {
            Log::warning('Notifikasi edit achievement gagal: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Data sertifikat/prestasi mahasiswa berhasil diperbarui.');
    }

    /**
     * Setujui semua prestasi yang masih pending sekaligus berdasarkan kategori tab (Beasiswa, Reguler, atau Semua).
     */
    public function approveAll(Request $request)
    {
        $request->validate([
            'approval_notes' => 'nullable|string',
            'tab'            => 'nullable|string|in:all,beasiswa,non_beasiswa',
        ]);

        $tab = $request->input('tab', 'all');

        $query = StudentAchievement::with('student')->where('status', 'pending');

        if ($tab === 'beasiswa') {
            $query->whereHas('student', fn ($q) => $this->applyBeasiswaCondition($q));
            $tabLabel = 'Penerima Beasiswa & Ambassador';
        } elseif ($tab === 'non_beasiswa') {
            $query->whereHas('student', fn ($q) => $this->applyNonBeasiswaCondition($q));
            $tabLabel = 'Mahasiswa Reguler';
        } else {
            $tabLabel = 'Seluruh Mahasiswa';
        }

        $achievements = $query->get();

        if ($achievements->isEmpty()) {
            return redirect()->back()->with('error', "Tidak ada data prestasi pending yang ditemukan untuk kategori {$tabLabel}.");
        }

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
                // Lanjutkan loop jika notifikasi gagal
            }
        }

        return redirect()->back()->with('success', "Semua data prestasi pending ({$achievements->count()} item) untuk kategori {$tabLabel} berhasil disetujui.");
    }

    /**
     * Terapkan / perbarui status beasiswa mahasiswa berdasarkan evaluasi POKEMA
     */
    public function updateStudentBeasiswa(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $request->validate([
            'beasiswa'  => 'required|string|max:20',
            'sk_number' => 'nullable|string|max:100',
            'notes'     => 'nullable|string|max:500',
        ]);

        $oldBeasiswa = $student->beasiswa ?: '0%';
        $newBeasiswa = trim($request->beasiswa);

        // Jika Admin memilih reset ke otomatis, kosongkan rekomendasi manual
        if (strtolower($newBeasiswa) === 'auto' || $newBeasiswa === 'reset') {
            $student->rekomendasi_manual = null;
            $logEntry = now()->format('d/m/Y H:i') . " - Reset rekomendasi manual ke rekomendasi otomatis sistem POKEMA.";
            if ($request->filled('notes')) {
                $logEntry .= ". Catatan: {$request->notes}";
            }
            $student->notes = ($student->notes ? $student->notes . "\n" : '') . $logEntry;
            $student->save();

            return redirect()->back()->with('success', "Rekomendasi manual untuk {$student->nama_lengkap} berhasil di-reset. Evaluasi beasiswa kembali mengacu pada sistem dinamis POKEMA.");
        }

        if (is_numeric($newBeasiswa)) {
            $newBeasiswa .= '%';
        }

        // SIMPAN KE REKOMENDASI MANUAL, JANGAN UBAH beasiswa SAAT INI!
        // Kolom beasiswa saat ini tetap utuh dan baru berubah ketika Keuangan meng-ACC dan tombol Update Data Beasiswa diklik.
        $student->rekomendasi_manual = $newBeasiswa;

        $logEntry = now()->format('d/m/Y H:i') . " - Input Rekomendasi Manual Beasiswa menjadi {$newBeasiswa}";
        if ($request->filled('sk_number')) {
            $logEntry .= " (Dasar SK: {$request->sk_number})";
        }
        if ($request->filled('notes')) {
            $logEntry .= ". Catatan: {$request->notes}";
        }

        $student->notes = ($student->notes ? $student->notes . "\n" : '') . $logEntry;
        $student->save();

        return redirect()->back()->with('success', "Rekomendasi beasiswa untuk {$student->nama_lengkap} berhasil disesuaikan secara manual menjadi {$newBeasiswa}. Silakan kirim rekomendasi ke SIAKAD.");
    }

    /**
     * Kirim rekomendasi beasiswa hasil evaluasi POKEMA mahasiswa langsung ke SIAKAD
     */
    public function sendRecommendationToSiakad(Request $request, $id, SiakadSyncService $syncService)
    {
        $student = Student::findOrFail($id);
        $eval = PokemaEvaluatorService::evaluate($student);

        // Prioritaskan rekomendasi manual yang diinput Admin jika ada.
        // Jika tidak ada rekomendasi manual, gunakan rekomendasi otomatis POKEMA.
        $recBeasiswa = !empty($student->rekomendasi_manual)
            ? $student->rekomendasi_manual
            : ($eval['recommended_beasiswa'] ?? ($student->beasiswa ?: '0%'));

        $catatan = $request->input('notes') ?: "Rekomendasi POKEMA: " . ($eval['reason'] ?? "Hasil evaluasi capaian beasiswa {$recBeasiswa}");

        try {
            $res = $syncService->sendRecommendation($student, $recBeasiswa, $catatan, 'draft');
            // Tandai status rekomendasi di database SuperApps bahwa usulan telah terkirim ke SIAKAD
            $student->update(['status_rekomendasi_siakad' => 'draft']);

            return redirect()->back()->with('success', "Rekomendasi beasiswa ({$recBeasiswa}) untuk {$student->nama_lengkap} (NIM: {$student->nim}) berhasil dikirim ke kolom Rekomendasi di SIAKAD!");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', "Gagal mengirim rekomendasi ke SIAKAD: " . $e->getMessage());
        }
    }

    /**
     * Kirim seluruh rekomendasi beasiswa hasil evaluasi POKEMA ke SIAKAD secara massal
     */
    public function sendAllRecommendationsToSiakad(Request $request, SiakadSyncService $syncService)
    {
        $students = Student::where('angkatan', '>=', 2026)
            ->whereNotNull('nim')
            ->where('nim', '!=', '-')
            ->where(function ($q) {
                $q->where('status', 'studentambassador')
                    ->orWhere(function ($sub) {
                        $sub->whereNotNull('beasiswa')
                            ->where('beasiswa', '!=', '')
                            ->where('beasiswa', '!=', '0%')
                            ->where('beasiswa', '!=', '0')
                            ->where('beasiswa', '!=', 'Non-Beasiswa');
                    });
            })
            ->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'Tidak ada data mahasiswa beasiswa angkatan 2026+ yang valid untuk dikirim ke Siakad.');
        }

        $items = [];
        foreach ($students as $stu) {
            $eval = PokemaEvaluatorService::evaluate($stu);

            // Prioritaskan rekomendasi manual yang diinput Admin jika ada.
            // Jika tidak ada, gunakan rekomendasi otomatis POKEMA.
            $recPct = !empty($stu->rekomendasi_manual)
                ? $stu->rekomendasi_manual
                : ($eval['recommended_beasiswa'] ?? ($stu->beasiswa ?: '0%'));

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

            // Tandai status bahwa rekomendasi massal telah berhasil dikirim ke SIAKAD
            $sentNims = collect($items)->pluck('nim')->filter()->values();
            if ($sentNims->isNotEmpty()) {
                Student::whereIn('nim', $sentNims)->update(['status_rekomendasi_siakad' => 'draft']);
            }

            return back()->with('success', "Berhasil mengirim {$successCount} rekomendasi beasiswa hasil evaluasi POKEMA ke SIAKAD (Menu Rekomendasi Beasiswa Termin). Data kini dapat ditinjau oleh Bagian Akademik & Keuangan di Siakad.");
        } catch (\Throwable $e) {
            return back()->with('error', "Gagal mengirim rekomendasi massal ke SIAKAD: " . $e->getMessage());
        }
    }

    /**
     * Update data beasiswa di SuperApps berdasarkan hasil ACC & penetapan termin resmi dari Keuangan di SIAKAD
     * Nilai beasiswa resmi ini menjadi acuan basis evaluasi mahasiswa untuk periode berikutnya.
     */
    public function applyAllRecommendations(Request $request, SiakadSyncService $syncService)
    {
        // 1. Tarik data terbaru dari SIAKAD terlebih dahulu agar status ACC & termin dari Keuangan termutakhir
        try {
            $syncService->refreshScholarshipAcademicData();
        } catch (\Throwable $e) {
            Log::warning("Sinkronisasi SIAKAD sebelum update beasiswa gagal: " . $e->getMessage());
        }

        $students = Student::where('angkatan', '>=', 2026)
            ->where(function ($q) {
                $q->where('status', 'studentambassador')
                    ->orWhere(function ($sub) {
                        $sub->whereNotNull('beasiswa')
                            ->where('beasiswa', '!=', '')
                            ->where('beasiswa', '!=', '0%')
                            ->where('beasiswa', '!=', '0')
                            ->where('beasiswa', '!=', 'Non-Beasiswa');
                    });
            })
            ->with(['achievements' => function ($q) {
                $q->where('status', 'approved');
            }, 'counselings'])
            ->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'Tidak ada data mahasiswa beasiswa atau ambassador yang ditemukan.');
        }

        $appliedCount = 0;
        $skNumber = '2638/SK/01/2026';

        foreach ($students as $student) {
            $oldBeasiswa = $student->beasiswa ?: '0%';

            // Prioritaskan nilai yang sudah resmi di-ACC / digenerate Keuangan di SIAKAD.
            // Jika belum ada di SIAKAD, gunakan rekomendasi manual atau rekomendasi otomatis POKEMA
            if (!empty($student->beasiswa_acc_keuangan)) {
                $targetBeasiswa = $student->beasiswa_acc_keuangan;
                $reasonText = "Penyesuaian resmi berdasarkan penetapan termin Keuangan di SIAKAD ({$student->beasiswa_acc_keuangan}).";
            } elseif (!empty($student->rekomendasi_manual)) {
                $targetBeasiswa = $student->rekomendasi_manual;
                $reasonText = "Penetapan status beasiswa berdasarkan penyesuaian manual Admin ({$student->rekomendasi_manual}).";
            } else {
                $eval = PokemaEvaluatorService::evaluate($student);
                $targetBeasiswa = $eval['recommended_beasiswa'] ?? '0%';
                $reasonText = $eval['reason'] ?? "Berdasarkan hasil evaluasi capaian POKEMA & IPK.";
            }

            $oldTier = PokemaEvaluatorService::normalizeTierKey($oldBeasiswa);
            $newTier = PokemaEvaluatorService::normalizeTierKey($targetBeasiswa);

            // Reset rekomendasi manual dan status SIAKAD karena termin & evaluasi periode ini telah resmi diselesaikan
            $student->rekomendasi_manual = null;
            $student->status_rekomendasi_siakad = null;
            $student->beasiswa_acc_keuangan = null;

            // Update jika terdapat perbedaan atau untuk meresmikan status evaluasi
            if ($oldTier !== $newTier) {
                $student->beasiswa = $newTier;

                $logEntry = now()->format('d/m/Y H:i') . " - Update Data Beasiswa dari {$oldBeasiswa} ke {$newTier} (Dasar SK: {$skNumber}). {$reasonText}";
                $student->notes = ($student->notes ? $student->notes . "\n" : '') . $logEntry;
                $student->save();

                // Notifikasi ke mahasiswa
                try {
                    $newLevel = PokemaEvaluatorService::TIERS[$newTier]['level'] ?? 0;
                    $oldLevel = PokemaEvaluatorService::TIERS[$oldTier]['level'] ?? 0;
                    $isUpgrade = $newLevel > $oldLevel;
                    $title = $isUpgrade ? 'Selamat! Kenaikan Beasiswa Disetujui' : 'Pembaruan Status Beasiswa';
                    $body = "Status beasiswa Anda telah resmi diperbarui menjadi {$newTier}. Siap digunakan sebagai acuan evaluasi periode berikutnya.";

                    NotificationHelper::notifyStudent(
                        $student->id,
                        'beasiswa.updated',
                        $title,
                        $body,
                        route('student.personal.achievements.index'),
                        [
                            'old_beasiswa' => $oldBeasiswa,
                            'new_beasiswa' => $newTier,
                            'sk_number'    => $skNumber,
                        ]
                    );
                } catch (\Throwable $e) {
                    Log::warning("Gagal kirim notifikasi beasiswa untuk NIM {$student->nim}: " . $e->getMessage());
                }

                $appliedCount++;
            } else {
                $student->save();
            }
        }

        if ($appliedCount === 0) {
            return back()->with('success', 'Seluruh data beasiswa mahasiswa di SuperApps sudah selaras dengan hasil penetapan Keuangan SIAKAD!');
        }

        return back()->with('success', "Berhasil memperbarui data beasiswa untuk {$appliedCount} mahasiswa berdasarkan penetapan resmi Keuangan di SIAKAD. Status beasiswa kini siap untuk evaluasi periode selanjutnya.");
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
     * Buka / Tutup portal pengajuan prestasi untuk mahasiswa Ambassador & Penerima Beasiswa.
     */
    public function toggleUploadPortal(Request $request)
    {
        $request->validate([
            'is_open' => 'required|boolean',
        ]);

        $isOpen = $request->boolean('is_open') ? '1' : '0';
        AppSetting::set('portal_upload_beasiswa_open', $isOpen);

        // Jika portal dibuka untuk siklus perkuliahan baru, bersihkan usulan rekomendasi manual lama
        // dan status pelacakan SIAKAD periode sebelumnya sehingga tabel kembali bersih
        if ($isOpen === '1') {
            Student::where(function ($q) {
                $q->whereNotNull('rekomendasi_manual')
                  ->orWhereNotNull('status_rekomendasi_siakad')
                  ->orWhereNotNull('beasiswa_acc_keuangan');
            })->update([
                'rekomendasi_manual' => null,
                'status_rekomendasi_siakad' => null,
                'beasiswa_acc_keuangan' => null,
            ]);
        }

        $statusText = $isOpen === '1' ? 'DIBUKA' : 'DITUTUP';
        $message = $isOpen === '1'
            ? 'Portal pengajuan prestasi untuk Ambassador & Beasiswa berhasil DIBUKA. Mahasiswa dapat mengunggah prestasi kembali.'
            : 'Portal pengajuan prestasi untuk Ambassador & Beasiswa berhasil DITUTUP. Mahasiswa tidak dapat mengunggah atau mengubah data prestasi selama masa review beasiswa.';

        return response()->json([
            'success' => true,
            'is_open' => $isOpen === '1',
            'status_text' => $statusText,
            'message' => $message,
        ]);
    }

    /**
     * Export Laporan Evaluasi Capaian Beasiswa & Student Ambassador (Excel / CSV)
     */
    public function exportScholarshipReport(Request $request)
    {
        $format = strtolower($request->input('format', 'xlsx'));
        $extension = in_array($format, ['csv', 'txt'], true) ? 'csv' : 'xlsx';
        $writerType = ($extension === 'csv') ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;

        $filename = 'Laporan_Evaluasi_Beasiswa_Ambassador_' . date('Ymd_His') . '.' . $extension;

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ScholarshipEvaluationExport($request),
            $filename,
            $writerType
        );
    }

    /**
     * Scope query pembantu untuk mahasiswa penerima beasiswa & student ambassador
     */
    public function applyBeasiswaCondition($query)
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
     * Scope query pembantu untuk mahasiswa reguler (non-beasiswa)
     */
    public function applyNonBeasiswaCondition($query)
    {
        return $query->where(function ($q) {
            $q->where(function ($sub) {
                $sub->whereNull('beasiswa')
                    ->orWhere('beasiswa', '')
                    ->orWhere('beasiswa', '0%')
                    ->orWhere('beasiswa', '0')
                    ->orWhere('beasiswa', 'Non-Beasiswa');
            })->where(function ($sub) {
                $sub->where('status', '!=', 'studentambassador')
                    ->orWhereNull('status');
            });
        });
    }
}

