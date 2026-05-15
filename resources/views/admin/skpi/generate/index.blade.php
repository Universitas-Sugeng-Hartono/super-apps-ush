@extends('admin.layouts.super-app')

@section('title', 'Generator SKPI USH')

@php
$selectedRegistration = $selectedRegistration ?? null;
$selectedRegistrationId = $selectedRegistration?->id;
$selectedStudent = $selectedRegistration?->student ?? null;

$nomorSkpi = $documentMeta['nomor_skpi'] ?? '';
$namaLengkap = $selectedRegistration?->nama_lengkap ?? $selectedStudent?->nama_lengkap ?? '';
$nim = $selectedRegistration?->nim ?? $selectedStudent?->nim ?? '';
$ttl = collect([
    $selectedRegistration?->tempat_lahir,
    $selectedRegistration?->tanggal_lahir ? $selectedRegistration->tanggal_lahir->translatedFormat('d F Y') : null
])->filter()->implode(', ');

$nomorIjazah = $selectedRegistration?->nomor_ijazah ?? '';
$gelar = $academicProfile?->gelar_lulusan ?? $selectedRegistration?->gelar ?? '';
$tahunMasuk = $selectedRegistration?->angkatan ?? '';
$programStudi = $selectedStudent?->program_studi ?? '';
$akreditasiProdi = $academicProfile?->nomor_akreditasi_program_studi ?? '';
$kkniLevel = $academicProfile?->jenjang_kualifikasi_kkni ?? '';
$thesisTitle = $selectedStudent?->finalProject?->title ?? '-';
@endphp

@section('content')
<div class="content-card">
    {{-- Header --}}
    <div class="card-header">
        <div class="header-left">
            <a href="{{ route('admin.skpi.index') }}" class="btn-back">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <h3><i class="bi bi-file-earmark-text"></i> Generator SKPI</h3>
        </div>
        <div class="header-actions">
            <button type="button" class="btn-primary" id="btnBulkDocx" title="Unduh ZIP Word">
                <i class="bi bi-file-earmark-word"></i> ZIP Word {{ isset($selectedStudyProgramIdFilter) && $selectedStudyProgramIdFilter ? '(Prodi)' : '' }}
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-success">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert-danger">
            <i class="bi bi-x-circle"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Filter / Search --}}
    <div class="search-box">
        <form method="GET" action="{{ route('admin.skpi.generate-skpi.index') }}" class="search-form" id="filterForm">
            <select name="study_program_id" class="filter-select" onchange="this.form.submit()">
                <option value="">Semua Program Studi</option>
                @foreach($studyPrograms as $sp)
                    <option value="{{ $sp->id }}" {{ ($selectedStudyProgramIdFilter ?? '') == $sp->id ? 'selected' : '' }}>
                        {{ $sp->name }}
                    </option>
                @endforeach
            </select>
            <select name="generate_status" class="filter-select" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="belum" {{ ($generateStatusFilter ?? '') === 'belum' ? 'selected' : '' }}>Belum Digenerate</option>
                <option value="sudah" {{ ($generateStatusFilter ?? '') === 'sudah' ? 'selected' : '' }}>Sudah Digenerate</option>
            </select>
        </form>
    </div>

    {{-- Table --}}
    @if($approvedRegistrations->count() > 0)
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Mahasiswa</th>
                    <th>NIM</th>
                    <th>Angkatan</th>
                    <th>Program Studi</th>
                    <th>Status SKPI</th>
                    <th>Generate At</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($approvedRegistrations as $registration)
                @php
                    $isSaved = $registration->hasGeneratedDocument();
                    $genAt = $registration->skpi_generated_at?->translatedFormat('d M Y');
                @endphp
                <tr>
                    <td>{{ $loop->iteration + ($approvedRegistrations->currentPage() - 1) * $approvedRegistrations->perPage() }}</td>
                    <td><strong>{{ $registration->nama_lengkap }}</strong></td>
                    <td class="font-monospace">{{ $registration->nim }}</td>
                    <td>
                        <span class="badge-year">{{ $registration->student?->angkatan ?? '-' }}</span>
                    </td>
                    <td>
                        <span class="badge-prodi">{{ $registration->student?->program_studi ?? '-' }}</span>
                    </td>
                    <td>
                        <span class="status-badge {{ $isSaved ? 'status-tersimpan' : 'status-belum' }}">
                            {{ $isSaved ? 'Tersimpan' : 'Belum' }}
                        </span>
                    </td>
                    <td>
                        @if($isSaved)
                            <span class="text-muted-sm">{{ $genAt }}</span>
                        @else
                            <span class="text-muted-sm">-</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button type="button"
                                class="btn-view"
                                onclick="openDetailModal({{ $registration->id }})"
                                data-id="{{ $registration->id }}"
                                data-nama="{{ $registration->nama_lengkap }}"
                                data-nim="{{ $registration->nim }}"
                                data-angkatan="{{ $registration->student?->angkatan ?? '-' }}"
                                data-prodi="{{ $registration->student?->program_studi ?? '-' }}"
                                data-saved="{{ $isSaved ? '1' : '0' }}"
                                data-genat="{{ $genAt ?? '-' }}"
                                data-ttl="{{ collect([$registration->tempat_lahir, $registration->tanggal_lahir?->translatedFormat('d F Y')])->filter()->implode(', ') }}"
                                data-ijazah="{{ $registration->nomor_ijazah ?? '-' }}"
                                data-gelar="{{ $registration->gelar ?? '-' }}"
                                data-route-generate="{{ route('admin.skpi.generate-skpi.download-all') }}"
                                data-route-saved="{{ $isSaved ? route('admin.skpi.generate-skpi.download-saved', $registration->id) : '' }}"
                                data-nomor-skpi="{{ $isSaved ? ($registration->skpi_nomor ?? '') : '' }}">
                                <i class="bi bi-eye"></i> Detail
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        {{ $approvedRegistrations->appends(['study_program_id' => $selectedStudyProgramIdFilter ?? '', 'generate_status' => $generateStatusFilter ?? ''])->links('pagination::bootstrap-5') }}
    </div>
    @else
    <div class="empty-state">
        <i class="bi bi-inbox"></i>
        <p>Tidak ada data mahasiswa yang disetujui</p>
    </div>
    @endif
