@extends('students.layouts.super-app')

@section('content')
    @if(session('success'))
        <div class="alert-card alert-success">
            <i class="bi bi-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert-card alert-error">
            <i class="bi bi-exclamation-circle"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="preview-hero">
        <div>
            <span class="preview-eyebrow">Preview Draft SKPI</span>
            <h3>{{ $student->nama_lengkap }}</h3>
            <p>Berikut data pemegang SKPI yang sudah tersimpan sebagai pengajuan mahasiswa dan siap ditinjau oleh superuser.</p>
        </div>
        <div class="preview-side">
            <span class="preview-badge {{ $holderMeta['complete'] ? 'complete' : 'draft' }}">
                {{ $holderMeta['complete'] ? 'Lengkap' : 'Draft Belum Lengkap' }}
            </span>
            <span class="status-badge {{ $registrationStatus['badge_class'] }}">{{ $registrationStatus['label'] }}</span>
        </div>
    </div>

    <div class="status-panel">
        <div class="status-item">
            <span>Status</span>
            <strong>{{ $registrationStatus['description'] }}</strong>
        </div>
        <div class="status-item">
            <span>Dikirim Pada</span>
            <strong>{{ $skpiRegistration->submitted_at?->format('d M Y H:i') ?? '-' }}</strong>
        </div>
        <div class="status-item">
            <span>Direview Oleh</span>
            <strong>{{ $skpiRegistration->approver->name ?? 'Belum direview' }}</strong>
        </div>
    </div>

    @if($skpiRegistration->approval_notes)
        <div class="review-alert">
            <i class="bi bi-chat-left-text"></i>
            <div>
                <h5>Catatan Superuser</h5>
                <p>{{ $skpiRegistration->approval_notes }}</p>
            </div>
        </div>
    @endif

    <div class="preview-grid">
        <div class="preview-card">
            <div class="card-head">
                <div>
                    <h4>Informasi Tentang Identitas Diri Pemegang SKPI</h4>
                    <p>Disusun mengikuti field utama pada template SKPI.</p>
                </div>
            </div>

            <div class="field-list">
                @foreach($holderFields as $field)
                    <div class="field-row">
                        <div class="field-label">{{ $field['label'] }}</div>
                        <div class="field-value {{ filled($field['value']) ? '' : 'is-empty' }}">
                            {{ filled($field['value']) ? ($field['display'] ?? $field['value']) : 'Belum diisi' }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="sidebar-card">
            <div class="sidebar-block">
                <h5>Status Kelengkapan</h5>
                <div class="completion-number">{{ $holderMeta['filled_count'] }}/{{ $holderMeta['total_count'] }}</div>
                <p>{{ $holderMeta['complete'] ? 'Semua data pemegang SKPI pada draft ini sudah terisi.' : 'Masih ada field yang belum terisi pada draft ini.' }}</p>
            </div>

            <div class="sidebar-block">
                <h5>Field Yang Masih Kosong</h5>
                @if($holderMeta['missing_fields']->isEmpty())
                    <div class="pill success">Tidak ada field kosong</div>
                @else
                    <div class="pill-list">
                        @foreach($holderMeta['missing_fields'] as $missingField)
                            <span class="pill">{{ $missingField }}</span>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="sidebar-block note-block">
                <h5>Catatan</h5>
                <p>Jika ada data yang perlu diperbaiki, Anda bisa kembali ke form dan simpan ulang pengajuan selama statusnya belum approved.</p>
            </div>
        </div>
    </div>

    <div class="action-bar">
        <a href="{{ route('student.skpi.daftar.index') }}" class="btn btn-muted">Kembali ke Menu Daftar</a>
        @if($canEditRegistration)
            <a href="{{ route('student.skpi.daftar.create') }}" class="btn btn-muted">Edit Pengajuan</a>
        @endif
        <a href="{{ route('student.personal.editDataIndex') }}" class="btn btn-muted">Lengkapi Profil</a>
    </div>
@endsection

@push('css')
<style>
    .preview-hero,
    .preview-card,
    .sidebar-card,
    .status-panel,
    .alert-card,
    .review-alert {
        background: white;
        border-radius: 20px;
        box-shadow: var(--shadow);
    }

    .preview-hero {
        padding: 24px;
        margin-bottom: 24px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 18px;
        background: linear-gradient(135deg, #FFF8EE, #FFFFFF);
    }

    .preview-eyebrow {
        display: inline-flex;
        padding: 5px 12px;
        border-radius: 999px;
        background: #FFF3E0;
        color: #E65100;
        font-size: 11px;
        font-weight: 700;
        margin-bottom: 12px;
    }

    .preview-hero h3,
    .card-head h4,
    .sidebar-block h5 {
        margin: 0 0 8px;
        color: var(--text-dark);
        font-weight: 700;
    }

    .preview-hero p,
    .card-head p,
    .sidebar-block p {
        margin: 0;
        color: #666;
        line-height: 1.7;
        font-size: 14px;
    }

    .preview-badge {
        display: inline-flex;
        padding: 8px 14px;
        border-radius: 999px;
        color: white;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .preview-badge.complete {
        background: linear-gradient(135deg, #4CAF50, #66BB6A);
    }

    .preview-badge.draft {
        background: linear-gradient(135deg, #FF9800, #FF7043);
    }

    .preview-side {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 10px;
    }

    .status-badge {
        display: inline-flex;
        padding: 7px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .status-badge.active {
        background: #E8F5E9;
        color: #2E7D32;
    }

    .status-badge.warning {
        background: #FFF3E0;
        color: #E65100;
    }

    .status-badge.info {
        background: #E3F2FD;
        color: #1565C0;
    }

    .status-badge.danger {
        background: #FFEBEE;
        color: #C62828;
    }

    .status-badge.muted {
        background: #F5F5F5;
        color: #757575;
    }

    .alert-card {
        padding: 16px 18px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 20px;
    }

    .alert-success {
        background: #E8F5E9;
        color: #2E7D32;
    }

    .alert-error {
        background: #FFEBEE;
        color: #C62828;
    }

    .status-panel {
        margin-bottom: 20px;
        padding: 18px;
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .status-item {
        padding: 14px 16px;
        border-radius: 16px;
        background: #FAFAFA;
        border: 1px solid #F1F1F1;
    }

    .status-item span {
        display: block;
        margin-bottom: 6px;
        font-size: 12px;
        color: #777;
    }

    .status-item strong {
        color: var(--text-dark);
        font-size: 14px;
        line-height: 1.6;
    }

    .review-alert {
        margin-bottom: 20px;
        padding: 18px;
        display: flex;
        gap: 12px;
        align-items: flex-start;
        background: #FFF8E1;
    }

    .review-alert i {
        color: #E65100;
        font-size: 18px;
        margin-top: 2px;
    }

    .review-alert h5 {
        margin: 0 0 4px;
        color: #795548;
    }

    .review-alert p {
        margin: 0;
        color: #795548;
        line-height: 1.7;
    }

    .preview-grid {
        display: grid;
        grid-template-columns: 1.4fr 0.8fr;
        gap: 18px;
        margin-bottom: 24px;
    }

    .preview-card,
    .sidebar-card {
        padding: 22px;
    }

    .card-head,
    .sidebar-block {
        margin-bottom: 18px;
    }

    .sidebar-block:last-child {
        margin-bottom: 0;
    }

    .field-list {
        display: grid;
        gap: 12px;
    }

    .field-row {
        display: grid;
        grid-template-columns: 220px 1fr;
        gap: 14px;
        padding: 14px 16px;
        border-radius: 16px;
        background: #FCFCFC;
        border: 1px solid #F2F2F2;
    }

    .field-label {
        font-size: 13px;
        font-weight: 700;
        color: #555;
    }

    .field-value {
        font-size: 14px;
        color: var(--text-dark);
        font-weight: 600;
    }

    .field-value.is-empty {
        color: #C62828;
    }

    .completion-number {
        font-size: 34px;
        font-weight: 800;
        color: #E65100;
        line-height: 1;
        margin-bottom: 8px;
    }

    .pill-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .pill {
        display: inline-flex;
        padding: 6px 12px;
        border-radius: 999px;
        background: #FFF3E0;
        color: #E65100;
        font-size: 12px;
        font-weight: 700;
    }

    .pill.success {
        background: #E8F5E9;
        color: #2E7D32;
    }

    .note-block {
        padding: 16px;
        border-radius: 16px;
        background: #FFF8E1;
    }

    .action-bar {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        flex-wrap: wrap;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 11px 18px;
        border-radius: 14px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
    }

    .btn-muted {
        background: white;
        color: var(--text-dark);
        box-shadow: var(--shadow);
    }

    @media (max-width: 768px) {
        .preview-hero,
        .action-bar,
        .status-panel {
            flex-direction: column;
        }

        .preview-grid,
        .field-row,
        .status-panel {
            grid-template-columns: 1fr;
        }

        .btn {
            width: 100%;
        }

        .preview-side {
            align-items: flex-start;
        }
    }
</style>
@endpush
