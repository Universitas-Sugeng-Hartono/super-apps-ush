<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\AppSetting;

class PokemaEvaluatorService
{
    /**
     * Matriks Standar POKEMA SK Rektor USH No: 2638/SK/01/2026
     */
    public const TIERS = [
        '100%' => [
            'level' => 4,
            'name' => 'Beasiswa 100%',
            'predikat' => 'Sangat Baik',
            'min_ipk' => 3.75,
            'targets' => [
                1 => [ // Tahun I (Bobot 40%)
                    'total' => 179,
                    'fields' => [
                        StudentAchievement::CATEGORY_WAJIB => 51,
                        StudentAchievement::CATEGORY_ORGANISASI => 21,
                        StudentAchievement::CATEGORY_PENALARAN => 18,
                        StudentAchievement::CATEGORY_MINAT_BAKAT => 18,
                        StudentAchievement::CATEGORY_KEPEDULIAN_SOSIAL => 18,
                        StudentAchievement::CATEGORY_LAINNYA => 9,
                        StudentAchievement::CATEGORY_VOLUNTEER => 44,
                    ]
                ],
                2 => [ // Tahun II (Bobot 40%)
                    'total' => 179,
                    'fields' => [
                        StudentAchievement::CATEGORY_WAJIB => 51,
                        StudentAchievement::CATEGORY_ORGANISASI => 21,
                        StudentAchievement::CATEGORY_PENALARAN => 18,
                        StudentAchievement::CATEGORY_MINAT_BAKAT => 18,
                        StudentAchievement::CATEGORY_KEPEDULIAN_SOSIAL => 18,
                        StudentAchievement::CATEGORY_LAINNYA => 9,
                        StudentAchievement::CATEGORY_VOLUNTEER => 44,
                    ]
                ],
                3 => [ // Tahun III (Bobot 20%)
                    'total' => 92,
                    'fields' => [
                        StudentAchievement::CATEGORY_WAJIB => 26,
                        StudentAchievement::CATEGORY_ORGANISASI => 11,
                        StudentAchievement::CATEGORY_PENALARAN => 9,
                        StudentAchievement::CATEGORY_MINAT_BAKAT => 9,
                        StudentAchievement::CATEGORY_KEPEDULIAN_SOSIAL => 9,
                        StudentAchievement::CATEGORY_LAINNYA => 5,
                        StudentAchievement::CATEGORY_VOLUNTEER => 23,
                    ]
                ],
                'total' => [ // Total Akumulasi (Bobot 100%)
                    'total' => 450,
                    'fields' => [
                        StudentAchievement::CATEGORY_WAJIB => 128,
                        StudentAchievement::CATEGORY_ORGANISASI => 53,
                        StudentAchievement::CATEGORY_PENALARAN => 45,
                        StudentAchievement::CATEGORY_MINAT_BAKAT => 45,
                        StudentAchievement::CATEGORY_KEPEDULIAN_SOSIAL => 45,
                        StudentAchievement::CATEGORY_LAINNYA => 23,
                        StudentAchievement::CATEGORY_VOLUNTEER => 111,
                    ]
                ],
                4 => [ // Alias untuk 'total'
                    'total' => 450,
                    'fields' => [
                        StudentAchievement::CATEGORY_WAJIB => 128,
                        StudentAchievement::CATEGORY_ORGANISASI => 53,
                        StudentAchievement::CATEGORY_PENALARAN => 45,
                        StudentAchievement::CATEGORY_MINAT_BAKAT => 45,
                        StudentAchievement::CATEGORY_KEPEDULIAN_SOSIAL => 45,
                        StudentAchievement::CATEGORY_LAINNYA => 23,
                        StudentAchievement::CATEGORY_VOLUNTEER => 111,
                    ]
                ],
            ]
        ],
        '75%' => [
            'level' => 3,
            'name' => 'Beasiswa 75%',
            'predikat' => 'Baik Sekali',
            'min_ipk' => 3.50,
            'targets' => [
                1 => [ // Tahun I (Bobot 40%)
                    'total' => 140,
                    'fields' => [
                        StudentAchievement::CATEGORY_WAJIB => 40,
                        StudentAchievement::CATEGORY_ORGANISASI => 16,
                        StudentAchievement::CATEGORY_PENALARAN => 14,
                        StudentAchievement::CATEGORY_MINAT_BAKAT => 14,
                        StudentAchievement::CATEGORY_KEPEDULIAN_SOSIAL => 14,
                        StudentAchievement::CATEGORY_LAINNYA => 7,
                        StudentAchievement::CATEGORY_VOLUNTEER => 35,
                    ]
                ],
                2 => [ // Tahun II (Bobot 40%)
                    'total' => 140,
                    'fields' => [
                        StudentAchievement::CATEGORY_WAJIB => 40,
                        StudentAchievement::CATEGORY_ORGANISASI => 16,
                        StudentAchievement::CATEGORY_PENALARAN => 14,
                        StudentAchievement::CATEGORY_MINAT_BAKAT => 14,
                        StudentAchievement::CATEGORY_KEPEDULIAN_SOSIAL => 14,
                        StudentAchievement::CATEGORY_LAINNYA => 7,
                        StudentAchievement::CATEGORY_VOLUNTEER => 35,
                    ]
                ],
                3 => [ // Tahun III (Bobot 20%)
                    'total' => 71,
                    'fields' => [
                        StudentAchievement::CATEGORY_WAJIB => 19,
                        StudentAchievement::CATEGORY_ORGANISASI => 9,
                        StudentAchievement::CATEGORY_PENALARAN => 7,
                        StudentAchievement::CATEGORY_MINAT_BAKAT => 7,
                        StudentAchievement::CATEGORY_KEPEDULIAN_SOSIAL => 7,
                        StudentAchievement::CATEGORY_LAINNYA => 4,
                        StudentAchievement::CATEGORY_VOLUNTEER => 18,
                    ]
                ],
                'total' => [ // Total Akumulasi (Bobot 100%)
                    'total' => 351,
                    'fields' => [
                        StudentAchievement::CATEGORY_WAJIB => 99,
                        StudentAchievement::CATEGORY_ORGANISASI => 41,
                        StudentAchievement::CATEGORY_PENALARAN => 35,
                        StudentAchievement::CATEGORY_MINAT_BAKAT => 35,
                        StudentAchievement::CATEGORY_KEPEDULIAN_SOSIAL => 35,
                        StudentAchievement::CATEGORY_LAINNYA => 18,
                        StudentAchievement::CATEGORY_VOLUNTEER => 88,
                    ]
                ],
                4 => [ // Alias untuk 'total'
                    'total' => 351,
                    'fields' => [
                        StudentAchievement::CATEGORY_WAJIB => 99,
                        StudentAchievement::CATEGORY_ORGANISASI => 41,
                        StudentAchievement::CATEGORY_PENALARAN => 35,
                        StudentAchievement::CATEGORY_MINAT_BAKAT => 35,
                        StudentAchievement::CATEGORY_KEPEDULIAN_SOSIAL => 35,
                        StudentAchievement::CATEGORY_LAINNYA => 18,
                        StudentAchievement::CATEGORY_VOLUNTEER => 88,
                    ]
                ],
            ]
        ],
        '50%' => [
            'level' => 2,
            'name' => 'Beasiswa 50%',
            'predikat' => 'Baik',
            'min_ipk' => 3.25,
            'targets' => [
                1 => [ // Tahun I (Bobot 40%)
                    'total' => 120,
                    'fields' => [
                        StudentAchievement::CATEGORY_WAJIB => 34,
                        StudentAchievement::CATEGORY_ORGANISASI => 14,
                        StudentAchievement::CATEGORY_PENALARAN => 12,
                        StudentAchievement::CATEGORY_MINAT_BAKAT => 12,
                        StudentAchievement::CATEGORY_KEPEDULIAN_SOSIAL => 12,
                        StudentAchievement::CATEGORY_LAINNYA => 6,
                        StudentAchievement::CATEGORY_VOLUNTEER => 30,
                    ]
                ],
                2 => [ // Tahun II (Bobot 40%)
                    'total' => 120,
                    'fields' => [
                        StudentAchievement::CATEGORY_WAJIB => 34,
                        StudentAchievement::CATEGORY_ORGANISASI => 14,
                        StudentAchievement::CATEGORY_PENALARAN => 12,
                        StudentAchievement::CATEGORY_MINAT_BAKAT => 12,
                        StudentAchievement::CATEGORY_KEPEDULIAN_SOSIAL => 12,
                        StudentAchievement::CATEGORY_LAINNYA => 6,
                        StudentAchievement::CATEGORY_VOLUNTEER => 30,
                    ]
                ],
                3 => [ // Tahun III (Bobot 20%)
                    'total' => 61,
                    'fields' => [
                        StudentAchievement::CATEGORY_WAJIB => 18,
                        StudentAchievement::CATEGORY_ORGANISASI => 7,
                        StudentAchievement::CATEGORY_PENALARAN => 6,
                        StudentAchievement::CATEGORY_MINAT_BAKAT => 6,
                        StudentAchievement::CATEGORY_KEPEDULIAN_SOSIAL => 6,
                        StudentAchievement::CATEGORY_LAINNYA => 3,
                        StudentAchievement::CATEGORY_VOLUNTEER => 15,
                    ]
                ],
                'total' => [ // Total Akumulasi (Bobot 100%)
                    'total' => 301,
                    'fields' => [
                        StudentAchievement::CATEGORY_WAJIB => 86,
                        StudentAchievement::CATEGORY_ORGANISASI => 35,
                        StudentAchievement::CATEGORY_PENALARAN => 30,
                        StudentAchievement::CATEGORY_MINAT_BAKAT => 30,
                        StudentAchievement::CATEGORY_KEPEDULIAN_SOSIAL => 30,
                        StudentAchievement::CATEGORY_LAINNYA => 15,
                        StudentAchievement::CATEGORY_VOLUNTEER => 75,
                    ]
                ],
                4 => [ // Alias untuk 'total'
                    'total' => 301,
                    'fields' => [
                        StudentAchievement::CATEGORY_WAJIB => 86,
                        StudentAchievement::CATEGORY_ORGANISASI => 35,
                        StudentAchievement::CATEGORY_PENALARAN => 30,
                        StudentAchievement::CATEGORY_MINAT_BAKAT => 30,
                        StudentAchievement::CATEGORY_KEPEDULIAN_SOSIAL => 30,
                        StudentAchievement::CATEGORY_LAINNYA => 15,
                        StudentAchievement::CATEGORY_VOLUNTEER => 75,
                    ]
                ],
            ]
        ],
        '25%' => [
            'level' => 1,
            'name' => 'Beasiswa 25%',
            'predikat' => 'Cukup Baik',
            'min_ipk' => 3.00,
            'targets' => [
                1 => [ // Tahun I (Bobot 40%)
                    'total' => 120,
                    'fields' => [
                        StudentAchievement::CATEGORY_WAJIB => 34,
                        StudentAchievement::CATEGORY_ORGANISASI => 14,
                        StudentAchievement::CATEGORY_PENALARAN => 12,
                        StudentAchievement::CATEGORY_MINAT_BAKAT => 12,
                        StudentAchievement::CATEGORY_KEPEDULIAN_SOSIAL => 12,
                        StudentAchievement::CATEGORY_LAINNYA => 6,
                        StudentAchievement::CATEGORY_VOLUNTEER => 30,
                    ]
                ],
                2 => [ // Tahun II (Bobot 40%)
                    'total' => 120,
                    'fields' => [
                        StudentAchievement::CATEGORY_WAJIB => 34,
                        StudentAchievement::CATEGORY_ORGANISASI => 14,
                        StudentAchievement::CATEGORY_PENALARAN => 12,
                        StudentAchievement::CATEGORY_MINAT_BAKAT => 12,
                        StudentAchievement::CATEGORY_KEPEDULIAN_SOSIAL => 12,
                        StudentAchievement::CATEGORY_LAINNYA => 6,
                        StudentAchievement::CATEGORY_VOLUNTEER => 30,
                    ]
                ],
                3 => [ // Tahun III (Bobot 20%)
                    'total' => 60,
                    'fields' => [
                        StudentAchievement::CATEGORY_WAJIB => 17,
                        StudentAchievement::CATEGORY_ORGANISASI => 7,
                        StudentAchievement::CATEGORY_PENALARAN => 6,
                        StudentAchievement::CATEGORY_MINAT_BAKAT => 6,
                        StudentAchievement::CATEGORY_KEPEDULIAN_SOSIAL => 6,
                        StudentAchievement::CATEGORY_LAINNYA => 3,
                        StudentAchievement::CATEGORY_VOLUNTEER => 15,
                    ]
                ],
                'total' => [ // Total Akumulasi (Bobot 100%)
                    'total' => 300,
                    'fields' => [
                        StudentAchievement::CATEGORY_WAJIB => 85,
                        StudentAchievement::CATEGORY_ORGANISASI => 35,
                        StudentAchievement::CATEGORY_PENALARAN => 30,
                        StudentAchievement::CATEGORY_MINAT_BAKAT => 30,
                        StudentAchievement::CATEGORY_KEPEDULIAN_SOSIAL => 30,
                        StudentAchievement::CATEGORY_LAINNYA => 15,
                        StudentAchievement::CATEGORY_VOLUNTEER => 75,
                    ]
                ],
                4 => [ // Alias untuk 'total'
                    'total' => 300,
                    'fields' => [
                        StudentAchievement::CATEGORY_WAJIB => 85,
                        StudentAchievement::CATEGORY_ORGANISASI => 35,
                        StudentAchievement::CATEGORY_PENALARAN => 30,
                        StudentAchievement::CATEGORY_MINAT_BAKAT => 30,
                        StudentAchievement::CATEGORY_KEPEDULIAN_SOSIAL => 30,
                        StudentAchievement::CATEGORY_LAINNYA => 15,
                        StudentAchievement::CATEGORY_VOLUNTEER => 75,
                    ]
                ],
            ]
        ],
        '0%' => [
            'level' => 0,
            'name' => 'Mahasiswa Reguler (Non-Beasiswa)',
            'predikat' => 'Cukup',
            'min_ipk' => 0.00,
            'targets' => [
                1 => ['total' => 0, 'fields' => []],
                2 => ['total' => 0, 'fields' => []],
                3 => ['total' => 0, 'fields' => []],
                'total' => [
                    'total' => 250,
                    'fields' => [
                        StudentAchievement::CATEGORY_WAJIB => 72,
                        StudentAchievement::CATEGORY_ORGANISASI => 29,
                        StudentAchievement::CATEGORY_PENALARAN => 25,
                        StudentAchievement::CATEGORY_MINAT_BAKAT => 25,
                        StudentAchievement::CATEGORY_KEPEDULIAN_SOSIAL => 25,
                        StudentAchievement::CATEGORY_LAINNYA => 12,
                        StudentAchievement::CATEGORY_VOLUNTEER => 62,
                    ]
                ],
                4 => [
                    'total' => 250,
                    'fields' => [
                        StudentAchievement::CATEGORY_WAJIB => 72,
                        StudentAchievement::CATEGORY_ORGANISASI => 29,
                        StudentAchievement::CATEGORY_PENALARAN => 25,
                        StudentAchievement::CATEGORY_MINAT_BAKAT => 25,
                        StudentAchievement::CATEGORY_KEPEDULIAN_SOSIAL => 25,
                        StudentAchievement::CATEGORY_LAINNYA => 12,
                        StudentAchievement::CATEGORY_VOLUNTEER => 62,
                    ]
                ],
            ]
        ],
    ];

