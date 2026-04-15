<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>SKPI PDF</title>
    <style>
        @page {
            size: A4;
            margin: 14mm 10mm 14mm 10mm;
        }

        body {
            font-family: "DejaVu Serif", serif;
            font-size: 11px;
            line-height: 1.45;
            color: #000000;
            margin: 0;
        }

        .skpi-doc {
            width: 100%;
        }

        .doc-header {
            display: table;
            width: 100%;
            margin-left: 8px;
            padding-right: 90px;
        }

        .logo-col,
        .identity-col {
            display: table-cell;
            vertical-align: middle;
        }

        .logo-col {
            width: 82px;
            text-align: center;
            padding: 0 10px 10px 0;
        }

        .logo-col img {
            width: 68px;
            height: auto;
        }

        .identity-col {
            color: #7d7d7d;
        }

        .campus-title {
            font-size: 25px;
            font-weight: 700;
            line-height: 1.1;
            margin-bottom: 3px;
        }

        .campus-subtitle {
            font-size: 18px;
            font-style: italic;
            font-weight: 700;
        }

        .doc-rule {
            border-top: 1px solid #000000;
            margin: 0 90px 10px 90px;
        }

        .doc-title-block,
        .doc-intro {
            margin-left: 90px;
            margin-right: 90px;
        }

        .doc-main-title {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .doc-sub-title {
            font-size: 11px;
            font-style: italic;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .doc-number {
            font-size: 11px;
            margin-bottom: 10px;
        }

        .doc-intro,
        .auth-section,
        .list-block,
        .thesis-section {
            padding: 0 0 10px;
        }

        .doc-intro p {
            margin: 0 0 6px;
        }

        .en {
            font-style: italic;
            font-weight: 400;
        }

        .section-title-row {
            background: #efefef;
            font-weight: 700;
            padding: 5px 8px;
            border-bottom: 1px solid #000000;
        }

        .doc-section {
            border-top: none;
            margin-left: 90px;
            margin-right: 90px;
            margin-top: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .field-table td {
            padding: 8px 10px;
            vertical-align: top;
            border: none;
        }

        .field-table tr:nth-child(odd) td {
            background: #f1f1f1;
        }

        .field-table tr:nth-child(even) td {
            background: #ffffff;
        }

        .label-col {
            width: 38%;
        }

        .sep-col {
            width: 3%;
            text-align: center;
        }

        .list-block:nth-child(odd),
        .thesis-section {
            background: #f1f1f1;
        }

        .list-block:nth-child(even) {
            background: #ffffff;
        }

        .list-cat {
            font-weight: 700;
            margin: 0 0 4px;
        }

        ul {
            margin: 0 0 0 18px;
            padding: 0;
        }

        li {
            margin: 0 0 3px;
        }

        .list-item-en {
            display: block;
            font-style: italic;
            font-weight: 400;
        }

        .outcomes-table td,
        .kkni-table td,
        .footer-table td {
            border: 1px solid #000000;
            padding: 6px 8px;
            vertical-align: top;
        }

        .footer-table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .footer-table td {
            border: none;
            padding: 0;
        }

        .footer-table td:first-child {
            padding-right: 20px;
        }

        .footer-table td:last-child {
            padding-left: 20px;
        }

        .cat-header {
            background: #e8e8e8;
            font-weight: 700;
        }

        .auth-section {
            border-top: 1px solid #000000;
        }

        .sign-block {
            margin-top: 56px;
        }

        .sign-block.has-signature {
            margin-top: 18px;
        }

        .signature-image {
            display: block;
            max-height: 80px;
            max-width: 220px;
            object-fit: contain;
            margin-bottom: 8px;
        }

        ._nm {
            text-decoration: underline;
            font-weight: 700;
        }

        .bold {
            font-weight: 700;
        }
    </style>
</head>

<body>
    @php
    use App\Models\StudentAchievement;

    $buildAchievementText = function ($achievement) {
    return collect([
    $achievement->activity_type_label ?? $achievement->activity_type,
    filled($achievement->level) && $achievement->level !== '-' ? '(' . $achievement->level . ')' : null,
    filled($achievement->participation_role) && $achievement->participation_role !== '-' ? $achievement->participation_role : null,
    ])->filter()->implode(' - ');
    };

    // Kategori transkrip SKPI
    $transkripCategories = [
    'wajib' => 'Wajib Universitas',
    'organisasi' => 'Kegiatan Bidang Organisasi dan Kepemimpinan',
    'penalaran' => 'Kegiatan Bidang Penalaran dan Keilmuan',
    'minat_bakat' => 'Kegiatan Bidang Minat dan Bakat',
    'kepedulian_sosial' => 'Kegiatan Bidang Kepedulian Sosial',
    'lainnya' => 'Kegiatan Lainnya',
    'volunteer' => 'Volunteer Mahasiswa',
    ];

    $groupedByCategory = [];
    $skpByCategory = [];
    foreach ($transkripCategories as $catKey => $catLabel) {
    $items = $selectedAchievements->where('category', $catKey)->values();
    $groupedByCategory[$catKey] = $items;
    $skpByCategory[$catKey] = $items->sum('skp_points');
    }
    $totalSkp = array_sum($skpByCategory);

    if ($totalSkp > 251) { $predikat = 'Sangat Baik'; }
    elseif ($totalSkp >= 151) { $predikat = 'Baik'; }
    elseif ($totalSkp >= 80) { $predikat = 'Cukup'; }
    else { $predikat = '-'; }

    $lists = [
    'prestasi' => $groupedByCategory['penalaran']->map($buildAchievementText)->values(),
    'organisasi' => $groupedByCategory['organisasi']->map($buildAchievementText)->values(),
    'magang' => $groupedByCategory['lainnya']->map($buildAchievementText)->values(),
    'pelatihan' => collect(),
    'sertif' => $groupedByCategory['wajib']->map($buildAchievementText)->values(),
    ];

    $ttl = collect([
    $selectedRegistration?->tempat_lahir,
    $selectedRegistration?->tanggal_lahir?->translatedFormat('d F Y'),
    ])->filter()->implode(', ');

    $kotaTgl = $documentMeta['authorization_place_date'] ?? ('Sukoharjo, ' . now()->translatedFormat('d F Y'));
    $skripsi = optional($automaticEntries->first())->event ?? ($selectedStudent?->finalProject?->title ?? '-');
    @endphp
    <div class="skpi-doc">
        <div class="doc-header">
            <div class="logo-col">
                @if($logoDataUri)
                <img src="{{ $logoDataUri }}" alt="Logo USH">
                @endif
            </div>
            <div class="identity-col">
                <div class="campus-title">Universitas Sugeng Hartono</div>
                <div class="campus-subtitle">Sugeng Hartono University</div>
            </div>
        </div>

        <div class="doc-rule"></div>

        <div class="doc-title-block">
            <div class="doc-main-title">SURAT KETERANGAN PENDAMPING IJAZAH</div>
            <div class="doc-sub-title">Diploma Supplement Certificate</div>
            <div class="doc-number">Nomor: {{ $documentMeta['nomor_skpi'] ?? '' }}</div>
        </div>

        <div class="doc-rule"></div>

        <div class="doc-intro">
            <p>Surat Keterangan Pendamping Ijazah (SKPI) ini mengacu pada Kerangka Kualifikasi Nasional Indonesia (KKNI) tentang pengakuan studi, ijazah dan gelar pendidikan tinggi. Tujuan penerbitan SKPI ini adalah menjadi dokumen yang menyatakan kemampuan kerja, penguasaan pengetahuan, dan sikap/moral pemegangnya.</p>
            <p class="en">This Diploma Supplement refers to the Indonesian Qualification Framework on the Recognition of Studies, Diplomas and Degrees in Higher Education. The purpose of the supplement is to provide a description of the nature, level, context and status of the studies that were pursued and successfully completed by the individual named on the original qualification to which this supplement is appended.</p>
        </div>

        <div class="doc-rule"></div>

        <div class="doc-section">
            <div class="section-title-row">1. INFORMASI TENTANG IDENTITAS DIRI PEMEGANG SKPI<br><span class="en">Information Identifying Diploma Supplement Holder</span></div>
            <table class="field-table">
                <tr>
                    <td class="label-col">NAMA LENGKAP<br><span class="en">Full Name</span></td>
                    <td class="sep-col">:</td>
                    <td>{{ $selectedRegistration?->nama_lengkap ?? '-' }}</td>
                </tr>
                <tr>
                    <td>TEMPAT / TANGGAL LAHIR<br><span class="en">Date / Place of Birth</span></td>
                    <td class="sep-col">:</td>
                    <td>{{ $ttl ?: '-' }}</td>
                </tr>
                <tr>
                    <td>NOMOR INDUK MAHASISWA<br><span class="en">Student Identification Number</span></td>
                    <td class="sep-col">:</td>
                    <td>{{ $selectedRegistration?->nim ?? '-' }}</td>
                </tr>
                <tr>
                    <td>TAHUN MASUK<br><span class="en">Year of Entry</span></td>
                    <td class="sep-col">:</td>
                    <td>{{ $selectedRegistration?->angkatan ?? '-' }}</td>
                </tr>
                <tr>
                    <td>NOMOR IJAZAH<br><span class="en">Number of Degree Certificate</span></td>
                    <td class="sep-col">:</td>
                    <td>{{ $selectedRegistration?->nomor_ijazah ?? '-' }}</td>
                </tr>
                <tr>
                    <td>GELAR<br><span class="en">Degree</span></td>
                    <td class="sep-col">:</td>
                    <td>{{ $selectedRegistration?->gelar ?? '-' }}</td>
                </tr>
            </table>
        </div>

        <div class="doc-section">
            <div class="section-title-row">2. INFORMASI TENTANG IDENTITAS PENYELENGGARA PROGRAM<br><span class="en">Information Identifying the Awarding Institution</span></div>
            <table class="field-table">
                <tr>
                    <td class="label-col">SK PENDIRIAN PERGURUAN TINGGI<br><span class="en">Degree of Establishment of College</span></td>
                    <td class="sep-col">:</td>
                    <td>{{ $academicProfile?->sk_pendirian_perguruan_tinggi ?? '-' }}</td>
                </tr>
                <tr>
                    <td>NAMA PERGURUAN TINGGI<br><span class="en">Name of College</span></td>
                    <td class="sep-col">:</td>
                    <td>{{ $academicProfile?->nama_perguruan_tinggi ?? 'UNIVERSITAS SUGENG HARTONO' }}</td>
                </tr>
                <tr>
                    <td>AKREDITASI PERGURUAN TINGGI<br><span class="en">College Accreditation</span></td>
                    <td class="sep-col">:</td>
                    <td>{{ $academicProfile?->akreditasi_perguruan_tinggi ?? '-' }}</td>
                </tr>
                <tr>
                    <td>PROGRAM STUDI<br><span class="en">Study Program</span></td>
                    <td class="sep-col">:</td>
                    <td>{{ $selectedStudent?->program_studi ?? '-' }}</td>
                </tr>
                <tr>
                    <td>AKREDITASI PROGRAM STUDI<br><span class="en">Accreditation of Study Program</span></td>
                    <td class="sep-col">:</td>
                    <td>{{ $academicProfile?->akreditasi_program_studi ?? '-' }}</td>
                </tr>
                <tr>
                    <td>JENIS DAN JENJANG PENDIDIKAN<br><span class="en">Type &amp; Level of Education</span></td>
                    <td class="sep-col">:</td>
                    <td>{{ $academicProfile?->jenis_dan_jenjang_pendidikan ?? '-' }}</td>
                </tr>
                <tr>
                    <td>JENJANG KUALIFIKASI SESUAI KKNI<br><span class="en">Qualification Level</span></td>
                    <td class="sep-col">:</td>
                    <td>{{ $academicProfile?->jenjang_kualifikasi_kkni ?? '-' }}</td>
                </tr>
                <tr>
                    <td>PERSYARATAN PENERIMAAN<br><span class="en">Entry Requirements</span></td>
                    <td class="sep-col">:</td>
                    <td>{{ $academicProfile?->persyaratan_penerimaan ?? '-' }}</td>
                </tr>
                <tr>
                    <td>BAHASA PENGANTAR KULIAH<br><span class="en">Language of Instruction</span></td>
                    <td class="sep-col">:</td>
                    <td>{{ $academicProfile?->bahasa_pengantar_kuliah ?? 'Inggris / Indonesia' }}</td>
                </tr>
                <tr>
                    <td>NO. AKREDITASI PERGURUAN TINGGI<br><span class="en">College Accreditation Number</span></td>
                    <td class="sep-col">:</td>
                    <td>{{ $academicProfile?->nomor_akreditasi_perguruan_tinggi ?? '-' }}</td>
                </tr>
                <tr>
                    <td>SISTEM PENILAIAN<br><span class="en">Grading System</span></td>
                    <td class="sep-col">:</td>
                    <td>{{ $academicProfile?->sistem_penilaian ?? 'Skala/Scale : 0-4 : A=4, A-=3.75, B+=3.5, B=3, C=2, D=1, E=0' }}</td>
                </tr>
                <tr>
                    <td>LAMA STUDI<br><span class="en">Length of Study</span></td>
                    <td class="sep-col">:</td>
                    <td>{{ $academicProfile?->lama_studi ?? '-' }}</td>
                </tr>
                <tr>
                    <td>NOMOR AKREDITASI PROGRAM STUDI<br><span class="en">Accreditation Number of Study Program</span></td>
                    <td class="sep-col">:</td>
                    <td>{{ $academicProfile?->nomor_akreditasi_program_studi ?? '-' }}</td>
                </tr>
                <tr>
                    <td>STATUS PROFESI (BILA ADA)<br><span class="en">Professional Status</span></td>
                    <td class="sep-col">:</td>
                    <td>{{ $academicProfile?->status_profesi ?? '-' }}</td>
                </tr>
            </table>
        </div>

        <div class="doc-section">
            <div class="section-title-row">3. INFORMASI TENTANG AKTIVITAS, PRESTASI, DAN PENGHARGAAN<br><span class="en">Information On Activities, Achievements, And Awards</span></div>
            @foreach([
            ['PRESTASI DAN PENGHARGAAN', 'Achievement and Rewards', $lists['prestasi']],
            ['KEIKUTSERTAAN DALAM ORGANISASI', 'Organization Experience', $lists['organisasi']],
            ['KERJA PRAKTEK / MAGANG', 'Internship', $lists['magang']],
            ['PELATIHAN / SEMINAR / WORKSHOP', 'Training / Seminar / Workshop', $lists['pelatihan']],
            ['SERTIFIKAT KEAHLIAN', 'Skill Certificate', $lists['sertif']],
            ] as [$labelId, $labelEn, $items])
            <div class="list-block">
                <div class="list-cat">{{ $labelId }}<br><span class="en">{{ $labelEn }}</span></div>
                <ul>
                    @forelse($items as $item)
                    <li>{{ $item }}<br><span class="list-item-en">{{ $item }}</span></li>
                    @empty
                    <li>-<br><span class="list-item-en">-</span></li>
                    @endforelse
                </ul>
            </div>
            @endforeach

            <div class="thesis-section">
                <div class="list-cat">SKRIPSI / <span class="en">Undergraduate Thesis</span></div>
                <div>{{ $skripsi }}</div>
                <div class="en">{{ $skripsi }}</div>
            </div>
        </div>

        <div class="doc-section">
            <div class="section-title-row">4. INFORMASI TENTANG KUALIFIKASI DAN HASIL YANG DICAPAI<br><span class="en">Information of the Qualification and Outcomes Obtained</span></div>
            <table class="outcomes-table">
                <tr>
                    <td class="cat-header" style="width:50%">CAPAIAN PEMBELAJARAN</td>
                    <td class="cat-header" style="width:50%"><span class="en">LEARNING OUTCOMES</span></td>
                </tr>
                <tr>
                    <td class="cat-header">Sikap / tata nilai</td>
                    <td class="cat-header"><span class="en">Attitudes</span></td>
                </tr>
                <tr>
                    <td>1. Bertaqwa kepada Tuhan Yang Maha Esa dan mampu menunjukkan sikap religius dan mengamalkan nilai-nilai Al Islam dan Kemuhammadiyahan</td>
                    <td><span class="en">1. Be conscious of Almighty God and able to show religious attitude and practice the values of Al Islam and Muhammadiyah</span></td>
                </tr>
                <tr>
                    <td>2. Menjunjung tinggi nilai kemanusiaan dalam menjalankan tugas berdasarkan agama, moral dan etika</td>
                    <td><span class="en">2. Uphold the value of humanity in carrying out duties based on religion, morals and ethics</span></td>
                </tr>
                <tr>
                    <td class="cat-header">Kemampuan Kerja</td>
                    <td class="cat-header"><span class="en">Work Ability</span></td>
                </tr>
                <tr>
                    <td>1. Mampu melaksanakan proses audit sesuai Standar Audit yang berlaku di Indonesia</td>
                    <td><span class="en">1. Be able to carry out the audit process in accordance with the applicable Audit Standards in Indonesia</span></td>
                </tr>
                <tr>
                    <td>2. Mampu menganalisis informasi akuntansi dan keuangan untuk pengambilan keputusan</td>
                    <td><span class="en">2. Be able to analyze accounting and financial information for decision making</span></td>
                </tr>
                <tr>
                    <td class="cat-header">Penguasaan Pengetahuan</td>
                    <td class="cat-header"><span class="en">Mastery of Knowledge</span></td>
                </tr>
                <tr>
                    <td>1. Menguasai konsep teoritis akuntansi secara mendalam dan penerapannya pada bidang akuntansi secara spesifik untuk menyelesaikan masalah secara sistematis.</td>
                    <td><span class="en">1. Master the theoretical concepts of accounting in depth and its application in the field of accounting specifically to solve problems systematically.</span></td>
                </tr>
                <tr>
                    <td>2. Menguasai konsep teoritis teknologi informasi untuk mendukung pekerjaan.</td>
                    <td><span class="en">2. Master the theoretical concepts of information technology to support the work.</span></td>
                </tr>
                <tr>
                    <td>3. Menguasai konsep teoritis dan keterampilan dalam melakukan pengauditan terhadap laporan keuangan pada berbagai entitas sesuai dengan standar pengauditan</td>
                    <td><span class="en">3. Master the theoretical concepts and skills in auditing financial statements at various entities in accordance with auditing standards.</span></td>
                </tr>
                <tr>
                    <td>4. Menguasai konsep undang-undang perpajakan yang berlaku di Indonesia</td>
                    <td><span class="en">4. Master the concept of applicable tax laws in Indonesia.</span></td>
                </tr>
                <tr>
                    <td>5. Menguasai konsep etika bisnis dan berwirausaha yang mendalam dan penerapannya di lapangan kerja</td>
                    <td><span class="en">5. Master the concept of business ethics and in-depth entrepreneurship and its application in employment.</span></td>
                </tr>
            </table>
        </div>

        <div class="doc-section">
            <div class="section-title-row">5. KERANGKA KUALIFIKASI NASIONAL INDONESIA<br><span class="en">Indonesian Qualifications Framework</span></div>
            <table class="kkni-table">
                <tr>
                    <td style="width:50%">
                        Kerangka Kualifikasi Nasional Indonesia (KKNI) adalah kerangka penjenjangan kualifikasi kompetensi yang dapat menyandingkan, menyetarakan, dan mengintegrasikan antara bidang pendidikan dan bidang pelatihan kerja serta pengalaman kerja dalam rangka pemberian pengakuan kompetensi kerja sesuai dengan struktur pekerjaan diberbagai sektor.
                    </td>
                    <td style="width:50%">
                        <span class="en">Indonesian National Qualifications Framework (IQF) is a national policy for regulated level of competence and qualifications framework which can compare, equalize, and integrate the fields of education and training system and work experience in order to award recognition of work competence in accordance to the structure of employment in various sectors.</span>
                    </td>
                </tr>
            </table>
        </div>

        <div class="doc-section">
            <div class="section-title-row">6. PENGESAHAN SKPI<br><span class="en">SKPI Authorization</span></div>
            <div class="auth-section">
                <div class="en">{{ $kotaTgl }}</div>
                <div style="margin-top: 8px;">{{ $documentMeta['vice_rector_title'] ?? 'Wakil Rektor I Universitas Sugeng Hartono' }}</div>
                <div class="sign-block {{ $signatureDataUri ? 'has-signature' : '' }}">
                    @if($signatureDataUri)
                    <img src="{{ $signatureDataUri }}" alt="Tanda tangan" class="signature-image" style="margin-left:20px;">
                    @endif
                    <div class="_nm">{{ $documentMeta['vice_rector_name'] ?: '____________________________________' }}</div>
                    <div style="font-size:10px;">Tanda Tangan &amp; Stempel / Signature &amp; Stamp</div>
                </div>
            </div>
        </div>

        <div class="doc-section">
            <table class="footer-table">
                <tr>
                    <td style="width:60%">
                        <div class="bold">Catatan Resmi:</div>
                        <ul>
                            <li>SKPI dikeluarkan oleh institusi pendidikan tinggi yang berwenang mengeluarkan ijazah sesuai dengan peraturan perundang-undangan yang berlaku.</li>
                            <li>SKPI hanya diterbitkan setelah mahasiswa dinyatakan lulus dari suatu program studi secara resmi oleh Perguruan Tinggi.</li>
                            <li>SKPI diterbitkan dalam Bahasa Indonesia dan Bahasa Inggris.</li>
                        </ul>
                    </td>
                    <td style="width:40%">
                        <div class="bold">Alamat :</div>
                        <div class="bold">UNIVERSITAS SUGENG HARTONO</div>
                        <div>Jl. Ir. Soekarno No.69, Dusun I, Madegondo, Kec. Grogol, Kabupaten Sukoharjo, Jawa Tengah 57552</div>
                        <div>Contact: 0811-2674-670</div>
                        <div>https://sugenghartono.ac.id/</div>
                        <div>Email: ush@sugenghartono.ac.id</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ═══════════ LAMPIRAN 3: TRANSKRIP SKPI ═══════════ --}}
    <div style="page-break-before: always;"></div>
    <div class="skpi-doc">
        <div class="doc-section" style="margin-top:0;">
            <div style="text-align:center; margin-bottom:14px;">
                <div style="font-size:10px;">LAMPIRAN 3</div>
                <div style="font-size:10px; margin-bottom:6px;">KEMENTERIAN RISET, TEKNOLOGI, DAN PENDIDIKAN TINGGI</div>
                <div style="font-size:14px; font-weight:700;">TRANSKRIP SURAT KETERANGAN PENDAMPING IJAZAH</div>
            </div>

            <table class="field-table" style="margin-bottom:14px;">
                <tr>
                    <td class="label-col">NAMA</td>
                    <td class="sep-col">:</td>
                    <td>{{ $selectedRegistration?->nama_lengkap ?? '-' }}</td>
                </tr>
                <tr>
                    <td>NIM</td>
                    <td class="sep-col">:</td>
                    <td>{{ $selectedRegistration?->nim ?? '-' }}</td>
                </tr>
                <tr>
                    <td>PROGRAM STUDI</td>
                    <td class="sep-col">:</td>
                    <td>{{ $selectedStudent?->program_studi ?? '-' }}</td>
                </tr>
            </table>

            <table style="border-collapse:collapse; width:100%;">
                <thead>
                    <tr>
                        <th style="border:1px solid #000; padding:6px 8px; width:40px; text-align:center; font-weight:normal;">No</th>
                        <th style="border:1px solid #000; padding:6px 8px; font-weight:normal;">Kriteria Kegiatan</th>
                        <th style="border:1px solid #000; padding:6px 8px; width:80px; text-align:center; font-weight:normal;">Nilai SKP</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="3" style="border:1px solid #000; padding:6px 8px;">A. Wajib Universitas</td>
                    </tr>
                    @forelse($groupedByCategory['wajib'] as $i => $ach)
                    <tr>
                        <td style="border:1px solid #000; padding:4px 8px; text-align:center;">{{ $i + 1 }}</td>
                        <td style="border:1px solid #000; padding:4px 8px;">{{ $ach->activity_type_label ?? $ach->activity_type }}</td>
                        <td style="border:1px solid #000; padding:4px 8px; text-align:center;">{{ $ach->skp_points }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td style="border:1px solid #000; padding:4px 8px; text-align:center;"></td>
                        <td style="border:1px solid #000; padding:4px 8px; color:#999;">Belum ada data</td>
                        <td style="border:1px solid #000; padding:4px 8px; text-align:center;">-</td>
                    </tr>
                    @endforelse

                    <tr>
                        <td colspan="3" style="border:1px solid #000; padding:6px 8px;">B. Bidang Kegiatan</td>
                    </tr>
                    @php $bidangCats = ['organisasi','penalaran','minat_bakat','kepedulian_sosial','lainnya']; @endphp
                    @foreach($bidangCats as $bIdx => $bCat)
                    <tr>
                        <td style="border:1px solid #000; padding:4px 8px; text-align:center;">{{ $bIdx + 1 }}.</td>
                        <td style="border:1px solid #000; padding:4px 8px;">{{ $transkripCategories[$bCat] }}</td>
                        <td style="border:1px solid #000; padding:4px 8px; text-align:center;">{{ $skpByCategory[$bCat] }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" style="border:1px solid #000; padding:6px 8px; text-align:left;">Jumlah Perolehan SKP</td>
                        <td style="border:1px solid #000; padding:6px 8px; text-align:center;">{{ $totalSkp }}</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="border:1px solid #000; padding:6px 8px; text-align:left;">Predikat</td>
                        <td style="border:1px solid #000; padding:6px 8px; text-align:center;">{{ $predikat }}</td>
                    </tr>
                </tfoot>
            </table>

            <div style="margin-top:12px; font-size:10px; color:#555;">
                <strong>Catatan Predikat SKPI S1:</strong><br>
                Sangat Baik = &gt;251 SKP &nbsp;|&nbsp; Baik = 151&ndash;250 SKP &nbsp;|&nbsp; Cukup = 80&ndash;150 SKP
            </div>

            <table style="border:none;">
                <tr>
                    <td style="border:none;">
                        <div>Sukoharjo, {{ $kotaTgl }}</div>
                        <div>
                            @if($signatureDataUri)
                            <img src="{{ $signatureDataUri }}" alt="TTD" style="max-height:60px; max-width:200px; display:block;  margin-left:30px;">
                            @endif
                        </div>
                        <div>
                            {{ $documentMeta['vice_rector_name'] ?? 'Wakil Rektor' }}
                        </div>

                        <p style="margin-top:6px">{{ $documentMeta['vice_rector_title'] ?? 'Wakil Rektor Akademik' }}</p>

                    </td>
                </tr>
            </table>

        </div>
    </div>

    {{-- ═══════════ LAMPIRAN 4: RINCIAN TRANSKRIP SKPI ═══════════ --}}
    <div style="page-break-before: always;"></div>
    <div class="skpi-doc">
        <div class="doc-section" style="margin-top:0;">
            <div style="text-align:center; margin-bottom:14px;">
                <div style="font-size:10px;">LAMPIRAN 4</div>
                <div style="font-size:10px;">KEMENTERIAN RISET, TEKNOLOGI, DAN PENDIDIKAN TINGGI</div>
                <div style="font-size:12px; font-weight:700;">UNIVERSITAS SUGENG HARTONO</div>
                <div style="font-size:10px;">PROGRAM STUDI {{ strtoupper($selectedStudent?->program_studi ?? '-') }}</div>
                <div style="font-size:14px; font-weight:700; margin-top:6px;">RINCIAN TRANSKRIP SURAT KETERANGAN PENDAMPING IJAZAH</div>
            </div>

            <table class="field-table" style="margin-bottom:14px;">
                <tr>
                    <td class="label-col">NAMA</td>
                    <td class="sep-col">:</td>
                    <td>{{ $selectedRegistration?->nama_lengkap ?? '-' }}</td>
                </tr>
                <tr>
                    <td>NIM</td>
                    <td class="sep-col">:</td>
                    <td>{{ $selectedRegistration?->nim ?? '-' }}</td>
                </tr>
                <tr>
                    <td>PROGRAM STUDI</td>
                    <td class="sep-col">:</td>
                    <td>{{ $selectedStudent?->program_studi ?? '-' }}</td>
                </tr>
            </table>

            {{-- A. Wajib Universitas --}}
            <div style="margin:10px 0 6px;">A. Wajib Universitas</div>
            @include('admin.skpi.generate._rincian_pdf_table', ['items' => $groupedByCategory['wajib']])

            {{-- B. Bidang Kegiatan --}}
            <div style="margin:14px 0 6px;">B. Bidang Kegiatan</div>

            @php $subNum = 1; @endphp
            @foreach(['organisasi','penalaran','minat_bakat','kepedulian_sosial','lainnya','volunteer'] as $bCat)
            <div style="margin:10px 0 4px; padding-left:10px;">{{ $subNum++ }}. {{ $transkripCategories[$bCat] }}</div>
            @include('admin.skpi.generate._rincian_pdf_table', ['items' => $groupedByCategory[$bCat]])
            @endforeach

            <table style="border:none;">
                <tr>
                    <td style="border:none;">
                        <div> {{ $kotaTgl }}</div>
                        <div>
                            @if($signatureDataUri)
                            <img src="{{ $signatureDataUri }}" alt="TTD" style="max-height:60px; max-width:200px; display:block; margin-left:20px;">
                            @endif
                        </div>
                        <div>
                            {{ $documentMeta['vice_rector_name'] ?? 'Wakil Rektor' }}
                        </div>

                        <p style="margin-top:6px">{{ $documentMeta['vice_rector_title'] ?? 'Wakil Rektor Akademik' }}</p>

                    </td>
                </tr>
            </table>

        </div>
    </div>
</body>

</html>