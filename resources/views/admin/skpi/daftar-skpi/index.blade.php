@extends('admin.layouts.super-app')

@section('content')
    <div class="dropdown-backdrop" id="dropdownBackdrop"></div>
    <div class="stats-row">
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #29375d, #4E6BA8);">
            <i class="bi bi-card-list"></i>
        </div>
        <div class="stat-info">
            <h5>Total Pendaftar</h5>
            <h3>{{ $stats['total'] }}</h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #FF9800, #FFB347);">
            <i class="bi bi-hourglass-split"></i>
        </div>
        <div class="stat-info">
            <h5>Pending</h5>
            <h3>{{ $stats['pending'] }}</h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #4CAF50, #81C784);">
            <i class="bi bi-check2-circle"></i>
        </div>
        <div class="stat-info">
            <h5>Approved</h5>
            <h3>{{ $stats['approved'] }}</h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #2196F3, #64B5F6);">
            <i class="bi bi-arrow-repeat"></i>
        </div>
        <div class="stat-info">
            <h5>Need Revision</h5>
            <h3>{{ $stats['needs_revision'] }}</h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #F44336, #FF8A80);">
            <i class="bi bi-x-circle"></i>
        </div>
        <div class="stat-info">
            <h5>Rejected</h5>
            <h3>{{ $stats['rejected'] }}</h3>
        </div>
    </div>
</div>

