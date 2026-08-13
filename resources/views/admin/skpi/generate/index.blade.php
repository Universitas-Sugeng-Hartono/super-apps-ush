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
        <div class="header-actions" style="display: flex; gap: 10px; align-items: center;">
            <button type="button" class="btn-success" id="btnBatchGenerate" title="Generate Semua SKPI" style="background-color: #10b981; color: white; border: none; padding: 9px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                <i class="bi bi-gear-wide-connected"></i> Generate Semua SKPI
            </button>
            @php
                $prodiNameLabel = '';
                if (isset($selectedStudyProgramIdFilter) && $selectedStudyProgramIdFilter) {
                    $found = $studyPrograms->firstWhere('id', $selectedStudyProgramIdFilter);
                    if ($found) {
                        $prodiNameLabel = '(' . $found->name . ')';
                    } else {
                        $prodiNameLabel = '(Prodi)';
                    }
                }
            @endphp
            <button type="button" class="btn-primary" id="btnBulkDocx" title="Unduh ZIP Word">
                <i class="bi bi-file-earmark-word"></i> ZIP Word {{ $prodiNameLabel }}
            </button>
            <button type="button" class="btn-danger" id="btnBulkPdf" title="Unduh ZIP PDF" style="background-color: #ef4444; color: white; border: none; padding: 9px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                <i class="bi bi-file-earmark-pdf"></i> ZIP PDF {{ $prodiNameLabel }}
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

    <div class="search-box">
        <form method="GET" action="{{ route('admin.skpi.generate-skpi.index') }}" class="search-form" id="filterForm" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <div class="search-input-wrapper" style="position: relative; display: flex; align-items: center; min-width: 250px; flex: 1;">
                <i class="bi bi-search" style="position: absolute; left: 12px; color: #9ca3af; font-size: 14px;"></i>
                <input type="text" name="search" id="searchInput" class="filter-input" placeholder="Cari Nama / NIM (otomatis)..." value="{{ request('search') }}" style="padding-left: 36px; padding-right: 12px; height: 38px; border-radius: 8px; border: 1px solid #d1d5db; font-size: 14px; width: 100%; outline: none;" autocomplete="off">
            </div>
            <select name="study_program_id" class="filter-select" onchange="this.form.submit()">
                <option value="">Semua Program Studi</option>
                @foreach($studyPrograms as $sp)
                    <option value="{{ $sp->id }}" {{ ($selectedStudyProgramIdFilter ?? '') == $sp->id ? 'selected' : '' }}>
                        {{ $sp->name }}
                    </option>
                @endforeach
            </select>
            <select name="generate_status" class="filter-select" onchange="this.form.submit()">
                <option value="">Semua Status Generate</option>
                <option value="belum" {{ ($generateStatusFilter ?? '') === 'belum' ? 'selected' : '' }}>Belum Generate</option>
                <option value="sudah" {{ ($generateStatusFilter ?? '') === 'sudah' ? 'selected' : '' }}>Sudah Generate</option>
            </select>
            @if(request('search') || request('study_program_id') || request('generate_status'))
                <a href="{{ route('admin.skpi.generate-skpi.index') }}" class="btn-cancel" style="height: 38px; padding: 0 12px; border-radius: 8px; font-size: 13px; display: inline-flex; align-items: center; gap: 4px; text-decoration: none; color: #6b7280; border: 1px solid #d1d5db; background: #fff;">
                    <i class="bi bi-x-circle"></i> Reset Filter
                </a>
            @endif
        </form>
    </div>

    {{-- Simpan nama prodi terpilih untuk dikirim ke URL ZIP --}}
    <span id="selectedProdiName" data-name="{{ $selectedStudyProgram?->name ?? '' }}" style="display:none;"></span>

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
                    <th>Status Registrasi</th>
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
                        @if($registration->status === 'approved')
                            <span class="status-badge status-tersimpan">Approved</span>
                        @elseif($registration->status === 'pending')
                            <span class="status-badge status-pending">Pending</span>
                        @else
                            <span class="status-badge">{{ ucfirst($registration->status) }}</span>
                        @endif
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
                                data-status-reg="{{ $registration->status }}"
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
        @if(($generateStatusFilter ?? '') === 'belum')
            <p>Tidak ada data mahasiswa yang belum digenerate</p>
        @elseif(($generateStatusFilter ?? '') === 'sudah')
            <p>Tidak ada data mahasiswa yang sudah digenerate</p>
        @else
            <p>Tidak ada data mahasiswa yang disetujui</p>
        @endif
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
                        <span class="detail-label">Status Registrasi</span>
                        <span class="detail-value" id="dStatusReg">-</span>
                    </div>
                    <div class="detail-item full">
                        <span class="detail-label">Status Generate</span>
                        <span class="detail-value" id="dStatus">-</span>
                    </div>
                    <div class="detail-item full" id="dGenAtRow">
                        <span class="detail-label">Generate Pada</span>
                        <span class="detail-value" id="dGenAt">-</span>
                    </div>
                </div>

                <div class="modal-actions" id="modalActionsWrapper">
                    <form method="POST" id="modalWordForm">
                        @csrf
                        <input type="hidden" name="registration_id" id="modalRegId">
                        <input type="hidden" name="nomor_skpi" id="modalNomorSkpi">
                        <button type="submit" class="btn-generate-word" id="btnGenerateWord">
                            <i class="bi bi-file-earmark-word-fill"></i> Generate &amp; Download Word
                        </button>
                    </form>
                    <a href="#" id="modalBtnSaved" class="btn-download-saved" style="display:none;">
                        <i class="bi bi-cloud-arrow-down-fill"></i> Download Tersimpan
                    </a>
                    <div id="pendingWarning" style="display:none;" class="alert-warning-inline">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        Registrasi ini masih <strong>Pending</strong>. Setujui terlebih dahulu sebelum generate SKPI.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('admin/css/skpi-generator.css') }}">
