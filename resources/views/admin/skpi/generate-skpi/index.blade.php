@extends('admin.layouts.super-app')

@php
use App\Models\StudentAchievement;

$formatDate = fn ($date) => $date ? $date->translatedFormat('d F Y') : '-';

// Kategori transkrip SKPI sesuai dokumen panduan
$transkripCategories = [
'wajib' => 'Wajib Universitas',
'organisasi' => 'Kegiatan Bidang Organisasi dan Kepemimpinan',
'penalaran' => 'Kegiatan Bidang Penalaran dan Keilmuan',
'minat_bakat' => 'Kegiatan Bidang Minat dan Bakat',
'kepedulian_sosial' => 'Kegiatan Bidang Kepedulian Sosial',
'lainnya' => 'Kegiatan Lainnya',
'volunteer' => 'Volunteer Mahasiswa',
];

// Group achievements by category
$groupedByCategory = [];
$skpByCategory = [];
foreach ($transkripCategories as $catKey => $catLabel) {
$items = $selectedAchievements->where('category', $catKey)->values();
$groupedByCategory[$catKey] = $items;
$skpByCategory[$catKey] = $items->sum('skp_points');
}

$totalSkp = array_sum($skpByCategory);

// Predikat SKPI S1
if ($totalSkp > 251) {
$predikat = 'Sangat Baik';
} elseif ($totalSkp >= 151) {
$predikat = 'Baik';
} elseif ($totalSkp >= 80) {
$predikat = 'Cukup';
} else {
$predikat = '-';
}

// Build text for old template sections
$buildAchievementText = function ($achievement) {
return collect([
$achievement->activity_type_label ?? $achievement->activity_type,
filled($achievement->level) && $achievement->level !== '-' ? '(' . $achievement->level . ')' : null,
filled($achievement->participation_role) && $achievement->participation_role !== '-' ? $achievement->participation_role : null,
])->filter()->implode(' - ');
};

$templatePayload = [
'nomor' => $selectedRegistration?->nomor_skpi ?? $documentMeta['nomor_skpi'] ?? '',
'nama' => $selectedRegistration?->nama_lengkap ?? '',
'ttl' => collect([
$selectedRegistration?->tempat_lahir,
$selectedRegistration?->tanggal_lahir?->translatedFormat('d F Y'),
])->filter()->implode(', '),
'nim' => $selectedRegistration?->nim ?? '',
'tahun_masuk' => $selectedRegistration->angkatan ?? '',
'no_ijazah' => $selectedRegistration->nomor_ijazah ?? '',
'gelar' => $academicProfile?->gelar_lulusan ?? $selectedRegistration->gelar ?? '',
'sk_pt' => $academicProfile?->sk_pendirian_perguruan_tinggi ?? '',
'nama_pt' => $academicProfile?->nama_perguruan_tinggi ?? 'UNIVERSITAS SUGENG HARTONO',
'akr_pt' => $academicProfile?->akreditasi_perguruan_tinggi ?? '',
'prodi' => $selectedStudent?->program_studi ?? '',
'akr_prodi' => $academicProfile?->akreditasi_program_studi ?? '',
'jenis_jenjang' => $academicProfile?->jenis_dan_jenjang_pendidikan ?? '',
'kkni_level' => $academicProfile?->jenjang_kualifikasi_kkni ?? '',
'entry_req' => $academicProfile?->persyaratan_penerimaan ?? '',
'bahasa_pengantar' => $academicProfile?->bahasa_pengantar_kuliah ?? 'Inggris / Indonesia',
'no_akr_pt' => $academicProfile?->nomor_akreditasi_perguruan_tinggi ?? '',
'sistem_penilaian' => $academicProfile?->sistem_penilaian ?? 'Skala/Scale : 0-4 : A=4, A-=3.75, B+=3.5, B=3, C=2, D=1, E=0',
'lama_studi' => $academicProfile?->lama_studi ?? '',
'no_akr_prodi' => $academicProfile?->nomor_akreditasi_program_studi ?? '',
'prof_status' => $academicProfile?->status_profesi ?? '-',
'prestasi' => $groupedByCategory['penalaran']->map($buildAchievementText)->values()->all(),
'organisasi' => $groupedByCategory['organisasi']->map($buildAchievementText)->values()->all(),
'magang' => $groupedByCategory['lainnya']->map($buildAchievementText)->values()->all(),
'pelatihan' => [],
'sertif' => $groupedByCategory['wajib']->map($buildAchievementText)->values()->all(),
'skripsi_id' => optional($automaticEntries->first())->event ?? ($selectedStudent?->finalProject?->title ?? ''),
'skripsi_en' => optional($automaticEntries->first())->event ?? ($selectedStudent?->finalProject?->title ?? ''),
'kota_tgl' => $documentMeta['authorization_place_date'] ?? ('Sukoharjo, ' . now()->translatedFormat('d F Y')),
'vice_rector_name' => $documentMeta['vice_rector_name'] ?? '',
'vice_rector_title' => $documentMeta['vice_rector_title'] ?? 'Wakil Rektor I Universitas Sugeng Hartono',
'signature_url' => $documentMeta['signature_data_uri'] ?? $documentMeta['signature_url'] ?? null,
];
@endphp