</div>

{{-- MODAL DETAIL --}}
<div class="modal-overlay" id="detailModal" onclick="closeModalOutside(event)">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-profile">
                <div class="modal-avatar" id="modalAvatar">MH</div>
                <div>
                    <h2 id="modalNama">-</h2>
                    <p id="modalNimProdi">-</p>
                </div>
            </div>
            <button class="modal-close" onclick="closeDetailModal()"><i class="bi bi-x-lg"></i></button>
        </div>

        <div class="modal-body">
            <div class="modal-tabs">
                <button class="mtab-btn active" data-mtab="mtab-identitas">Identitas</button>
                <button class="mtab-btn" data-mtab="mtab-dokumen">Dokumen SKPI</button>
            </div>

            {{-- Tab Identitas --}}
            <div id="mtab-identitas" class="mtab-panel active">
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Nama Lengkap</span>
                        <span class="detail-value" id="dNama">-</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">NIM</span>
                        <span class="detail-value font-monospace" id="dNim">-</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Program Studi</span>
                        <span class="detail-value" id="dProdi">-</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Angkatan</span>
                        <span class="detail-value" id="dAngkatan">-</span>
                    </div>
                    <div class="detail-item full">
                        <span class="detail-label">Tempat, Tanggal Lahir</span>
                        <span class="detail-value" id="dTtl">-</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Nomor Ijazah</span>
                        <span class="detail-value" id="dIjazah">-</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Gelar</span>
                        <span class="detail-value" id="dGelar">-</span>
                    </div>
                </div>
            </div>

            {{-- Tab Dokumen --}}
            <div id="mtab-dokumen" class="mtab-panel">
                <div class="detail-grid">
                    <div class="detail-item full">
                        <span class="detail-label">Status Generate</span>
                        <span class="detail-value" id="dStatus">-</span>
                    </div>
                    <div class="detail-item full" id="dGenAtRow">
                        <span class="detail-label">Generate Pada</span>
                        <span class="detail-value" id="dGenAt">-</span>
                    </div>
                </div>

                <div class="modal-actions">
                    <form method="POST" id="modalWordForm">
                        @csrf
                        <input type="hidden" name="registration_id" id="modalRegId">
                        <input type="hidden" name="nomor_skpi" id="modalNomorSkpi">
                        <button type="submit" class="btn-generate-word">
                            <i class="bi bi-file-earmark-word-fill"></i> Generate &amp; Download Word
                        </button>
                    </form>
                    <a href="#" id="modalBtnSaved" class="btn-download-saved" style="display:none;">
                        <i class="bi bi-cloud-arrow-down-fill"></i> Download Tersimpan
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('admin/css/skpi-generator.css') }}">
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Modal Tabs
    document.querySelectorAll('.mtab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.dataset.mtab;
            document.querySelectorAll('.mtab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.mtab-panel').forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById(target).classList.add('active');
        });
    });

    // Bulk ZIP Word
    const btnBulk = document.getElementById('btnBulkDocx');
    if (btnBulk) {
        btnBulk.onclick = () => {
            // Ambil semua ID registrasi yang ada di tabel saat ini
            const ids = Array.from(document.querySelectorAll('.btn-view'))
                             .map(btn => btn.dataset.id)
                             .filter(id => id);
            
            if (ids.length === 0) {
                alert('Tidak ada data mahasiswa yang bisa di-download di tabel ini.');
                return;
            }

            const url = "{{ route('admin.skpi.generate-skpi.download-all') }}?registration_ids=" + ids.join(',');
            
            window.location.href = url;
        };
    }
});

