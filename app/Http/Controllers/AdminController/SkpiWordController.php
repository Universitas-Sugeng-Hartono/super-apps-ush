<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use App\Models\SkpiAcademicProfile;
use App\Models\SkpiDocumentSetting;
use App\Models\SkpiLearningOutcome;
use App\Models\SkpiRegistration;
use App\Models\StudentAchievement;
use App\Models\StudyProgram;
use App\Services\SkpiDocumentEncryption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\TemplateProcessor;

class SkpiWordController extends Controller
{
    public function downloadAllApproved(Request $request)
    {
        $registrationsQuery = SkpiRegistration::query()
            ->with(['student.finalProject', 'approver'])
            ->where('status', 'approved');

        // Jika ada list ID spesifik (dari JS), gunakan itu (Paling Akurat untuk IDM)
        if ($request->filled('registration_ids')) {
            $ids = explode(',', $request->registration_ids);
            $registrationsQuery->whereIn('id', $ids);
        } elseif ($request->filled('registration_id')) {
            $registrationsQuery->where('id', $request->registration_id);
        } else {
            // Fallback ke filter prodi/status jika tidak ada ID (biasanya untuk aksi lain)
            $registrationsQuery->whereHas('student', function ($query) use ($request) {
                if ($request->filled('study_program_id')) {
                    $studyProgram = StudyProgram::find($request->study_program_id);
                    if ($studyProgram) {
                        $query->where('program_studi', $studyProgram->name);
                    }
                }
            })
                ->when($request->filled('generate_status'), function ($q) use ($request) {
                    if ($request->generate_status === 'belum') {
                        $q->whereNull('skpi_document');
                    } elseif ($request->generate_status === 'sudah') {
                        $q->whereNotNull('skpi_document');
                    }
                });
        }

        $registrations = $registrationsQuery->get();

        if ($registrations->isEmpty()) {
            // Jika request melalui AJAX/XHR, kirim JSON. Jika biasa, redirect back.
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Tidak ada data mahasiswa yang cocok dengan filter.'], 404);
            }
            return redirect()
                ->back()
                ->with('error', 'Tidak ada data mahasiswa yang cocok dengan filter untuk di-generate.');
        }

        $documentMeta     = $this->resolveDocumentMeta();
        $manualCategories = array_keys(StudentAchievement::manualCategoryOptions());

        $tempDir = storage_path('app/temp_skpi_' . uniqid());
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $generatedFiles = [];

