<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentAchievement;

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

        // Ambil akumulasi poin yang sudah approved per bidang
        $achievements = $student->achievements ? $student->achievements->where('status', 'approved') : collect();
        $actualFields = [];
        $totalApprovedPoints = 0;

        foreach (self::FIELD_LABELS as $key => $label) {
            $points = (int) $achievements->where('category', $key)->sum('skp_points');
            $actualFields[$key] = $points;
            $totalApprovedPoints += $points;
        }

        // Tentukan kualifikasi tier tertinggi yang berhasil dicapai mahasiswa (Hukum Nilai Minimum: IPK & POKEMA)
        $qualifiedTierKey = '0%';
        $orderedTiers = ['100%', '75%', '50%', '25%'];

        foreach ($orderedTiers as $tKey) {
            $tConfig = self::TIERS[$tKey];
            $minIpk = $tConfig['min_ipk'];
            $targetPoin = $tConfig['targets'][$studyYear]['total'] ?? $tConfig['targets'][1]['total'];

            if ($ipk >= $minIpk && $totalApprovedPoints >= $targetPoin) {
                $qualifiedTierKey = $tKey;
                break;
            }
        }

        $qualifiedTier = self::TIERS[$qualifiedTierKey];
        $currentLevel = $currentTier['level'];
        $qualifiedLevel = $qualifiedTier['level'];

        // Tentukan jenis aksi rekomendasi
        if ($qualifiedLevel > $currentLevel) {
            $actionType = 'upgrade';
            $actionLabel = "Naikkan ke Beasiswa {$qualifiedTierKey}";
            $actionClass = 'btn-upgrade';
            $badgeClass = 'badge-upgrade';
            $targetPoinNeeded = $qualifiedTier['targets'][$studyYear]['total'] ?? $qualifiedTier['targets'][1]['total'];
            $reason = "IPK Kumulatif {$ipk} (Syarat ≥ {$qualifiedTier['min_ipk']}) dan Total POKEMA {$totalApprovedPoints} Poin (Target ≥ {$targetPoinNeeded} Poin) memenuhi syarat kenaikan ke Beasiswa {$qualifiedTierKey}.";
        } elseif ($qualifiedLevel === $currentLevel && $currentLevel > 0) {
            $actionType = 'maintain';
            $actionLabel = "Pertahankan Beasiswa {$currentTierKey}";
            $actionClass = 'btn-maintain';
            $badgeClass = 'badge-maintain';
            $reason = "Prestasi IPK ({$ipk}) dan POKEMA ({$totalApprovedPoints} Poin) stabil memenuhi standar Beasiswa {$currentTierKey}.";
        } elseif ($qualifiedLevel < $currentLevel) {
            if ($qualifiedLevel === 0) {
                $actionType = 'revoke';
                $actionLabel = "Cabut Beasiswa (Turun ke Reguler 0%)";
                $actionClass = 'btn-revoke';
                $badgeClass = 'badge-revoke';
                $target25Poin = self::TIERS['25%']['targets'][$studyYear]['total'] ?? 120;
                $reason = "Capaian IPK ({$ipk}) atau POKEMA ({$totalApprovedPoints} Poin) belum mencapai ambang batas minimal Beasiswa 25% (Min. IPK 3.00 & {$target25Poin} Poin).";
            } else {
                $actionType = 'downgrade';
                $actionLabel = "Sesuaikan ke Beasiswa {$qualifiedTierKey}";
                $actionClass = 'btn-downgrade';
                $badgeClass = 'badge-downgrade';
                $targetCurrent = $currentTier['targets'][$studyYear]['total'] ?? 0;
                $reason = "Capaian POKEMA ({$totalApprovedPoints}/{$targetCurrent} Poin) atau IPK ({$ipk}/{$currentTier['min_ipk']}) berada di bawah target {$currentTierKey}, namun memenuhi kualifikasi Beasiswa {$qualifiedTierKey}.";
            }
        } else {
            // Reguler tetap reguler
            $actionType = 'maintain';
            $actionLabel = "Mahasiswa Reguler (0%)";
            $actionClass = 'btn-maintain';
            $badgeClass = 'badge-reguler';
            $targetReguler = self::TIERS['0%']['targets']['total']['total'] ?? 250;
            $reason = "Mahasiswa Reguler. Perolehan saat ini {$totalApprovedPoints}/{$targetReguler} Poin menuju syarat SKPI.";
        }

        // Tentukan target per bidang yang diacu (gunakan target dari tier yang relevan atau target saat ini)
        $benchmarkTierKey = ($qualifiedLevel > $currentLevel) ? $qualifiedTierKey : ($currentLevel > 0 ? $currentTierKey : '25%');
        $targetFields = self::TIERS[$benchmarkTierKey]['targets'][$studyYear]['fields'] ?? [];
        $totalTargetPoin = self::TIERS[$benchmarkTierKey]['targets'][$studyYear]['total'] ?? 0;

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
        if ($achievements->isNotEmpty()) {
            $maxSem = max($maxSem, (int) $achievements->max('semester'));
        }
        $maxSem = min(14, $maxSem);

        $semesterBreakdown = [];
        $runningCumulativePoints = 0;

        for ($sem = 1; $sem <= $maxSem; $sem++) {
            $semYear = ($sem <= 2) ? 1 : (($sem <= 5) ? 2 : 3);
            $semYearLabel = match($semYear) {
                1 => 'Tahun I',
                2 => 'Tahun II',
                default => 'Tahun III',
            };

            $counseling = null;
            if ($student->relationLoaded('counselings') && $student->counselings) {
                $counseling = $student->counselings->firstWhere('semester', $sem);
            }

            $semAchs = $achievements->where('semester', $sem);
            $semPts = (int) $semAchs->sum('skp_points');
            $runningCumulativePoints += $semPts;

            $yearTarget = self::TIERS[$benchmarkTierKey]['targets'][$semYear]['total'] ?? 0;
            $yearTargetMet = $runningCumulativePoints >= $yearTarget;
            $progressPercent = $yearTarget > 0 ? min(100, round(($runningCumulativePoints / $yearTarget) * 100)) : ($runningCumulativePoints > 0 ? 100 : 0);

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
                'cumulative_points' => $runningCumulativePoints,
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
            'total_target_points' => $totalTargetPoin,
            'benchmark_tier_key' => $benchmarkTierKey,
            'qualified_beasiswa' => $qualifiedTierKey,
            'action_type' => $actionType,
            'action_label' => $actionLabel,
            'action_class' => $actionClass,
            'badge_class' => $badgeClass,
            'reason' => $reason,
            'fields_progress' => $fieldsProgress,
            'semester_breakdown' => $semesterBreakdown,
        ];
    }
}