    /**
     * Label 7 Bidang Kegiatan POKEMA
     */
    public const FIELD_LABELS = [
        StudentAchievement::CATEGORY_WAJIB => 'Kegiatan Wajib',
        StudentAchievement::CATEGORY_ORGANISASI => 'Organisasi & Kepemimpinan',
        StudentAchievement::CATEGORY_PENALARAN => 'Penalaran & Keilmuan',
        StudentAchievement::CATEGORY_MINAT_BAKAT => 'Minat & Bakat',
        StudentAchievement::CATEGORY_KEPEDULIAN_SOSIAL => 'Kepedulian Sosial',
        StudentAchievement::CATEGORY_LAINNYA => 'Kegiatan Lainnya',
        StudentAchievement::CATEGORY_VOLUNTEER => 'Volunteer Mahasiswa',
    ];

    /**
     * Dapatkan semester aktual dan tahun studi mahasiswa berdasarkan Card Counseling / Semester
     */
    public static function resolveStudentSemesterAndYear(Student $student): array
    {
        $latestCounseling = null;
        if ($student->relationLoaded('counselings') && $student->counselings) {
            $latestCounseling = $student->counselings->sortByDesc('semester')->first();
        } else {
            $latestCounseling = $student->counselings()->orderByDesc('semester')->first();
        }

        $semester = null;
        $ipk = null;

        if ($latestCounseling) {
            if ($latestCounseling->semester) {
                $semester = (int) $latestCounseling->semester;
            }
            if ($latestCounseling->ipk !== null && is_numeric($latestCounseling->ipk)) {
                $ipk = (float) $latestCounseling->ipk;
            }
        }

        // Jika belum ada kartu konseling, cek kolom semester di tabel students
        if (!$semester && !empty($student->semester)) {
            $semester = (int) $student->semester;
        }

        // Jika masih belum ada, hitung dari angkatan kalender
        if (!$semester) {
            $semester = $student->getCurrentSemester();
        }

        // IPK fallback ke profil student
        if ($ipk === null && is_numeric($student->ipk)) {
            $ipk = (float) $student->ipk;
        }

        // Petakan Semester ke Tahun Studi POKEMA (Berdasarkan SK Rektor Lampiran II)
        // Tahun I: Semester 1–2 (Bobot 40%)
        // Tahun II: Semester 3–5 (Bobot 40%)
        // Tahun III: Semester 5–8+ (Bobot 20%)
        if ($semester <= 2) {
            $studyYear = 1;
        } elseif ($semester <= 5) {
            $studyYear = 2;
        } else {
            $studyYear = 3;
        }

        return [
            'semester'   => $semester,
            'study_year' => $studyYear,
            'ipk'        => $ipk ?: 0.00,
        ];
    }

