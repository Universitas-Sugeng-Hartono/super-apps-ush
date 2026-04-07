@extends('students.layouts.super-app')

@section('content')
    <div class="stats-card">
        <div class="stats-header">
            <h3>Status Pendaftaran Seminar Proposal</h3>
        </div>
        <p style="margin-top: 10px; color: #666; font-size: 14px;">
            Berikut status pendaftaran seminar proposal Tugas Akhir Anda.
        </p>
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

    @php
        $docs = $proposal->finalProject?->documents
            ? $proposal->finalProject->documents->where('document_type', 'proposal')->values()
            : collect();
        $hasEditableDocs = $docs->whereIn('review_status', ['needs_revision', 'rejected'])->count() > 0;
    @endphp

    {{-- Banner needs_revision --}}
    @if($hasEditableDocs)
        <div class="alert-revision">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>
                <strong>Ada dokumen yang perlu direvisi</strong>
                <p>Periksa daftar dokumen di bawah dan upload ulang dokumen yang ditandai.</p>
            </div>
        </div>
    @endif

    <div class="form-card">
        <h4>Informasi Sempro</h4>

        <div class="info-row">
            <div class="label">Status</div>
            <div class="value">
                <span class="status-badge {{ $proposal->status === 'approved' ? 'active' : ($proposal->status === 'rejected' ? 'danger' : 'warning') }}">
                    {{ ucfirst($proposal->status) }}
                </span>
            </div>
        </div>

        <div class="info-row">
            <div class="label">Tanggal Daftar</div>
            <div class="value">{{ $proposal->registered_at?->translatedFormat('d M Y H:i') ?? '-' }}</div>
        </div>

        <div class="info-row">
            <div class="label">Jadwal Sempro</div>
            <div class="value">
                {{ $proposal->scheduled_at?->translatedFormat('d M Y H:i') ?? 'Belum dijadwalkan oleh Kaprodi' }}
                @if($proposal->scheduled_at)
                    <div class="muted">Jadwal ini juga akan muncul di menu Calendar.</div>
                @endif
            </div>
        </div>

        <div class="info-row">
            <div class="label">Disetujui Oleh</div>
            <div class="value">{{ $proposal->approver?->name ?? '-' }}</div>
        </div>

        @if($proposal->approval_notes)
            <div class="info-row">
                <div class="label">Catatan</div>
                <div class="value" style="color: #C62828; font-weight: 600;">
                    {{ $proposal->approval_notes }}
                </div>
            </div>
        @endif
    </div>

    <div class="form-card">
        <h4>Dokumen Sempro</h4>
        @if($docs->count() > 0)
            <div class="doc-list">
                @foreach($docs as $d)
                    @php
                        $needsRevision = in_array($d->review_status, ['needs_revision', 'rejected'], true);
                        $isRejectedDoc = $d->review_status === 'rejected';
                    @endphp
                    <div class="doc-item {{ $needsRevision ? 'doc-item-revision' : '' }}">
                        <div class="doc-left">
                            <div class="doc-icon {{ $needsRevision ? 'doc-icon-revision' : '' }}">
                                <i class="bi bi-file-earmark-{{ $needsRevision ? 'x' : 'text' }}"></i>
                            </div>
                            <div class="doc-meta">
                                <div class="doc-title">
                                    {{ $d->title }}
                                    @if($needsRevision)
                                        <span class="badge-revision">{{ $isRejectedDoc ? 'Ditolak' : 'Perlu Revisi' }}</span>
                                    @elseif($d->review_status === 'approved')
                                        <span class="badge-approved">Disetujui</span>
                                    @endif
                                </div>
                                <div class="doc-sub">
                                    Upload: {{ $d->uploaded_at?->translatedFormat('d M Y H:i') ?? '-' }}
                                    · v{{ $d->version }}
                                </div>
                                @if($needsRevision && $d->review_notes)
                                    <div class="doc-revision-note">
                                        <i class="bi bi-chat-left-text"></i>
                                        {{ $d->review_notes }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <a class="doc-link" href="{{ asset('storage/' . ltrim($d->file_path, '/')) }}" target="_blank" rel="noopener">
                            Lihat
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <div class="muted">Belum ada dokumen yang tersimpan.</div>
        @endif
    </div>

    <div class="form-actions">
        <a href="{{ route('student.final-project.index') }}" class="btn-secondary">Kembali</a>

        @if($proposal->status === 'rejected')
            <a href="{{ route('student.final-project.proposal.edit', $proposal->id) }}" class="btn-edit">
                <i class="bi bi-pencil-square"></i> Edit & Ajukan Ulang
            </a>
        @elseif($hasEditableDocs)
            <a href="{{ route('student.final-project.proposal.edit', $proposal->id) }}" class="btn-revision">
                <i class="bi bi-pencil-square"></i> Edit Dokumen
            </a>
        @elseif($proposal->status === 'approved')
            <a href="{{ route('calendar.index') }}" class="btn-primary-soft">Buka Calendar</a>
        @endif
    </div>
@endsection

@push('css')
<style>
    .stats-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        box-shadow: var(--shadow);
        margin-bottom: 20px;
    }
    .stats-header h3 {
        font-size: 20px;
        font-weight: 700;
        margin: 0;
    }
    .alert-success {
        background: #E8F5E9;
        color: #2E7D32;
        padding: 15px 20px;
        border-radius: 15px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
    }
    .alert-danger {
        background: #FFEBEE;
        color: #C62828;
        padding: 15px 20px;
        border-radius: 15px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
    }
    .alert-revision {
        background: #FFF8E1;
        border: 1px solid #FFE082;
        color: #E65100;
        padding: 16px 20px;
        border-radius: 15px;
        margin-bottom: 20px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        font-weight: 600;
    }
    .alert-revision i { font-size: 20px; flex-shrink: 0; margin-top: 2px; }
    .alert-revision p { margin: 4px 0 0; font-size: 13px; font-weight: 500; }
    .form-card {
        background: white;
        border-radius: 20px;
        padding: 25px;
        box-shadow: var(--shadow);
        margin-bottom: 20px;
    }
    .form-card h4 {
        font-size: 16px;
        font-weight: 700;
        margin: 0 0 16px;
        color: var(--primary-orange);
    }
    .info-row {
        display: grid;
        grid-template-columns: 160px 1fr;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #F0F0F0;
    }
    .info-row:last-child { border-bottom: none; }
    .label { color: #666; font-weight: 700; font-size: 13px; }
    .value { color: #333; font-size: 14px; }
    .muted { color: #777; font-size: 12px; font-weight: 600; margin-top: 6px; }
    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
    }
    .status-badge.active  { background: #E8F5E9; color: #2E7D32; }
    .status-badge.warning { background: #FFF3E0; color: #E65100; }
    .status-badge.danger  { background: #FFEBEE; color: #C62828; }
    .doc-list { display: flex; flex-direction: column; gap: 12px; }
    .doc-item {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 14px;
        border-radius: 16px;
        border: 1px solid rgba(0,0,0,0.06);
        background: #fff;
    }
    .doc-item-revision {
        border: 1.5px solid #FFB300;
        background: #FFFDE7;
    }
    .doc-left { display: flex; align-items: flex-start; gap: 12px; min-width: 0; }
    .doc-icon {
        width: 42px; height: 42px; border-radius: 14px;
        background: rgba(255,152,0,0.14);
        display: flex; align-items: center; justify-content: center;
        color: var(--primary-orange);
        font-size: 18px;
        flex: 0 0 auto;
    }
    .doc-icon-revision {
        background: rgba(245,124,0,0.15);
        color: #F57C00;
    }
    .doc-meta { min-width: 0; }
    .doc-title {
        font-weight: 900;
        color: #333;
        font-size: 13px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
    }
    .doc-sub { color: #777; font-size: 12px; font-weight: 600; margin-top: 4px; }
    .doc-revision-note {
        display: flex;
        gap: 6px;
        align-items: flex-start;
        margin-top: 6px;
        font-size: 12px;
        color: #E65100;
        font-weight: 700;
        line-height: 1.5;
    }
    .badge-revision {
        display: inline-block;
        background: #FFF3E0;
        color: #E65100;
        font-size: 10px;
        font-weight: 800;
        padding: 2px 8px;
        border-radius: 8px;
    }
    .badge-approved {
        display: inline-block;
        background: #E8F5E9;
        color: #2E7D32;
        font-size: 10px;
        font-weight: 800;
        padding: 2px 8px;
        border-radius: 8px;
    }
    .doc-link { color: var(--primary-orange); font-weight: 900; text-decoration: none; white-space: nowrap; }
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 10px;
        flex-wrap: wrap;
    }
    .btn-secondary {
        background: #E0E0E0;
        color: #666;
        padding: 12px 20px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 700;
    }
    .btn-primary-soft {
        background: linear-gradient(135deg, var(--primary-orange), #FFB347);
        color: white;
        padding: 12px 20px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 900;
        box-shadow: 0 10px 26px rgba(255,152,0,0.22);
    }
    .btn-edit {
        background: linear-gradient(135deg, #C62828, #EF5350);
        color: white;
        padding: 12px 20px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 900;
        box-shadow: 0 10px 26px rgba(198,40,40,0.22);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .btn-revision {
        background: linear-gradient(135deg, #F57C00, #FFB300);
        color: white;
        padding: 12px 20px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 900;
        box-shadow: 0 10px 26px rgba(245,124,0,0.22);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    @media (max-width: 768px) {
        .info-row { grid-template-columns: 1fr; }
    }
</style>
@endpush
