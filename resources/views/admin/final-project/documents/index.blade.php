@extends('admin.layouts.super-app')

@section('content')
    <div class="content-card">
        <div class="card-header">
            <div>
                <h3>{{ $canManageAll ? 'Review Dokumen Mahasiswa' : 'Log Dokumen Mahasiswa' }}</h3>
                <p class="card-subtitle">
                    {{ $canManageAll ? 'Tinjau dokumen yang masih menunggu keputusan review.' : 'Halaman ini hanya menampilkan status dokumen mahasiswa bimbingan tanpa aksi review.' }}
                </p>
            </div>
            <div class="filters">
                <form method="GET" action="{{ route('admin.final-project.documents.index') }}" class="filter-form">
                    @if($canManageAll)
                    <select name="prodi" onchange="this.form.submit()" class="filter-select">
                        <option value="">Semua Program Studi</option>
                        @foreach($availableProdis as $prodi)
                            <option value="{{ $prodi }}" {{ ($prodiFilter ?? '') == $prodi ? 'selected' : '' }}>
                                {{ $prodi }}
                            </option>
                        @endforeach
                    </select>
                    @endif
                    <select name="status" onchange="this.form.submit()" class="filter-select">
                        <option value="">{{ $canManageAll ? 'Pending Review' : 'Semua Status' }}</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="needs_revision" {{ request('status') == 'needs_revision' ? 'selected' : '' }}>Need Revision</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="alert-success">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if($documents->count() > 0)
            @foreach($documents as $doc)
                <div class="document-card status-{{ $doc->review_status }}">
                    <div class="doc-header">
                        <div class="doc-icon-wrapper">
                            <div class="doc-icon">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                            <div class="doc-info">
                                <h4>{{ $doc->title }}</h4>
                                <p class="meta">
                                    <span><i class="bi bi-person"></i> {{ $doc->finalProject->student->nama_lengkap }}</span>
                                    <span><i class="bi bi-card-text"></i> {{ $doc->finalProject->student->nim }}</span>
                                </p>
                                <p class="meta-secondary">
                                    <span class="doc-type">{{ ucwords(str_replace('_', ' ', $doc->document_type)) }}</span>
                                    <span>Version {{ $doc->version }}</span>
                                    <span>{{ $doc->uploaded_at->format('d M Y H:i') }}</span>
                                </p>
                            </div>
                        </div>
                        @if($canManageAll && $doc->review_status === 'pending')
                            <div class="action-buttons">
                                <button type="button" class="btn-download" onclick="downloadDoc({{ $doc->id }})">
                                    <i class="bi bi-download"></i>
                                </button>
                            </div>
                        @else
                            <span class="status-badge status-{{ $doc->review_status }}">
                                {{ ucfirst(str_replace('_', ' ', $doc->review_status)) }}
                            </span>
                        @endif
                    </div>

                    @if($doc->review_notes)
                        <div class="review-notes">
                            <h5><i class="bi bi-chat-left-text"></i> Review Notes:</h5>
                            <p>{{ $doc->review_notes }}</p>
                            <small>Reviewed by {{ $doc->reviewer->name ?? 'You' }} on {{ $doc->reviewed_at->format('d M Y H:i') }}</small>
                        </div>
                    @endif

                    @if($canManageAll && $doc->review_status === 'pending')
                        <div class="review-actions">
                            <button type="button" class="btn-approve" onclick="showApproveModal({{ $doc->id }})">
                                <i class="bi bi-check-circle"></i> Approve
                            </button>
                            <button type="button" class="btn-revision" onclick="showRevisionModal({{ $doc->id }})">
                                <i class="bi bi-arrow-repeat"></i> Need Revision
                            </button>
                            <button type="button" class="btn-reject" onclick="showRejectModal({{ $doc->id }})">
                                <i class="bi bi-x-circle"></i> Reject
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach

            <div class="pagination-wrapper">
                {{ $documents->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        @else
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h4>{{ $canManageAll ? 'Tidak ada dokumen untuk direview' : 'Belum ada dokumen untuk ditampilkan' }}</h4>
                <p>{{ $canManageAll ? 'Semua dokumen sudah direview atau belum ada dokumen baru' : 'Dokumen mahasiswa bimbingan akan muncul di halaman ini beserta statusnya.' }}</p>
            </div>
        @endif
    </div>

    @if($canManageAll)
        <!-- Approve Modal -->
        <div id="approveModal" class="modal" style="display: none;">
            <div class="modal-content">
                <div class="modal-header success">
                    <i class="bi bi-check-circle-fill"></i>
                    <h4>Approve Dokumen</h4>
                </div>
                <form id="approveForm" method="POST">
                    @csrf
                    <p class="modal-desc">Dokumen akan ditandai sebagai <strong>Approved</strong>. Mahasiswa akan menerima notifikasi.</p>
                    <div class="form-group">
                        <label>Catatan Review (Opsional)</label>
                        <textarea name="review_notes" class="form-control" rows="3" placeholder="Berikan catatan atau feedback positif..."></textarea>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
                        <button type="submit" class="btn-approve-confirm">
                            <i class="bi bi-check-circle"></i> Approve
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Revision Modal -->
        <div id="revisionModal" class="modal" style="display: none;">
            <div class="modal-content">
                <div class="modal-header warning">
                    <i class="bi bi-arrow-repeat"></i>
                    <h4>Request Revision</h4>
                </div>
                <form id="revisionForm" method="POST">
                    @csrf
                    <p class="modal-desc">Dokumen memerlukan <strong>revisi</strong>. Mohon berikan feedback yang jelas.</p>
                    <div class="form-group">
                        <label>Catatan Revisi *</label>
                        <textarea name="review_notes" class="form-control" rows="4" required placeholder="Jelaskan bagian mana yang perlu direvisi dan alasannya..."></textarea>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
                        <button type="submit" class="btn-revision-confirm">
                            <i class="bi bi-arrow-repeat"></i> Request Revision
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Reject Modal -->
        <div id="rejectModal" class="modal" style="display: none;">
            <div class="modal-content">
                <div class="modal-header danger">
                    <i class="bi bi-x-circle-fill"></i>
                    <h4>Reject Dokumen</h4>
                </div>
                <form id="rejectForm" method="POST">
                    @csrf
                    <p class="modal-desc">Dokumen akan <strong>ditolak</strong>. Action ini menandakan dokumen tidak dapat diterima.</p>
                    <div class="form-group">
                        <label>Alasan Penolakan *</label>
                        <textarea name="review_notes" class="form-control" rows="4" required placeholder="Berikan alasan yang jelas mengapa dokumen ditolak..."></textarea>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
                        <button type="submit" class="btn-reject-confirm">
                            <i class="bi bi-x-circle"></i> Reject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@push('css')
<style>
    .content-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
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
        color: #333;
    }

    .card-subtitle {
        margin: 6px 0 0;
        font-size: 13px;
        color: #777;
    }

    .filter-form {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }

    .filter-select {
        padding: 8px 15px;
        border: 2px solid #E0E0E0;
        border-radius: 8px;
        font-size: 14px;
        cursor: pointer;
        background: white;
    }

    .alert-success {
        background: linear-gradient(135deg, #E8F5E9, #C8E6C9);
        color: #2E7D32;
        padding: 15px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
    }

    .document-card {
        background: white;
        border: 2px solid #E0E0E0;
        border-left: 5px solid #E0E0E0;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 20px;
        transition: all 0.3s;
    }

    .document-card:hover {
        box-shadow: 0 6px 20px rgba(0,0,0,0.12);
        transform: translateY(-2px);
    }

    .document-card.status-approved {
        border-left-color: #4CAF50;
        background: linear-gradient(to right, #F1F8F4, #FFFFFF);
    }

    .document-card.status-pending {
        border-left-color: #FF9800;
        background: linear-gradient(to right, #FFF8F0, #FFFFFF);
    }

    .document-card.status-needs_revision {
        border-left-color: #2196F3;
        background: linear-gradient(to right, #E3F2FD, #FFFFFF);
    }

    .document-card.status-rejected {
        border-left-color: #F44336;
        background: linear-gradient(to right, #FFEBEE, #FFFFFF);
    }

    .doc-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
    }

    .doc-icon-wrapper {
        display: flex;
        gap: 15px;
        flex: 1;
    }

    .doc-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #FF7043, #FFB347);
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        color: white;
        flex-shrink: 0;
        box-shadow: 0 4px 15px rgba(255, 112, 67, 0.3);
    }

    .doc-info h4 {
        font-size: 18px;
        font-weight: 600;
        margin: 0 0 8px;
        color: #333;
    }

    .meta {
        display: flex;
        gap: 20px;
        font-size: 13px;
        color: #666;
        margin: 0 0 6px;
    }

    .meta span {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .meta-secondary {
        display: flex;
        gap: 15px;
        font-size: 12px;
        color: #999;
        margin: 0;
    }

    .doc-type {
        background: #E3F2FD;
        color: #1976D2;
        padding: 3px 10px;
        border-radius: 8px;
        font-weight: 500;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .btn-download {
        width: 45px;
        height: 45px;
        background: linear-gradient(135deg, #2196F3, #42A5F5);
        color: white;
        border-radius: 12px;
        border: none;
        font-size: 18px;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(33, 150, 243, 0.3);
    }

    .btn-download:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(33, 150, 243, 0.4);
    }

    .status-badge {
        padding: 8px 16px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
    }

    .status-badge.status-approved {
        background: #4CAF50;
        color: white;
        box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3);
    }

    .status-badge.status-needs_revision {
        background: #2196F3;
        color: white;
        box-shadow: 0 2px 8px rgba(33, 150, 243, 0.3);
    }

    .status-badge.status-rejected {
        background: #F44336;
        color: white;
        box-shadow: 0 2px 8px rgba(244, 67, 54, 0.3);
    }

    .review-notes {
        background: linear-gradient(135deg, #FFF3E0, #FFE0B2);
        border-left: 4px solid #FF9800;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 15px;
    }

    .review-notes h5 {
        font-size: 14px;
        font-weight: 600;
        color: #E65100;
        margin: 0 0 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .review-notes p {
        font-size: 14px;
        color: #333;
        margin: 0 0 8px;
        line-height: 1.6;
    }

    .review-notes small {
        font-size: 12px;
        color: #666;
    }

    .review-actions {
        display: flex;
        gap: 12px;
        padding-top: 15px;
        border-top: 2px dashed #E0E0E0;
    }

    .btn-approve, .btn-revision, .btn-reject, .btn-cancel,
    .btn-approve-confirm, .btn-revision-confirm, .btn-reject-confirm {
        padding: 12px 20px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
    }

    .btn-approve, .btn-approve-confirm {
        background: linear-gradient(135deg, #4CAF50, #66BB6A);
        color: white;
        box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
    }

    .btn-approve:hover, .btn-approve-confirm:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(76, 175, 80, 0.4);
    }

    .btn-revision, .btn-revision-confirm {
        background: linear-gradient(135deg, #2196F3, #42A5F5);
        color: white;
        box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);
    }

    .btn-revision:hover, .btn-revision-confirm:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(33, 150, 243, 0.4);
    }

    .btn-reject, .btn-reject-confirm {
        background: linear-gradient(135deg, #F44336, #EF5350);
        color: white;
        box-shadow: 0 4px 12px rgba(244, 67, 54, 0.3);
    }

    .btn-reject:hover, .btn-reject-confirm:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(244, 67, 54, 0.4);
    }

    .btn-cancel {
        background: #EEEEEE;
        color: #666;
    }

    .btn-cancel:hover {
        background: #E0E0E0;
    }

    .empty-state {
        text-align: center;
        padding: 80px 20px;
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
        margin: 0;
    }

    /* Modal */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        backdrop-filter: blur(5px);
    }

    .modal-content {
        background: white;
        border-radius: 20px;
        padding: 0;
        max-width: 550px;
        width: 90%;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        animation: modalSlideUp 0.3s ease;
        overflow: hidden;
    }

    @keyframes modalSlideUp {
        from {
            transform: translateY(50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .modal-header {
        padding: 25px 30px;
        display: flex;
        align-items: center;
        gap: 12px;
        border-bottom: 2px solid #F0F0F0;
    }

    .modal-header i {
        font-size: 32px;
    }

    .modal-header.success {
        background: linear-gradient(135deg, #E8F5E9, #C8E6C9);
        color: #2E7D32;
    }

    .modal-header.warning {
        background: linear-gradient(135deg, #E3F2FD, #BBDEFB);
        color: #1565C0;
    }

    .modal-header.danger {
        background: linear-gradient(135deg, #FFEBEE, #FFCDD2);
        color: #C62828;
    }

    .modal-header h4 {
        font-size: 20px;
        font-weight: 600;
        margin: 0;
    }

    .modal-content form {
        padding: 25px 30px 30px;
    }

    .modal-desc {
        font-size: 14px;
        color: #666;
        margin: 0 0 20px;
        line-height: 1.6;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 8px;
        color: #333;
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #E0E0E0;
        border-radius: 10px;
        font-size: 14px;
        font-family: inherit;
        transition: border-color 0.3s;
    }

    .form-control:focus {
        outline: none;
        border-color: #2196F3;
    }

    .modal-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        margin-top: 25px;
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
    .status-badge.status-pending {
    background: linear-gradient(135deg, #FF9800, #FFB347);
    color: white;
    box-shadow: 0 2px 8px rgba(255, 152, 0, 0.3);
}
</style>
@endpush

@push('scripts')
@if($canManageAll)
    <script>
    function downloadDoc(docId) {
        window.location.href = `/admin/final-project/documents/${docId}/download`;
    }

    function showApproveModal(docId) {
        const modal = document.getElementById('approveModal');
        const form = document.getElementById('approveForm');
        form.action = `/admin/final-project/documents/${docId}/approve`;
        modal.style.display = 'flex';
    }

    function showRevisionModal(docId) {
        const modal = document.getElementById('revisionModal');
        const form = document.getElementById('revisionForm');
        form.action = `/admin/final-project/documents/${docId}/revision`;
        modal.style.display = 'flex';
    }

    function showRejectModal(docId) {
        const modal = document.getElementById('rejectModal');
        const form = document.getElementById('rejectForm');
        form.action = `/admin/final-project/documents/${docId}/reject`;
        modal.style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('approveModal').style.display = 'none';
        document.getElementById('revisionModal').style.display = 'none';
        document.getElementById('rejectModal').style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            closeModal();
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
    </script>
@endif
@endpush
