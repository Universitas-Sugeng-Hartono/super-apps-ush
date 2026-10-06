<?php

namespace App\Http\Controllers\AdminController;

use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\StudyProgram;
use App\Services\PokemaEvaluatorService;
use App\Services\SkpPointCalculator;
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
            'studyPrograms',
            'skpDictionary'
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

        $request->validate([
            'category'           => "required|string|in:{$categoryKeys}",
            'activity_type'      => 'required|string|max:100',
            'event'              => 'required|string|max:255',
            'organizer'          => 'required|string|max:255',
            'event_year'         => 'required|string|max:10',
            'level'              => 'required|string|max:100',
            'participation_role' => 'required|string|max:100',
            'certificate'        => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5048',
            'status'             => 'required|string|in:pending,approved,rejected',
            'approval_notes'     => 'nullable|string',
        ]);

        $skpPoints = SkpPointCalculator::calculate(
            $request->category,
            $request->activity_type,
            $request->level,
            $request->participation_role
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
     * Setujui semua prestasi yang masih pending sekaligus
     */
    public function approveAll(Request $request)
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
                // Lanjutkan loop jika notifikasi gagal
            }
        }

        return redirect()->back()->with('success', 'Semua data pending (' . $achievements->count() . ' item) berhasil disetujui.');
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
        if (is_numeric($newBeasiswa)) {
            $newBeasiswa .= '%';
        }

        $student->beasiswa = $newBeasiswa;

        $logEntry = now()->format('d/m/Y H:i') . " - Penyesuaian Beasiswa dari {$oldBeasiswa} ke {$newBeasiswa}";
        if ($request->filled('sk_number')) {
            $logEntry .= " (Dasar SK: {$request->sk_number})";
        }
        if ($request->filled('notes')) {
            $logEntry .= ". Catatan: {$request->notes}";
        }

        $student->notes = ($student->notes ? $student->notes . "\n" : '') . $logEntry;
        $student->save();

        // Notifikasi ke mahasiswa
        try {
            $newTierKey = PokemaEvaluatorService::normalizeTierKey($newBeasiswa);
            $oldTierKey = PokemaEvaluatorService::normalizeTierKey($oldBeasiswa);
            $newLevel = PokemaEvaluatorService::TIERS[$newTierKey]['level'] ?? 0;
            $oldLevel = PokemaEvaluatorService::TIERS[$oldTierKey]['level'] ?? 0;

            $isUpgrade = $newLevel > $oldLevel;
            $title = $isUpgrade ? 'Selamat! Kenaikan Beasiswa Disetujui' : 'Pembaruan Status Beasiswa';
            $body = "Status beasiswa Anda telah disesuaikan menjadi {$newBeasiswa}. " . ($request->notes ?: 'Berdasarkan hasil evaluasi capaian POKEMA & IPK.');

            NotificationHelper::notifyStudent(
                $student->id,
                'beasiswa.updated',
                $title,
                $body,
                route('student.personal.achievements.index'),
                [
                    'old_beasiswa' => $oldBeasiswa,
                    'new_beasiswa' => $newBeasiswa,
                    'sk_number' => $request->sk_number,
                ]
            );
        } catch (\Exception $e) {
            Log::warning('Gagal kirim notifikasi beasiswa: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', "Status beasiswa {$student->nama_lengkap} berhasil diperbarui menjadi {$newBeasiswa}.");
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
}
