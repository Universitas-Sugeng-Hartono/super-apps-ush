@extends('admin.layouts.super-app')

@section('content')
    <div class="content-card">
        <div class="card-header">
            <div>
                <h3>Detail Tugas Akhir</h3>
                <p style="margin: 4px 0 0; font-size: 13px; color: #777;">
                    {{ $finalProject->student->nama_lengkap }} ({{ $finalProject->student->nim }})
                </p>
            </div>
            <a href="{{ route('admin.final-project.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="detail-grid">
            <div class="detail-section">
                <h4>Informasi Mahasiswa</h4>
                <div class="detail-row">
                    <span class="label">Nama</span>
                    <span class="value">{{ $finalProject->student->nama_lengkap }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">NIM</span>
                    <span class="value">{{ $finalProject->student->nim }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Program Studi</span>
                    <span class="value">{{ $finalProject->student->program_studi }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Angkatan</span>
                    <span class="value">{{ $finalProject->student->angkatan }}</span>
                </div>
            </div>

            <div class="detail-section">
                <h4>Informasi Tugas Akhir</h4>
                <div class="detail-row">
                    <span class="label">Judul (ID)</span>
                    <span class="value" style="font-weight: 600;">{{ $finalProject->title ?? '-' }}</span>
                </div>
                @if($finalProject->title && $finalProject->title_en)
                <div class="detail-row">
                    <span class="label">Judul (EN)</span>
                    <span class="value" style="font-style: italic; color: #666;">{{ $finalProject->title_en }}</span>
                </div>
                @endif
                <div class="detail-row">
                    <span class="label">Status</span>
                    <span class="value">
                        <span class="status-badge status-{{ $finalProject->status }}">
                            {{ ucfirst($finalProject->status) }}
                        </span>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="label">Progress</span>
                    <span class="value">
                        <div class="progress-mini">
                            <div class="progress-fill" style="width: {{ $finalProject->progress_percentage }}%"></div>
                        </div>
                        <small>{{ $finalProject->progress_percentage }}%</small>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="label">Mulai</span>
                    <span class="value">
                        {{ optional($finalProject->started_at)->format('d M Y') ?? '-' }}
                    </span>
                </div>
                <div class="detail-row">
                    <span class="label">Selesai</span>
                    <span class="value">
                        {{ optional($finalProject->completed_at)->format('d M Y') ?? '-' }}
                    </span>
                </div>
            </div>

            <div class="detail-section">
                <h4>Pembimbing</h4>
                <div class="detail-row">
                    <span class="label">Pembimbing 1</span>
                    <span class="value">
                        {{ $finalProject->supervisor1?->name ?? '-' }}
                    </span>
                </div>
                <div class="detail-row">
                    <span class="label">Pembimbing 2</span>
                    <span class="value">
                        {{ $finalProject->supervisor2?->name ?? '-' }}
                    </span>
                </div>
            </div>

            <div class="detail-section">
                <h4>Proposal</h4>
                @if($finalProject->proposal)
                    <div class="detail-row">
                        <span class="label">Status</span>
                        <span class="value">
                            <span class="status-badge status-{{ $finalProject->proposal->status }}">
                                {{ ucfirst($finalProject->proposal->status) }}
                            </span>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Daftar</span>
                        <span class="value">
                            {{ optional($finalProject->proposal->registered_at)->format('d M Y') ?? '-' }}
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Jadwal</span>
                        <span class="value">
                            {{ optional($finalProject->proposal->scheduled_at)->format('d M Y') ?? '-' }}
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Nilai</span>
                        <span class="value">{{ $finalProject->proposal->grade ?? '-' }}</span>
                    </div>
                @else
                    <p class="empty-text">Belum ada data proposal.</p>
                @endif
            </div>

            <div class="detail-section">
                <h4>Sidang</h4>
                @if($finalProject->defense)
                    <div class="detail-row">
                        <span class="label">Status</span>
                        <span class="value">
                            <span class="status-badge status-{{ $finalProject->defense->status }}">
                                {{ ucfirst($finalProject->defense->status) }}
                            </span>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Daftar</span>
                        <span class="value">
                            {{ optional($finalProject->defense->registered_at)->format('d M Y') ?? '-' }}
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Jadwal</span>
                        <span class="value">
                            {{ optional($finalProject->defense->scheduled_at)->format('d M Y') ?? '-' }}
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Nilai Akhir</span>
                        <span class="value">{{ $finalProject->defense->final_grade ?? '-' }}</span>
                    </div>
                @else
                    <p class="empty-text">Belum ada data sidang.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="content-card" style="margin-top: 20px;">
        <div class="card-header">
            <h3>Log Bimbingan</h3>
        </div>

        @if($finalProject->guidanceLogs->count() > 0)
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Pembimbing</th>
                            <th>Materi</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($finalProject->guidanceLogs as $log)
                            <tr>
                                <td>{{ optional($log->guidance_date)->format('d M Y') ?? '-' }}</td>
                                <td>{{ $log->supervisor?->name ?? '-' }}</td>
                                <td>{{ $log->materials_discussed }}</td>
                                <td>
                                    <span class="status-badge status-{{ $log->status }}">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <p>Belum ada log bimbingan.</p>
            </div>
        @endif
    </div>

    <div class="content-card" style="margin-top: 20px;">
        <div class="card-header">
            <h3>Dokumen</h3>
        </div>

        @if($finalProject->documents->count() > 0)
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Jenis</th>
                            <th>Judul</th>
                            <th>Versi</th>
                            <th>Status Review</th>
                            <th>Diunggah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($finalProject->documents as $doc)
                            <tr>
                                <td>{{ ucfirst(str_replace('_', ' ', $doc->document_type)) }}</td>
                                <td>{{ $doc->title }}</td>
                                <td>v{{ $doc->version }}</td>
                                <td>
                                    <span class="status-badge status-{{ $doc->review_status }}">
                                        {{ ucfirst(str_replace('_', ' ', $doc->review_status)) }}
                                    </span>
                                </td>
                                <td>{{ optional($doc->uploaded_at)->format('d M Y') ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <p>Belum ada dokumen tugas akhir.</p>
            </div>
        @endif
    </div>
@endsection

@push('css')
<style>
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
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #F5F5F5;
    }
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 20px;
    }
    .detail-section {
        background: #FAFAFA;
        border-radius: 12px;
        padding: 16px 18px;
    }
    .detail-section h4 {
        font-size: 15px;
        font-weight: 600;
        margin: 0 0 12px;
        color: var(--primary-orange);
    }
    .detail-row {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        padding: 6px 0;
        border-bottom: 1px dashed #E0E0E0;
        font-size: 13px;
    }
    .detail-row:last-child {
        border-bottom: none;
    }
    .detail-row .label {
        color: #777;
        min-width: 110px;
    }
    .detail-row .value {
        font-weight: 500;
        text-align: right;
    }
    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 600;
    }
    .status-badge.status-proposal { background: #E1BEE7; color: #6A1B9A; }
    .status-badge.status-research { background: #BBDEFB; color: #1565C0; }
    .status-badge.status-defense { background: #FFCCBC; color: #D84315; }
    .status-badge.status-completed { background: #C8E6C9; color: #2E7D32; }
    .status-badge.status-pending { background: #FFF9C4; color: #F9A825; }
    .status-badge.status-approved { background: #C8E6C9; color: #2E7D32; }
    .status-badge.status-rejected,
    .status-badge.status-needs_revision { background: #FFCDD2; color: #C62828; }
    .progress-mini {
        width: 140px;
        height: 8px;
        background: #E0E0E0;
        border-radius: 10px;
        overflow: hidden;
        display: inline-block;
        vertical-align: middle;
        margin-right: 6px;
    }
    .progress-fill {
        height: 100%;
        background: linear-gradient(135deg, var(--primary-orange), #FFB347);
    }
    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }
    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .data-table thead {
        background: #F5F5F5;
    }
    .data-table th {
        padding: 10px 12px;
        text-align: left;
        color: #666;
        font-weight: 600;
    }
    .data-table td {
        padding: 10px 12px;
        border-top: 1px solid #EEE;
    }
    .empty-state {
        text-align: center;
        padding: 40px 10px;
        color: #999;
    }
    .empty-state i {
        font-size: 40px;
        margin-bottom: 8px;
    }
    .empty-text {
        font-size: 13px;
        color: #777;
        margin: 8px 0 0;
    }
</style>
@endpush