<style>
.skpi-modal-scroll::-webkit-scrollbar {
    width: 6px;
}
.skpi-modal-scroll::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 8px;
}
.skpi-modal-scroll::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 8px;
}
.skpi-modal-scroll::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Automatic Search with Debounce
    const searchInput = document.getElementById('searchInput');
    const filterForm = document.getElementById('filterForm');
    let searchTimeout = null;

    if (searchInput && filterForm) {
        if (searchInput.value) {
            searchInput.focus();
            const valLen = searchInput.value.length;
            searchInput.setSelectionRange(valLen, valLen);
        }

        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function () {
                filterForm.submit();
            }, 400);
        });
    }

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
            const prodiSelect = document.querySelector('select[name="study_program_id"]');
            const genSelect = document.querySelector('select[name="generate_status"]');
            const searchInput = document.querySelector('input[name="search"]');

            const prodiId = prodiSelect?.value || '';
            const genStatus = genSelect?.value || '';
            const searchVal = searchInput?.value.trim() || '';

            let url = "{{ route('admin.skpi.generate-skpi.download-all') }}";
            let params = new URLSearchParams();

            if (prodiId) {
                params.append('study_program_id', prodiId);
                const selectedOption = prodiSelect.options[prodiSelect.selectedIndex];
                if (selectedOption) {
                    params.append('study_program_name', selectedOption.text.trim());
                }
            }

            if (genStatus) {
                params.append('generate_status', genStatus);
            }

            if (searchVal) {
                params.append('search', searchVal);
            }

            const queryString = params.toString();
            if (queryString) {
                url += '?' + queryString;
            }

            window.location.href = url;
        };
    }

    // Bulk ZIP PDF
    const btnBulkPdf = document.getElementById('btnBulkPdf');
    if (btnBulkPdf) {
        btnBulkPdf.onclick = () => {
            const prodiSelect = document.querySelector('select[name="study_program_id"]');
            const genSelect = document.querySelector('select[name="generate_status"]');
            const searchInput = document.querySelector('input[name="search"]');

            const prodiId = prodiSelect?.value || '';
            const genStatus = genSelect?.value || '';
            const searchVal = searchInput?.value.trim() || '';

            let url = "{{ route('admin.skpi.generate-skpi.download-all') }}";
            let params = new URLSearchParams();
            params.append('format', 'pdf');

            if (prodiId) {
                params.append('study_program_id', prodiId);
                const selectedOption = prodiSelect.options[prodiSelect.selectedIndex];
                if (selectedOption) {
                    params.append('study_program_name', selectedOption.text.trim());
                }
            }

            if (genStatus) {
                params.append('generate_status', genStatus);
            }

            if (searchVal) {
                params.append('search', searchVal);
            }

            const queryString = params.toString();
            if (queryString) {
                url += '?' + queryString;
            }

            window.location.href = url;
        };
    }

    // Batch Generate SKPI
    const btnBatchGen = document.getElementById('btnBatchGenerate');
    if (btnBatchGen) {
        btnBatchGen.onclick = () => {
            const doSubmit = () => {
                const prodiSelect = document.querySelector('select[name="study_program_id"]');
                const genSelect = document.querySelector('select[name="generate_status"]');
                const searchInput = document.querySelector('input[name="search"]');

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('admin.skpi.generate-skpi.batch-generate') }}";

                const tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = '_token';
                tokenInput.value = "{{ csrf_token() }}";
                form.appendChild(tokenInput);

                if (prodiSelect && prodiSelect.value) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'study_program_id';
                    input.value = prodiSelect.value;
                    form.appendChild(input);
                }

                if (genSelect && genSelect.value) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'generate_status';
                    input.value = genSelect.value;
                    form.appendChild(input);
                }

                if (searchInput && searchInput.value.trim()) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'search';
                    input.value = searchInput.value.trim();
                    form.appendChild(input);
                }

                document.body.appendChild(form);
                form.submit();
            };

            const buttons = Array.from(document.querySelectorAll('.btn-view'));
            const students = buttons.map(btn => ({
                nama: btn.dataset.nama || 'Mahasiswa',
                nim: btn.dataset.nim || '-',
                prodi: btn.dataset.prodi || '-'
            }));

            if (students.length === 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Tidak Ada Data',
                        text: 'Tidak ada mahasiswa yang sesuai dengan filter saat ini untuk di-generate.',
                        icon: 'warning',
                        confirmButtonColor: '#10b981'
                    });
                } else {
                    alert('Tidak ada mahasiswa yang sesuai dengan filter saat ini untuk di-generate.');
                }
                return;
            }

            const escapeHtml = (str) => {
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            };

            const studentListHtml = students.map((s, idx) => `
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 9px 12px; border-bottom: 1px solid #f1f5f9; ${idx === students.length - 1 ? 'border-bottom: none;' : ''}">
                    <div style="text-align: left; max-width: 65%;">
                        <strong style="color: #1e293b; display: block; font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${idx + 1}. ${escapeHtml(s.nama)}</strong>
                        <span style="font-size: 11.5px; color: #64748b; font-family: monospace;">NIM: ${escapeHtml(s.nim)}</span>
                    </div>
                    <div style="text-align: right;">
                        <span style="background: #f1f5f9; color: #475569; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 6px; display: inline-block;">
                            ${escapeHtml(s.prodi)}
                        </span>
                    </div>
                </div>
            `).join('');

            const modalContent = `
                <div style="text-align: left; font-size: 13.5px; color: #334155;">
                    <p style="margin-bottom: 10px; color: #475569;">
                        Apakah Anda yakin ingin meng-generate dokumen SKPI untuk <strong style="color: #0f172a;">${students.length} mahasiswa</strong> berikut?
                    </p>
                    <div class="skpi-modal-scroll" style="max-height: 220px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 10px; background: #ffffff; box-shadow: inset 0 1px 2px rgba(0,0,0,0.03);">
                        ${studentListHtml}
                    </div>
                </div>
            `;

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Generate SKPI Mahasiswa',
                    html: modalContent,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="bi bi-gear-wide-connected"></i> Ya, Generate (' + students.length + ')',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    customClass: {
                        popup: 'rounded-4 shadow-lg'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        doSubmit();
                    }
                });
            } else {
                if (confirm(`Apakah Anda yakin ingin meng-generate dokumen SKPI untuk ${students.length} mahasiswa?`)) {
                    doSubmit();
                }
            }
        };
    }
});

