@extends('students.layouts.super-app')

@section('content')
    <div class="stats-card">
        <div class="stats-header">
            <h3>Status Pendaftaran Sidang TA</h3>
        </div>
        <p style="margin-top: 10px; color: #666; font-size: 14px;">
            Berikut status pendaftaran sidang Tugas Akhir Anda.
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

    @if($defense->status === 'rejected')
        <div class="alert-rejected">
            <i class="bi bi-x-circle-fill"></i>
            <div>
                <div class="alert-title">Pendaftaran Sidang Ditolak</div>
                <div class="alert-body">
                    {{ $defense->approval_notes ?? 'Tidak ada catatan.' }}
                </div>
            </div>
        </div>
    @endif

    <div class="form-card">
        <h4>Informasi Sidang</h4>

        <div class="info-row">
            <div class="label">Status</div>
            <div class="value">
                <span class="status-badge {{ $defense->status === 'approved' ? 'active' : ($defense->status === 'rejected' ? 'danger' : 'warning') }}">
                    {{ ucfirst($defense->status) }}
                </span>
            </div>
        </div>

        <div class="info-row">
            <div class="label">Tanggal Daftar</div>
            <div class="value">{{ $defense->registered_at?->translatedFormat('d M Y H:i') ?? '-' }}</div>
        </div>

        <div class="info-row">
            <div class="label">Jadwal Sidang</div>
            <div class="value">
                {{ $defense->scheduled_at?->translatedFormat('d M Y H:i') ?? 'Belum dijadwalkan oleh Kaprodi' }}
            </div>
        </div>

        @if($defense->approval_notes && $defense->status !== 'rejected')
            <div class="info-row">
                <div class="label">Catatan</div>
                <div class="value">{{ $defense->approval_notes }}</div>
            </div>
        @endif
    </div>

    {{-- Dokumen --}}
    @php
        $draftDoc = $defense->finalProject->documents
            ->where('document_type', 'final')
            ->where('title', 'Draft Final TA')
            ->sortByDesc('version')
            ->first();
        $canEditDocument = $draftDoc && in_array($draftDoc->review_status, ['needs_revision', 'rejected'], true);
    @endphp

    @if($draftDoc)
        <div class="form-card">
            <h4>Dokumen Sidang</h4>
            <div class="doc-item">
                <div class="doc-left">
                    <div class="doc-icon">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <div class="doc-meta">
                        <div class="doc-title">Draft Final TA</div>
                        <div class="doc-sub">
                            Upload: {{ $draftDoc->uploaded_at?->translatedFormat('d M Y H:i') ?? '-' }}
                            · v{{ $draftDoc->version }}
                        </div>
                    </div>
                </div>
                <a class="doc-link" href="{{ asset('storage/' . ltrim($draftDoc->file_path, '/')) }}"
                   target="_blank">Lihat</a>
            </div>
        </div>
    @endif

    <div class="form-actions">

        <a href="{{ route('student.final-project.index') }}" class="btn-secondary">Kembali</a>

        @if($defense->status === 'rejected' || $canEditDocument)
            <a href="{{ route('student.final-project.defense.edit', $defense->id) }}" class="btn-edit">
                <i class="bi bi-pencil-square"></i> {{ $defense->status === 'rejected' ? 'Edit & Ajukan Ulang' : 'Edit Dokumen' }}
            </a>
         @elseif($defense->status === 'approved')
            <a href="{{ route('calendar.index') }}" class="btn-calendar">
                <i class="bi bi-calendar-check"></i> Lihat Jadwal di Kalender
            </a>
        @endif
    </div>
@endsection