    /**
     * Hitung tahun studi mahasiswa (Tahun 1, 2, atau 3) berdasarkan semester konseling
     */
    public static function estimateStudyYear(Student $student): int
    {
        $resolved = self::resolveStudentSemesterAndYear($student);
        return $resolved['study_year'];
    }

    /**
     * Normalisasi string beasiswa ke key standar ('100%', '75%', '50%', '25%', '0%')
     */
    public static function normalizeTierKey(?string $beasiswa): string
    {
        if (empty($beasiswa)) {
            return '0%';
        }

        $val = trim($beasiswa);
        if (in_array($val, ['0%', '0', 'Non-Beasiswa', 'reguler'], true)) {
            return '0%';
        }

        if (is_numeric($val)) {
            $val .= '%';
        }

        if (isset(self::TIERS[$val])) {
            return $val;
        }

        // Cek jika mengandung angka
        if (preg_match('/(\d+)%?/', $val, $matches)) {
            $num = (int) $matches[1];
            if ($num >= 100) return '100%';
            if ($num >= 75) return '75%';
            if ($num >= 50) return '50%';
            if ($num >= 25) return '25%';
        }

        return '0%';
    }

    /**
     * Dapatkan tier 1 tingkat di bawahnya untuk sanksi degradasi berjenjang (step-down)
     */
    public static function getNextLowerTierKey(string $tierKey): string
    {
        return match ($tierKey) {
            '100%' => '75%',
            '75%' => '50%',
            '50%' => '25%',
            default => '0%',
        };
    }

