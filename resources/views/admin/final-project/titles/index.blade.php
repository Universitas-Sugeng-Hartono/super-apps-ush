@extends('admin.layouts.super-app')

@section('content')
    <div class="content-card">
        <div class="card-header">
            <h3>Pengajuan Judul Tugas Akhir - Pending Approval</h3>
        </div>

        @if(session('success'))
            <div class="alert-success">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if($pendingTitles->count() > 0)
            @foreach($pendingTitles as $finalProject)
                <div class="approval-card">
                    <div class="approval-header">
                        <div class="student-section">
                            <h4>{{ $finalProject->student->nama_lengkap }}</h4>
                            <p class="meta">
                                <span><i class="bi bi-card-text"></i> {{ $finalProject->student->nim }}</span>
                                <span><i class="bi bi-calendar3"></i> Diajukan: {{ $finalProject->created_at->format('d M Y') }}</span>
                            </p>
                        </div>
                        <div class="action-buttons">
                            <form action="{{ route('admin.final-project.titles.approve', $finalProject->id) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="button" class="btn-approve" onclick="approveTitle(this)">
                                    <i class="bi bi-check-circle"></i> Setujui
                                </button>
                            </form>
                            <button type="button" class="btn-reject" onclick="showRejectModal({{ $finalProject->id }})">
                                <i class="bi bi-x-circle"></i> Tolak
                            </button>
                        </div>
                    </div>

                    <div class="proposal-details">
                        <div class="detail-row">
                            <strong>Judul TA (Bahasa Indonesia):</strong>
                            <p style="font-weight: 600; color: #333;">{{ $finalProject->title }}</p>
                        </div>
                        @if($finalProject->title_en)
                        <div class="detail-row">
                            <strong>Judul TA (Bahasa Inggris):</strong>
                            <p style="font-style: italic; color: #666;">{{ $finalProject->title_en }}</p>
                        </div>
                        @endif
                        <div class="detail-row">
                            <strong>Pembimbing PA:</strong>
                            <p>{{ $finalProject->student->dosenPA->name ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <p>Tidak ada pengajuan judul yang menunggu persetujuan</p>
            </div>
        @endif
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h4>Tolak Judul Tugas Akhir</h4>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="form-group">
                    <label>Alasan Penolakan *</label>
                    <textarea name="rejection_notes" class="form-control" rows="4" required placeholder="Berikan alasan penolakan yang jelas..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeRejectModal()">Batal</button>
                    <button type="submit" class="btn-reject-confirm">Tolak Judul</button>
                </div>
            </form>
        </div>
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
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #F5F5F5;
    }

    .card-header h3 {
        font-size: 20px;
        font-weight: 600;
        margin: 0;
    }

    .alert-success {
        background: #E8F5E9;
        color: #2E7D32;
        padding: 15px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .approval-card {
        background: #FAFAFA;
        border: 2px solid #E0E0E0;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .approval-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px dashed #E0E0E0;
    }

    .student-section h4 {
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
        margin: 0;
    }

    .meta span {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
    }

    .btn-approve, .btn-reject, .btn-cancel, .btn-reject-confirm {
        padding: 10px 20px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
    }

    .btn-approve {
        background: #4CAF50;
        color: white;
    }

    .btn-approve:hover {
        background: #45A049;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(76, 175, 80, 0.4);
    }

    .btn-reject, .btn-reject-confirm {
        background: #F44336;
        color: white;
    }

    .btn-reject:hover, .btn-reject-confirm:hover {
        background: #E53935;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(244, 67, 54, 0.4);
    }

    .btn-cancel {
        background: #E0E0E0;
        color: #666;
    }

    .proposal-details {
        margin-bottom: 20px;
    }

    .detail-row {
        margin-bottom: 15px;
    }

    .detail-row strong {
        display: block;
        font-size: 13px;
        color: #666;
        margin-bottom: 5px;
    }

    .detail-row p {
        font-size: 14px;
        color: #333;
        margin: 0;
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

    .empty-state p {
        color: #999;
        margin: 0;
        font-size: 16px;
    }

    /* Modal */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .modal-content {
        background: white;
        border-radius: 15px;
        padding: 30px;
        max-width: 500px;
        width: 90%;
    }

    .modal-content h4 {
        font-size: 20px;
        font-weight: 600;
        margin: 0 0 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 8px;
    }

    .form-control {
        width: 100%;
        padding: 12px;
        border: 2px solid #E0E0E0;
        border-radius: 10px;
        font-size: 14px;
        font-family: inherit;
    }

    .modal-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }
</style>
@endpush

@push('scripts')
<script>
    function approveTitle(btn) {
        if (confirm('Yakin ingin menyetujui judul ini?')) {
            btn.closest('form').submit();
        }
    }

    function showRejectModal(id) {
        const modal = document.getElementById('rejectModal');
        const form = document.getElementById('rejectForm');
        form.action = `/admin/final-project/titles/${id}/reject`;
        modal.style.display = 'flex';
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').style.display = 'none';
        document.getElementById('rejectForm').reset();
    }

    window.onclick = function(event) {
        const modal = document.getElementById('rejectModal');
        if (event.target == modal) {
            closeRejectModal();
        }
    }
</script>
@endpush