@push('css')
<style>
    .btn-calendar {
        background: linear-gradient(135deg, var(--primary-orange), #FFB347);
        color: white; padding: 12px 20px; border-radius: 12px;
        text-decoration: none; font-weight: 900;
        box-shadow: 0 10px 26px rgba(255,152,0,0.22);
        display: flex; align-items: center; gap: 8px;
    }
    .btn-calendar:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 30px rgba(255,152,0,0.35);
    }
    .stats-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        box-shadow: var(--shadow);
        margin-bottom: 20px;
    }
    .stats-header h3 { font-size: 20px; font-weight: 700; margin: 0; }
    .alert-success {
        background: #E8F5E9; color: #2E7D32;
        padding: 15px 20px; border-radius: 15px;
        margin-bottom: 20px; display: flex;
        align-items: center; gap: 10px; font-weight: 600;
    }
    .alert-danger {
        background: #FFEBEE; color: #C62828;
        padding: 15px 20px; border-radius: 15px;
        margin-bottom: 20px; display: flex;
        align-items: center; gap: 10px; font-weight: 600;
    }
    .alert-rejected {
        display: flex; gap: 14px; align-items: flex-start;
        background: #FFEBEE; border: 1px solid #FFCDD2;
        color: #C62828; padding: 18px 20px;
        border-radius: 16px; margin-bottom: 20px;
    }
    .alert-rejected i { font-size: 22px; flex-shrink: 0; margin-top: 2px; }
    .alert-title { font-weight: 800; font-size: 14px; margin-bottom: 4px; }
    .alert-body { font-size: 13px; line-height: 1.6; }
    .form-card {
        background: white; border-radius: 20px;
        padding: 25px; box-shadow: var(--shadow); margin-bottom: 20px;
    }
    .form-card h4 {
        font-size: 16px; font-weight: 700;
        margin: 0 0 16px; color: var(--primary-orange);
    }
    .info-row {
        display: grid; grid-template-columns: 160px 1fr;
        gap: 12px; padding: 12px 0;
        border-bottom: 1px solid #F0F0F0;
    }
    .info-row:last-child { border-bottom: none; }
    .label { color: #666; font-weight: 700; font-size: 13px; }
    .value { color: #333; font-size: 14px; }
    .status-badge {
        display: inline-block; padding: 6px 12px;
        border-radius: 999px; font-size: 12px; font-weight: 800;
    }
    .status-badge.active  { background: #E8F5E9; color: #2E7D32; }
    .status-badge.warning { background: #FFF3E0; color: #E65100; }
    .status-badge.danger  { background: #FFEBEE; color: #C62828; }
    .doc-item {
        display: flex; align-items: center;
        justify-content: space-between; gap: 12px;
        padding: 12px 14px; border-radius: 16px;
        border: 1px solid rgba(0,0,0,0.06);
    }
    .doc-left { display: flex; align-items: center; gap: 12px; }
    .doc-icon {
        width: 42px; height: 42px; border-radius: 14px;
        background: rgba(255,152,0,0.14);
        display: flex; align-items: center; justify-content: center;
        color: var(--primary-orange); font-size: 18px;
    }
    .doc-title { font-weight: 900; color: #333; font-size: 13px; }
    .doc-sub { color: #777; font-size: 12px; font-weight: 600; margin-top: 4px; }
    .doc-link { color: var(--primary-orange); font-weight: 900; text-decoration: none; }
    .form-actions {
        display: flex; justify-content: flex-end;
        gap: 10px; margin-top: 10px; flex-wrap: wrap;
    }
    .btn-secondary {
        background: #E0E0E0; color: #666;
        padding: 12px 20px; border-radius: 12px;
        text-decoration: none; font-weight: 700;
    }
    .btn-edit {
        background: linear-gradient(135deg, #C62828, #EF5350);
        color: white; padding: 12px 20px; border-radius: 12px;
        text-decoration: none; font-weight: 900;
        box-shadow: 0 10px 26px rgba(198,40,40,0.22);
        display: flex; align-items: center; gap: 8px;
    }
    @media (max-width: 768px) {
        .info-row { grid-template-columns: 1fr; }
    }
</style>
@endpush
