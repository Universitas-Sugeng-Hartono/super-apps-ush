@extends('students.layouts.super-app')

@section('content')
    <div class="stats-card">
        <div class="stats-header">
            <h3>Log Book Bimbingan</h3>
            <a href="{{ route('student.final-project.guidance.create') }}" class="btn-add">
                <i class="bi bi-plus-circle"></i> Tambah Bimbingan
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-success">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if($logs->count() > 0)
        <div class="logs-container">
            @foreach($logs as $log)
                <div class="log-card status-{{ $log->status }}">
                    <div class="log-header">
                        <div>
                            <h4>{{ $log->guidance_date->format('d M Y') }}</h4>
                            <p class="supervisor-name">
                                <i class="bi bi-person"></i> {{ $log->supervisor->name }}
                            </p>
                        </div>
                        <span class="status-badge status-{{ $log->status }}">
                            {{ ucfirst($log->status) }}
                        </span>
                    </div>
                    
                    <div class="log-content">
                        <div class="log-section">
                            <h5>Materi yang Dibimbing:</h5>
                            <p>{{ $log->materials_discussed }}</p>
                        </div>

                        @if($log->student_notes)
                            <div class="log-section">
                                <h5>Catatan Mahasiswa:</h5>
                                <p>{{ $log->student_notes }}</p>
                            </div>
                        @endif

                        @if($log->supervisor_feedback)
                            <div class="log-section feedback">
                                <h5>Feedback Dosen:</h5>
                                <p>{{ $log->supervisor_feedback }}</p>
                            </div>
                        @endif

                        @if($log->file_path)
                            <div class="log-section">
                                <a href="{{ route('student.final-project.guidance.download', $log->id) }}" class="file-link">
                                    <i class="bi bi-file-earmark-pdf"></i> Download File Lampiran
                                </a>
                            </div>
                        @endif
                    </div>

                    @if($log->status === 'pending')
                        <div class="log-actions">
                            <a href="{{ route('student.final-project.guidance.edit', $log->id) }}" class="btn-edit">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <form action="{{ route('student.final-project.guidance.destroy', $log->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus log ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="pagination-wrapper">
            {{ $logs->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    @else
        <div class="empty-state">
            <i class="bi bi-journal-x"></i>
            <h4>Belum ada log bimbingan</h4>
            <p>Mulai catat bimbingan Anda dengan dosen pembimbing</p>
            <a href="{{ route('student.final-project.guidance.create') }}" class="btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Log Bimbingan
            </a>
        </div>
    @endif
@endsection

@push('css')
<style>
    .stats-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        box-shadow: var(--shadow);
        margin-bottom: 25px;
    }

    .stats-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .stats-header h3 {
        font-size: 20px;
        font-weight: 600;
        margin: 0;
    }

    .btn-add {
        background: linear-gradient(135deg, var(--primary-orange), #FFB347);
        color: white;
        padding: 10px 20px;
        border-radius: 12px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
    }

    .btn-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 112, 67, 0.4);
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
    }

    .logs-container {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .log-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        box-shadow: var(--shadow);
        border-left: 5px solid #E0E0E0;
    }

    .log-card.status-approved {
        border-left-color: #4CAF50;
    }

    .log-card.status-pending {
        border-left-color: #FF9800;
    }

    .log-card.status-rejected {
        border-left-color: #F44336;
    }

    .log-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 2px solid #F5F5F5;
    }

    .log-header h4 {
        font-size: 18px;
        font-weight: 600;
        margin: 0 0 5px;
        color: var(--text-dark);
    }

    .supervisor-name {
        font-size: 13px;
        color: #666;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .status-badge {
        padding: 6px 16px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-badge.status-approved {
        background: #E8F5E9;
        color: #2E7D32;
    }

    .status-badge.status-pending {
        background: #FFF3E0;
        color: #F57C00;
    }

    .status-badge.status-rejected {
        background: #FFEBEE;
        color: #C62828;
    }

    .log-content {
        margin-bottom: 15px;
    }

    .log-section {
        margin-bottom: 15px;
    }

    .log-section h5 {
        font-size: 13px;
        font-weight: 600;
        color: #666;
        margin: 0 0 8px;
    }

    .log-section p {
        font-size: 14px;
        color: #333;
        margin: 0;
        line-height: 1.6;
    }

    .log-section.feedback {
        background: #F5F5F5;
        padding: 15px;
        border-radius: 12px;
    }

    .file-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--primary-orange);
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
    }

    .file-link:hover {
        text-decoration: underline;
    }

    .log-actions {
        display: flex;
        gap: 10px;
        padding-top: 15px;
        border-top: 2px solid #F5F5F5;
    }

    .btn-edit, .btn-delete {
        padding: 8px 16px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
        border: none;
        display: flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
    }

    .btn-edit {
        background: #E3F2FD;
        color: #1976D2;
    }

    .btn-edit:hover {
        background: #BBDEFB;
    }

    .btn-delete {
        background: #FFEBEE;
        color: #C62828;
    }

    .btn-delete:hover {
        background: #FFCDD2;
    }

    .empty-state {
        background: white;
        border-radius: 20px;
        padding: 60px 20px;
        text-align: center;
        box-shadow: var(--shadow);
    }

    .empty-state i {
        font-size: 80px;
        color: #E0E0E0;
        margin-bottom: 20px;
    }

    .empty-state h4 {
        font-size: 20px;
        font-weight: 600;
        color: #666;
        margin: 0 0 10px;
    }

    .empty-state p {
        color: #999;
        margin: 0 0 25px;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-orange), #FFB347);
        color: white;
        padding: 12px 24px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .pagination-wrapper {
        margin-top: 30px;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    /* Custom Pagination Styling - Aggressive Override */
    .pagination-wrapper .pagination {
        display: flex !important;
        list-style: none !important;
        padding: 0 !important;
        margin: 0 !important;
        gap: 8px !important;
        flex-wrap: wrap !important;
        justify-content: center !important;
        align-items: center !important;
        border: none !important;
        background: transparent !important;
    }

    .pagination-wrapper .pagination .page-item {
        margin: 0 !important;
        list-style: none !important;
        display: inline-block !important;
    }

    .pagination-wrapper .pagination .page-link {
        padding: 10px 16px !important;
        border: 2px solid #E0E0E0 !important;
        border-radius: 10px !important;
        color: #666 !important;
        text-decoration: none !important;
        background: white !important;
        transition: all 0.3s !important;
        font-size: 14px !important;
        font-weight: 500 !important;
        min-width: 44px !important;
        text-align: center !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        line-height: 1.5 !important;
        position: relative !important;
        margin: 0 !important;
        margin-left: 0 !important;
    }

    .pagination-wrapper .pagination .page-link:hover {
        background: var(--primary-orange) !important;
        color: white !important;
        border-color: var(--primary-orange) !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3) !important;
        z-index: 1 !important;
    }

    .pagination-wrapper .pagination .page-item.active .page-link {
        background: var(--primary-orange) !important;
        color: white !important;
        border-color: var(--primary-orange) !important;
        font-weight: 600 !important;
        z-index: 2 !important;
    }

    .pagination-wrapper .pagination .page-item.disabled .page-link {
        background: #F5F5F5 !important;
        color: #999 !important;
        border-color: #E0E0E0 !important;
        cursor: not-allowed !important;
        opacity: 0.6 !important;
        pointer-events: none !important;
    }

    .pagination-wrapper .pagination .page-item.disabled .page-link:hover {
        background: #F5F5F5 !important;
        color: #999 !important;
        border-color: #E0E0E0 !important;
        transform: none !important;
        box-shadow: none !important;
    }

    .pagination-wrapper .pagination .page-link::before,
    .pagination-wrapper .pagination .page-link::after {
        display: none !important;
        content: none !important;
    }

    .pagination-wrapper .pagination .page-link svg,
    .pagination-wrapper .pagination .page-link i {
        display: none !important;
    }

    .pagination-wrapper .pagination .page-item:first-child .page-link {
        margin-left: 0 !important;
        border-top-left-radius: 10px !important;
        border-bottom-left-radius: 10px !important;
        font-size: 14px !important;
    }

    .pagination-wrapper .pagination .page-item:last-child .page-link {
        margin-right: 0 !important;
        border-top-right-radius: 10px !important;
        border-bottom-right-radius: 10px !important;
        font-size: 14px !important;
    }

    .pagination-wrapper .pagination .page-link span {
        display: inline !important;
        font-size: 14px !important;
    }

    .pagination-wrapper .pagination .page-link[aria-label*="Previous"],
    .pagination-wrapper .pagination .page-link[aria-label*="Next"] {
        font-size: 14px !important;
        padding: 10px 16px !important;
    }

    .pagination-wrapper {
        overflow: visible !important;
        width: 100% !important;
        position: relative !important;
    }

    .pagination-wrapper .pagination {
        max-width: 100% !important;
        overflow: visible !important;
        position: relative !important;
    }
</style>
@endpush