        foreach ($registrations as $registration) {
            $student         = $registration->student;
            $academicProfile = null;
            $learningOutcome = null;

            if ($student && $student->program_studi) {
                $studyProgram = StudyProgram::query()->where('name', $student->program_studi)->first();
                if ($studyProgram) {
                    $academicProfile = SkpiAcademicProfile::query()
                        ->where('study_program_id', $studyProgram->id)
                        ->first();

                    // Point 4: Ambil dari tabel terpisah
                    $learningOutcome = SkpiLearningOutcome::query()
                        ->where('study_program_id', $studyProgram->id)
                        ->first();
                }
            }

            $approvedAchievements = StudentAchievement::query()
                ->where('student_id', $student->id)
                ->where('status', 'approved')
                ->whereIn('category', $manualCategories)
                ->latest()
                ->get();

            $groupedByCategory = [];
            foreach ($approvedAchievements as $achievement) {
                $groupedByCategory[$achievement->category][] = $achievement;
            }

            // ─────────────────────────────────────────────────────────────
            // Tabel tanpa border, dipakai untuk list pencapaian point 3.
            // CATATAN: label kategori (PRESTASI DAN PENGHARGAAN, dst.) TIDAK
            // dimasukkan di sini karena template Word sudah punya label sendiri
            // sebelum setiap placeholder ${LIST_*}.
            // ─────────────────────────────────────────────────────────────
            $tableStyle = [
                'unit'              => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
                'borderTopSize'     => 0,
                'borderTopColor'     => 'ffffff',
                'borderBottomSize'  => 0,
                'borderBottomColor'  => 'ffffff',
                'borderLeftSize'    => 0,
                'borderLeftColor'    => 'ffffff',
                'borderRightSize'   => 0,
                'borderRightColor'   => 'ffffff',
                'borderInsideHSize' => 0,
                'borderInsideHColor' => 'ffffff',
                'borderInsideVSize' => 0,
                'borderInsideVColor' => 'ffffff',
                'cellMargin'        => 0,
                'indent'            => new \PhpOffice\PhpWord\ComplexType\TblWidth(
                    400,
                    \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP
                ),
            ];

            // Peta terjemahan level & peran ke bahasa Inggris
            $levelEnMap = [
                'Internasional'       => 'International',
                'Nasional'            => 'National',
                'Daerah/Regional'     => 'Regional',
                'Universitas'         => 'University',
                'Fakultas'            => 'Faculty',
                'Jurusan/Program Studi' => 'Department/Study Program',
                'Lanjut'              => 'Advanced',
                'Menengah'            => 'Intermediate',
                'Dasar'               => 'Basic',
                'Lainnya'             => 'Others',
                'Mandiri'             => 'Independent',
                'Kemitraan'           => 'Partnership',
                'Lokal'               => 'Local',
                'Regional'            => 'Regional',
                'Daerah'              => 'Regional',
            ];
            $roleEnMap = [
                'Peserta'             => 'Participant',
                'Panitia'             => 'Committee',
                'Ketua'               => 'Chairman',
                'Wakil Ketua'         => 'Vice Chairman',
                'Sekretaris'          => 'Secretary',
                'Wakil Sekretaris'    => 'Vice Secretary',
                'Bendahara'           => 'Treasurer',
                'Wakil Bendahara'     => 'Vice Treasurer',
                'Ketua Seksi'         => 'Section Head',
                'Anggota Pengurus'    => 'Board Member',
                'Anggota'             => 'Member',
                'Pembicara'           => 'Speaker',
                'Moderator'           => 'Moderator',
                'Pemateri'            => 'Presenter',
                'Penemu/Kreator'      => 'Inventor/Creator',
                'Ketua/Penulis Utama' => 'Lead Author',
                'Anggota/Penulis Anggota' => 'Co-Author',
                'Delegasi'            => 'Delegate',
                'Peserta Undangan'    => 'Invited Participant',
                'Peserta Biasa'       => 'Regular Participant',
                'Asisten/Anggota'     => 'Research Assistant',
                'Juara I'             => '1st Place',
                'Juara II'            => '2nd Place',
                'Juara III'           => '3rd Place',
                'Finalis'             => 'Finalist',
                'Peserta Terpilih'    => 'Selected Participant',
                'Relawan'             => 'Volunteer',
                'Peserta/Relawan'     => 'Participant/Volunteer',
                'Pembimbing'          => 'Mentor',
                'Pelaku'              => 'Participant',
                'Volunteer'           => 'Volunteer',
                'Calon/Anggota'       => 'Candidate/Member',
                'Peserta Magang'      => 'Intern',
                'Pembina'             => 'Supervisor',
                'Pelatih/Pembimbing'  => 'Coach/Mentor',
                'Pengelola'           => 'Manager',
                'Mitra Tanding'       => 'Sparring Partner',
                'Kreator/Seniman'     => 'Creator/Artist',
                'Asisten'             => 'Assistant',
            ];

            // Label kategori -> terjemahan Inggris untuk activity_type_label
            $activityEnMap = [
                'Memperoleh Prestasi Lomba Karya Ilmiah dsb.'  => 'Achievement in Scientific Competition etc.',
                'Mengikuti Kegiatan Lomba (Peserta)'           => 'Participating in Competition (Participant)',
                'Mengikuti Kegiatan / Forum Ilmiah'            => 'Participating in Scientific Forum/Event',
                'Menghasilkan Penemuan Inovasi yang Dipatenkan' => 'Innovation/Invention with Patent',
                'Karya Ilmiah / Jurnal Dipublikasikan'         => 'Published Scientific Work/Journal',
                'Karya Ilmiah Populer di Media Massa'          => 'Popular Scientific Work in Mass Media',
                'Menghasilkan Karya yang Didanai Pihak Lain'   => 'Externally Funded Work',
                'Memberikan Pelatihan Penyusunan Karya Tulis'  => 'Providing Academic Writing Training',
                'Mengikuti Kuliah Tamu/Umum'                   => 'Attending Guest/Public Lecture',
                'Terlibat Dalam Penelitian Dosen'              => 'Involved in Lecturer Research',
                'Pemilihan Mahasiswa Berprestasi (Pilmapres) / Debat' => 'Outstanding Student Selection/Debate',
                'Pelatihan / Pembinaan Softskills'             => 'Softskills Training/Development',
                'Pengurus Organisasi'                          => 'Organization Board Member',
                'Mengikuti Pelatihan Kepemimpinan'             => 'Leadership Training',
                'Panitia dalam Suatu Kegiatan Kemahasiswaan'   => 'Committee in Student Activity',
                'Calon Ketua/Anggota Organisasi Intra'         => 'Candidate/Member of Intra-Campus Organization',
                'Calon Ketua/Anggota Organisasi Ekstra Universitas' => 'Candidate/Member of Extra-Campus Organization',
                'Prestasi Minat dan Bakat (Olahraga, Seni, dll)' => 'Achievement in Interest and Talent (Sports, Arts, etc.)',
                'Mengikuti Kegiatan Minat dan Bakat'           => 'Participating in Interest and Talent Activity',
                'Menjadi Pelatih/Pembimbing Kegiatan Minat Bakat' => 'Coaching/Mentoring Interest and Talent Activity',
                'Melaksanakan Pembinaan Khusus Minat Bakat'    => 'Special Coaching in Interest and Talent',
                'Menjadi Mitra Tanding'                        => 'Serving as Sparring Partner',
                'Menghasilkan Karya Seni (Konser, Pameran, dll)' => 'Producing Artwork (Concert, Exhibition, etc.)',
                'Mengelola Kewirausahaan'                      => 'Managing Entrepreneurship',
                'Mengikuti Pelaksanaan Bakti Sosial'           => 'Participating in Social Service',
                'Penanganan Bencana'                           => 'Disaster Relief',
                'Bimbingan Rutin (Sekolah, Pengajian, TPA, PAUD)' => 'Regular Mentoring (School, Religious, Early Childhood)',
                'Kegiatan Sosial Individual Lainnya'           => 'Other Individual Social Activities',
                'Entepreunership Day'                          => 'Entrepreneurship Day',
                'Universitas (PKKMB)'                          => 'University Orientation (PKKMB)',
                'TOEFL/TDA/Ujian Kompetensi'                   => 'TOEFL/TDA/Competency Test',
                'Kuliah pakar'                                 => 'Expert Lecture',
                'Upacara / Apel'                               => 'Ceremony/Assembly',
                'Berpartisipasi dalam Organisasi Alumni'       => 'Participating in Alumni Organization',
                'Kunjungan / Studi Banding'                    => 'Study Visit/Benchmarking',
                'Magang Kerja Non-Akademik'                    => 'Non-Academic Internship',
                'Penerimaan Mahasiswa Baru / Ekspo PMB'        => 'New Student Admission/Expo',
                'Dies Natalis'                                 => 'Dies Natalis',
                'Panitia PKKMB'                                => 'PKKMB Committee',
                'Asisten Dosen'                                => 'Lecturer Assistant',
            ];

            // Bangun tabel item prestasi — hanya isi, tanpa judul kategori
            $buildAchievementBlock = function (array $items) use ($tableStyle, $levelEnMap, $roleEnMap, $activityEnMap): ?\PhpOffice\PhpWord\Element\Table {
                $table = new \PhpOffice\PhpWord\Element\Table($tableStyle);

                if (empty($items)) {
                    // Jika kosong, kembalikan null agar placeholder diganti dengan string kosong
                    return null;
                }

                foreach ($items as $item) {
                    $labelId = $item->activity_type_label ?? $item->activity_type;

                    // Teks Indonesia
                    $textId = $labelId;
                    if (filled($item->level) && $item->level !== '-') {
                        $textId .= ' (' . $item->level . ')';
                    }
                    if (filled($item->participation_role) && $item->participation_role !== '-') {
                        $textId .= ' - ' . $item->participation_role;
                    }

                    // Bahasa Inggris
                    $labelEn = $activityEnMap[$labelId] ?? $labelId;
                    $textEn  = $labelEn;
                    if (filled($item->level) && $item->level !== '-') {
                        $textEn .= ' (' . ($levelEnMap[$item->level] ?? $item->level) . ')';
                    }
                    if (filled($item->participation_role) && $item->participation_role !== '-') {
                        $textEn .= ' - ' . ($roleEnMap[$item->participation_role] ?? $item->participation_role);
                    }

                    // Gunakan bullet karakter • manual agar tidak auto-numbering lintas kategori
                    $bulletParaStyle = [
                        'spaceAfter'  => 0,
                        'spaceBefore' => 0,
                        'indentation' => ['left' => 360, 'hanging' => 220],
                    ];
                    $engParaStyle = [
                        'alignment'  => \PhpOffice\PhpWord\SimpleType\Jc::LEFT,
                        'spaceAfter' => 60,
                        'spaceBefore' => 0,
                        'indentation' => ['left' => 360],
                    ];

                    $cell = $table->addRow()->addCell(9000);

                    // Baris Indonesia: bullet • + teks hitam
                    $cell->addText(
                        "\u{2022} " . htmlspecialchars($textId),
                        ['size' => 10, 'bold' => false, 'color' => '000000'],
                        $bulletParaStyle
                    );

                    // Baris Inggris: italic biru, indent sejajar teks Indonesia
                    $cell->addText(
                        htmlspecialchars($textEn),
                        ['size' => 9, 'italic' => true, 'color' => '000000'],
                        $engParaStyle
                    );
                }



                return $table;
            };

            // ─── Mapping & Grouping Kategori ke Placeholder Template (Point 1) ──
            // Agar semua kategori di sistem (minat bakat, volunteer, dll) muncul di Word.

            // 1. Prestasi: Penalaran + Minat Bakat
            $prestasiItems = array_merge(
                $groupedByCategory['penalaran'] ?? [],
                $groupedByCategory['minat_bakat'] ?? []
            );

            // 2. Organisasi: Organisasi
            $organisasiItems = $groupedByCategory['organisasi'] ?? [];

            // 3. Magang: Lainnya (Magang) + Volunteer
            $magangItems = array_merge(
                $groupedByCategory['lainnya'] ?? [],
                $groupedByCategory['volunteer'] ?? []
            );

            // 4. Pelatihan: Kepedulian Sosial (atau bisa juga memecah penalaran non-lomba di sini)
            $pelatihanItems = $groupedByCategory['kepedulian_sosial'] ?? [];

            // 5. Sertifikat: Wajib (TOEFL, Kompetensi, dll)
            $sertifikatItems = $groupedByCategory['wajib'] ?? [];

            $prestasiList   = $buildAchievementBlock($prestasiItems);
            $organisasiList = $buildAchievementBlock($organisasiItems);
            $magangList     = $buildAchievementBlock($magangItems);
            $pelatihanList  = $buildAchievementBlock($pelatihanItems);
            $sertifList     = $buildAchievementBlock($sertifikatItems);

            // ─── Template processor ───────────────────────────────────────
            $templateProcessor = new TemplateProcessor(
                public_path('FIXED TEMPLATE SKPI USH ver.2003.docx')
            );

            $ttl = collect([
                $registration->tempat_lahir,
                $registration->tanggal_lahir
                    ? $registration->tanggal_lahir->translatedFormat('d F Y')
                    : null,
            ])->filter()->implode(', ');

            $skripsiTitle   = $student?->finalProject?->title ?? '-';
            $skripsiTitleEn = $student?->finalProject?->title_en ?? '-';

            // ─── Bagian 1: Data Diri ──────────────────────────────────────
            $templateProcessor->setValue('NOMOR_SKPI',              htmlspecialchars($documentMeta['nomor_skpi'] ?? ''));
            $templateProcessor->setValue('NAMA_LENGKAP',            htmlspecialchars($registration->nama_lengkap ?? '-'));
            $templateProcessor->setValue('TTL',                     htmlspecialchars($ttl ?: '-'));
            $templateProcessor->setValue('NIM',                     htmlspecialchars($registration->nim ?? '-'));
            $templateProcessor->setValue('TAHUN_MASUK',             htmlspecialchars($registration->angkatan ?? '-'));
            $templateProcessor->setValue('NOMOR_IJAZAH',            htmlspecialchars($registration->nomor_ijazah ?? '-'));
            $templateProcessor->setValue('GELAR',                   htmlspecialchars($academicProfile?->gelar_lulusan ?? $registration->gelar ?? '-'));

            // ─── Bagian 2: Data Akademik ──────────────────────────────────
            $templateProcessor->setValue('SK_PENDIRIAN',             htmlspecialchars($academicProfile?->sk_pendirian_perguruan_tinggi ?? '-'));
            $templateProcessor->setValue('NAMA_PT',                  htmlspecialchars($academicProfile?->nama_perguruan_tinggi ?? 'UNIVERSITAS SUGENG HARTONO'));
            $templateProcessor->setValue('AKREDITASI_PT',            htmlspecialchars($academicProfile?->akreditasi_perguruan_tinggi ?? '-'));
            $templateProcessor->setValue('PROGRAM_STUDI',            htmlspecialchars($student?->program_studi ?? '-'));
            $templateProcessor->setValue('AKREDITASI_PRODI',         htmlspecialchars($academicProfile?->akreditasi_program_studi ?? '-'));
            $templateProcessor->setValue('JENIS_JENJANG_PENDIDIKAN', htmlspecialchars($academicProfile?->jenis_dan_jenjang_pendidikan ?? '-'));
            $templateProcessor->setValue('JENJANG_KUALIFIKASI_KKNI', htmlspecialchars($academicProfile?->jenjang_kualifikasi_kkni ?? '-'));
            $templateProcessor->setValue('PERSYARATAN_PENERIMAAN',   htmlspecialchars($academicProfile?->persyaratan_penerimaan ?? '-'));
            $templateProcessor->setValue('BAHASA_PENGANTAR',         htmlspecialchars($academicProfile?->bahasa_pengantar_kuliah ?? 'Inggris / Indonesia'));
            $templateProcessor->setValue('NO_AKREDITASI_PT',         htmlspecialchars($academicProfile?->nomor_akreditasi_perguruan_tinggi ?? '-'));
            $templateProcessor->setValue('LAMA_STUDI',               htmlspecialchars($academicProfile?->lama_studi ?? '-'));
            $templateProcessor->setValue('NO_AKREDITASI_PRODI',      htmlspecialchars($academicProfile?->nomor_akreditasi_program_studi ?? '-'));
            $templateProcessor->setValue('STATUS_PROFESI',           htmlspecialchars($academicProfile?->status_profesi ?? '-'));

            $formatRepeater = function ($items) {
                if (empty($items)) return '-';
                if (is_string($items)) $items = [$items];
                if (!is_array($items)) return '-';

                $xmlBlocks = [];
                foreach (array_values($items) as $index => $item) {
                    $cleanItem = trim($item);
                    if (empty($cleanItem)) continue;

                    // Hapus angka manual jika admin sudah terlanjur mengetiknya (contoh: "1. Teks" -> "Teks")
                    $cleanItem = preg_replace('/^\d+\.\s*/', '', $cleanItem);

                    $number = ($index + 1) . '.';
                    $textHtml = str_replace("\n", '<w:br/>', htmlspecialchars($cleanItem));

                    // Injeksi XML Paragraf dengan Hanging Indent (left="360" hanging="360")
                    $xml = '<w:p>' .
                        '<w:pPr>' .
                        '<w:ind w:left="420" w:hanging="420"/>' .
                        '<w:spacing w:after="120"/>' . // Spacing antar poin
                        '</w:pPr>' .
                        '<w:r>' .
                        '<w:t>' . $number . '</w:t>' .
                        '<w:tab/>' .
                        '<w:t xml:space="preserve">' . $textHtml . '</w:t>' .
                        '</w:r>' .
                        '</w:p>';
                    $xmlBlocks[] = $xml;
                }

                if (empty($xmlBlocks)) return '-';

                // Trik keluar dari tag <w:t> bawaan template, lalu masukkan blok kita, lalu buka tag baru untuk ditutup oleh template
                return '</w:t></w:r></w:p>' . implode('', $xmlBlocks) . '<w:p><w:r><w:t>';
            };

            // ─── Point 4: Kualifikasi & Capaian Pembelajaran (tabel terpisah) ──
            // Versi Indonesia
            $templateProcessor->setValue('CP_SIKAP',         $formatRepeater($learningOutcome?->cp_sikap));
            $templateProcessor->setValue('CP_PENGETAHUAN',   $formatRepeater($learningOutcome?->cp_pengetahuan));

            // Versi Inggris
            $templateProcessor->setValue('CP_SIKAP_EN',         $formatRepeater($learningOutcome?->cp_sikap_en));
            $templateProcessor->setValue('CP_PENGETAHUAN_EN',   $formatRepeater($learningOutcome?->cp_pengetahuan_en));

            // ─── Bagian 3: List Aktivitas/Prestasi ───────────────────────
            if ($prestasiList) {
                $templateProcessor->setComplexBlock('LIST_PRESTASI', $prestasiList);
            } else {
                $templateProcessor->setValue('LIST_PRESTASI', '');
            }
            if ($organisasiList) {
                $templateProcessor->setComplexBlock('LIST_ORGANISASI', $organisasiList);
            } else {
                $templateProcessor->setValue('LIST_ORGANISASI', '');
            }
            if ($magangList) {
                $templateProcessor->setComplexBlock('LIST_MAGANG', $magangList);
            } else {
                $templateProcessor->setValue('LIST_MAGANG', '');
            }
            if ($pelatihanList) {
                $templateProcessor->setComplexBlock('LIST_PELATIHAN', $pelatihanList);
            } else {
                $templateProcessor->setValue('LIST_PELATIHAN', '');
            }
            if ($sertifList) {
                $templateProcessor->setComplexBlock('LIST_SERTIFIKAT', $sertifList);
            } else {
                $templateProcessor->setValue('LIST_SERTIFIKAT', '');
            }
            $templateProcessor->setValue('JUDUL_SKRIPSI',           htmlspecialchars($skripsiTitle));
            $templateProcessor->setValue('JUDUL_SKRIPSI_EN',        htmlspecialchars($skripsiTitleEn));

            // ─── Lampiran 3: Transkrip Ringkasan SKP ─────────────────────
            $l3Cats = ['wajib', 'organisasi', 'penalaran', 'minat_bakat', 'kepedulian_sosial', 'lainnya', 'volunteer'];
            $skpByCategory = [];
            $totalSkp = 0;
            foreach ($l3Cats as $cat) {
                $sum = collect($groupedByCategory[$cat] ?? [])->sum('skp_points');
                $skpByCategory[$cat] = $sum;
                $totalSkp += $sum;
            }

            $templateProcessor->setValue('L3_SKP_WAJIB',             (string) ($skpByCategory['wajib'] ?? 0));
            $templateProcessor->setValue('L3_SKP_ORGANISASI',        (string) ($skpByCategory['organisasi'] ?? 0));
            $templateProcessor->setValue('L3_SKP_PENALARAN',         (string) ($skpByCategory['penalaran'] ?? 0));
            $templateProcessor->setValue('L3_SKP_MINAT_BAKAT',       (string) ($skpByCategory['minat_bakat'] ?? 0));
            $templateProcessor->setValue('L3_SKP_KEPEDULIAN_SOSIAL', (string) ($skpByCategory['kepedulian_sosial'] ?? 0));
            $templateProcessor->setValue('L3_SKP_LAINNYA',           (string) ($skpByCategory['lainnya'] ?? 0));
            $templateProcessor->setValue('L3_TOTAL_SKP',             (string) $totalSkp);

            if ($totalSkp > 251) {
                $predikat = 'Sangat Baik';
            } elseif ($totalSkp >= 151) {
                $predikat = 'Baik';
            } elseif ($totalSkp >= 80) {
                $predikat = 'Cukup';
            } else {
                $predikat = '-';
            }
            $templateProcessor->setValue('L3_PREDIKAT', $predikat);

            // ─── Lampiran 3: Tabel Wajib Universitas (L3_WAJIB_TABLE) ─────
            // Header + baris A sekarang ada di dalam PHP generate (user hapus dari Word template)
            $l3BorderStyle = [
                'borderTopSize'      => 8,
                'borderTopColor'      => '000000',
                'borderBottomSize'   => 8,
                'borderBottomColor'   => '000000',
                'borderLeftSize'     => 8,
                'borderLeftColor'     => '000000',
                'borderRightSize'    => 8,
                'borderRightColor'    => '000000',
                'borderInsideHSize'  => 8,
                'borderInsideHColor'  => '000000',
                'borderInsideVSize'  => 8,
                'borderInsideVColor'  => '000000',
                'valign'             => 'center',
            ];
            $l3WajibTableStyle = array_merge($l3BorderStyle, [
                'unit'            => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
                'width'           => 9213,
                'cellMarginLeft'  => 108,
                'cellMarginRight' => 108,
            ]);
            $l3ColNo   = 856;   // 1.51 cm
            $l3ColKrit = 5948;  // 10.49 cm
            $l3ColSkp  = 2409;  // 4.25 cm
            $l3RowH    = 488;   // 0.86 cm
            $Jc = \PhpOffice\PhpWord\SimpleType\Jc::class;

            $l3WajibItems = $groupedByCategory['wajib'] ?? [];
            $l3WajibTable = new \PhpOffice\PhpWord\Element\Table($l3WajibTableStyle);

            // Baris header (No | Kriteria Kegiatan | Nilai SKP)
            $lh = $l3WajibTable->addRow($l3RowH);
            $lh->addCell($l3ColNo,   $l3BorderStyle)->addText('No',               ['size' => 9, 'bold' => true], ['alignment' => $Jc::CENTER]);
            $lh->addCell($l3ColKrit, $l3BorderStyle)->addText('Kriteria Kegiatan', ['size' => 9, 'bold' => true], ['alignment' => $Jc::CENTER]);
            $lh->addCell($l3ColSkp,  $l3BorderStyle)->addText('Nilai SKP',        ['size' => 9, 'bold' => true], ['alignment' => $Jc::CENTER]);

            // Baris A | Wajib Universitas
            $la = $l3WajibTable->addRow($l3RowH);
            $la->addCell($l3ColNo,   $l3BorderStyle)->addText('A',                 ['size' => 9, 'bold' => true], ['alignment' => $Jc::CENTER]);
            $la->addCell($l3ColKrit, $l3BorderStyle)->addText('Wajib Universitas', ['size' => 9]);
            $la->addCell($l3ColSkp,  $l3BorderStyle)->addText('',                  ['size' => 9]);

            // Baris item wajib individual
            if (empty($l3WajibItems)) {
                $le = $l3WajibTable->addRow($l3RowH);
                $le->addCell($l3ColNo,   $l3BorderStyle)->addText('',               ['size' => 9], ['alignment' => $Jc::CENTER]);
                $le->addCell($l3ColKrit, $l3BorderStyle)->addText('Belum ada data', ['size' => 9, 'color' => '999999'], ['alignment' => $Jc::LEFT]);
                $le->addCell($l3ColSkp,  $l3BorderStyle)->addText('-',              ['size' => 9], ['alignment' => $Jc::CENTER]);
            } else {
                foreach ($l3WajibItems as $wi => $wItem) {
                    $lr = $l3WajibTable->addRow($l3RowH);
                    $lr->addCell($l3ColNo,   $l3BorderStyle)->addText((string)($wi + 1), ['size' => 9], ['alignment' => $Jc::CENTER]);
                    $lr->addCell($l3ColKrit, $l3BorderStyle)->addText(htmlspecialchars($wItem->activity_type_label ?? $wItem->activity_type ?? '-'), ['size' => 9]);
                    $lr->addCell($l3ColSkp,  $l3BorderStyle)->addText((string)($wItem->skp_points ?? 0), ['size' => 9], ['alignment' => $Jc::CENTER]);
                }
            }

            try {
                $templateProcessor->setComplexBlock('L3_WAJIB_TABLE', $l3WajibTable);
            } catch (\Throwable $e) {
                Log::warning('[SkpiWord] setComplexBlock(L3_WAJIB_TABLE) gagal: ' . $e->getMessage());
                $templateProcessor->setValue('L3_WAJIB_TABLE', '');
            }

            // ─── Lampiran 4: Rincian Detail SKP per Kategori ──────────────
            $cellBorder = [
                'borderTopSize'      => 8,
                'borderTopColor'      => '000000',
                'borderBottomSize'   => 8,
                'borderBottomColor'   => '000000',
                'borderLeftSize'     => 8,
                'borderLeftColor'     => '000000',
                'borderRightSize'    => 8,
                'borderRightColor'    => '000000',
                'borderInsideHSize'  => 8,
                'borderInsideHColor'  => '000000',
                'borderInsideVSize'  => 8,
                'borderInsideVColor'  => '000000',
                'valign'             => 'center',
            ];
            // Lampiran 4 — total lebar sama (9213 twips), distribusi 6 kolom:
            // No=856(1.51cm), Nama=3500, Tempat=1800, Tahun=850, SKP=850, Bukti=1357
            $l4RowH = 488; // 0.86 cm, sama dengan Lampiran 3
            $rincianTableStyle = array_merge($cellBorder, [
                'unit'            => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
                'width'           => 9213,
                'layout'          => 'fixed',
                'cellMarginLeft'  => 108,
                'cellMarginRight' => 108,
            ]);

            // Closure: buat tabel rincian untuk satu kategori + label judul
            $buildRincianBlock = function (array $items, string $label) use ($cellBorder, $rincianTableStyle, $l4RowH): \PhpOffice\PhpWord\Element\Table {
                $table = new \PhpOffice\PhpWord\Element\Table($rincianTableStyle);
                $fnt9  = ['size' => 9];
                $fnt9b = ['size' => 9, 'bold' => true];
                $noSpc = ['spaceAfter' => 0, 'spaceBefore' => 0];
                $left  = array_merge($noSpc, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT]);
                $mid   = array_merge($noSpc, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

                // Lebar kolom L4 (total 9213 twips)
                $cNo    = 856;
                $cNama  = 3500;
                $cTmpat = 1800;
                $cThn   = 850;
                $cSkp   = 850;
                $cBukti = 1357;
                $totalW = $cNo + $cNama + $cTmpat + $cThn + $cSkp + $cBukti; // 9213

                // ── TAMBAHAN: cell border TANPA noWrap untuk kolom teks panjang ──
                $cellBorderWrap = array_merge($cellBorder, ['noWrap' => false]);

                // Baris judul kategori — 6 cell biasa tanpa gridSpan agar tidak corrupt OOXML
                // Kita pisah "A." dan "Wajib Universitas" ke kolom 1 dan 2 agar teks tidak menumpuk.
                $parts = explode(' ', $label, 2);
                $prefix = $parts[0] ?? '';
                $titleText = $parts[1] ?? '';

                $lt = $table->addRow($l4RowH);
                $lt->addCell($cNo,    $cellBorder)->addText(htmlspecialchars($prefix), $fnt9b, $mid);
                $lt->addCell($cNama,  $cellBorder)->addText(htmlspecialchars($titleText), $fnt9b, $left);
                $lt->addCell($cTmpat, $cellBorder)->addText('', $fnt9, $left);
                $lt->addCell($cThn,   $cellBorder)->addText('', $fnt9, $left);
                $lt->addCell($cSkp,   $cellBorder)->addText('', $fnt9, $left);
                $lt->addCell($cBukti, $cellBorder)->addText('', $fnt9, $left);

                // Header kolom
                $h = $table->addRow($l4RowH);
                $h->addCell($cNo,    $cellBorder)->addText('No',             $fnt9b, $mid);
                $h->addCell($cNama,  $cellBorder)->addText('Nama Kegiatan',  $fnt9b, $mid);
                $h->addCell($cTmpat, $cellBorder)->addText('Tempat/Tingkat', $fnt9b, $mid);
                $h->addCell($cThn,   $cellBorder)->addText('Tahun',          $fnt9b, $mid);
                $h->addCell($cSkp,   $cellBorder)->addText('Nilai SKP',      $fnt9b, $mid);
                $h->addCell($cBukti, $cellBorder)->addText('Bukti Fisik',    $fnt9b, $mid);

                if (empty($items)) {
                    $e = $table->addRow($l4RowH);
                    $e->addCell($cNo,    $cellBorder)->addText('',               $fnt9, $mid);
                    $e->addCell($cNama,  $cellBorder)->addText('Belum ada data', ['size' => 9, 'color' => '999999'], $left);
                    $e->addCell($cTmpat, $cellBorder)->addText('', $fnt9, $mid);
                    $e->addCell($cThn,   $cellBorder)->addText('', $fnt9, $mid);
                    $e->addCell($cSkp,   $cellBorder)->addText('-', $fnt9, $mid);
                    $e->addCell($cBukti, $cellBorder)->addText('', $fnt9, $mid);
                    return $table;
                }

                foreach ($items as $i => $item) {
                    $r = $table->addRow($l4RowH);
                    $r->addCell($cNo,    $cellBorder)->addText((string)($i + 1), $fnt9, $mid);
                    $r->addCell($cNama,  $cellBorderWrap)->addText(htmlspecialchars($item->activity_type_label ?? $item->activity_type ?? '-'), $fnt9, $left);
                    $r->addCell($cTmpat, $cellBorder)->addText(htmlspecialchars($item->level ?? '-'), $fnt9, $mid);
                    $r->addCell($cThn,   $cellBorder)->addText($item->created_at ? $item->created_at->format('Y') : '-', $fnt9, $mid);
                    $r->addCell($cSkp,   $cellBorder)->addText((string)($item->skp_points ?? 0), $fnt9, $mid);
                    $r->addCell($cBukti, $cellBorder)->addText(!empty($item->certificate) ? 'Terlampir' : '-', $fnt9, $mid);
                }

                // Footer total
                $totalRow = collect($items)->sum('skp_points');
                $f = $table->addRow($l4RowH);
                $f->addCell($cNo,    $cellBorder)->addText('',                $fnt9b, $left);
                $f->addCell($cNama,  $cellBorder)->addText('Jumlah SKP',      $fnt9b, $left);
                $f->addCell($cTmpat, $cellBorder)->addText('',                $fnt9,  $left);
                $f->addCell($cThn,   $cellBorder)->addText('',                $fnt9,  $left);
                $f->addCell($cSkp,   $cellBorder)->addText((string)$totalRow, $fnt9b, $mid);
                $f->addCell($cBukti, $cellBorder)->addText('',                $fnt9,  $left);

                return $table;
            };

            // Map placeholder → [kategori, label judul]
            $l4Map = [
                'L4_RINCIAN_WAJIB'             => ['cat' => 'wajib',             'label' => 'A. Wajib Universitas'],
                'L4_RINCIAN_ORGANISASI'        => ['cat' => 'organisasi',        'label' => '1. Kegiatan Bidang Organisasi Kemahasiswaan'],
                'L4_RINCIAN_PENALARAN'         => ['cat' => 'penalaran',         'label' => '2. Kegiatan Bidang Penalaran / Keilmuan'],
                'L4_RINCIAN_MINAT_BAKAT'       => ['cat' => 'minat_bakat',       'label' => '3. Kegiatan Bidang Minat & Bakat'],
                'L4_RINCIAN_KEPEDULIAN_SOSIAL' => ['cat' => 'kepedulian_sosial', 'label' => '4. Kegiatan Bidang Kepedulian Sosial'],
                'L4_RINCIAN_LAINNYA'           => ['cat' => 'lainnya',           'label' => '5. Kegiatan Lainnya'],
                'L4_RINCIAN_VOLUNTEER'         => ['cat' => 'volunteer',         'label' => '6. Volunteer Mahasiswa'],
            ];

            foreach ($l4Map as $placeholder => $info) {
                $items = $groupedByCategory[$info['cat']] ?? [];
                try {
                    $tbl = $buildRincianBlock($items, $info['label']);

                    // Tambahkan spacer row (tanpa border) di akhir setiap tabel
                    // agar ada jeda visual antar blok kategori
                    $noBorder = [
                        'borderTopSize'    => 0,
                        'borderTopColor'    => 'FFFFFF',
                        'borderBottomSize' => 0,
                        'borderBottomColor' => 'FFFFFF',
                        'borderLeftSize'   => 0,
                        'borderLeftColor'   => 'FFFFFF',
                        'borderRightSize'  => 0,
                        'borderRightColor'  => 'FFFFFF',
                    ];
                    $spacer = $tbl->addRow(160);
                    foreach ([856, 3500, 1800, 850, 850, 1357] as $w) {
                        $spacer->addCell($w, $noBorder)->addText('');
                    }

                    $templateProcessor->setComplexBlock($placeholder, $tbl);
                } catch (\Throwable $e) {
                    Log::warning("[SkpiWord] setComplexBlock({$placeholder}) gagal: " . $e->getMessage());
                    $templateProcessor->setValue($placeholder, '');
                }
            }

            // ─── Bagian 6: Pengesahan ─────────────────────────────────────
            $templateProcessor->setValue('KOTA_TANGGAL', htmlspecialchars($documentMeta['authorization_place_date'] ?? ''));
            $templateProcessor->setValue('NAMA_TTD',     htmlspecialchars($documentMeta['vice_rector_name'] ?: '____________________________________'));
            $templateProcessor->setValue('JABATAN_TTD',  htmlspecialchars($documentMeta['vice_rector_title'] ?: 'Wakil Rektor I Universitas Sugeng Hartono'));

            // ─── Tanda Tangan: setImageValue ke placeholder ${TANDA_TANGAN} ──
            if (!empty($documentMeta['signature_path'])) {
                // Ambil langsung dari Storage facade
                $sigPath = \Illuminate\Support\Facades\Storage::disk('public')->path(ltrim($documentMeta['signature_path'], '/'));

                if (!\Illuminate\Support\Facades\File::exists($sigPath)) {
                    // Fallback public_path jika symlink
                    $sigPath = public_path('storage/' . ltrim($documentMeta['signature_path'], '/'));
                }

                if (File::exists($sigPath)) {
                    try {
                        $templateProcessor->setImageValue('TANDA_TANGAN', [
                            'path'   => $sigPath,
                            'width'  => 150,
                            'height' => 75,
                            'ratio'  => true,
                        ]);
                    } catch (\Throwable $e) {
                        Log::warning('[SkpiWord] setImageValue (TANDA_TANGAN) gagal: ' . $e->getMessage());
                        $templateProcessor->setValue('TANDA_TANGAN', '');
                    }
                    try {
                        $templateProcessor->setImageValue('signature_path', [
                            'path'   => $sigPath,
                            'width'  => 150,
                            'height' => 75,
                            'ratio'  => true,
                        ]);
                    } catch (\Throwable $e) {
                        Log::warning('[SkpiWord] setImageValue (signature_path) gagal: ' . $e->getMessage());
                        $templateProcessor->setValue('signature_path', '');
                    }
                } else {
                    Log::warning('[SkpiWord] File tanda tangan tidak ditemukan: ' . $documentMeta['signature_path']);
                    $templateProcessor->setValue('TANDA_TANGAN', '');
                    $templateProcessor->setValue('signature_path', '');
                }
            } else {
                // Jika tidak ada tanda tangan, hilangkan placeholdernya
                $templateProcessor->setValue('TANDA_TANGAN', '');
                $templateProcessor->setValue('signature_path', '');
            }

            // ─── Simpan ───────────────────────────────────────────────────
            $safeNim  = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $registration->nim)          ?: 'student';
            $safeName = preg_replace('/[^A-Za-z0-9_ -]/', '', (string) $registration->nama_lengkap) ?: 'name';

