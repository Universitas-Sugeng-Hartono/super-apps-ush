@extends('admin.layouts.super-app')

@section('content')
    <div class="content-card">
        <div class="card-header">
            <h3>Pendaftaran Seminar Defense - Pending Approval</h3>
        </div>

        @if(session('success'))
            <div class="alert-success">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if($defenses->count() > 0)
            @foreach($defenses as $proposal)
                <div class="approval-card">
                    <div class="approval-header">
                        <div class="student-section">
                            <h4>{{ $proposal->finalProject->student->nama_lengkap }}</h4>
                            <p class="meta">
                                <span><i class="bi bi-card-text"></i> {{ $proposal->finalProject->student->nim }}</span>
                                <span><i class="bi bi-calendar3"></i> Daftar: {{ $proposal->registered_at->format('d M Y') }}</span>
                            </p>
                        </div>
                        <div class="action-buttons">
                            <button type="button" class="btn-approve" onclick="showApproveModal({{ $proposal->id }})">
                                    <i class="bi bi-check-circle"></i> Setujui
                                </button>
                            <button type="button" class="btn-reject" onclick="showRejectModal({{ $proposal->id }})">
                                <i class="bi bi-x-circle"></i> Tolak
                            </button>
                        </div>
                    </div>

                    <div class="proposal-details">
                        <div class="detail-row">
                            <strong>Judul TA:</strong>
                            <p>{{ $proposal->finalProject->title ?? 'Belum ditentukan' }}</p>
                        </div>
                        <div class="detail-row">
                            <strong>Pembimbing:</strong>
                            <p>
                                {{ $proposal->finalProject->supervisor1->name ?? '-' }}
                                @if($proposal->finalProject->supervisor2)
                                    , {{ $proposal->finalProject->supervisor2->name }}
                                @endif
                            </p>
                        </div>
                        <div class="detail-row">
                            <strong>Jumlah Bimbingan:</strong>
                            <p>{{ $proposal->finalProject->guidanceLogs()->approved()->count() }} kali bimbingan</p>
                        </div>
                    </div>

                    @if($proposal->finalProject->documents->where('document_type', 'proposal')->count() > 0)
                        <div class="documents-section">
                            <h5><i class="bi bi-paperclip"></i> Dokumen Terlampir</h5>
                            <div class="doc-list">
                                @foreach($proposal->finalProject->documents->where('document_type', 'proposal') as $doc)
                                    <a href="{{ route('admin.final-project.documents.download', $doc->id) }}" class="doc-item">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                        {{ $doc->title }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        @else
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h4>Tidak ada pendaftaran sidang pending</h4>
                <p>Semua pendaftaran sudah diproses</p>
            </div>
        @endif
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h4>Tolak Pendaftaran Sidang</h4>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="form-group">
                    <label>Alasan Penolakan *</label>
                    <textarea name="approval_notes" class="form-control" rows="4" required placeholder="Berikan alasan penolakan yang jelas..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeRejectModal()">Batal</button>
                    <button type="submit" class="btn-reject-confirm">Tolak Pendaftaran</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Approve Modal -->
    <div id="approveModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h4>Setujui Pendaftaran Sidang</h4>
            <form id="approveForm" method="POST">
                @csrf
                @if(in_array(($role ?? null), ['superadmin', 'masteradmin'], true))
                    <div class="form-group">
                        <label>Jadwal Sidang (Opsional)</label>
                        <input type="datetime-local" name="scheduled_at" class="form-control">
                        <small>Kosongkan jika jadwal belum ditentukan. Jika diisi, akan muncul di kalender landing page.</small>
                    </div>
                @endif
                <div class="form-group">
                    <label>Catatan (Opsional)</label>
                    <textarea name="approval_notes" class="form-control" rows="3" placeholder="Catatan persetujuan..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeApproveModal()">Batal</button>
                    <button type="submit" class="btn-approve-confirm">
                        <i class="bi bi-check-circle"></i> Setujui
                    </button>
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

    .btn-approve, .btn-approve-confirm, .btn-reject, .btn-cancel, .btn-reject-confirm {
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

    .btn-approve:focus,
    .btn-approve-confirm:focus,
    .btn-reject:focus,
    .btn-reject-confirm:focus,
    .btn-cancel:focus {
        outline: none;
    }

    .btn-approve:focus-visible,
    .btn-approve-confirm:focus-visible,
    .btn-reject:focus-visible,
    .btn-reject-confirm:focus-visible,
    .btn-cancel:focus-visible {
        box-shadow: 0 0 0 4px rgba(255, 152, 0, 0.25);
    }

    .btn-approve, .btn-approve-confirm {
        background: #4CAF50;
        color: white;
    }

    .btn-approve:hover, .btn-approve-confirm:hover {
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

    .btn-cancel:hover {
        background: #D0D0D0;
        transform: translateY(-2px);
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

    .documents-section {
        background: white;
        padding: 15px;
        border-radius: 10px;
        margin-top: 15px;
    }

    .documents-section h5 {
        font-size: 14px;
        font-weight: 600;
        color: #666;
        margin: 0 0 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .doc-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .doc-item {
        background: #E3F2FD;
        color: #1976D2;
        padding: 10px 15px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
    }

    .doc-item:hover {
        background: #BBDEFB;
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
    /* tambahan */
    .student-section {
    flex: 1;
    min-width: 0;
}

.action-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: flex-end;
    flex-shrink: 0;
}

.btn-approve,
.btn-approve-confirm,
.btn-reject,
.btn-cancel,
.btn-reject-confirm {
    white-space: nowrap;
}

@media (max-width: 768px) {
    .content-card {
        padding: 16px;
    }

    .approval-header {
        flex-direction: column;
        align-items: stretch;
        gap: 14px;
    }

    .meta {
        flex-direction: column;
        gap: 8px;
    }

    .action-buttons {
        width: 100%;
        justify-content: stretch;
    }

    .action-buttons button {
        flex: 1 1 100%;
        justify-content: center;
    }

    .modal-content {
        padding: 20px;
        width: calc(100% - 24px);
    }

    .modal-actions {
        flex-direction: column;
    }

    .modal-actions button {
        width: 100%;
        justify-content: center;
    }
}

</style>
@endpush

@push('scripts')
<script>
function showApproveModal(defenseId) {
    const modal = document.getElementById('approveModal');
    const form = document.getElementById('approveForm');
    form.action = `/admin/final-project/defenses/${defenseId}/approve`;
    modal.style.display = 'flex';
}

function showRejectModal(proposalId) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    form.action = `/admin/final-project/defenses/${proposalId}/reject`;
    modal.style.display = 'flex';
}

function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}

function closeApproveModal() {
    document.getElementById('approveModal').style.display = 'none';
}
</script>
@endpush