    /**
     * Evaluasi kelayakan beasiswa mahasiswa secara menyeluruh
     */
    public static function evaluate(Student $student, int|string|null $forceYear = null): array
    {
        $resolved = self::resolveStudentSemesterAndYear($student);
        $semester = $resolved['semester'];
        $studyYear = $forceYear !== null ? $forceYear : $resolved['study_year'];
        if ($studyYear === 4 || $studyYear === '4') {
            $studyYear = 'total';
        }

        $studyYearLabel = match ((string) $studyYear) {
            '1' => 'Tahun I (Semester 1–2, Bobot 40%)',
            '2' => 'Tahun II (Semester 3–5, Bobot 40%)',
            '3' => 'Tahun III (Semester 5–8, Bobot 20%)',
            'total', '4' => 'Total Keseluruhan (Semester 1–8, Bobot 100%)',
            default => 'Tahun I (Semester 1–2, Bobot 40%)',
        };

        $currentTierKey = self::normalizeTierKey($student->beasiswa);
        $currentTier = self::TIERS[$currentTierKey];
        $ipk = $resolved['ipk'];

        // Tentukan semester yang termasuk dalam periode studi ini
        // Berdasarkan SK Rektor No: 2638/SK/01/2026:
        // - Tahun I : Semester 1 & 2
        // - Tahun II : Semester 3, 4, 5
        // - Tahun III: Semester 6, 7, 8+
        // - Total    : Seluruh semester
        $periodSemesters = match ((string) $studyYear) {
            '1' => [1, 2],
            '2' => [3, 4, 5],
            '3' => [6, 7, 8, 9, 10, 11, 12, 13, 14],
            default => null, // null berarti seluruh semester ('total')
        };

        // Ambil semua prestasi yang approved
        $allApprovedAchievements = $student->achievements ? $student->achievements->where('status', 'approved') : collect();
        $overallApprovedPoints = (int) $allApprovedAchievements->sum('skp_points');

        // Filter prestasi yang murni masuk dalam periode evaluasi saat ini
        if ($periodSemesters !== null) {
            $periodAchievements = $allApprovedAchievements->filter(function ($ach) use ($periodSemesters, $studyYear) {
                if (empty($ach->semester)) {
                    // Fallback data lama tanpa semester: masukkan ke Tahun 1
                    return (string) $studyYear === '1';
                }
                return in_array((int) $ach->semester, $periodSemesters, true);
            });
        } else {
            $periodAchievements = $allApprovedAchievements;
        }

        $actualFields = [];
        $totalApprovedPoints = 0;

        foreach (self::FIELD_LABELS as $key => $label) {
            $points = (int) $periodAchievements->where('category', $key)->sum('skp_points');
            $actualFields[$key] = $points;
            $totalApprovedPoints += $points;
        }

        // Target 7 bidang untuk tier saat ini
        $currentTargetFields = $currentTier['targets'][$studyYear]['fields'] ?? [];
        $currentTierFulfilledCount = 0;
        $currentTierUnfulfilledList = [];

        foreach (self::FIELD_LABELS as $fKey => $fLabel) {
            $fActual = $actualFields[$fKey] ?? 0;
            $fTarget = $currentTargetFields[$fKey] ?? 0;
            if ($fActual >= $fTarget) {
                $currentTierFulfilledCount++;
            } else {
                $deficit = $fTarget - $fActual;
                $currentTierUnfulfilledList[] = "{$fLabel} (kurang {$deficit} Poin)";
            }
        }
        $currentTierAllFieldsMet = ($currentTierFulfilledCount === count(self::FIELD_LABELS));

        // Tentukan kualifikasi tier tertinggi yang berhasil dicapai mahasiswa
        // Berdasarkan SK Rektor No: 2638/SK/01/2026: Syarat beasiswa adalah IPK + Total POKEMA + Seluruh 7 Bidang Wajib Terpenuhi
        $qualifiedTierKey = '0%';
        $orderedTiers = ['100%', '75%', '50%', '25%'];

        foreach ($orderedTiers as $tKey) {
            $tConfig = self::TIERS[$tKey];
            $minIpk = $tConfig['min_ipk'];
            $targetPoin = $tConfig['targets'][$studyYear]['total'] ?? $tConfig['targets'][1]['total'];
            $tierFields = $tConfig['targets'][$studyYear]['fields'] ?? [];

            // Evaluasi apakah seluruh 7 bidang terpenuhi untuk tier ini
            $tierAllFieldsMet = true;
            foreach ($tierFields as $fKey => $fTarget) {
                if (($actualFields[$fKey] ?? 0) < $fTarget) {
                    $tierAllFieldsMet = false;
                    break;
                }
            }

            if ($ipk >= $minIpk && $totalApprovedPoints >= $targetPoin && $tierAllFieldsMet) {
                $qualifiedTierKey = $tKey;
                break;
            }
        }

        $qualifiedTier = self::TIERS[$qualifiedTierKey];
        $currentLevel = $currentTier['level'];
        $qualifiedLevel = $qualifiedTier['level'];

        $activeStudyYear = $resolved['study_year'];
        $isCurrentYear = ($studyYear == $activeStudyYear);
        $targetCurrent = $currentTier['targets'][$studyYear]['total'] ?? 0;
        $minIpkCurrent = $currentTier['min_ipk'] ?? 3.00;
        $pointsRemaining = max(0, $targetCurrent - $totalApprovedPoints);
        $progressPct = $targetCurrent > 0 ? round(($totalApprovedPoints / $targetCurrent) * 100, 1) : 100;

        // Tentukan jenis aksi rekomendasi
        if ($currentLevel === 0) {
            // 1. Mahasiswa Reguler (Non-Beasiswa)
            $actionType = 'reguler';
            $actionLabel = "Mahasiswa Reguler (0%)";
            $actionClass = 'btn-maintain';
            $badgeClass = 'badge-reguler';
            $targetReguler = self::TIERS['0%']['targets']['total']['total'] ?? 250;
            $reason = "Mahasiswa Reguler. Perolehan saat ini {$totalApprovedPoints}/{$targetReguler} Poin menuju syarat kelulusan SKPI.";
        } elseif ($qualifiedLevel > $currentLevel) {
            // 2. Kualifikasi melampaui tier saat ini (Promosi / Upgrade)
            $actionType = 'upgrade';
            $actionLabel = "Rekomendasi Promosi ke Beasiswa {$qualifiedTierKey}";
            $actionClass = 'btn-upgrade';
            $badgeClass = 'badge-upgrade';
            $targetPoinNeeded = $qualifiedTier['targets'][$studyYear]['total'] ?? $qualifiedTier['targets'][1]['total'];
            $reason = "Prestasi IPK ({$ipk} ≥ {$qualifiedTier['min_ipk']}), Total POKEMA ({$totalApprovedPoints} ≥ {$targetPoinNeeded} Poin), dan seluruh 7 bidang kegiatan memenuhi kualifikasi kenaikan ke Beasiswa {$qualifiedTierKey}.";
        } elseif ($totalApprovedPoints >= $targetCurrent && $ipk >= $minIpkCurrent && $currentTierAllFieldsMet) {
            // 3. Target periode telah terpenuhi penuh (IPK + Poin + Seluruh 7 Bidang Lengkap)
            $actionType = 'maintain';
            $actionLabel = "Target Periode Terpenuhi (Pertahankan {$currentTierKey})";
            $actionClass = 'btn-maintain';
            $badgeClass = 'badge-maintain';
            $reason = "Perolehan {$totalApprovedPoints} Poin, IPK {$ipk}, dan seluruh 7 bidang kegiatan POKEMA lengkap memenuhi standar Beasiswa {$currentTierKey} (Target {$targetCurrent} Poin & Min. IPK {$minIpkCurrent}).";
        } elseif ($isCurrentYear && AppSetting::isBeasiswaUploadOpen()) {
            // 4. Periode Tahun Berjalan (On-Going / Nilai terus di-update) - Hanya jika portal DIBUKA
            if ($totalApprovedPoints >= $targetCurrent && $ipk >= $minIpkCurrent && !$currentTierAllFieldsMet) {
                // Poin sudah mencapai target, tapi 7 bidang belum lengkap
                $unfulfilledCount = count($currentTierUnfulfilledList);
                $actionType = 'warning';
                $actionLabel = "Poin Tercapai, Namun Sebaran 7 Bidang Belum Lengkap ({$currentTierFulfilledCount}/7 Terpenuhi)";
                $actionClass = 'btn-warning';
                $badgeClass = 'badge-warning';
                $reason = "Tahun {$studyYear} sedang berlangsung (Semester {$semester}). Total capaian ({$totalApprovedPoints}/{$targetCurrent} Poin) dan IPK ({$ipk}) telah mencapai standar, namun berdasarkan SK Rektor No: 2638/SK/01/2026 seluruh 7 bidang wajib terpenuhi. Mahasiswa belum memenuhi {$unfulfilledCount} bidang: " . implode(', ', $currentTierUnfulfilledList) . ". Mahasiswa harus melengkapi bidang tersebut sebelum evaluasi akhir periode beasiswa.";
            } elseif ($ipk >= $minIpkCurrent && $totalApprovedPoints >= ($targetCurrent * 0.35)) {
                // On Track: IPK aman & perolehan poin berjalan positif
                $actionType = 'on_track';
                $actionLabel = "Progres Berjalan: Beasiswa {$currentTierKey} ({$currentTierFulfilledCount}/7 Bidang)";
                $actionClass = 'btn-on-track';
                $badgeClass = 'badge-on-track';
                $unfulfilledNote = !empty($currentTierUnfulfilledList) ? ' serta melengkapi bidang yang belum terpenuhi (' . implode(', ', $currentTierUnfulfilledList) . ')' : '';
                $reason = "Tahun {$studyYear} sedang berlangsung (Semester {$semester}). Capaian saat ini {$totalApprovedPoints} dari {$targetCurrent} Poin ({$progressPct}%) dengan IPK {$ipk} (Aman ≥ {$minIpkCurrent}), dan {$currentTierFulfilledCount}/7 bidang terpenuhi. Mahasiswa membutuhkan {$pointsRemaining} Poin lagi{$unfulfilledNote} sebelum evaluasi akhir periode.";
            } elseif ($ipk >= 3.00) {
                // Perlu peningkatan: Poin masih tertinggal
                $actionType = 'warning';
                $actionLabel = "Perlu Peningkatan: Sisa {$pointsRemaining} Poin ({$currentTierFulfilledCount}/7 Bidang)";
                $actionClass = 'btn-warning';
                $badgeClass = 'badge-warning';
                $unfulfilledNote = !empty($currentTierUnfulfilledList) ? ' dan pemenuhan bidang yang belum lengkap (' . implode(', ', $currentTierUnfulfilledList) . ')' : '';
                $reason = "Tahun {$studyYear} sedang berlangsung (Semester {$semester}). Capaian saat ini {$totalApprovedPoints} dari {$targetCurrent} Poin ({$progressPct}%) dengan IPK {$ipk}, baru {$currentTierFulfilledCount}/7 bidang terpenuhi. Mahasiswa perlu menambah {$pointsRemaining} Poin lagi{$unfulfilledNote} sebelum evaluasi akhir periode.";
            } else {
                // IPK di bawah standar minimal beasiswa (Min. 3.00)
                $actionType = 'warning';
                $actionLabel = "Peringatan: IPK di Bawah Standar ({$ipk} < 3.00)";
                $actionClass = 'btn-warning';
                $badgeClass = 'badge-warning';
                $reason = "IPK mahasiswa saat ini ({$ipk}) berada di bawah standar minimal beasiswa (Min. 3.00). Diperlukan evaluasi dan bimbingan akademik dosen PA agar beasiswa dapat dipertahankan.";
            }
        } elseif (is_numeric($studyYear) && (int) $studyYear > (int) $activeStudyYear) {
            // 5. Periode Tahun Mendatang (Belum Dimulai)
            $actionType = 'future';
            $actionLabel = "Target Tahun {$studyYear} (Periode Mendatang)";
            $actionClass = 'btn-on-track';
            $badgeClass = 'badge-on-track';
            $reason = "Mahasiswa saat ini berada di Tahun {$activeStudyYear} (Semester {$semester}). Target akumulasi Tahun {$studyYear} untuk Beasiswa {$currentTierKey} adalah {$targetCurrent} Poin (Saat ini terkumpul {$totalApprovedPoints} Poin).";
        } elseif ($studyYear === 'total' && (int) $activeStudyYear < 3 && AppSetting::isBeasiswaUploadOpen()) {
            // 6. Akumulasi Total Saat Masa Studi Berjalan (Hanya jika portal DIBUKA)
            $actionType = 'on_track';
            $actionLabel = "Akumulasi Keseluruhan: Target {$targetCurrent} Poin";
            $actionClass = 'btn-on-track';
            $badgeClass = 'badge-on-track';
            $reason = "Akumulasi seluruh periode beasiswa hingga kelulusan. Capaian saat ini {$totalApprovedPoints} dari {$targetCurrent} Poin ({$progressPct}%) dengan IPK {$ipk}.";
        } else {
            // 7. Periode Telah Berakhir atau Portal Ditutup & Gagal Memenuhi Syarat: Turun 1 Tingkat (Step-Down Downgrade)
            $isPortalOpen = AppSetting::isBeasiswaUploadOpen();
            $stepDownTierKey = self::getNextLowerTierKey($currentTierKey);
            $nextTierLevel = self::TIERS[$stepDownTierKey]['level'] ?? 0;
            $unfulfilledText = !empty($currentTierUnfulfilledList) ? 'Bidang belum terpenuhi: ' . implode(', ', $currentTierUnfulfilledList) . '. ' : '';
            $nextYear = is_numeric($studyYear) ? min(3, (int)$studyYear + 1) : 2;
            $nextYearRoman = match($nextYear) { 1 => 'I', 2 => 'II', default => 'III' };

            if ($nextTierLevel > 0) {
                // Turun 1 tingkat (100% -> 75%, 75% -> 50%, 50% -> 25%)
                $actionType = 'downgrade';
                $recommendedTierKey = $stepDownTierKey;
                $actionLabel = "Evaluasi Akhir: Turun ke Beasiswa {$stepDownTierKey}";
                $actionClass = 'btn-downgrade';
                $badgeClass = 'badge-downgrade';
                $nextMinIpk = self::TIERS[$stepDownTierKey]['min_ipk'];
                $nextTargetPts = self::TIERS[$stepDownTierKey]['targets'][$nextYear]['total'] ?? 120;

                $reason = (!$isPortalOpen)
                    ? "Portal pengajuan prestasi ditutup untuk evaluasi akhir periode. Capaian belum memenuhi seluruh syarat Beasiswa {$currentTierKey} ({$unfulfilledText}Total: {$totalApprovedPoints}/{$targetCurrent} Poin, IPK: {$ipk}/{$minIpkCurrent}). Sesuai ketentuan, beasiswa diturunkan 1 tingkat ke Beasiswa {$stepDownTierKey} untuk periode Tahun {$nextYearRoman}. Pada Tahun {$nextYearRoman}, mahasiswa wajib memenuhi 3 standar Beasiswa {$stepDownTierKey}: IPK ≥ {$nextMinIpk}, target {$nextTargetPts} Poin, dan seluruh 7 bidang POKEMA."
                    : "Periode Tahun {$studyYear} telah berakhir. Capaian belum memenuhi seluruh syarat Beasiswa {$currentTierKey} ({$unfulfilledText}Total: {$totalApprovedPoints}/{$targetCurrent} Poin, IPK: {$ipk}/{$minIpkCurrent}). Sesuai ketentuan, beasiswa diturunkan 1 tingkat ke Beasiswa {$stepDownTierKey} untuk periode Tahun {$nextYearRoman}. Pada Tahun {$nextYearRoman}, mahasiswa wajib memenuhi 3 standar Beasiswa {$stepDownTierKey}: IPK ≥ {$nextMinIpk}, target {$nextTargetPts} Poin, dan seluruh 7 bidang POKEMA.";
            } else {
                // Mahasiswa pada tier terendah (25%) gagal memenuhi syarat -> Turun ke Reguler (0%)
                $actionType = 'revoke';
                $recommendedTierKey = '0%';
                $actionLabel = "Evaluasi Akhir: Belum Memenuhi Syarat Minimal (Turun ke 0%)";
                $actionClass = 'btn-revoke';
                $badgeClass = 'badge-revoke';

                $reason = (!$isPortalOpen)
                    ? "Portal pengajuan prestasi ditutup untuk evaluasi akhir periode. Mahasiswa pada Beasiswa 25% belum memenuhi syarat minimal ({$unfulfilledText}Total: {$totalApprovedPoints} Poin, IPK: {$ipk}). Sesuai ketentuan, beasiswa dicabut dan status dialihkan ke Mahasiswa Reguler (0%). Admin Kemahasiswaan tetap dapat menyesuaikan status ini secara manual."
                    : "Periode Tahun {$studyYear} telah berakhir. Mahasiswa pada Beasiswa 25% belum memenuhi syarat minimal ({$unfulfilledText}Total: {$totalApprovedPoints} Poin, IPK: {$ipk}). Sesuai ketentuan, beasiswa dicabut dan status dialihkan ke Mahasiswa Reguler (0%). Admin Kemahasiswaan tetap dapat menyesuaikan status ini secara manual.";
            }
        }

        // Tentukan tier yang direkomendasikan jika belum di-set secara spesifik
        if (!isset($recommendedTierKey)) {
            $recommendedTierKey = ($actionType === 'upgrade') ? $qualifiedTierKey : $currentTierKey;
        }

        // Tentukan target per bidang yang diacu (gunakan target dari tier yang relevan atau target saat ini)
        $benchmarkTierKey = ($currentLevel > 0) ? (($qualifiedLevel > $currentLevel) ? $qualifiedTierKey : $currentTierKey) : '0%';
        $targetFields = self::TIERS[$benchmarkTierKey]['targets'][$studyYear]['fields'] ?? [];
        $totalTargetPoin = self::TIERS[$benchmarkTierKey]['targets'][$studyYear]['total'] ?? 250;

        $fieldsProgress = [];
        foreach (self::FIELD_LABELS as $key => $label) {
            $cur = $actualFields[$key] ?? 0;
            $tgt = $targetFields[$key] ?? 0;
            $pct = $tgt > 0 ? min(100, round(($cur / $tgt) * 100)) : ($cur > 0 ? 100 : 0);
            $realPct = $tgt > 0 ? round(($cur / $tgt) * 100) : ($cur > 0 ? 100 : 0);

            $fieldsProgress[$key] = [
                'key' => $key,
                'label' => $label,
                'current' => $cur,
                'target' => $tgt,
                'percent' => $pct,
                'real_percent' => $realPct,
                'is_fulfilled' => $cur >= $tgt,
            ];
        }

        // Hitung Breakdown Rincian & Akumulasi Per Semester
        $maxSem = max(2, (int) $semester);
        if ($student->relationLoaded('counselings') && $student->counselings && $student->counselings->isNotEmpty()) {
            $maxSem = max($maxSem, (int) $student->counselings->max('semester'));
        }
        if ($allApprovedAchievements->isNotEmpty()) {
            $maxSem = max($maxSem, (int) $allApprovedAchievements->max('semester'));
        }
        $maxSem = min(14, $maxSem);

        $semesterBreakdown = [];
        $runningCumulativePoints = 0;
        $runningYearPoints = 0;
        $prevSemYear = null;

        for ($sem = 1; $sem <= $maxSem; $sem++) {
            $semYear = ($sem <= 2) ? 1 : (($sem <= 5) ? 2 : 3);
            if ($prevSemYear !== null && $semYear !== $prevSemYear) {
                $runningYearPoints = 0; // Reset akumulasi per periode tahun studi
            }
            $prevSemYear = $semYear;

            $semYearLabel = match($semYear) {
                1 => 'Tahun I',
                2 => 'Tahun II',
                default => 'Tahun III',
            };

            $counseling = null;
            if ($student->relationLoaded('counselings') && $student->counselings) {
                $counseling = $student->counselings->firstWhere('semester', $sem);
            }

            $semAchs = $allApprovedAchievements->filter(function ($a) use ($sem) {
                return ((int) ($a->semester ?: 1)) === $sem;
            });
            $semPts = (int) $semAchs->sum('skp_points');
            $runningCumulativePoints += $semPts;
            $runningYearPoints += $semPts;

            $semCountInYear = match($semYear) {
                1 => 2, // Sem 1 - 2
                2 => 3, // Sem 3 - 5
                default => 3, // Sem 6 - 8
            };
            $yearTarget = self::TIERS[$benchmarkTierKey]['targets'][$semYear]['total'] ?? 0;
            $semTarget = $yearTarget > 0 ? (int) round($yearTarget / $semCountInYear) : 0;
            $semProgressPercent = $semTarget > 0 ? min(100, round(($semPts / $semTarget) * 100)) : ($semPts > 0 ? 100 : 0);
            $semTargetMet = $semTarget > 0 ? ($semPts >= $semTarget) : ($semPts > 0);

            $yearTargetMet = $runningYearPoints >= $yearTarget;
            $progressPercent = $yearTarget > 0 ? min(100, round(($runningYearPoints / $yearTarget) * 100)) : ($runningYearPoints > 0 ? 100 : 0);

            $semesterBreakdown[] = [
                'semester' => $sem,
                'semester_label' => "Semester {$sem}",
                'study_year' => $semYear,
                'study_year_label' => $semYearLabel,
                'is_current' => $sem === (int) $semester,
                'ipk' => ($counseling && $counseling->ipk !== null) ? number_format((float) $counseling->ipk, 2) : null,
                'ip' => ($counseling && $counseling->ip !== null) ? number_format((float) $counseling->ip, 2) : null,
                'has_counseling' => $counseling !== null,
                'achievements_count' => $semAchs->count(),
                'semester_points' => $semPts,
                'semester_target' => $semTarget,
                'semester_progress_percent' => $semProgressPercent,
                'is_semester_target_met' => $semTargetMet,
                'cumulative_points' => $runningYearPoints,
                'overall_cumulative_points' => $runningCumulativePoints,
                'year_target' => $yearTarget,
                'progress_percent' => $progressPercent,
                'is_year_target_met' => $yearTargetMet,
                'is_period_milestone' => ($sem === 2 || $sem === 5 || $sem === 8),
            ];
        }

        return [
            'student_id' => $student->id,
            'student_name' => $student->nama_lengkap,
            'nim' => $student->nim,
            'study_program' => $student->program_studi,
            'semester' => $semester,
            'semester_label' => "Semester {$semester}",
            'study_year' => $studyYear,
            'study_year_label' => $studyYearLabel,
            'current_beasiswa' => $currentTierKey,
            'current_beasiswa_label' => $currentTier['name'],
            'ipk' => $ipk,
            'total_approved_points' => $totalApprovedPoints,
            'overall_approved_points' => $overallApprovedPoints,
            'total_target_points' => $totalTargetPoin,
            'qualified_beasiswa' => $recommendedTierKey,
            'recommended_beasiswa' => $recommendedTierKey,
            'raw_qualified_beasiswa' => $qualifiedTierKey,
            'action_type' => $actionType,
            'action_label' => $actionLabel,
            'action_class' => $actionClass,
            'badge_class' => $badgeClass,
            'reason' => $reason,
            'target_current' => $targetCurrent,
            'points_remaining' => $pointsRemaining,
            'progress_pct' => $progressPct,
            'is_current_year' => $isCurrentYear,
            'fields_progress' => $fieldsProgress,
            'semester_breakdown' => $semesterBreakdown,
            'fulfilled_fields_count' => $currentTierFulfilledCount,
            'unfulfilled_fields_count' => count($currentTierUnfulfilledList),
            'unfulfilled_fields_list' => $currentTierUnfulfilledList,
            'all_fields_met' => $currentTierAllFieldsMet,
        ];
    }
}
