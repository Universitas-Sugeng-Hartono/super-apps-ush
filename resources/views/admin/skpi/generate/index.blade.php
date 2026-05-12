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
<style>
    /* === BASE CARD === */
    .content-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: var(--shadow);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #F5F5F5;
    }

    .card-header h3 {
        font-size: 20px;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: #F5F5F5;
        color: #555;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
        border: 1.5px solid #E0E0E0;
    }

    .btn-back:hover {
        background: #FFEBEE;
        color: #C62828;
        border-color: #FFCDD2;
        transform: translateX(-2px);
    }

    /* === ALERTS === */
    .alert-success {
        background: #E8F5E9; color: #2E7D32;
        padding: 15px 20px; border-radius: 12px;
        margin-bottom: 20px; display: flex; align-items: center; gap: 10px;
    }
    .alert-danger {
        background: #FFEBEE; color: #C62828;
        padding: 15px 20px; border-radius: 12px;
        margin-bottom: 20px; display: flex; align-items: center; gap: 10px;
    }

    /* === BUTTONS === */
    .btn-primary {
        background: linear-gradient(135deg, var(--primary-orange), #FFB347);
        color: white; padding: 10px 20px; border-radius: 10px;
        text-decoration: none; font-size: 14px; font-weight: 600;
        display: inline-flex; align-items: center; gap: 8px;
        transition: all 0.3s; border: none; cursor: pointer;
    }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255,112,67,0.4); }

    /* === SEARCH / FILTER === */
    .search-box { margin-bottom: 20px; }
    .search-form { display: flex; gap: 10px; flex-wrap: wrap; }
    .filter-select {
        padding: 12px 15px; border: 2px solid #E0E0E0;
        border-radius: 10px; font-size: 14px; background: white;
        min-width: 200px; cursor: pointer;
    }
    .filter-select:focus { outline: none; border-color: var(--primary-orange); }

    /* === TABLE === */
    .table-responsive { overflow-x: auto; }
    .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .data-table th {
        padding: 12px; text-align: left;
        background: #F5F5F5; font-weight: 600; color: #333; font-size: 13px;
    }
    .data-table td { padding: 15px 12px; border-bottom: 1px solid #E0E0E0; vertical-align: middle; }
    .data-table tr:hover { background: #F9F9F9; }

    .font-monospace { font-family: 'Courier New', monospace; color: #666; }
    .text-muted-sm { font-size: 12px; color: #999; }

    /* === BADGES === */
    .badge-year {
        background: #E8F5E9; color: #2E7D32;
        padding: 5px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;
    }
    .badge-prodi {
        background: #E3F2FD; color: #1976D2;
        padding: 5px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;
    }
    .status-badge {
        padding: 5px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;
    }
    .status-badge.status-tersimpan { background: #DCFCE7; color: #166534; }
    .status-badge.status-belum { background: #FEF3C7; color: #92400E; }

    /* === ACTION BUTTONS === */
    .action-buttons { display: flex; gap: 8px; flex-wrap: wrap; }
    .btn-view {
        padding: 6px 12px; background: #2196F3; color: white;
        border-radius: 6px; border: none; font-size: 12px; cursor: pointer;
        transition: all 0.3s; display: inline-flex; align-items: center; gap: 5px;
    }
    .btn-view:hover { background: #1976D2; transform: translateY(-2px); }

    /* === PAGINATION === */
    .pagination-wrapper {
        margin-top: 30px; display: flex;
        justify-content: center; align-items: center;
    }
    .pagination-wrapper .pagination {
        display: flex !important; list-style: none !important;
        padding: 0 !important; margin: 0 !important; gap: 8px !important;
        flex-wrap: wrap !important; justify-content: center !important;
    }
    .pagination-wrapper .pagination .page-link {
        padding: 10px 16px !important; border: 2px solid #E0E0E0 !important;
        border-radius: 10px !important; color: #666 !important;
        background: white !important; transition: all 0.3s !important;
        font-size: 14px !important; min-width: 44px !important;
        display: inline-flex !important; align-items: center !important; justify-content: center !important;
    }
    .pagination-wrapper .pagination .page-link:hover {
        background: var(--primary-orange) !important; color: white !important;
        border-color: var(--primary-orange) !important; transform: translateY(-2px) !important;
    }
    .pagination-wrapper .pagination .page-item.active .page-link {
        background: var(--primary-orange) !important; color: white !important;
        border-color: var(--primary-orange) !important;
    }
    .pagination-wrapper .pagination .page-item.disabled .page-link {
        opacity: 0.6 !important; cursor: not-allowed !important;
    }

    /* === EMPTY STATE === */
    .empty-state { text-align: center; padding: 80px 20px; }
    .empty-state i { font-size: 80px; color: #E0E0E0; margin-bottom: 20px; }
    .empty-state p { color: #999; font-size: 16px; }

    /* ===================== MODAL ===================== */
    .modal-overlay {
        display: none; position: fixed;
        inset: 0; background: rgba(0,0,0,0.45);
        z-index: 9999; align-items: center; justify-content: center;
        padding: 20px;
    }
    .modal-overlay.open { display: flex; }

    .modal-box {
        background: white; border-radius: 20px;
        width: 100%; max-width: 620px;
        box-shadow: 0 25px 60px rgba(0,0,0,0.2);
        animation: modalIn 0.3s ease;
        display: flex; flex-direction: column;
        max-height: 90vh; overflow: hidden;
    }

    @keyframes modalIn {
        from { opacity: 0; transform: translateY(30px) scale(0.97); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    .modal-header {
        display: flex; justify-content: space-between; align-items: center;
        padding: 20px 24px; border-bottom: 1px solid #F0F0F0;
        background: linear-gradient(135deg, #f8fafc, #fff);
    }
    .modal-profile { display: flex; align-items: center; gap: 14px; }
    .modal-avatar {
        width: 48px; height: 48px; border-radius: 12px;
        background: linear-gradient(135deg, var(--primary-orange), #FFB347);
        color: white; font-weight: 800; font-size: 16px;
        display: flex; align-items: center; justify-content: center;
    }
    .modal-profile h2 { margin: 0; font-size: 17px; font-weight: 700; }
    .modal-profile p { margin: 0; font-size: 12px; color: #999; }
    .modal-close {
        width: 36px; height: 36px; border: none; background: #F5F5F5;
        border-radius: 10px; cursor: pointer; font-size: 14px; color: #666;
        transition: all 0.2s; display: flex; align-items: center; justify-content: center;
    }
    .modal-close:hover { background: #FFEBEE; color: #C62828; }

    .modal-body { overflow-y: auto; flex: 1; }

    /* Modal Tabs */
    .modal-tabs {
        display: flex; gap: 0; padding: 0 24px;
        border-bottom: 2px solid #F0F0F0;
    }
    .mtab-btn {
        border: none; background: none;
        padding: 14px 20px; font-size: 13px; font-weight: 700;
        color: #999; cursor: pointer; position: relative;
        transition: color 0.2s;
    }
    .mtab-btn.active { color: var(--primary-orange); }
    .mtab-btn.active::after {
        content: ''; position: absolute; bottom: -2px; left: 0;
        width: 100%; height: 2px;
        background: var(--primary-orange); border-radius: 2px 2px 0 0;
    }

    .mtab-panel { display: none; padding: 24px; }
    .mtab-panel.active { display: block; animation: panelIn 0.25s ease; }

    @keyframes panelIn {
        from { opacity: 0; transform: translateY(8px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* Detail Grid */
    .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .detail-item { display: flex; flex-direction: column; gap: 4px; }
    .detail-item.full { grid-column: span 2; }
    .detail-label {
        font-size: 11px; font-weight: 700; color: #999;
        text-transform: uppercase; letter-spacing: 0.5px;
    }
    .detail-value {
        font-size: 14px; font-weight: 600; color: #1E293B;
        background: #F8FAFC; padding: 10px 14px;
        border-radius: 10px; border: 1px solid #E2E8F0;
    }

    /* Modal Actions */
    .modal-actions { margin-top: 24px; display: flex; flex-direction: column; gap: 10px; }
    .btn-generate-word {
        width: 100%; padding: 14px; border: none; border-radius: 12px;
        background: #2B5797; color: #fff; font-weight: 700; font-size: 14px;
        display: flex; align-items: center; justify-content: center; gap: 10px;
        cursor: pointer; transition: all 0.3s;
        box-shadow: 0 6px 20px rgba(43,87,151,0.2);
    }
    .btn-generate-word:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(43,87,151,0.3); }
    .btn-download-saved {
        display: flex; align-items: center; justify-content: center; gap: 10px;
        width: 100%; padding: 12px 16px; border-radius: 12px;
        background: #DCFCE7; color: #166534; font-weight: 700; font-size: 14px;
        text-decoration: none; transition: all 0.2s; border: 1.5px solid #BBF7D0;
    }
    .btn-download-saved:hover { background: #BBF7D0; transform: translateY(-1px); }
</style>
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