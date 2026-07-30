@extends('students.layouts.super-app')

@section('content')
    <div class="stats-card">
        <div class="stats-header">
            <h3>Dokumen Tugas Akhir</h3>
            <a href="{{ route('student.final-project.documents.create') }}" class="btn-add">
                <i class="bi bi-plus-circle"></i> Upload Dokumen
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-success">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if($documents->count() > 0)
        @foreach($documents as $type => $docs)
            <div class="document-group">
                <h4 class="group-title">
                    <i class="bi bi-folder2-open"></i>
                    {{ ucwords(str_replace('_', ' ', $type)) }}
                </h4>
                
                <div class="documents-list">
                    @foreach($docs as $doc)
                        <div class="document-card status-{{ $doc->review_status }}">
                            <div class="doc-header">
                                <div class="doc-icon">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </div>
                                <div class="doc-info">
                                    <h5>{{ $doc->title }}</h5>
                                    <p class="doc-meta">
                                        <span>v{{ $doc->version }}</span> • 
                                        <span>{{ $doc->uploaded_at->format('d M Y') }}</span>
                                    </p>
                                </div>
                                <span class="status-badge status-{{ $doc->review_status }}">
                                    {{ ucfirst(str_replace('_', ' ', $doc->review_status)) }}
                                </span>
                            </div>

                            @if($doc->review_notes)
                                <div class="review-notes">
                                    <h6><i class="bi bi-chat-left-text"></i> Catatan Reviewer:</h6>
                                    <p>{{ $doc->review_notes }}</p>
                                    @if($doc->reviewer)
                                        <small>oleh {{ $doc->reviewer->name }}</small>
                                    @endif
                                </div>
                            @endif

                            <div class="doc-actions">
                                <a href="{{ route('student.final-project.documents.download', $doc->id) }}" class="btn-download">
                                    <i class="bi bi-download"></i> Download
                                </a>
                                @if($doc->review_status === 'pending')
                                    <form action="{{ route('student.final-project.documents.destroy', $doc->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus dokumen ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-delete">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @else
        <div class="empty-state">
            <i class="bi bi-folder-x"></i>
            <h4>Belum ada dokumen</h4>
            <p>Upload dokumen Tugas Akhir Anda untuk direview oleh dosen pembimbing</p>
            <a href="{{ route('student.final-project.documents.create') }}" class="btn-primary">
                <i class="bi bi-plus-circle"></i> Upload Dokumen
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

    .document-group {
        margin-bottom: 30px;
    }

    .group-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--primary-orange);
        margin: 0 0 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .documents-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .document-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: var(--shadow);
        border-left: 4px solid #E0E0E0;
    }

    .document-card.status-approved { border-left-color: #4CAF50; }
    .document-card.status-pending { border-left-color: #FF9800; }
    .document-card.status-needs_revision { border-left-color: #2196F3; }
    .document-card.status-rejected { border-left-color: #F44336; }

    .doc-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 12px;
    }

    .doc-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, var(--primary-orange), #FFB347);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
        flex-shrink: 0;
    }

    .doc-info {
        flex: 1;
    }

    .doc-info h5 {
        font-size: 15px;
        font-weight: 600;
        margin: 0 0 5px;
        color: var(--text-dark);
    }

    .doc-meta {
        font-size: 12px;
        color: #999;
        margin: 0;
    }

    .status-badge {
        padding: 6px 16px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .status-badge.status-approved {
        background: #E8F5E9;
        color: #2E7D32;
    }

    .status-badge.status-pending {
        background: #FFF3E0;
        color: #F57C00;
    }

    .status-badge.status-needs_revision {
        background: #E3F2FD;
        color: #1976D2;
    }

    .status-badge.status-rejected {
        background: #FFEBEE;
        color: #C62828;
    }

    .review-notes {
        background: #F5F5F5;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 12px;
    }

    .review-notes h6 {
        font-size: 13px;
        font-weight: 600;
        color: #666;
        margin: 0 0 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .review-notes p {
        font-size: 14px;
        color: #333;
        margin: 0 0 5px;
        line-height: 1.5;
    }

    .review-notes small {
        font-size: 12px;
        color: #999;
    }

    .doc-actions {
        display: flex;
        gap: 10px;
    }

    .btn-download, .btn-delete {
        padding: 8px 16px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        border: none;
        display: flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
    }

    .btn-download {
        background: #E3F2FD;
        color: #1976D2;
    }

    .btn-delete {
        background: #FFEBEE;
        color: #C62828;
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
</style>
@endpush
