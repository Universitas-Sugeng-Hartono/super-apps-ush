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
<link rel="stylesheet" href="{{ asset('admin/css/skpi-generate-skpi.css') }}">
<style>
    .doc-link {
        color: #1D4ED8;
        text-decoration: none;
        font-size: 18px;
    }

    .text-muted {
        color: #9CA3AF;
    }

    .note-card {
        padding: 12px 16px;
        background: #F3F4F6;
        border-radius: 10px;
        font-size: 13px;
        color: #4B5563;
        border-left: 4px solid #D1D5DB;
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