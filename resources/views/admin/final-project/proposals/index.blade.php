@extends('admin.layouts.super-app')

@section('content')
    <div class="content-card">
        <div class="card-header">
            <h3>Pendaftaran Seminar Proposal - Pending Approval</h3>
            
            <div class="filter-toolbar">
                <form action="{{ route('admin.final-project.proposals.index') }}" method="GET" class="filter-form">
                    <select name="prodi" class="form-select select2-prodi" onchange="this.form.submit()">
                        <option value="">-- Semua Program Studi --</option>
                        @foreach($availableProdis as $p)
                            <option value="{{ $p }}" {{ ($prodiFilter ?? '') == $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </form>
                
                <a href="{{ route('admin.final-project.proposals.bulk-zip', ['prodi' => $prodiFilter ?? null, 'type' => 'proposal']) }}" class="btn-download-zip">
                    <i class="bi bi-file-earmark-zip"></i> Download ZIP
                </a>

                @if($proposals->count() > 0)
                    <button type="button" class="btn-download-zip" style="background: linear-gradient(135deg, #4CAF50, #2E7D32); border: none; box-shadow: 0 4px 10px rgba(76, 175, 80, 0.2); cursor: pointer;" onclick="showBulkApproveModal()">
                        <i class="bi bi-check2-all"></i> Approve All
                    </button>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="alert-success">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if($proposals->count() > 0)
            @foreach($proposals as $proposal)
                <div class="approval-card">
                    <div class="approval-header">
                        <div class="student-section">
                            <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 5px;">
                                <h4 style="margin: 0;">{{ $proposal->finalProject->student->nama_lengkap }}</h4>
                                @php
                                    $docs = $proposal->finalProject->documents->where('document_type', 'proposal');
                                    $hasRevision = $docs->where('review_status', 'needs_revision')->isNotEmpty();
                                    $hasRejected = $docs->where('review_status', 'rejected')->isNotEmpty();
                                    
                                    $statusText = 'Pending';
                                    $statusClass = 'status-pending';
                                    
                                    if ($hasRejected) {
                                        $statusText = 'Ditolak (Cek Dokumen)';
                                        $statusClass = 'status-rejected';
                                    } elseif ($hasRevision) {
                                        $statusText = 'Perlu Revisi';
                                        $statusClass = 'status-needs_revision';
                                    }
                                @endphp
                                <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                            </div>
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
                            <strong>Judul TA (Bahasa Indonesia):</strong>
                            <p style="font-weight: 600; color: #333;">{{ $proposal->finalProject->title ?? '-' }}</p>
                        </div>
                        @if($proposal->finalProject->title_en)
                        <div class="detail-row">
                            <strong>Judul TA (Bahasa Inggris):</strong>
                            <p style="font-style: italic; color: #666;">{{ $proposal->finalProject->title_en }}</p>
                        </div>
                        @endif
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
                <h4>Tidak ada pendaftaran sempro pending</h4>
                <p>Semua pendaftaran sudah diproses</p>
            </div>
        @endif
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h4>Tolak Pendaftaran Sempro</h4>
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
            <h4>Setujui Pendaftaran Sempro</h4>
            <form id="approveForm" method="POST">
                @csrf
                @if(in_array(($role ?? null), ['superadmin', 'masteradmin'], true))
                    <div class="form-group">
                        <label>Jadwal Sempro (Opsional)</label>
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

    <!-- Bulk Approve Modal -->
    <div id="bulkApproveModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 800px; width: 100%;">
            <h4>Approve Masal Pendaftaran Sempro</h4>
            <p style="margin-bottom: 15px; color: #666;">Pilih mahasiswa yang ingin disetujui. Anda dapat menentukan jadwal yang berbeda untuk masing-masing mahasiswa.</p>
            <form id="bulkApproveForm" method="POST" action="{{ route('admin.final-project.proposals.approve-all') }}">
                @csrf
                <input type="hidden" name="prodi" value="{{ $prodiFilter ?? '' }}">
                
                <div class="table-responsive" style="max-height: 30vh; overflow-y: auto; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 15px;">
                    <table class="table" style="width: 100%; border-collapse: collapse; margin: 0;">
                        <thead style="background: #f5f5f5; position: sticky; top: 0; z-index: 1;">
                            <tr>
                                <th style="padding: 12px; border-bottom: 2px solid #ddd; text-align: center; width: 50px;">
                                    <input type="checkbox" id="checkAllProposals" onclick="toggleAllCheckboxes(this, 'proposal-checkbox')" checked>
                                </th>
                                <th style="padding: 12px; border-bottom: 2px solid #ddd; text-align: left;">Nama & Prodi</th>
                                @if(in_array(($role ?? null), ['superadmin', 'masteradmin'], true))
                                <th style="padding: 12px; border-bottom: 2px solid #ddd; text-align: left; width: 250px;">Jadwal Sempro (Opsional)</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($proposals as $proposal)
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px; text-align: center;">
                                    <input type="checkbox" class="proposal-checkbox" name="selected_ids[]" value="{{ $proposal->id }}" checked>
                                </td>
                                <td style="padding: 12px;">
                                    <div style="font-weight: 600; color: #333;">{{ $proposal->finalProject->student->nama_lengkap }}</div>
                                    <div style="font-size: 13px; color: #666; margin-top: 4px;">{{ $proposal->finalProject->student->program_studi }} - {{ $proposal->finalProject->student->nim }}</div>
                                </td>
                                @if(in_array(($role ?? null), ['superadmin', 'masteradmin'], true))
                                <td style="padding: 12px;">
                                    <input type="datetime-local" name="scheduled_dates[{{ $proposal->id }}]" class="form-control" style="width: 100%; padding: 8px; font-size: 13px;">
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ in_array(($role ?? null), ['superadmin', 'masteradmin'], true) ? 3 : 2 }}" style="padding: 20px; text-align: center; color: #888;">Tidak ada data pending</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="form-group">
                    <label>Catatan Umum (Opsional)</label>
                    <textarea name="approval_notes" class="form-control" rows="2" placeholder="Catatan ini akan berlaku untuk semua mahasiswa yang dicentang..."></textarea>
                </div>
                <div class="modal-actions" style="margin-top: 20px;">
                    <button type="button" class="btn-cancel" onclick="closeBulkApproveModal()">Batal</button>
                    <button type="submit" class="btn-approve-confirm" style="background: linear-gradient(135deg, #4CAF50, #2E7D32);">
                        <i class="bi bi-check2-all"></i> Approve Terpilih
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
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #F5F5F5;
    }

    .filter-toolbar {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .filter-form {
        margin: 0;
    }

    .select2-container--default .select2-selection--single {
        height: 42px;
        border: 2px solid #E0E0E0;
        border-radius: 10px;
        display: flex;
        align-items: center;
        transition: all 0.3s;
    }

    .select2-container--default .select2-selection--single:focus,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #FF9800;
        box-shadow: 0 0 0 4px rgba(255, 152, 0, 0.1);
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #333;
        font-weight: 500;
        font-size: 14px;
        line-height: normal;
        padding-left: 12px;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
        right: 8px;
    }

    .select2-dropdown {
        border: 2px solid #E0E0E0;
        border-radius: 10px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #E0E0E0;
        border-radius: 6px;
        padding: 8px 12px;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field:focus {
        border-color: #FF9800;
        outline: none;
    }

    .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
        background: #FFF3E0;
        color: #E65100;
        font-weight: 600;
    }

    .select2-results__option {
        padding: 10px 14px;
        font-size: 14px;
    }

    .btn-download-zip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 15px;
        background: linear-gradient(135deg, #FF9800, #F57C00);
        color: white;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        text-decoration: none;
        box-shadow: 0 4px 10px rgba(255, 152, 0, 0.3);
        transition: all 0.3s;
    }

    .btn-download-zip:hover {
        background: linear-gradient(135deg, #F57C00, #E65100);
        transform: translateY(-2px);
        color: white;
    }

    @media (max-width: 768px) {
        .card-header {
            flex-direction: column;
            align-items: flex-start;
        }
        .filter-toolbar {
            width: 100%;
            justify-content: space-between;
        }
        .form-select {
            width: 100%;
        }
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

    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        margin-left: 10px;
    }
    .status-badge.status-pending { background: #FFF9C4; color: #F9A825; }
    .status-badge.status-approved { background: #C8E6C9; color: #2E7D32; }
    .status-badge.status-rejected,
    .status-badge.status-needs_revision { background: #FFCDD2; color: #C62828; }

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
        z-index: 9999;
        padding: 40px 20px 100px 20px;
        box-sizing: border-box;
        overflow-y: auto;
    }

    .modal-content {
        background: white;
        border-radius: 15px;
        padding: 20px;
        max-width: 500px;
        width: 100%;
        margin: auto;
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
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2-prodi').select2({
        placeholder: "-- Cari Program Studi --",
        allowClear: true,
        width: '100%'
    });
});

function showRejectModal(id) {
    document.getElementById('rejectForm').action = `/admin/final-project/proposals/${id}/reject`;
    document.getElementById('rejectModal').style.display = 'flex';
}

function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}

function showApproveModal(id) {
    document.getElementById('approveForm').action = `/admin/final-project/proposals/${id}/approve`;
    document.getElementById('approveModal').style.display = 'flex';
}

function closeApproveModal() {
    document.getElementById('approveModal').style.display = 'none';
}

function showBulkApproveModal() {
    document.getElementById('bulkApproveModal').style.display = 'flex';
}

function closeBulkApproveModal() {
    document.getElementById('bulkApproveModal').style.display = 'none';
}

function toggleAllCheckboxes(source, className) {
    let checkboxes = document.getElementsByClassName(className);
    for(let i=0; i<checkboxes.length; i++) {
        checkboxes[i].checked = source.checked;
    }
}

window.onclick = function(event) {
    if (event.target == document.getElementById('rejectModal')) {
        closeRejectModal();
    }
    if (event.target == document.getElementById('approveModal')) {
        closeApproveModal();
    }
    if (event.target == document.getElementById('bulkApproveModal')) {
        closeBulkApproveModal();
    }
}
</script>
@endpush
