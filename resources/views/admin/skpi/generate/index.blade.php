@extends('admin.layouts.super-app')

@php
    $pageTitle = 'Generate SKPI';
    $formatDate = fn ($date) => $date ? $date->translatedFormat('d F Y') : '';

    $buildAchievementText = function ($achievement) {
        $segments = collect([
            $achievement->achievement,
            $achievement->event,
            filled($achievement->level) ? '(' . $achievement->level . ')' : null,
        ])->filter()->values();

        return $segments->implode(' - ');
    };

    $achievementGroups = [
        \App\Models\StudentAchievement::CATEGORY_PRESTASI => $selectedAchievements->where('category', \App\Models\StudentAchievement::CATEGORY_PRESTASI)->values(),
        \App\Models\StudentAchievement::CATEGORY_ORGANISASI => $selectedAchievements->where('category', \App\Models\StudentAchievement::CATEGORY_ORGANISASI)->values(),
        \App\Models\StudentAchievement::CATEGORY_MAGANG => $selectedAchievements->where('category', \App\Models\StudentAchievement::CATEGORY_MAGANG)->values(),
        \App\Models\StudentAchievement::CATEGORY_SKILL_CERTIFICATE => $selectedAchievements->where('category', \App\Models\StudentAchievement::CATEGORY_SKILL_CERTIFICATE)->values(),
    ];

    $thesisTitle = optional($automaticEntries->first())->event ?? ($selectedStudent?->finalProject?->title ?? '');
    $ttl = collect([$selectedRegistration?->tempat_lahir, $formatDate($selectedRegistration?->tanggal_lahir)])->filter()->implode(', ');

    $nomorSkpi = '';
    $namaLengkap = $selectedRegistration?->nama_lengkap ?? '';
    $nim = $selectedRegistration?->nim ?? '';
    $tahunMasuk = $selectedRegistration?->angkatan ?? '';
    $nomorIjazah = $selectedRegistration?->nomor_ijazah ?? '';
    $gelar = $selectedRegistration?->gelar ?? '';

    $skPendirian = $academicProfile?->sk_pendirian_perguruan_tinggi ?? '';
    $namaPerguruanTinggi = $academicProfile?->nama_perguruan_tinggi ?? 'UNIVERSITAS SUGENG HARTONO';
    $akreditasiPt = $academicProfile?->akreditasi_perguruan_tinggi ?? '';
    $programStudi = $selectedStudent?->program_studi ?? '';
    $akreditasiProdi = $academicProfile?->akreditasi_program_studi ?? '';
    $jenisJenjang = $academicProfile?->jenis_dan_jenjang_pendidikan ?? '';
    $kkniLevel = $academicProfile?->jenjang_kualifikasi_kkni ?? '';
    $persyaratanPenerimaan = $academicProfile?->persyaratan_penerimaan ?? '';
    $bahasaPengantar = $academicProfile?->bahasa_pengantar_kuliah ?? 'Inggris / Indonesia';
    $nomorAkreditasiPt = $academicProfile?->nomor_akreditasi_perguruan_tinggi ?? '';
    $sistemPenilaian = $academicProfile?->sistem_penilaian ?? 'Skala/Scale : 0-4 : A=4, A-=3.75, B+=3.5, B=3, C=2, D=1, E=0';
    $lamaStudi = $academicProfile?->lama_studi ?? '';
    $nomorAkreditasiProdi = $academicProfile?->nomor_akreditasi_program_studi ?? '';
    $statusProfesi = $academicProfile?->status_profesi ?? '-';
    $kotaTanggal = 'Sukoharjo, ' . now()->translatedFormat('d F Y');
@endphp