@section('content')
<div class="page-shell">
    <div class="hero-card">
        <div>
            <span class="hero-badge">Generate Draft SKPI</span>
            <h3>Siapkan Data Cetak SKPI</h3>
        </div>
        <div class="hero-stats">
            <div class="stat-chip">
                <span>Mahasiswa Approved</span>
                <strong>{{ $stats['approved_registrations'] }}</strong>
            </div>
            <div class="stat-chip">
                <span>Prestasi Approved</span>
                <strong>{{ $stats['approved_achievements'] }}</strong>
            </div>
            <div class="stat-chip">
                <span>Prestasi Dipilih</span>
                <strong>{{ $stats['selected_achievements'] }}</strong>
            </div>
        </div>
    </div>

    {{-- Section List per Prodi --}}
    @php
    $checkMissing = function($reg, $profile) {
    $missing = [];
    if (!$reg->nomor_ijazah) $missing[] = 'No. Ijazah';

    if (!$profile) {
    $missing[] = 'Profil Prodi';
    } else {
    if (!$profile->gelar_lulusan) $missing[] = 'Gelar';
    if (!$profile->sk_pendirian_perguruan_tinggi) $missing[] = 'SK PT';
    if (!$profile->akreditasi_program_studi) $missing[] = 'Akr. Prodi';
    if (!$profile->jenjang_kualifikasi_kkni) $missing[] = 'KKNI';
    }
    return $missing;
    };
    @endphp

    <div class="prodi-list-container">
        @foreach($studyPrograms as $prodi)
        @php
        $prodiRegistrations = $approvedRegistrations->filter(function($reg) use ($prodi) {
        return ($reg->student->program_studi ?? '') === $prodi->name;
        });
        $profile = $prodi->skpiAcademicProfile;
        @endphp

        <div class="content-card prodi-section" style="margin-bottom: 30px;">
            <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; margin-bottom: 20px;">
                <div>
                    <h4 style="margin:0; color: var(--primary);">{{ $prodi->name }}</h4>
                    <p style="font-size: 13px; color: #666;">Total: {{ $prodiRegistrations->count() }} Mahasiswa Approved</p>
                </div>
                @if(!$profile)
                <span class="badge-error"><i class="bi bi-exclamation-triangle"></i> Profil Prodi Belum Diisi</span>
                @endif
            </div>

            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th width="120">NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th width="150">No. Ijazah</th>
                            <th width="100">Gelar</th>
                            <th>Data Kosong</th>
                            <th width="120" style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prodiRegistrations as $index => $reg)
                        @php
                        $missing = $checkMissing($reg, $profile);
                        $isSelected = $selectedRegistrationId === $reg->id;
                        @endphp
                        <tr class="{{ $isSelected ? 'row-selected' : '' }}">
                            <td>{{ $index + 1 }}</td>
                            <td><code>{{ $reg->nim }}</code></td>
                            <td><strong>{{ $reg->nama_lengkap }}</strong></td>
                            <td>
                                @if($reg->nomor_ijazah)
                                <span class="text-success">{{ $reg->nomor_ijazah }}</span>
                                @else
                                <span class="text-danger"><em>Belum ada</em></span>
                                @endif
                            </td>
                            <td>
                                @if($profile?->gelar_lulusan)
                                <span class="text-success">{{ $profile->gelar_lulusan }}</span>
                                @else
                                <span class="text-danger"><em>-</em></span>
                                @endif
                            </td>
                            <td>
                                @if(empty($missing))
                                <span class="badge-success">Lengkap</span>
                                @else
                                @foreach($missing as $m)
                                <span class="badge-missing">{{ $m }}</span>
                                @endforeach
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px; justify-content: center;">
                                    <a href="{{ route('admin.skpi.generate-skpi.index', ['registration_id' => $reg->id]) }}" class="btn-icon-sm" title="Preview / Siapkan">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.skpi.generate-skpi.download-all') }}" style="margin:0;">
                                        @csrf
                                        <input type="hidden" name="registration_id" value="{{ $reg->id }}">
                                        <button type="submit" class="btn-icon-sm btn-download" title="Download Word">
                                            <i class="bi bi-file-earmark-word"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px; color: #999;">Belum ada mahasiswa approved di prodi ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>

    @if($selectedRegistration && $selectedStudent)
    <div id="selected-student-focus" style="margin-bottom: 20px; padding: 20px; background: #eef2ff; border-radius: 15px; border: 1px solid #c7d2fe;">
        <h4 style="margin:0 0 10px; color: #4338ca;">Detail Preview: {{ $selectedRegistration->nama_lengkap }} ({{ $selectedRegistration->nim }})</h4>
        <div class="form-actions" style="margin-top: 15px; display: flex; gap: 10px;">
            <button type="button" class="btn-secondary" id="generateTemplateBtn" style="padding: 8px 16px; font-size: 13px;">
                <i class="bi bi-file-earmark-richtext"></i> Tampilkan Preview HTML
            </button>
            <a href="{{ route('admin.skpi.generate-skpi.index') }}" class="btn-cancel" style="text-decoration:none; padding: 8px 16px; font-size: 13px; background: #fff; border: 1px solid #ccc; border-radius: 8px; color: #666;">
                <i class="bi bi-x-lg"></i> Tutup Preview
            </a>
        </div>
    </div>

    <div class="content-card template-preview-card" id="templatePreviewCard">
        <div class="section-header">
            <div>
                <h4>Preview Template SKPI</h4>
            </div>
        </div>
        <div class="template-preview-shell">
            <div id="templatePreviewArea">
                <div class="empty-state compact-empty">
                    <i class="bi bi-file-earmark-text"></i>
                    <h4>Template belum digenerate</h4>
                    <p>Klik tombol <strong>Tampilkan Preview HTML</strong> untuk mengecek data.</p>
                </div>
            </div>
        </div>
    </div>

    @include('admin.skpi.generate.template')
    @endif