function openDetailModal(id) {
    const btn = document.querySelector(`.btn-view[data-id="${id}"]`);
    if (!btn) return;

    const nama     = btn.dataset.nama;
    const nim      = btn.dataset.nim;
    const prodi    = btn.dataset.prodi;
    const angkatan = btn.dataset.angkatan;
    const isSaved  = btn.dataset.saved === '1';
    const genAt    = btn.dataset.genat;
    const ttl      = btn.dataset.ttl    || '-';
    const ijazah   = btn.dataset.ijazah || '-';
    const gelar    = btn.dataset.gelar  || '-';
    const statusReg     = btn.dataset.statusReg || '';
    const routeGenerate = btn.dataset.routeGenerate;
    const routeSaved    = btn.dataset.routeSaved;
    const nomorSkpi     = btn.dataset.nomorSkpi || '';
    const isApproved    = statusReg === 'approved';

    // Header
    const initials = nama.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase();
    document.getElementById('modalAvatar').textContent = initials;
    document.getElementById('modalNama').textContent = nama;
    document.getElementById('modalNimProdi').textContent = nim + ' • ' + prodi;

    // Tab Identitas
    document.getElementById('dNama').textContent     = nama;
    document.getElementById('dNim').textContent      = nim;
    document.getElementById('dProdi').textContent    = prodi;
    document.getElementById('dAngkatan').textContent = angkatan;
    document.getElementById('dTtl').textContent      = ttl;
    document.getElementById('dIjazah').textContent   = ijazah;
    document.getElementById('dGelar').textContent    = gelar;

    // Tab Dokumen – status registrasi
    const statusRegEl = document.getElementById('dStatusReg');
    if (isApproved) {
        statusRegEl.innerHTML = '<span class="status-badge status-tersimpan">Approved</span>';
    } else {
        statusRegEl.innerHTML = '<span class="status-badge status-pending">Pending</span>';
    }

    // Tab Dokumen – status generate
    const statusEl = document.getElementById('dStatus');
    statusEl.innerHTML = isSaved
        ? '<span class="status-badge status-tersimpan">Tersimpan</span>'
        : '<span class="status-badge status-belum">Belum Digenerate</span>';

    document.getElementById('dGenAt').textContent = isSaved ? genAt : '-';

    // Form generate & download – hanya tampil jika approved
    const wordForm      = document.getElementById('modalWordForm');
    const btnSaved      = document.getElementById('modalBtnSaved');
    const pendingWarn   = document.getElementById('pendingWarning');

    if (isApproved) {
        wordForm.style.display    = '';
        pendingWarn.style.display = 'none';
        wordForm.action = routeGenerate;
        document.getElementById('modalRegId').value    = id;
        document.getElementById('modalNomorSkpi').value = nomorSkpi;

        if (isSaved && routeSaved) {
            btnSaved.href         = routeSaved;
            btnSaved.style.display = 'flex';
        } else {
            btnSaved.style.display = 'none';
        }
    } else {
        // Pending: sembunyikan form generate & download
        wordForm.style.display    = 'none';
        btnSaved.style.display    = 'none';
        pendingWarn.style.display = 'flex';
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