@section('content')
    <div class="skpi-generator-page">
        <header class="generator-header">
            <div class="logo-circle">USH</div>
            <div>
                <h1>Generator SKPI</h1>
                <p>Universitas Sugeng Hartono - Surat Keterangan Pendamping Ijazah</p>
            </div>
        </header>

        <div class="generator-container">
            <aside class="generator-sidebar">
                <div class="sidebar-inner">
                    <form method="GET" action="{{ route('admin.skpi.generate-skpi.index') }}" class="source-form">
                        <div class="section-label">Sumber Data</div>

                        <label for="registration_id">Mahasiswa Approved</label>
                        <select name="registration_id" id="registration_id" onchange="this.form.submit()">
                            <option value="">Pilih mahasiswa</option>
                            @foreach($approvedRegistrations as $registration)
                                <option value="{{ $registration->id }}" {{ $selectedRegistrationId === $registration->id ? 'selected' : '' }}>
                                    {{ $registration->nim }} - {{ $registration->nama_lengkap }} - {{ $registration->student->program_studi ?? '-' }}
                                </option>
                            @endforeach
                        </select>

                        <label for="achievement_ids">Aktivitas Approved</label>
                        <select name="achievement_ids[]" id="achievement_ids" multiple size="6">
                            @forelse($approvedAchievements as $achievement)
                                <option value="{{ $achievement->id }}" {{ in_array($achievement->id, $selectedAchievementIds, true) ? 'selected' : '' }}>
                                    {{ $achievement->category_label }} - {{ $achievement->achievement }} - {{ $achievement->event }}
                                </option>
                            @empty
                                <option value="" disabled>Belum ada aktivitas approved</option>
                            @endforelse
                        </select>

                        <p class="source-note">
                            Dropdown ini hanya menampilkan pendaftaran SKPI approved dan aktivitas mahasiswa yang sudah disetujui. Skripsi tetap otomatis diambil dari modul tugas akhir.
                        </p>

                        <button type="submit" class="btn-apply">Terapkan Data Approved</button>
                    </form>

                    <div class="section-label">1. Identitas Mahasiswa</div>

                    <label for="nomor">Nomor SKPI</label>
                    <input type="text" id="nomor" value="{{ $nomorSkpi }}" placeholder="Contoh: 5335/UN15.9/PP/2024">

                    <label for="nama">Nama Lengkap <span class="sub">/ Full Name</span></label>
                    <input type="text" id="nama" value="{{ $namaLengkap }}">

                    <label for="ttl">Tempat / Tanggal Lahir <span class="sub">/ Date &amp; Place of Birth</span></label>
                    <input type="text" id="ttl" value="{{ $ttl }}" placeholder="Sukoharjo, 1 Januari 2000">

                    <label for="nim">NIM <span class="sub">/ Student ID Number</span></label>
                    <input type="text" id="nim" value="{{ $nim }}">

                    <label for="tahun_masuk">Tahun Masuk <span class="sub">/ Year of Entry</span></label>
                    <input type="text" id="tahun_masuk" value="{{ $tahunMasuk }}" placeholder="2020">

                    <label for="no_ijazah">Nomor Ijazah <span class="sub">/ Degree Certificate Number</span></label>
                    <input type="text" id="no_ijazah" value="{{ $nomorIjazah }}">

                    <label for="gelar">Gelar <span class="sub">/ Degree</span></label>
                    <input type="text" id="gelar" value="{{ $gelar }}" placeholder="S.E. / S.T.">

                    <div class="section-label">2. Identitas Program Studi</div>

                    <label for="sk_pt">SK Pendirian Perguruan Tinggi</label>
                    <input type="text" id="sk_pt" value="{{ $skPendirian }}">

                    <label for="nama_pt">Nama Perguruan Tinggi <span class="sub">/ Name of College</span></label>
                    <input type="text" id="nama_pt" value="{{ $namaPerguruanTinggi }}">

                    <label for="akr_pt">Akreditasi PT <span class="sub">/ College Accreditation</span></label>
                    <input type="text" id="akr_pt" value="{{ $akreditasiPt }}">

                    <label for="prodi">Program Studi <span class="sub">/ Study Program</span></label>
                    <input type="text" id="prodi" value="{{ $programStudi }}" placeholder="Akuntansi">

                    <label for="akr_prodi">Akreditasi Prodi <span class="sub">/ Study Program Accreditation</span></label>
                    <input type="text" id="akr_prodi" value="{{ $akreditasiProdi }}" placeholder="Unggul / A">

                    <label for="jenis_jenjang">Jenis dan Jenjang Pendidikan</label>
                    <input type="text" id="jenis_jenjang" value="{{ $jenisJenjang }}">

                    <label for="kkni_level">Jenjang Kualifikasi KKNI</label>
                    <input type="text" id="kkni_level" value="{{ $kkniLevel }}" placeholder="Level 6">

                    <label for="entry_req">Persyaratan Penerimaan <span class="sub">/ Entry Requirements</span></label>
                    <input type="text" id="entry_req" value="{{ $persyaratanPenerimaan }}" placeholder="SMA / Sederajat">

                    <label for="bahasa_pengantar">Bahasa Pengantar Kuliah</label>
                    <input type="text" id="bahasa_pengantar" value="{{ $bahasaPengantar }}">

                    <label for="no_akr_pt">No. Akreditasi PT</label>
                    <input type="text" id="no_akr_pt" value="{{ $nomorAkreditasiPt }}">

                    <label for="no_akr_prodi">No. Akreditasi Prodi</label>
                    <input type="text" id="no_akr_prodi" value="{{ $nomorAkreditasiProdi }}">

                    <label for="sistem_penilaian">Sistem Penilaian</label>
                    <textarea id="sistem_penilaian" rows="3">{{ $sistemPenilaian }}</textarea>

                    <label for="prof_status">Status Profesi <span class="sub">/ Professional Status</span></label>
                    <input type="text" id="prof_status" value="{{ $statusProfesi }}" placeholder="(bila ada)">

                    <label for="lama_studi">Lama Studi <span class="sub">/ Length of Study</span></label>
                    <input type="text" id="lama_studi" value="{{ $lamaStudi }}" placeholder="3 tahun 6 bulan">

                    <div class="section-label">3. Aktivitas &amp; Prestasi</div>

                    <label>Prestasi &amp; Penghargaan <span class="sub">/ Achievements &amp; Rewards</span></label>
                    <div class="list-group" id="grp_prestasi">
                        @foreach($achievementGroups[\App\Models\StudentAchievement::CATEGORY_PRESTASI] as $achievement)
                            <div class="list-item">
                                <textarea rows="3">{{ $buildAchievementText($achievement) }}</textarea>
                                <button type="button" class="btn-remove" onclick="this.parentElement.remove()" title="Hapus">x</button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn-add" onclick="addItem('grp_prestasi', 'Prestasi (Indonesia) | Achievement (English)')">+ Tambah Prestasi</button>

                    <label>Keikutsertaan Organisasi <span class="sub">/ Organization Experience</span></label>
                    <div class="list-group" id="grp_org">
                        @foreach($achievementGroups[\App\Models\StudentAchievement::CATEGORY_ORGANISASI] as $achievement)
                            <div class="list-item">
                                <textarea rows="3">{{ $buildAchievementText($achievement) }}</textarea>
                                <button type="button" class="btn-remove" onclick="this.parentElement.remove()" title="Hapus">x</button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn-add" onclick="addItem('grp_org', 'Organisasi (Indonesia) | Organization (English)')">+ Tambah Organisasi</button>

                    <label>Kerja Praktek / Magang <span class="sub">/ Internship</span></label>
                    <div class="list-group" id="grp_magang">
                        @foreach($achievementGroups[\App\Models\StudentAchievement::CATEGORY_MAGANG] as $achievement)
                            <div class="list-item">
                                <textarea rows="3">{{ $buildAchievementText($achievement) }}</textarea>
                                <button type="button" class="btn-remove" onclick="this.parentElement.remove()" title="Hapus">x</button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn-add" onclick="addItem('grp_magang', 'Magang (Indonesia) | Internship (English)')">+ Tambah Magang</button>

                    <label>Pelatihan / Seminar / Workshop <span class="sub">/ Training / Seminar / Workshop</span></label>
                    <div class="list-group" id="grp_pelatihan"></div>
                    <button type="button" class="btn-add" onclick="addItem('grp_pelatihan', 'Pelatihan (Indonesia) | Training (English)')">+ Tambah Pelatihan</button>

                    <label>Sertifikat Keahlian <span class="sub">/ Skill Certificate</span></label>
                    <div class="list-group" id="grp_sertif">
                        @foreach($achievementGroups[\App\Models\StudentAchievement::CATEGORY_SKILL_CERTIFICATE] as $achievement)
                            <div class="list-item">
                                <textarea rows="3">{{ $buildAchievementText($achievement) }}</textarea>
                                <button type="button" class="btn-remove" onclick="this.parentElement.remove()" title="Hapus">x</button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn-add" onclick="addItem('grp_sertif', 'Sertifikat (Indonesia) | Certificate (English)')">+ Tambah Sertifikat</button>

                    <label for="skripsi_id">Judul Skripsi <span class="sub">/ Thesis Title (Indonesian)</span></label>
                    <textarea id="skripsi_id" rows="2">{{ $thesisTitle }}</textarea>

                    <label for="skripsi_en">Judul Skripsi <span class="sub">/ Thesis Title (English)</span></label>
                    <textarea id="skripsi_en" rows="2">{{ $thesisTitle }}</textarea>

                    <div class="section-label">6. Pengesahan</div>

                    <label for="kota_tgl">Kota &amp; Tanggal <span class="sub">/ City &amp; Date</span></label>
                    <input type="text" id="kota_tgl" value="{{ $kotaTanggal }}" placeholder="Sukoharjo, 30 Maret 2026">

                    <button type="button" class="btn-generate" onclick="generate()">Generate SKPI</button>
                    <button type="button" class="btn-print" id="btnPrint" onclick="window.print()">Cetak / Print</button>
                </div>
            </aside>

            <main class="preview-pane">
                <h2>Pratinjau Dokumen</h2>
                <div id="previewArea">
                    <div class="preview-placeholder">
                        <svg width="64" height="64" fill="none" stroke="#9e9e9e" stroke-width="1.5" viewBox="0 0 24 24">
                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p>Isi form di sebelah kiri,<br>lalu klik <strong>Generate SKPI</strong></p>
                    </div>
                </div>
            </main>
        </div>

        @include('admin.skpi.generate.template')
    </div>