function openDetailModal(id) {
    const btn = document.querySelector(`.btn-view[data-id="${id}"]`);
    if (!btn) return;

    const nama = btn.dataset.nama;
    const nim = btn.dataset.nim;
    const prodi = btn.dataset.prodi;
    const angkatan = btn.dataset.angkatan;
    const isSaved = btn.dataset.saved === '1';
    const genAt = btn.dataset.genat;
    const ttl = btn.dataset.ttl || '-';
    const ijazah = btn.dataset.ijazah || '-';
    const gelar = btn.dataset.gelar || '-';
    const routeGenerate = btn.dataset.routeGenerate;
    const routeSaved = btn.dataset.routeSaved;
    const nomorSkpi = btn.dataset.nomorSkpi || '';

    // Header
    const initials = nama.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase();
    document.getElementById('modalAvatar').textContent = initials;
    document.getElementById('modalNama').textContent = nama;
    document.getElementById('modalNimProdi').textContent = nim + ' • ' + prodi;

    // Tab Identitas
    document.getElementById('dNama').textContent = nama;
    document.getElementById('dNim').textContent = nim;
    document.getElementById('dProdi').textContent = prodi;
    document.getElementById('dAngkatan').textContent = angkatan;
    document.getElementById('dTtl').textContent = ttl;
    document.getElementById('dIjazah').textContent = ijazah;
    document.getElementById('dGelar').textContent = gelar;

    // Tab Dokumen
    const statusEl = document.getElementById('dStatus');
    statusEl.innerHTML = isSaved
        ? '<span class="status-badge status-tersimpan">Tersimpan</span>'
        : '<span class="status-badge status-belum">Belum Digenerate</span>';

    document.getElementById('dGenAt').textContent = isSaved ? genAt : '-';

    // Form generate
    const wordForm = document.getElementById('modalWordForm');
    wordForm.action = routeGenerate;
    document.getElementById('modalRegId').value = id;
    document.getElementById('modalNomorSkpi').value = nomorSkpi;

    // Download tersimpan
    const btnSaved = document.getElementById('modalBtnSaved');
    if (isSaved && routeSaved) {
        btnSaved.href = routeSaved;
        btnSaved.style.display = 'flex';
    } else {
        btnSaved.style.display = 'none';
    }

    // Reset ke tab pertama
    document.querySelectorAll('.mtab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.mtab-panel').forEach(p => p.classList.remove('active'));
    document.querySelector('.mtab-btn[data-mtab="mtab-identitas"]').classList.add('active');
    document.getElementById('mtab-identitas').classList.add('active');

    document.getElementById('detailModal').classList.add('open');
}

function closeDetailModal() {
    document.getElementById('detailModal').classList.remove('open');
}

function closeModalOutside(event) {
    if (event.target === document.getElementById('detailModal')) {
        closeDetailModal();
    }
}

// ESC to close
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeDetailModal();
});
</script>
@endpush