            $fileName  = "SKPI_{$safeNim}_{$safeName}.docx";
            $savePath  = $tempDir . '/' . $fileName;

            $templateProcessor->saveAs($savePath);
            $this->patchTemplateProcessorInsertedImagesForWordCompatibility($savePath);
            $this->patchVAlignCenter($savePath);

            // ─── Enkripsi dan simpan ke database ────────────────────────
            try {
                $fileContent = File::get($savePath);
                $encrypted   = SkpiDocumentEncryption::encrypt($fileContent);

                $registration->update([
                    'skpi_document'      => $encrypted,
                    'skpi_generated_at'  => now(),
                ]);

                Log::info('[SkpiWord] File SKPI berhasil dienkripsi dan disimpan ke DB untuk NIM: ' . $registration->nim);
            } catch (\Throwable $e) {
                Log::error('[SkpiWord] Gagal menyimpan file SKPI ke DB: ' . $e->getMessage());
                // Tidak abort — file tetap dikirim ke browser meski gagal disimpan
            }

            // --- TAMBAHAN: Simpan ke database agar sinkron ---
            try {
                $fileContent = file_get_contents($savePath);
                $encrypted   = SkpiDocumentEncryption::encrypt($fileContent);

                $registration->update([
                    'skpi_document'     => $encrypted,
                    'skpi_generated_at' => now(),
                    'skpi_generated_by' => auth()->id(),
                ]);
            } catch (\Exception $e) {
                Log::error('[SkpiWord] Gagal menyimpan dokumen ke DB untuk ID ' . $registration->id . ': ' . $e->getMessage());
            }