@endsection

@push('css')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Source+Sans+3:wght@300;400;600&display=swap');

    body .container {
        max-width: none !important;
        width: 100% !important;
        padding: 0 !important;
    }

    .skpi-generator-page {
        font-family: 'Source Sans 3', sans-serif;
        background: #faf7f2;
        color: #4a4a4a;
        min-height: calc(100vh - 80px);
    }

    .skpi-generator-page * {
        box-sizing: border-box;
    }

    .generator-header {
        background: #1a2744;
        color: #ffffff;
        padding: 18px 40px;
        display: flex;
        align-items: center;
        gap: 16px;
        border-bottom: 3px solid #c9972b;
    }

    .generator-header .logo-circle {
        width: 48px;
        height: 48px;
        background: #c9972b;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Playfair Display', serif;
        font-size: 20px;
        font-weight: 700;
        color: #1a2744;
        flex-shrink: 0;
    }

    .generator-header h1 {
        font-family: 'Playfair Display', serif;
        font-size: 20px;
        font-weight: 600;
        line-height: 1.2;
        margin: 0;
    }

    .generator-header p {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.7);
        margin: 2px 0 0;
    }

    .generator-container {
        display: grid;
        grid-template-columns: 420px 1fr;
        min-height: calc(100vh - 153px);
    }

    .generator-sidebar {
        background: #ffffff;
        border-right: 1px solid #e4dfd5;
        overflow-y: auto;
        max-height: calc(100vh - 153px);
        position: sticky;
        top: 80px;
    }

    .sidebar-inner {
        padding: 28px 28px 40px;
    }

    .section-label {
        font-family: 'Playfair Display', serif;
        font-size: 13px;
        font-weight: 600;
        color: #1a2744;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin: 24px 0 12px;
        padding-bottom: 6px;
        border-bottom: 2px solid #c9972b;
    }

    .section-label:first-child {
        margin-top: 0;
    }

    .source-form {
        margin-bottom: 22px;
    }

    .source-note {
        font-size: 12px;
        line-height: 1.5;
        color: #7a7a7a;
        margin: 8px 0 0;
    }

    .skpi-generator-page label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #1a2744;
        margin-bottom: 4px;
        margin-top: 12px;
    }

    .skpi-generator-page label .sub {
        font-weight: 300;
        color: #9e9e9e;
        font-style: italic;
    }

    .skpi-generator-page input[type="text"],
    .skpi-generator-page textarea,
    .skpi-generator-page select {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #e6e2da;
        border-radius: 8px;
        font-family: 'Source Sans 3', sans-serif;
        font-size: 13px;
        color: #4a4a4a;
        background: #f3f1ec;
        transition: border 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
        outline: none;
        box-shadow: none;
    }

    .skpi-generator-page input:focus,
    .skpi-generator-page textarea:focus,
    .skpi-generator-page select:focus {
        border-color: #c9972b;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(201, 151, 43, 0.12);
    }

    .skpi-generator-page textarea {
        resize: vertical;
        min-height: 60px;
    }

    .list-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .list-item {
        display: flex;
        align-items: flex-start;
        gap: 6px;
    }

    .list-item textarea {
        flex: 1;
        min-height: 48px;
    }

    .btn-remove {
        background: none;
        border: none;
        cursor: pointer;
        color: #c0392b;
        font-size: 18px;
        padding: 6px 4px;
        line-height: 1;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .btn-add,
    .btn-apply {
        margin-top: 8px;
        font-size: 12px;
        padding: 8px 12px;
        border-radius: 8px;
        cursor: pointer;
        font-family: 'Source Sans 3', sans-serif;
        font-weight: 600;
        transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
    }

    .btn-add {
        background: none;
        border: 1.5px dashed #c9972b;
        color: #c9972b;
    }

    .btn-add:hover {
        background: #c9972b;
        color: #ffffff;
    }

    .btn-apply {
        width: 100%;
        border: none;
        background: #e8b84b;
        color: #1a2744;
    }

    .btn-generate {
        width: 100%;
        margin-top: 28px;
        padding: 14px;
        background: #1a2744;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        font-family: 'Playfair Display', serif;
        font-size: 15px;
        font-weight: 600;
        letter-spacing: 0.5px;
        cursor: pointer;
        transition: background 0.2s ease, transform 0.1s ease;
        position: relative;
        overflow: hidden;
    }

    .btn-generate::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 4px;
        height: 100%;
        background: #c9972b;
    }

    .btn-generate:hover {
        background: #263660;
        transform: translateY(-1px);
    }

    .btn-print {
        display: none;
        width: 100%;
        margin-top: 10px;
        padding: 10px;
        background: none;
        border: 2px solid #c9972b;
        color: #c9972b;
        border-radius: 8px;
        font-family: 'Source Sans 3', sans-serif;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s ease, color 0.2s ease;
    }

    .btn-print:hover {
        background: #c9972b;
        color: #ffffff;
    }

    .btn-print.visible {
        display: block;
    }

    .preview-pane {
        padding: 40px;
        overflow-y: auto;
        background: #dfdbd2;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .preview-pane h2 {
        font-family: 'Playfair Display', serif;
        font-size: 14px;
        color: #8f8b84;
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-bottom: 20px;
        align-self: flex-start;
    }

    .skpi-doc {
        background: #ffffff;
        width: 100%;
        max-width: 820px;
        box-shadow: 0 4px 40px rgba(0, 0, 0, 0.18);
        font-size: 11px;
        line-height: 1.5;
        color: #000000;
    }

    .skpi-doc table {
        width: 100%;
        border-collapse: collapse;
    }

    .skpi-doc td,
    .skpi-doc th {
        border: 1px solid #000000;
        padding: 5px 8px;
        vertical-align: top;
    }

    .doc-header {
        display: flex;
        border: 2px solid #000000;
        border-bottom: none;
    }

    .doc-header .logo-col {
        width: 90px;
        border-right: 1px solid #000000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 8px;
    }

    .doc-header .logo-col img {
        width: 70px;
        height: 70px;
        object-fit: contain;
    }

    .doc-header .logo-placeholder {
        width: 70px;
        height: 70px;
        background: #f0f0f0;
        border: 1px dashed #999999;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 9px;
        color: #999999;
        text-align: center;
    }

    .doc-header .title-col {
        flex: 1;
        text-align: center;
        padding: 12px 10px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .doc-header .title-col .main-title {
        font-size: 14px;
        font-weight: bold;
        letter-spacing: 1px;
        margin-bottom: 2px;
    }

    .doc-header .title-col .sub-title {
        font-size: 11px;
        font-style: italic;
    }

    .doc-nomor {
        border: 2px solid #000000;
        border-top: none;
        border-bottom: none;
        padding: 4px 8px;
        font-size: 11px;
    }

    .doc-intro {
        border: 2px solid #000000;
        border-top: none;
        border-bottom: none;
        padding: 6px 8px;
        font-size: 10.5px;
    }

    .doc-intro p {
        margin-bottom: 4px;
    }

    .doc-intro .en {
        font-style: italic;
    }

    .doc-section {
        border: 2px solid #000000;
        border-top: none;
    }

    .section-title-row {
        padding: 5px 8px;
        background: #efefef;
        font-weight: bold;
        font-size: 11px;
        border-bottom: 1px solid #000000;
    }

    .section-title-row .en {
        font-style: italic;
        font-weight: normal;
    }

    .field-table td {
        border: 1px solid #000000;
        padding: 4px 8px;
        font-size: 10.5px;
    }

    .field-table .label-col {
        width: 38%;
    }

    .field-table .sep-col {
        width: 3%;
        text-align: center;
    }

    .field-table .value-col {
        width: 59%;
    }

    .list-section {
        padding: 6px 8px;
    }

    .list-section .list-cat {
        font-weight: bold;
        font-size: 10.5px;
        margin: 8px 0 2px;
    }

    .list-section .list-cat:first-child {
        margin-top: 0;
    }

    .list-section .list-cat .en {
        font-style: italic;
        font-weight: normal;
    }

    .list-section ul {
        margin-left: 20px;
    }

    .list-section li {
        margin-bottom: 2px;
        font-size: 10.5px;
    }

    .list-section .li-id {
        font-weight: bold;
    }

    .list-section .li-en {
        font-style: italic;
    }

    .thesis-section {
        padding: 6px 8px;
    }

    .thesis-section p {
        font-size: 10.5px;
        margin: 2px 0;
    }

    .thesis-section .thesis-id {
        font-weight: bold;
    }

    .thesis-section .thesis-en {
        font-style: italic;
    }

    .outcomes-table td,
    .kkni-table td,
    .footer-table td {
        border: 1px solid #000000;
        padding: 6px 8px;
        font-size: 10px;
        vertical-align: top;
    }

    .outcomes-table .cat-header {
        background: #e8e8e8;
        font-weight: bold;
        font-size: 10.5px;
    }

    .outcomes-table .cat-header .en {
        font-style: italic;
    }

    .auth-section {
        border-top: 1px solid #000000;
        padding: 8px;
        font-size: 10.5px;
    }

    .auth-section .place-date {
        margin-bottom: 2px;
    }

    .auth-section .sign-block {
        margin-top: 60px;
        font-weight: bold;
        font-size: 11px;
    }

    .auth-section ._nm {
        text-decoration: underline;
    }

    .footer-table ul,
    .kkni-table ul {
        margin-left: 14px;
    }

    .footer-table li,
    .kkni-table li {
        margin-bottom: 2px;
    }

    .footer-table .address-col {
        font-size: 10px;
    }

    .footer-table .bold {
        font-weight: bold;
    }

    .preview-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 400px;
        color: #9e9e9e;
        text-align: center;
    }

    .preview-placeholder svg {
        margin-bottom: 16px;
        opacity: 0.35;
    }

    .preview-placeholder p {
        font-size: 14px;
    }

    @media (max-width: 992px) {
        .generator-container {
            grid-template-columns: 1fr;
        }

        .generator-sidebar {
            position: relative;
            top: 0;
            max-height: none;
            border-right: none;
            border-bottom: 1px solid #e4dfd5;
        }

        .preview-pane {
            padding: 24px 16px 96px;
        }
    }

    @media print {
        body {
            background: #ffffff;
            padding-bottom: 0 !important;
        }

        .header-section,
        .bottom-nav,
        .sidebar-nav,
        .generator-header,
        .generator-sidebar,
        .preview-pane h2 {
            display: none !important;
        }

        body .container {
            margin: 0 !important;
            padding: 0 !important;
        }

        .preview-pane {
            padding: 0;
            background: #ffffff;
        }

        .skpi-doc {
            box-shadow: none;
            max-width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    const logoSrc = @json(asset('ush.png'));

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatMultiline(value) {
        return escapeHtml(value).replace(/\n/g, '<br>');
    }

    function addItem(groupId, placeholder) {
        const group = document.getElementById(groupId);
        const item = document.createElement('div');
        item.className = 'list-item';
        item.innerHTML = `<textarea placeholder="${escapeHtml(placeholder)}" rows="3"></textarea><button type="button" class="btn-remove" onclick="this.parentElement.remove()" title="Hapus">x</button>`;
        group.appendChild(item);
    }

    function getListItems(groupId) {
        return Array.from(document.querySelectorAll(`#${groupId} textarea`))
            .map((textarea) => textarea.value.trim())
            .filter(Boolean);
    }

    function parseItem(raw) {
        const parts = raw.split('|');
        return {
            id: (parts[0] || '').trim(),
            en: (parts[1] || '').trim(),
        };
    }

    function buildListHTML(items) {
        if (!items.length) {
            return '<li style="color:#999;font-style:italic">-</li>';
        }

        return items.map((raw) => {
            const parsed = parseItem(raw);
            return `<li><span class="li-id">${escapeHtml(parsed.id)}</span>${parsed.en ? `<br><span class="li-en">${escapeHtml(parsed.en)}</span>` : ''}</li>`;
        }).join('');
    }

    function generate() {
        const value = (id) => document.getElementById(id)?.value.trim() || '';

        const nomor = value('nomor') || '___________________';
        const nama = value('nama') || '___________________';
        const ttl = value('ttl') || '___________________';
        const nim = value('nim') || '___________________';
        const tahunMasuk = value('tahun_masuk') || '___';
        const noIjazah = value('no_ijazah') || '___________________';
        const gelar = value('gelar') || '___';
        const skPt = value('sk_pt') || '___________________';
        const namaPt = value('nama_pt') || 'UNIVERSITAS SUGENG HARTONO';
        const akrPt = value('akr_pt') || '___________________';
        const prodi = value('prodi') || '___________________';
        const akrProdi = value('akr_prodi') || '___________________';
        const jenisJenjang = value('jenis_jenjang') || '___________________';
        const kkniLevel = value('kkni_level') || '___';
        const entryReq = value('entry_req') || '___________________';
        const bahasaPengantar = value('bahasa_pengantar') || 'Inggris / Indonesia';
        const noAkrPt = value('no_akr_pt') || '___________________';
        const noAkrProdi = value('no_akr_prodi') || '___________________';
        const sistemPenilaian = value('sistem_penilaian') || '___________________';
        const profStatus = value('prof_status') || '-';
        const lamaStudi = value('lama_studi') || '___________________';
        const kotaTgl = value('kota_tgl') || '___________________';
        const skripsiId = value('skripsi_id') || '___________________';
        const skripsiEn = value('skripsi_en') || '___________________';

        const prestasi = getListItems('grp_prestasi');
        const organisasi = getListItems('grp_org');
        const magang = getListItems('grp_magang');
        const pelatihan = getListItems('grp_pelatihan');
        const sertif = getListItems('grp_sertif');

        let html = document.getElementById('skpiDocumentTemplate').innerHTML;
        const logoMarkup = logoSrc
            ? `<img src="${escapeHtml(logoSrc)}" alt="Logo USH">`
            : '<div class="logo-placeholder">Logo<br>USH</div>';

        const replacements = {
            '%%LOGO%%': logoMarkup,
            '%%NOMOR%%': escapeHtml(nomor),
            '%%NAMA%%': escapeHtml(nama),
            '%%TTL%%': escapeHtml(ttl),
            '%%NIM%%': escapeHtml(nim),
            '%%TAHUN_MASUK%%': escapeHtml(tahunMasuk),
            '%%NO_IJAZAH%%': escapeHtml(noIjazah),
            '%%GELAR%%': escapeHtml(gelar),
            '%%SK_PT%%': escapeHtml(skPt),
            '%%NAMA_PT%%': escapeHtml(namaPt),
            '%%AKR_PT%%': escapeHtml(akrPt),
            '%%PRODI%%': escapeHtml(prodi),
            '%%AKR_PRODI%%': escapeHtml(akrProdi),
            '%%JENIS_JENJANG%%': escapeHtml(jenisJenjang),
            '%%KKNI_LEVEL%%': escapeHtml(kkniLevel),
            '%%ENTRY_REQ%%': escapeHtml(entryReq),
            '%%BAHASA_PENGANTAR%%': escapeHtml(bahasaPengantar),
            '%%NO_AKR_PT%%': escapeHtml(noAkrPt),
            '%%SISTEM_PENILAIAN%%': formatMultiline(sistemPenilaian),
            '%%LAMA_STUDI%%': escapeHtml(lamaStudi),
            '%%NO_AKR_PRODI%%': escapeHtml(noAkrProdi),
            '%%PROF_STATUS%%': escapeHtml(profStatus),
            '%%PRESTASI%%': buildListHTML(prestasi),
            '%%ORGANISASI%%': buildListHTML(organisasi),
            '%%MAGANG%%': buildListHTML(magang),
            '%%PELATIHAN%%': buildListHTML(pelatihan),
            '%%SERTIF%%': buildListHTML(sertif),
            '%%SKRIPSI_ID%%': escapeHtml(skripsiId),
            '%%SKRIPSI_EN%%': escapeHtml(skripsiEn),
            '%%KOTA_TGL%%': escapeHtml(kotaTgl),
        };

        Object.entries(replacements).forEach(([key, replacement]) => {
            html = html.replaceAll(key, replacement);
        });

        document.getElementById('previewArea').innerHTML = html;
        document.getElementById('btnPrint').classList.add('visible');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const hasSelectedRegistration = @json((bool) ($selectedRegistration && $selectedStudent));

        if (hasSelectedRegistration) {
            generate();
        }
    });
</script>
@endpush