@if(session('success'))
<div class="flash-card flash-success">
    <i class="bi bi-check-circle"></i> {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="flash-card flash-error">
    @foreach($errors->all() as $error)
    <div>{{ $error }}</div>
    @endforeach
</div>
@endif

<div class="content-card">
    <div class="card-header">
        <div>
            <h3>Daftar Pengajuan SKPI</h3>
            <p class="card-subtitle">Review pendaftar SKPI dari mahasiswa, lalu ubah statusnya sesuai hasil verifikasi awal.</p>
        </div>

        <div class="header-actions">
            {{-- Approve Semua --}}
            @if($stats['pending'] > 0)
            <button type="button" class="btn-approve-all" onclick="showApproveAllModal()">
                <i class="bi bi-check2-all"></i>
                Approve Semua Pending
                <span class="pending-count">{{ $stats['pending'] }}</span>
            </button>
            @endif

            <form method="GET" action="{{ route('admin.skpi.daftar-skpi.index') }}" class="filter-form">
                <input type="text" name="search" value="{{ $search }}" class="filter-input" placeholder="Cari nama, NIM, atau prodi">
                <select name="status" class="filter-select">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="needs_revision" {{ $status === 'needs_revision' ? 'selected' : '' }}>Need Revision</option>
                    <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
                <button type="submit" class="btn-filter">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </form>
        </div>
    </div>

    @if($registrations->count() > 0)
    <div class="registration-list">
        @foreach($registrations as $registration)
        <div class="registration-card status-{{ $registration->status }}">
            <div class="registration-head">
                <div>
                    <h4>{{ $registration->nama_lengkap }}</h4>
                    <p>{{ $registration->nim }} • {{ $registration->student->program_studi ?? '-' }} • Angkatan {{ $registration->angkatan }}</p>
                </div>
                <span class="status-badge status-{{ $registration->status }}">
                    {{ match($registration->status) {
                                    'approved'       => 'Approved',
                                    'needs_revision' => 'Need Revision',
                                    'rejected'       => 'Rejected',
                                    default          => 'Pending',
                                } }}
                </span>
            </div>

            <div class="registration-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span>Tempat, Tanggal Lahir</span>
                        <strong>{{ $registration->tempat_lahir }}, {{ $registration->tanggal_lahir?->translatedFormat('d F Y') ?? '-' }}</strong>
                    </div>
                    <div class="info-item">
                        <span>Nomor Ijazah</span>
                        <strong>{{ $registration->nomor_ijazah }}</strong>
                    </div>
                    <div class="info-item">
                        <span>Gelar</span>
                        <strong>{{ $registration->gelar }}</strong>
                    </div>
                    <div class="info-item">
                        <span>Dikirim Pada</span>
                        <strong>{{ $registration->submitted_at?->format('d M Y H:i') ?? '-' }}</strong>
                    </div>
                    <div class="info-item">
                        <span>Direview Oleh</span>
                        <strong>{{ $registration->approver->name ?? 'Belum direview' }}</strong>
                    </div>
                    <div class="info-item">
                        <span>Review Terakhir</span>
                        <strong>{{ $registration->approved_at?->format('d M Y H:i') ?? '-' }}</strong>
                    </div>
                </div>

                {{-- Indikator Kelengkapan Prasyarat --}}
                @php
                $s = $registration->student;
                $hasIpkSks = filled($s->ipk) && filled($s->sks);
                $hasFinalProject = filled(optional($s->finalProject)->title);
                $hasFoto = filled($s->foto);
                $hasTtd = filled($s->ttd);
                $allReady = $hasIpkSks && $hasFinalProject && $hasFoto && $hasTtd;
                @endphp
                <div class="prereq-row">
                    <span class="prereq-badge {{ $hasIpkSks ? 'complete' : 'incomplete' }}">
                        <i class="bi {{ $hasIpkSks ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"></i>
                        IPK/SKS
                    </span>
                    <span class="prereq-badge {{ $hasFinalProject ? 'complete' : 'incomplete' }}">
                        <i class="bi {{ $hasFinalProject ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"></i>
                        Tugas Akhir
                    </span>
                    <span class="prereq-badge {{ $hasFoto ? 'complete' : 'incomplete' }}">
                        <i class="bi {{ $hasFoto ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"></i>
                        Foto
                    </span>
                    <span class="prereq-badge {{ $hasTtd ? 'complete' : 'incomplete' }}">
                        <i class="bi {{ $hasTtd ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"></i>
                        Tanda Tangan
                    </span>
                    @if($allReady)
                    <span class="prereq-summary ready">
                        <i class="bi bi-shield-check"></i> Siap Approve
                    </span>
                    @else
                    <span class="prereq-summary not-ready">
                        <i class="bi bi-shield-exclamation"></i> Data Belum Lengkap
                    </span>
                    @endif
                </div>

                @if($registration->approval_notes)
                <div class="notes-box">
                    <span>Catatan Review</span>
                    <p>{{ $registration->approval_notes }}</p>
                </div>
                @endif

                {{-- Tombol aksi: tampil sesuai status --}}
                @if($registration->status === 'approved')
                {{-- Sudah approved: tampilkan dropdown ubah status --}}
                <div class="action-buttons">
                    <div class="approved-info">
                        <i class="bi bi-check-circle-fill"></i>
                        Sudah disetujui
                    </div>
                    <div class="dropdown-wrapper">
                        <button type="button" class="btn-change-status" onclick="toggleDropdown({{ $registration->id }})">
                            <i class="bi bi-pencil-square"></i> Ubah Status
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu" id="dropdown-{{ $registration->id }}">
                            <button type="button" class="dropdown-item item-revision"
                                onclick="closeDropdown({{ $registration->id }}); showRevisionModal({{ $registration->id }})">
                                <i class="bi bi-arrow-repeat"></i> Set Need Revision
                            </button>
                            <button type="button" class="dropdown-item item-reject"
                                onclick="closeDropdown({{ $registration->id }}); showRejectModal({{ $registration->id }})">
                                <i class="bi bi-x-circle"></i> Reject
                            </button>
                            <button type="button" class="dropdown-item d-md-none text-muted"
                                    onclick="closeDropdown({{ $registration->id }})" 
                                    style="margin-top: 10px; border-top: 1px solid #eee; justify-content: center; font-weight: 700;">
                                <i class="bi bi-x-lg"></i> Batalkan
                            </button>
                        </div>
                    </div>
                </div>
                @else
                {{-- Belum approved: tampilkan tombol aksi lengkap --}}
                <div class="action-buttons">
                    <button type="button" class="btn-action btn-approve"
                        onclick="showApproveModal({{ $registration->id }})">
                        <i class="bi bi-check-circle"></i> Approve
                    </button>
                    <button type="button" class="btn-action btn-revision"
                        onclick="showRevisionModal({{ $registration->id }})">
                        <i class="bi bi-arrow-repeat"></i> Need Revision
                    </button>
                    <button type="button" class="btn-action btn-reject"
                        onclick="showRejectModal({{ $registration->id }})">
                        <i class="bi bi-x-circle"></i> Reject
                    </button>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <div class="pagination-wrapper">
        <div class="pagination-info">
            Menampilkan {{ $registrations->firstItem() }}–{{ $registrations->lastItem() }} dari {{ $registrations->total() }} pendaftar
        </div>
        {{ $registrations->links('pagination::bootstrap-5') }}
    </div>
    @else
    <div class="empty-state">
        <i class="bi bi-inbox"></i>
        <p>Belum ada pengajuan SKPI dari mahasiswa.</p>
    </div>
    @endif
</div>

{{-- Modal Approve --}}
<div id="approveModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-head">
            <div class="modal-icon icon-approve"><i class="bi bi-check-circle"></i></div>
            <div>
                <h4>Approve Pendaftaran SKPI</h4>
                <p>Mahasiswa akan mendapat notifikasi dan SKPI siap diunduh.</p>
            </div>
        </div>
        <form id="approveForm" method="POST">
            @csrf
            <div class="form-group">
                <label>Catatan Approval <span class="optional">(Opsional)</span></label>
                <textarea name="approval_notes" class="form-control" rows="3"
                    placeholder="Tambahkan catatan jika diperlukan..."></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeApproveModal()">Batal</button>
                <button type="submit" class="btn-action btn-approve">
                    <i class="bi bi-check-circle"></i> Approve
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Need Revision --}}
<div id="revisionModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-head">
            <div class="modal-icon icon-revision"><i class="bi bi-arrow-repeat"></i></div>
            <div>
                <h4>Set Need Revision</h4>
                <p>Mahasiswa akan diminta memperbaiki data pengajuan SKPI.</p>
            </div>
        </div>
        <form id="revisionForm" method="POST">
            @csrf
            <div class="form-group">
                <label>Catatan Revisi <span class="required">*</span></label>
                <textarea name="approval_notes" class="form-control" rows="4" required
                    placeholder="Tulis catatan revisi yang harus diperbaiki mahasiswa..."></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeRevisionModal()">Batal</button>
                <button type="submit" class="btn-action btn-revision">
                    <i class="bi bi-arrow-repeat"></i> Simpan Revisi
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Reject --}}
<div id="rejectModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-head">
            <div class="modal-icon icon-reject"><i class="bi bi-x-circle"></i></div>
            <div>
                <h4>Reject Pendaftaran SKPI</h4>
                <p>Pengajuan SKPI mahasiswa akan ditolak.</p>
            </div>
        </div>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="form-group">
                <label>Alasan Penolakan <span class="required">*</span></label>
                <textarea name="approval_notes" class="form-control" rows="4" required
                    placeholder="Tulis alasan penolakan yang jelas..."></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeRejectModal()">Batal</button>
                <button type="submit" class="btn-action btn-reject">
                    <i class="bi bi-x-circle"></i> Reject
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Approve Semua --}}
<div id="approveAllModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-head">
            <div class="modal-icon icon-approve"><i class="bi bi-check2-all"></i></div>
            <div>
                <h4>Approve Semua Pending</h4>
                <p>Semua <strong>{{ $stats['pending'] }} pengajuan</strong> berstatus pending akan disetujui sekaligus.</p>
            </div>
        </div>
        <form id="approveAllForm" method="POST" action="{{ route('admin.skpi.daftar-skpi.approve-all') }}">
            @csrf
            <div class="form-group">
                <label>Catatan Approval Massal <span class="optional">(Opsional)</span></label>
                <textarea name="approval_notes" class="form-control" rows="3"
                    placeholder="Catatan ini akan diterapkan ke semua pengajuan yang di-approve..."></textarea>
            </div>
            <div class="approve-all-warning">
                <i class="bi bi-exclamation-triangle"></i>
                Tindakan ini tidak bisa dibatalkan sekaligus. Pastikan Anda sudah memeriksa semua pengajuan.
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeApproveAllModal()">Batal</button>
                <button type="submit" class="btn-action btn-approve">
                    <i class="bi bi-check2-all"></i> Approve Semua
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('css')
<style>
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: var(--shadow);
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        color: white;
        font-size: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-info h5 {
        font-size: 13px;
        color: #666;
        margin: 0 0 4px;
    }

    .stat-info h3 {
        font-size: 28px;
        color: #333;
        margin: 0;
    }

    .flash-card {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 18px;
        border-radius: 14px;
        margin-bottom: 16px;
        box-shadow: var(--shadow);
        font-weight: 600;
    }

    .flash-success {
        background: #E8F5E9;
        color: #2E7D32;
    }

    .flash-error {
        background: #FFEBEE;
        color: #C62828;
    }

    .content-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: var(--shadow);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 2px solid #F5F5F5;
        flex-wrap: wrap;
    }

    .card-header h3 {
        margin: 0;
        font-size: 22px;
        color: #333;
    }

    .card-subtitle {
        margin: 8px 0 0;
        color: #777;
        font-size: 14px;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .filter-form {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .filter-input,
    .filter-select,
    .form-control {
        border: 2px solid #E0E0E0;
        border-radius: 12px;
        padding: 10px 14px;
        font-size: 14px;
        font-family: inherit;
    }

    .filter-input {
        min-width: 220px;
    }

    .btn-filter {
        border: none;
        border-radius: 12px;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 600;
        color: white;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        background: linear-gradient(135deg, var(--primary-orange), #FFB347);
        transition: all 0.2s ease;
    }

    /* ── Tombol Approve Semua ──────────────────────────────── */
    .btn-approve-all {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: none;
        border-radius: 12px;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 700;
        color: white;
        background: linear-gradient(135deg, #4CAF50, #66BB6A);
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .btn-approve-all:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 14px rgba(76, 175, 80, 0.4);
    }

    .pending-count {
        background: rgba(255, 255, 255, 0.3);
        border-radius: 999px;
        padding: 2px 8px;
        font-size: 12px;
        font-weight: 700;
    }

    /* ── Registration Card ─────────────────────────────────── */
    .registration-list {
        display: grid;
        gap: 18px;
    }

    .registration-card {
        border-radius: 18px;
        border: 1px solid #F0F0F0;
        background: #FCFCFC;
        overflow: hidden;
    }

    .registration-card.status-approved {
        border-left: 5px solid #4CAF50;
    }

    .registration-card.status-needs_revision {
        border-left: 5px solid #2196F3;
    }

    .registration-card.status-rejected {
        border-left: 5px solid #F44336;
    }

    .registration-card.status-pending {
        border-left: 5px solid #FF9800;
    }

    .registration-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        padding: 20px 20px 16px;
        background: white;
        border-bottom: 1px solid #F4F4F4;
    }

    .registration-head h4 {
        margin: 0 0 6px;
        font-size: 18px;
        color: #333;
    }

    .registration-head p {
        margin: 0;
        font-size: 13px;
        color: #777;
    }

    .status-badge {
        display: inline-flex;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-badge.status-pending {
        background: #FFF3E0;
        color: #E65100;
    }

    .status-badge.status-approved {
        background: #E8F5E9;
        color: #2E7D32;
    }

    .status-badge.status-needs_revision {
        background: #E3F2FD;
        color: #1565C0;
    }

    .status-badge.status-rejected {
        background: #FFEBEE;
        color: #C62828;
    }

    .registration-body {
        padding: 20px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 16px;
    }

    .info-item {
        background: white;
        border: 1px solid #F1F1F1;
        border-radius: 14px;
        padding: 14px;
    }

    .info-item span {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #777;
        margin-bottom: 6px;
    }

    .info-item strong {
        font-size: 14px;
        color: #333;
        line-height: 1.5;
    }

    .notes-box {
        padding: 14px 16px;
        border-radius: 14px;
        background: #FFF8E1;
        color: #795548;
        margin-bottom: 16px;
    }

    .notes-box span {
        display: block;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 6px;
    }

    .notes-box p {
        margin: 0;
        line-height: 1.6;
    }

    /* ── Action Buttons ────────────────────────────────────── */
    .action-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
        align-items: center;
        padding-top: 12px;
        border-top: 1px dashed #E0E0E0;
        margin-top: 8px;
    }

    .btn-action {
        border: none;
        border-radius: 12px;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 600;
        color: white;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-action:hover {
        transform: translateY(-2px);
    }

    .btn-approve {
        background: linear-gradient(135deg, #4CAF50, #66BB6A);
    }

    .btn-revision {
        background: linear-gradient(135deg, #2196F3, #64B5F6);
    }

    .btn-reject {
        background: linear-gradient(135deg, #F44336, #FF8A80);
    }

    .btn-approve:hover {
        box-shadow: 0 4px 12px rgba(76, 175, 80, 0.35);
    }

    .btn-revision:hover {
        box-shadow: 0 4px 12px rgba(33, 150, 243, 0.35);
    }

    .btn-reject:hover {
        box-shadow: 0 4px 12px rgba(244, 67, 54, 0.35);
    }

    /* ── Approved info + dropdown ubah status ──────────────── */
    .approved-info {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
        color: #2E7D32;
        background: #E8F5E9;
        padding: 8px 14px;
        border-radius: 999px;
    }

    .dropdown-wrapper {
        position: relative;
    }

    .btn-change-status {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 2px solid #E0E0E0;
        border-radius: 12px;
        padding: 9px 14px;
        font-size: 13px;
        font-weight: 600;
        color: #555;
        background: white;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-change-status:hover {
        border-color: #BDBDBD;
        background: #FAFAFA;
    }

    .dropdown-menu {
        display: none;
        position: absolute;
        right: 0;
        background: white;
        border: 1px solid #E0E0E0;
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        overflow: hidden;
        z-index: 100;
        min-width: 190px;
    }

    .dropdown-menu.open {
        display: block;
    }

    .dropdown-item {
        display: flex;
        align-items: center;
        gap: 8px;
        width: 100%;
        padding: 12px 16px;
        font-size: 13px;
        font-weight: 600;
        border: none;
        background: white;
        cursor: pointer;
        transition: background 0.15s ease;
        text-align: left;
    }

    .dropdown-item:hover {
        background: #F5F5F5;
    }

    .item-revision {
        color: #1565C0;
    }

    .item-reject {
        color: #C62828;
    }

    /* ── Modal ─────────────────────────────────────────────── */
    .modal {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.45);
        z-index: 9999;
        padding: 20px;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background: white;
        border-radius: 20px;
        padding: 28px;
        width: min(100%, 520px);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
    }

    .modal-head {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        margin-bottom: 22px;
    }

    .modal-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        font-size: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: white;
    }

    .icon-approve {
        background: linear-gradient(135deg, #4CAF50, #66BB6A);
    }

    .icon-revision {
        background: linear-gradient(135deg, #2196F3, #64B5F6);
    }

    .icon-reject {
        background: linear-gradient(135deg, #F44336, #FF8A80);
    }

    .modal-head h4 {
        margin: 0 0 4px;
        font-size: 18px;
        color: #333;
    }

    .modal-head p {
        margin: 0;
        font-size: 13px;
        color: #777;
        line-height: 1.5;
    }

    .form-group {
        margin-bottom: 18px;
    }

    .form-group label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #555;
        margin-bottom: 8px;
    }

    .optional {
        font-weight: 400;
        color: #999;
    }

    .required {
        color: #F44336;
    }

    .form-control {
        width: 100%;
        resize: vertical;
        font-family: inherit;
    }

    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }

    .btn-cancel {
        border: none;
        border-radius: 12px;
        padding: 10px 18px;
        background: #EEEEEE;
        color: #555;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s ease;
    }

    .btn-cancel:hover {
        background: #E0E0E0;
    }

    /* ── Approve All Warning ───────────────────────────────── */
    .approve-all-warning {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px 14px;
        border-radius: 12px;
        background: #FFF8E1;
        color: #795548;
        font-size: 13px;
        line-height: 1.5;
        margin-bottom: 18px;
    }

    .approve-all-warning i {
        font-size: 16px;
        flex-shrink: 0;
        margin-top: 1px;
        color: #FF9800;
    }

    /* ── Indikator Kelengkapan Prasyarat ─────────────────── */
    .prereq-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
        margin-bottom: 16px;
        padding: 12px 14px;
        border-radius: 14px;
        background: #FAFAFA;
        border: 1px solid #F0F0F0;
    }

    .prereq-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
    }

    .prereq-badge.complete {
        background: #E8F5E9;
        color: #2E7D32;
    }

    .prereq-badge.incomplete {
        background: #FFF3E0;
        color: #E65100;
    }

    .prereq-summary {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        margin-left: auto;
    }

    .prereq-summary.ready {
        background: #E8F5E9;
        color: #1B5E20;
    }

    .prereq-summary.not-ready {
        background: #FFEBEE;
        color: #B71C1C;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }

    .empty-state i {
        font-size: 56px;
        margin-bottom: 12px;
        display: block;
    }

    .pagination-wrapper {
        margin-top: 24px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        padding: 20px;
        background: #FAFAFA;
        border-radius: 14px;
        border: 1px solid #F0F0F0;
    }

    .pagination-info {
        font-size: 13px;
        color: #777;
        font-weight: 600;
    }

    /* ── Dropdown Backdrop ── */
    .dropdown-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        z-index: 10000;
        backdrop-filter: blur(3px);
        -webkit-backdrop-filter: blur(3px);
    }

    .dropdown-backdrop.show {
        display: block;
    }

    /* ── Mobile Layout Refinements ── */
    @media (max-width: 768px) {
        .stats-row {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 10px;
        }

        .stat-card {
            padding: 12px;
            flex-direction: column;
            text-align: center;
            gap: 5px;
            border-radius: 12px;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            font-size: 16px;
        }

        .stat-info h3 {
            font-size: 20px;
        }

        .card-header {
            flex-direction: column;
            text-align: center;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }

        .header-actions {
            width: 100%;
        }

        .filter-form {
            width: 100%;
            gap: 8px;
        }

        .filter-input, 
        .filter-select, 
        .btn-filter,
        .btn-approve-all {
            width: 100% !important;
            margin: 0;
            padding: 12px !important;
        }

        .registration-card {
            margin-bottom: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .registration-head {
            flex-direction: column;
            align-items: flex-start;
            padding: 15px;
            gap: 10px;
        }

        .registration-head h4 {
            font-size: 16px;
            margin-bottom: 4px;
        }

        .status-badge {
            font-size: 10px;
            padding: 4px 10px;
        }

        .info-grid {
            grid-template-columns: 1fr !important;
            gap: 8px;
        }

        .info-item {
            padding: 10px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: none;
            background: #fdfdfd;
            border-bottom: 1px solid #f5f5f5;
            border-radius: 0;
        }

        .info-item:last-child { border-bottom: none; }

        .info-item span { margin-bottom: 0; font-size: 11px; }
        .info-item strong { font-size: 13px; text-align: right; }

        .prereq-row {
            flex-direction: column;
            padding: 12px;
            gap: 8px;
        }

        .prereq-badge {
            width: 100%;
            padding: 8px 12px;
        }

        .prereq-summary {
            margin-left: 0;
            width: 100%;
            justify-content: center;
            padding: 10px;
            border-radius: 10px;
            font-size: 13px;
        }

        .action-buttons {
            flex-direction: column;
            gap: 8px;
            padding-top: 15px;
        }

        .btn-action, 
        .btn-change-status,
        .approved-info {
            width: 100%;
            justify-content: center;
            padding: 12px !important;
            font-size: 14px;
        }

        /* ── Dropdown as Bottom Sheet ── */
        .dropdown-menu {
            position: fixed !important;
            left: 0 !important;
            right: 0 !important;
            bottom: -100% !important; /* Start hidden */
            top: auto !important;
            width: 100% !important;
            border-radius: 20px 20px 0 0 !important;
            padding: 10px 0 35px 0 !important;
            z-index: 10001 !important;
            box-shadow: 0 -10px 40px rgba(0,0,0,0.25) !important;
            border: none !important;
            display: block !important;
            visibility: hidden;
            transition: bottom 0.3s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.3s !important;
        }

        .dropdown-menu.open {
            bottom: 0 !important;
            visibility: visible;
        }

        .dropdown-item {
            padding: 18px 24px !important;
            font-size: 16px !important;
            border-bottom: 1px solid #f5f5f5;
        }

        .dropdown-item:last-child { border-bottom: none; }

        /* Modal fixes */
        .modal-content {
            padding: 20px;
            border-radius: 15px;
        }

        .modal-actions {
            flex-direction: column;
            gap: 8px;
        }

        .modal-actions button {
            width: 100% !important;
        }
    }

    @media (max-width: 480px) {
        .stats-row {
            grid-template-columns: 1fr;
        }

        .registration-head h4 {
            font-size: 16px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    /* ── Modal individual ──────────────────────────────────── */
    function showApproveModal(registrationId) {
        document.getElementById('approveForm').action =
            `/admin/skpi/daftar-skpi/${registrationId}/approve`;
        document.getElementById('approveModal').style.display = 'flex';
    }

    function showRevisionModal(registrationId) {
        document.getElementById('revisionForm').action =
            `/admin/skpi/daftar-skpi/${registrationId}/revision`;
        document.getElementById('revisionModal').style.display = 'flex';
    }

    function showRejectModal(registrationId) {
        document.getElementById('rejectForm').action =
            `/admin/skpi/daftar-skpi/${registrationId}/reject`;
        document.getElementById('rejectModal').style.display = 'flex';
    }

    function closeApproveModal() {
        document.getElementById('approveModal').style.display = 'none';
    }

    function closeRevisionModal() {
        document.getElementById('revisionModal').style.display = 'none';
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').style.display = 'none';
    }

    /* ── Modal approve semua ───────────────────────────────── */
    function showApproveAllModal() {
        document.getElementById('approveAllModal').style.display = 'flex';
    }

    function closeApproveAllModal() {
        document.getElementById('approveAllModal').style.display = 'none';
    }

    /* ── Dropdown ubah status (untuk approved) ─────────────── */
    function toggleDropdown(id) {
        const menu = document.getElementById(`dropdown-${id}`);
        const btn = menu.previousElementSibling;
        const isOpen = menu.classList.contains('open');
        const backdrop = document.getElementById('dropdownBackdrop');

        // Close all other menus
        document.querySelectorAll('.dropdown-menu.open').forEach(m => m.classList.remove('open'));

        if (!isOpen) {
            if (window.innerWidth > 768) {
                const btnRect = btn.getBoundingClientRect();
                const menuHeight = 100;
                const spaceBelow = window.innerHeight - btnRect.bottom;

                menu.style.position = 'fixed';
                menu.style.right = (window.innerWidth - btnRect.right) + 'px';
                menu.style.width = btnRect.width + 'px';

                if (spaceBelow < menuHeight) {
                    menu.style.top = 'auto';
                    menu.style.bottom = (window.innerHeight - btnRect.top + 6) + 'px';
                } else {
                    menu.style.top = (btnRect.bottom + 6) + 'px';
                    menu.style.bottom = 'auto';
                }
            } else {
                // Mobile: CSS handles positioning, JS handles backdrop
                backdrop.classList.add('show');
                document.body.style.overflow = 'hidden';
            }
            menu.classList.add('open');
        } else {
            closeDropdown(id);
        }
    }

    function closeDropdown(id) {
        const menu = document.getElementById(`dropdown-${id}`);
        if (menu) menu.classList.remove('open');
        
        const backdrop = document.getElementById('dropdownBackdrop');
        if (backdrop) backdrop.classList.remove('show');
        document.body.style.overflow = '';
    }

    // Close on backdrop click (for mobile)
    document.getElementById('dropdownBackdrop')?.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu.open').forEach(m => {
            const id = m.id.replace('dropdown-', '');
            closeDropdown(id);
        });
    });

    // Close on outside click (for desktop)
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.dropdown-wrapper') && window.innerWidth > 768) {
            document.querySelectorAll('.dropdown-menu.open').forEach(m => {
                const id = m.id.replace('dropdown-', '');
                closeDropdown(id);
            });
        }
    });

    // Tutup modal kalau klik backdrop
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) modal.style.display = 'none';
        });
    });
</script>
@endpush