</div>
@endsection

@push('css')
<style>
    .page-shell {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .hero-card,
    .content-card,
    .summary-card,
    .empty-state {
        background: white;
        border-radius: 18px;
        padding: 24px;
        box-shadow: var(--shadow);
    }

    .hero-card {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        background: linear-gradient(135deg, #FFF8EE, #FFFFFF);
        border: 1px solid #F4E5CD;
    }

    .hero-badge,
    .summary-label,
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .hero-badge,
    .summary-label {
        background: #FFF1DA;
        color: #D97706;
    }

    .hero-card h3,
    .section-header h4,
    .summary-card h4,
    .achievement-body h5,
    .empty-state h4 {
        margin: 10px 0 8px;
        color: #213555;
        font-weight: 700;
    }

    .hero-card p,
    .section-header p,
    .summary-card p,
    .detail-item span,
    .achievement-body p,
    .empty-state p,
    .form-group small {
        margin: 0;
        color: #6B7280;
        line-height: 1.6;
    }

    .hero-stats,
    .summary-grid,
    .form-grid,
    .detail-grid {
        display: grid;
        gap: 16px;
    }

    .hero-stats {
        grid-template-columns: repeat(3, minmax(120px, 1fr));
        width: min(420px, 100%);
    }

    .stat-chip,
    .summary-card,
    .detail-item,
    .achievement-item {
        border: 1px solid #ECE6DA;
        border-radius: 16px;
        background: #FFFFFF;
    }

    .stat-chip {
        padding: 16px;
    }

    .stat-chip span,
    .summary-meta span,
    .detail-item span {
        display: block;
        font-size: 12px;
        margin-bottom: 8px;
    }

    .stat-chip strong,
    .summary-meta strong,
    .detail-item strong {
        font-size: 16px;
        color: #213555;
    }

    .generate-form .form-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-group label {
        font-size: 14px;
        font-weight: 700;
        color: #374151;
    }

    .form-control {
        width: 100%;
        border: 1px solid #D9DEE8;
        border-radius: 12px;
        padding: 12px 14px;
        font-size: 14px;
        font-family: inherit;
        color: #1F2937;
        background: #FFFFFF;
    }

    .form-control:focus {
        outline: none;
        border-color: #D97706;
        box-shadow: 0 0 0 4px rgba(217, 119, 6, 0.12);
    }

    .multi-select {
        min-height: 164px;
    }

    .form-actions {
        margin-top: 18px;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .btn-primary,
    .btn-secondary {
        border: none;
        border-radius: 12px;
        padding: 12px 18px;
        font-size: 14px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        cursor: pointer;
    }

    .btn-primary:disabled,
    .btn-secondary:disabled {
        cursor: not-allowed;
        opacity: 0.6;
    }

    .btn-primary {
        background: #D97706;
        color: white;
    }

    .btn-secondary {
        background: #EEF2F7;
        color: #64748B;
    }

    .print-btn {
        display: none;
    }

    .summary-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .summary-card {
        padding: 20px;
    }

    .summary-card h4 {
        margin-top: 10px;
    }

    .summary-meta {
        margin-top: 14px;
    }

    .content-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .template-upload-grid {
        display: grid;
        grid-template-columns: 1.2fr 0.8fr;
        gap: 20px;
    }

    .template-upload-form,
    .template-status-card {
        border: 1px solid #ECE6DA;
        border-radius: 16px;
        background: #FFFFFF;
        padding: 20px;
    }

    .placeholder-guide-card {
        margin-top: 20px;
        border: 1px solid #ECE6DA;
        border-radius: 16px;
        background: linear-gradient(135deg, #FFF8ED, #FFFFFF);
        padding: 20px;
    }

    .placeholder-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 10px;
    }

    .placeholder-grid code {
        display: block;
        padding: 10px 12px;
        border-radius: 12px;
        background: #FFFFFF;
        border: 1px solid #F0D9BA;
        color: #9A3412;
        font-size: 13px;
        font-weight: 700;
    }

    .template-status-card h4 {
        margin: 10px 0 8px;
        color: #213555;
        font-weight: 700;
    }

    .template-status-card p {
        margin: 0;
        color: #6B7280;
        line-height: 1.6;
    }

    .inline-action-form {
        margin: 0;
    }

    .inline-note {
        margin: 14px 0 0;
        font-size: 13px;
        line-height: 1.6;
        color: #7C5A10;
    }

    .signature-preview-card {
        margin-top: 18px;
        padding: 16px;
        border: 1px solid #ECE6DA;
        border-radius: 14px;
        background: #FFFCF7;
    }

    .signature-preview-card span {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: #6B7280;
        margin-bottom: 10px;
    }

    .signature-preview-card img {
        max-height: 90px;
        max-width: 280px;
        object-fit: contain;
        display: block;
    }

    .section-header {
        margin-bottom: 18px;
    }

    .detail-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .detail-item {
        padding: 16px;
        background: #FAFBFC;
    }

    .achievement-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .achievement-item {
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .achievement-item.auto-item {
        background: #F8FAFC;
    }

    .achievement-icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        background: linear-gradient(135deg, #FF9800, #FFB347);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .achievement-body {
        flex: 1;
    }

    .achievement-body h5 {
        margin: 0 0 6px;
    }

    .status-badge.active {
        background: #E8F5E9;
        color: #2E7D32;
    }

    .status-badge.info {
        background: #E3F2FD;
        color: #1565C0;
    }

    /* ── Transkrip & Rincian Tables ── */
    .transkrip-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    .transkrip-table th,
    .transkrip-table td {
        border: 1px solid #E5E7EB;
        padding: 10px 14px;
        text-align: left;
    }

    .transkrip-table thead th {
        background: #213555;
        color: white;
        font-weight: 700;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .transkrip-table .cat-row td {
        background: #FFF8E1;
        font-weight: 700;
        color: #92400E;
        border-bottom: 2px solid #F4E5CD;
    }

    .transkrip-table .sub-cat-row td {
        background: #FAFBFC;
        color: #374151;
    }

    /* ── Custom Table for Student List ── */
    .custom-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 8px;
        font-size: 14px;
    }

    .custom-table th {
        background: #f8fafc;
        color: #64748b;
        font-weight: 700;
        padding: 12px 16px;
        text-align: left;
        border-bottom: 2px solid #e2e8f0;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
    }

    .custom-table td {
        background: white;
        padding: 12px 16px;
        border-top: 1px solid #f1f5f9;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .custom-table tr td:first-child {
        border-left: 1px solid #f1f5f9;
        border-top-left-radius: 12px;
        border-bottom-left-radius: 12px;
    }

    .custom-table tr td:last-child {
        border-right: 1px solid #f1f5f9;
        border-top-right-radius: 12px;
        border-bottom-right-radius: 12px;
    }

    .custom-table tr:hover td {
        background: #fdfdfd;
        border-color: #cbd5e1;
    }

    .row-selected td {
        background: #eff6ff !important;
        border-color: #bfdbfe !important;
    }

    .badge-missing {
        background: #fff1f2;
        color: #e11d48;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        margin-right: 4px;
        margin-bottom: 4px;
        display: inline-block;
        border: 1px solid #fecdd3;
    }

    .badge-success {
        background: #f0fdf4;
        color: #16a34a;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
        border: 1px solid #bbf7d0;
    }

    .badge-error {
        background: #ef4444;
        color: white;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
    }

    .btn-icon-sm {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-icon-sm:hover {
        background: #e2e8f0;
        color: #1e293b;
    }

    .btn-download {
        background: #2b5797;
        color: white;
        border: none;
    }

    .btn-download:hover {
        background: #1e3e6d;
        color: white;
    }

    .text-success {
        color: #16a34a;
        font-weight: 600;
    }

    .text-danger {
        color: #dc2626;
        font-weight: 500;
    }

    .prodi-section {
        border-left: 5px solid var(--primary);
    }

    .transkrip-table .total-row td {
        background: #213555;
        color: white;
        font-weight: 700;
        font-size: 15px;
    }

    .transkrip-table .subtotal-row td {
        background: #F3F4F6;
        font-weight: 700;
        color: #213555;
    }

    .rincian-cat-title {
        margin: 0 0 10px;
        padding: 10px 14px;
        background: #FFF1DA;
        border-radius: 10px;
        color: #92400E;
        font-weight: 700;
        font-size: 16px;
    }

    .rincian-sub-title {
        margin: 18px 0 8px;
        padding: 8px 14px;
        background: #FAFBFC;
        border-left: 3px solid #D97706;
        color: #374151;
        font-weight: 700;
        font-size: 14px;
    }

    .rincian-table {
        margin-bottom: 8px;
    }

    .doc-link {
        color: #1D4ED8;
        text-decoration: none;
        font-size: 18px;
    }

    .text-muted {
        color: #9CA3AF;
    }

    .note-card {
        background: linear-gradient(135deg, #FFF8E1, #FFFFFF);
    }

    .template-preview-card {
        display: none;
    }

    .template-preview-card.visible {
        display: block;
    }

    .template-preview-shell {
        background: #DFDBD2;
        border-radius: 18px;
        padding: 24px;
        overflow-x: auto;
    }

    .compact-empty {
        padding: 32px 20px;
    }

    .template-file-preview,
    .template-word-preview {
        background: #FFFFFF;
        border-radius: 16px;
        padding: 18px;
        min-height: 420px;
    }

    .template-file-preview object,
    .template-file-preview iframe {
        width: 100%;
        min-height: 820px;
        border: none;
        border-radius: 12px;
        background: #FFFFFF;
    }

    .template-word-preview {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        gap: 12px;
    }

    .template-word-preview i {
        font-size: 52px;
        color: #D97706;
    }

    .template-word-preview h4 {
        margin: 0;
        color: #213555;
        font-weight: 700;
    }

    .template-word-preview p {
        margin: 0;
        color: #6B7280;
        max-width: 560px;
    }

    .skpi-doc {
        background: #FFFFFF;
        width: 100%;
        max-width: 820px;
        margin: 0 auto;
        box-shadow: 0 4px 40px rgba(0, 0, 0, 0.18);
        font-family: "Times New Roman", Georgia, serif;
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
        align-items: center;
        gap: 16px;
        padding: 0 90px 10px 8px;
    }

    .doc-header .logo-col {
        width: 82px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .doc-header .logo-col img {
        width: 68px;
        height: 68px;
        object-fit: contain;
    }

    .doc-header .logo-placeholder {
        width: 70px;
        height: 70px;
        background: #F0F0F0;
        border: 1px dashed #999999;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 9px;
        color: #999999;
        text-align: center;
    }

    .doc-header .identity-col {
        flex: 1;
        color: #7D7D7D;
    }

    .doc-header .identity-col .campus-title {
        font-size: 25px;
        font-weight: bold;
        line-height: 1.1;
        margin-bottom: 3px;
    }

    .doc-header .identity-col .campus-subtitle {
        font-size: 18px;
        font-style: italic;
        font-weight: bold;
    }

    .doc-rule {
        border-top: 1px solid #000000;
        margin: 0 90px 9px 90px;
    }

    .doc-title-block {
        margin: 0 90px 10px 90px;
        margin-bottom: 10px;
    }

    .doc-main-title {
        font-size: 15px;
        font-weight: bold;
        margin-bottom: 2px;
    }

    .doc-sub-title {
        font-size: 11px;
        font-style: italic;
        font-weight: bold;
        margin-bottom: 2px;
    }

    .doc-number {
        font-size: 11px;
    }

    .doc-intro {
        padding: 0 90px 10px 90px;
        font-size: 10.5px;
    }

    .doc-intro p {
        margin-bottom: 6px;
    }

    .doc-intro .en {
        font-style: italic;
    }

    .doc-section {
        border-top: none;
        margin-left: 90px;
        margin-right: 90px;
        margin-top: 14px;
    }

    .section-title-row {
        padding: 5px 8px;
        background: #EFEFEF;
        font-weight: bold;
        font-size: 11px;
        border-bottom: 1px solid #000000;
    }

    .section-title-row .en {
        font-style: italic;
        font-weight: normal;
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

    .field-table {
        border-collapse: separate;
        border-spacing: 0;
    }

    .field-table tr:nth-child(odd) td {
        background: #F1F1F1;
    }

    .field-table tr:nth-child(even) td {
        background: #FFFFFF;
    }

    .field-table td {
        border: none;
        padding: 8px 10px;
    }

    .list-section {
        padding: 0;
    }

    .list-section .list-block:nth-child(odd),
    .thesis-section {
        background: #F1F1F1;
    }

    .list-section .list-block:nth-child(even) {
        background: #FFFFFF;
    }

    .list-section .list-block,
    .thesis-section {
        padding: 8px 10px;
    }

    .list-section .list-cat {
        font-weight: bold;
        font-size: 10.5px;
        margin: 0 0 4px;
    }

    .list-section .list-cat .en,
    .list-section .li-en,
    .thesis-section .thesis-en,
    .outcomes-table .cat-header .en {
        font-style: italic;
        font-weight: normal;
    }

    .list-section ul,
    .thesis-section ul {
        margin-left: 20px;
        margin-bottom: 0;
    }

    .list-section li {
        margin-bottom: 2px;
        font-size: 10.5px;
    }

    .list-section .li-id,
    .thesis-section .thesis-id {
        font-weight: bold;
    }

    .thesis-section {
        border-top: none;
    }

    .thesis-section p {
        font-size: 10.5px;
        margin: 2px 0;
    }

    .outcomes-table td,
    .kkni-table td,
    .footer-table td {
        border: 1px solid #000000;
        padding: 6px 8px;
        font-size: 10px;
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

    .outcomes-table .cat-header {
        background: #E8E8E8;
        font-weight: bold;
        font-size: 10.5px;
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

    .auth-section .sign-block.has-signature {
        margin-top: 18px;
    }

    .auth-section .signature-image {
        display: block;
        max-height: 80px;
        max-width: 220px;
        margin-bottom: 8px;
        object-fit: contain;
    }

    .auth-section ._nm {
        text-decoration: underline;
    }

    .kkni-table ul,
    .footer-table ul {
        margin-left: 14px;
    }

    .kkni-table li,
    .footer-table li {
        margin-bottom: 2px;
    }

    .footer-table .address-col {
        font-size: 10px;
    }

    .footer-table .bold {
        font-weight: bold;
    }

    .empty-state {
        text-align: center;
        padding: 56px 24px;
    }

    .empty-state i {
        font-size: 64px;
        color: #D1D5DB;
        margin-bottom: 14px;
    }

    @media (max-width: 1100px) {
        .hero-card {
            flex-direction: column;
            gap: 20px;
        }

        .hero-stats,
        .summary-grid,
        .generate-form .form-grid,
        .detail-grid,
        .template-upload-grid,
        .content-grid {
            grid-template-columns: 1fr !important;
        }
    }

    @media (max-width: 768px) {
        .page-shell {
            gap: 16px;
        }

        .hero-card,
        .content-card,
        .summary-card {
            padding: 20px;
        }

        .hero-stats {
            grid-template-columns: repeat(2, 1fr) !important;
            width: 100%;
        }

        .stat-chip {
            padding: 12px;
        }

        .stat-chip strong {
            font-size: 14px;
        }

        .achievement-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
            padding: 15px;
        }

        .achievement-icon {
            width: 44px;
            height: 44px;
            font-size: 20px;
        }

        .achievement-body h5 {
            font-size: 14px;
        }

        .achievement-body p {
            font-size: 11px;
        }

        .status-badge {
            align-self: flex-start;
        }

        /* ── Preview Adjustments ── */
        .template-preview-shell {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding: 10px;
            background: #f1f1f1;
            border-radius: 12px;
        }

        .skpi-doc {
            width: fit-content;
            min-width: 100%;
            margin: 0 auto !important;
            padding: 20px 0 !important;
        }

        .doc-intro,
        .doc-section {
            margin-left: 15px !important;
            margin-right: 15px !important;
        }

        .doc-header {
            padding: 0 15px !important;
        }

        .doc-intro {
            padding-top: 5px !important;
            padding-bottom: 5px !important;
        }

        .auth-section {
            padding: 15px !important;
        }

        .template-preview-shell table {
            font-size: 9px !important;
        }

        .section-title-row {
            font-size: 10px !important;
        }

        .form-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .form-actions button,
        .form-actions form {
            width: 100%;
        }

        .btn-primary,
        .btn-secondary {
            width: 100%;
            justify-content: center;
        }
    }

    @media print {
        body {
            background: #FFFFFF;
            padding-bottom: 0 !important;
        }

        .header-section,
        .bottom-nav,
        .sidebar-nav,
        .hero-card,
        .generate-form,
        .summary-grid,
        .content-grid,
        .achievement-list,
        .note-card,
        .section-header,
        #generateTemplateBtn,
        #printTemplateBtn {
            display: none !important;
        }

        .page-shell,
        .template-preview-card,
        .template-preview-shell {
            display: block !important;
            padding: 0 !important;
            background: #FFFFFF !important;
            box-shadow: none !important;
            border: none !important;
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
    const skpiTemplatePayload = @json($templatePayload);
    const hasSelectedSkpiData = @json((bool)($selectedRegistration && $selectedStudent));
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

    function parseItem(raw) {
        const parts = String(raw ?? '').split('|');
        return {
            id: (parts[0] || '').trim(),
            en: (parts[1] || '').trim(),
        };
    }

    function buildListHTML(items) {
        if (!items.length) {
            return '<li><span class="li-id">-</span><br><span class="li-en">-</span></li>';
        }

        return items.map((raw) => {
            const parsed = parseItem(raw);
            const englishLine = parsed.en || parsed.id;
            return `<li><span class="li-id">${escapeHtml(parsed.id)}</span><br><span class="li-en">${escapeHtml(englishLine)}</span></li>`;
        }).join('');
    }

    function renderSkpiTemplate() {
        if (!hasSelectedSkpiData) {
            return;
        }

        let html = document.getElementById('skpiDocumentTemplate').innerHTML;
        const logoMarkup = logoSrc ?
            `<img src="${escapeHtml(logoSrc)}" alt="Logo USH">` :
            '<div class="logo-placeholder">Logo<br>USH</div>';
        const signatureMarkup = skpiTemplatePayload.signature_url ?
            `<img src="${escapeHtml(skpiTemplatePayload.signature_url)}" alt="Tanda tangan" class="signature-image" style="max-height:60px; max-width:200px; display:block;">` :
            '';
        const signBlockClass = skpiTemplatePayload.signature_url ? 'has-signature' : '';

        const replacements = {
            '%%LOGO%%': logoMarkup,
            '%%NOMOR%%': escapeHtml(skpiTemplatePayload.nomor || '___________________'),
            '%%NAMA%%': escapeHtml(skpiTemplatePayload.nama || '___________________'),
            '%%TTL%%': escapeHtml(skpiTemplatePayload.ttl || '___________________'),
            '%%NIM%%': escapeHtml(skpiTemplatePayload.nim || '___________________'),
            '%%TAHUN_MASUK%%': escapeHtml(skpiTemplatePayload.tahun_masuk || '___'),
            '%%NO_IJAZAH%%': escapeHtml(skpiTemplatePayload.no_ijazah || '___________________'),
            '%%GELAR%%': escapeHtml(skpiTemplatePayload.gelar || '___'),
            '%%SK_PT%%': escapeHtml(skpiTemplatePayload.sk_pt || '___________________'),
            '%%NAMA_PT%%': escapeHtml(skpiTemplatePayload.nama_pt || 'UNIVERSITAS SUGENG HARTONO'),
            '%%AKR_PT%%': escapeHtml(skpiTemplatePayload.akr_pt || '___________________'),
            '%%PRODI%%': escapeHtml(skpiTemplatePayload.prodi || '___________________'),
            '%%AKR_PRODI%%': escapeHtml(skpiTemplatePayload.akr_prodi || '___________________'),
            '%%JENIS_JENJANG%%': escapeHtml(skpiTemplatePayload.jenis_jenjang || '___________________'),
            '%%KKNI_LEVEL%%': escapeHtml(skpiTemplatePayload.kkni_level || '___'),
            '%%ENTRY_REQ%%': escapeHtml(skpiTemplatePayload.entry_req || '___________________'),
            '%%BAHASA_PENGANTAR%%': escapeHtml(skpiTemplatePayload.bahasa_pengantar || 'Inggris / Indonesia'),
            '%%NO_AKR_PT%%': escapeHtml(skpiTemplatePayload.no_akr_pt || '___________________'),
            '%%SISTEM_PENILAIAN%%': formatMultiline(skpiTemplatePayload.sistem_penilaian || '___________________'),
            '%%LAMA_STUDI%%': escapeHtml(skpiTemplatePayload.lama_studi || '___________________'),
            '%%NO_AKR_PRODI%%': escapeHtml(skpiTemplatePayload.no_akr_prodi || '___________________'),
            '%%PROF_STATUS%%': escapeHtml(skpiTemplatePayload.prof_status || '-'),
            '%%PRESTASI%%': buildListHTML(skpiTemplatePayload.prestasi || []),
            '%%ORGANISASI%%': buildListHTML(skpiTemplatePayload.organisasi || []),
            '%%MAGANG%%': buildListHTML(skpiTemplatePayload.magang || []),
            '%%PELATIHAN%%': buildListHTML(skpiTemplatePayload.pelatihan || []),
            '%%SERTIF%%': buildListHTML(skpiTemplatePayload.sertif || []),
            '%%SKRIPSI_ID%%': escapeHtml(skpiTemplatePayload.skripsi_id || '___________________'),
            '%%SKRIPSI_EN%%': escapeHtml(skpiTemplatePayload.skripsi_en || '___________________'),
            '%%KOTA_TGL%%': escapeHtml(skpiTemplatePayload.kota_tgl || '___________________'),
            '%%VICE_RECTOR_NAME%%': escapeHtml(skpiTemplatePayload.vice_rector_name || '____________________________________'),
            '%%VICE_RECTOR_TITLE%%': escapeHtml(skpiTemplatePayload.vice_rector_title || 'Wakil Rektor I Universitas Sugeng Hartono'),
            '%%SIGNATURE%%': signatureMarkup,
            '%%SIGN_BLOCK_CLASS%%': signBlockClass,
        };

        Object.entries(replacements).forEach(([key, replacement]) => {
            html = html.replaceAll(key, replacement);
        });

        document.getElementById('templatePreviewArea').innerHTML = html;
        document.getElementById('templatePreviewCard').classList.add('visible');
        document.getElementById('printTemplateBtn').style.display = 'inline-flex';
        document.getElementById('templatePreviewCard').scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const generateButton = document.getElementById('generateTemplateBtn');

        if (generateButton) {
            generateButton.addEventListener('click', renderSkpiTemplate);
        }
    });
</script>
@endpush