            $generatedFiles[] = $savePath;
        }

        // Jika hanya 1 file karena filter registration_id, return langsung docx-nya
        if (count($generatedFiles) === 1 && $request->filled('registration_id')) {
            $singleFile = $generatedFiles[0];
            return response()->download($singleFile)->deleteFileAfterSend(true);
        }

        // ─── Buat ZIP ─────────────────────────────────────────────────────
        $zipFileName = 'SKPI_Approved_All_' . date('Ymd_His') . '.zip';
        $zipPath     = storage_path('app/' . $zipFileName);

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            foreach ($generatedFiles as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close();
        }

        File::deleteDirectory($tempDir);

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    private function resolveDocumentMeta(): array
    {
        $setting          = SkpiDocumentSetting::query()->latest('updated_at')->first();
        $defaultPlaceDate = 'Sukoharjo, ' . now()->translatedFormat('d F Y');

        return [
            'nomor_skpi'               => $setting?->nomor_skpi ?? '',
            'authorization_place_date' => $setting?->authorization_place_date ?: $defaultPlaceDate,
            'vice_rector_name'         => $setting?->vice_rector_name ?? '',
            'vice_rector_title'        => $setting?->vice_rector_title ?: 'Wakil Rektor I Universitas Sugeng Hartono',
            'signature_path'           => $setting?->signature_path,
        ];
    }

    /**
     * PhpWord TemplateProcessor::setImageValue menulis gambar sebagai VML (<w:pict>/<v:shape>).
     * VML sering tidak tampil di Word Web / beberapa viewer modern, sehingga bagian tanda tangan
     * terlihat kosong walaupun file gambar sudah masuk ke dokumen.
     *
     * Patch ini mengonversi gambar yang ditambahkan TemplateProcessor (Target media/image_rId*_document.*)
     * menjadi DrawingML (<w:drawing>) tanpa mengubah relasi/file media-nya.
     */
    private function patchTemplateProcessorInsertedImagesForWordCompatibility(string $docxPath): void
    {
        $zip = new \ZipArchive();
        if ($zip->open($docxPath) !== true) {
            return;
        }

        $relsXml = $zip->getFromName('word/_rels/document.xml.rels');
        $docXml  = $zip->getFromName('word/document.xml');

        if (!is_string($relsXml) || !is_string($docXml)) {
            $zip->close();
            return;
        }

        // Ambil rId untuk gambar yang ditambahkan oleh TemplateProcessor (bukan gambar bawaan template).
        $matches = [];
        preg_match_all(
            '/<Relationship\\b[^>]*\\bId=\"(rId\\d+)\"[^>]*\\bType=\"[^\"]*\\/image\"[^>]*\\bTarget=\"media\\/image_rId\\d+_document\\.[^\"]+\"[^>]*\\/>/i',
            $relsXml,
            $matches
        );

        $rIds = $matches[1] ?? [];
        if (empty($rIds)) {
            $zip->close();
            return;
        }

        foreach ($rIds as $rId) {
            $docXml = $this->replaceVmlPictWithDrawingMl($docXml, $rId);
        }

        $zip->addFromString('word/document.xml', $docXml);
        $zip->close();
    }

    private function replaceVmlPictWithDrawingMl(string $xml, string $rId): string
    {
        $pattern = '/<w:pict>.*?<v:imagedata\\b[^>]*\\br:id=\"' . preg_quote($rId, '/') . '\"[^>]*\\/>.*?<\\/w:pict>/si';

        return preg_replace_callback($pattern, function (array $m) use ($rId) {
            $widthPx  = 120.0;
            $heightPx = 60.0;

            if (preg_match('/style=\"[^\"]*width:([0-9.]+)px;height:([0-9.]+)px[^\"]*\"/i', $m[0], $s)) {
                $widthPx  = (float) $s[1];
                $heightPx = (float) $s[2];
            }

            // Konversi px (asumsi 96 DPI) ke EMU (1 px = 9525 EMU)
            $cx = (int) round($widthPx * 9525);
            $cy = (int) round($heightPx * 9525);

            $numericId = (int) preg_replace('/\\D+/', '', $rId);
            $docPrId   = 5000 + ($numericId ?: 1);

            return $this->buildDrawingMlImageXml($rId, $cx, $cy, $docPrId);
        }, $xml);
    }

    private function buildDrawingMlImageXml(string $rId, int $cx, int $cy, int $docPrId): string
    {
        return
            '<w:drawing>' .
            '<wp:anchor distT="0" distB="0" distL="114300" distR="114300" simplePos="0" relativeHeight="251658240" behindDoc="1" locked="0" layoutInCell="1" allowOverlap="1">' .
            '<wp:simplePos x="0" y="0"/>' .
            '<wp:positionH relativeFrom="character"><wp:posOffset>0</wp:posOffset></wp:positionH>' .
            '<wp:positionV relativeFrom="line"><wp:posOffset>-400000</wp:posOffset></wp:positionV>' .
            '<wp:extent cx="' . $cx . '" cy="' . $cy . '"/>' .
            '<wp:effectExtent l="0" t="0" r="0" b="0"/>' .
            '<wp:wrapNone/>' .
            '<wp:docPr id="' . $docPrId . '" name="Tanda Tangan ' . htmlspecialchars($rId, ENT_QUOTES) . '"/>' .
            '<a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">' .
            '<a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">' .
            '<pic:pic xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">' .
            '<pic:nvPicPr>' .
            '<pic:cNvPr id="0" name="Tanda Tangan"/>' .
            '<pic:cNvPicPr/>' .
            '</pic:nvPicPr>' .
            '<pic:blipFill>' .
            '<a:blip xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" r:embed="' . htmlspecialchars($rId, ENT_QUOTES) . '"/>' .
            '<a:stretch><a:fillRect/></a:stretch>' .
            '</pic:blipFill>' .
            '<pic:spPr>' .
            '<a:xfrm>' .
            '<a:off x="0" y="0"/>' .
            '<a:ext cx="' . $cx . '" cy="' . $cy . '"/>' .
            '</a:xfrm>' .
            '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom>' .
            '</pic:spPr>' .
            '</pic:pic>' .
            '</a:graphicData>' .
            '</a:graphic>' .
            '</wp:anchor>' .
            '</w:drawing>';
    }

    /**
     * Inject <w:vAlign w:val="center"/> ke semua cell tabel yang belum punya vAlign,
     * sehingga teks secara vertikal berada di tengah cell (bukan menggantung di atas).
     */
    private function patchVAlignCenter(string $docxPath): void
    {
        $zip = new \ZipArchive();
        if ($zip->open($docxPath) !== true) {
            return;
        }

        $docXml = $zip->getFromName('word/document.xml');
        if (!is_string($docXml)) {
            $zip->close();
            return;
        }

        // Inject <w:vAlign w:val="center"/> ke semua cell yang belum punya vAlign
        $count   = 0;
        $patched = preg_replace_callback(
            '/<w:tcPr>[^<]*(?:<(?!\/?w:tcPr>)[^<]*)*<\/w:tcPr>/',
            function (array $m) use (&$count): string {
                if (str_contains($m[0], 'w:vAlign')) {
                    return $m[0];
                }
                $count++;
                return str_replace('</w:tcPr>', '<w:vAlign w:val="center"/></w:tcPr>', $m[0]);
            },
            $docXml
        );

        if ($count > 0 && is_string($patched)) {
            $zip->addFromString('word/document.xml', $patched);
        }

        $zip->close();
    }
}
