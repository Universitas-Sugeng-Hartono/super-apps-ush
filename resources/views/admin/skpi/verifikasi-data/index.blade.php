@extends('admin.layouts.super-app')

@section('content')
    <div class="page-shell">
        <div class="hero-card">
            <div>
                <span class="hero-badge">Review Prestasi Mahasiswa</span>
                <h3>Verifikasi Data SKPI</h3>
                <p>Prestasi yang disetujui di halaman ini menjadi data yang siap dipakai saat proses generate atau print SKPI.</p>
            </div>
            <div class="stats-grid">
                <div class="stat-card">
                    <span>Total</span>
                    <strong>{{ $stats['total'] }}</strong>
                </div>
                <div class="stat-card warning">
                    <span>Pending</span>
                    <strong>{{ $stats['pending'] }}</strong>
                </div>
                <div class="stat-card success">
                    <span>Approved</span>
                    <strong>{{ $stats['approved'] }}</strong>
                </div>
                <div class="stat-card danger">
                    <span>Rejected</span>
                    <strong>{{ $stats['rejected'] }}</strong>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert-success">
                <i class="bi bi-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="content-card">
            <form method="GET" action="{{ route('admin.skpi.verifikasi-data.index') }}" class="filter-form">
                <div class="filter-group search-group">
                    <label for="search">Cari Data</label>
                    <input type="text" id="search" name="search" class="form-control" value="{{ $search }}" placeholder="Nama mahasiswa, NIM, event, prestasi, tingkat">
                </div>
                <div class="filter-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="category">Jenis</label>
                    <select id="category" name="category" class="form-control">
                        <option value="">Semua Jenis</option>
                        @foreach($categoryOptions as $value => $label)
                            <option value="{{ $value }}" {{ $category === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>

                </div>
                <div class="filter-group">
                    <label for="program_studi">Program Studi</label>
                    <select id="program_studi" name="program_studi" class="form-control">
                        <option value="">Semua Program Studi</option>
                        @foreach($studyPrograms as $studyProgram)
                            <option value="{{ $studyProgram->name }}" {{ $programStudi === $studyProgram->name ? 'selected' : '' }}>
                                {{ $studyProgram->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn-filter">
                        <i class="bi bi-funnel"></i> Terapkan
                    </button>
                    <a href="{{ route('admin.skpi.verifikasi-data.index') }}" class="btn-reset">Reset</a>
                </div>
            </form>
        </div>

        @if($stats['pending'] > 0)
        <div class="content-card" style="background: #FFFBF0; border: 1px solid #F4E5CD;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <strong style="color:#C46A00;"><i class="bi bi-lightning-charge"></i> {{ $stats['pending'] }} data menunggu verifikasi</strong>
                    <p style="margin:4px 0 0; color:#6B7280; font-size:14px;">Klik tombol di samping untuk menyetujui semua data pending sekaligus.</p>
                </div>
                <button type="button" class="btn-approve" onclick="showApproveAllModal()">
                    <i class="bi bi-check-all"></i> Approve Semua Pending
                </button>
            </div>
        </div>
        @endif

        <div class="list-stack">
            @forelse($achievements as $achievement)
                @php
                    $statusClass = match($achievement->status) {
                        'approved' => 'approved',
                        'rejected' => 'rejected',
                        default => 'pending',
                    };
                    $statusLabel = match($achievement->status) {
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        default => 'Pending',
                    };
                @endphp

                <div class="achievement-card">
                    <div class="card-head">
                        <div>
                            <div class="student-meta">
                                <h4>{{ $achievement->student->nama_lengkap ?? '-' }}</h4>
                                <span class="status-pill {{ $statusClass }}">{{ $statusLabel }}</span>
                            </div>
                            <p>{{ $achievement->student->nim ?? '-' }} • {{ $achievement->student->program_studi ?? '-' }}</p>
                        </div>
                        <div class="submitted-meta">
                            <span>Diajukan</span>
                            <strong>{{ $achievement->created_at?->format('d M Y H:i') ?? '-' }}</strong>
                        </div>
                    </div>

                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Kategori</span>
                            <strong>{{ $achievement->category_label }}</strong>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Jenis Kegiatan</span>
                            <strong>{{ $achievement->activity_type_label ?? $achievement->activity_type }}</strong>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Tingkat</span>
                            <strong>{{ $achievement->level }}</strong>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Jabatan / Peran</span>
                            <strong>{{ $achievement->participation_role ?? '-' }}</strong>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Nilai SKP</span>
                            <strong class="skp-value">{{ $achievement->skp_points ?? 0 }}</strong>
                        </div>
                    </div>

                    <div class="supporting-row">
                        <div class="supporting-card">
                            <span class="detail-label">Bukti / Piagam</span>
                            @if($achievement->certificate)
                                <a href="{{ asset('storage/' . $achievement->certificate) }}" target="_blank" class="doc-link">
                                    <i class="bi bi-file-earmark-arrow-down"></i> Lihat Dokumen
                                </a>
                            @else
                                <p class="muted-copy">Mahasiswa belum mengunggah file pendukung.</p>
                            @endif
                        </div>
                        <div class="supporting-card">
                            <span class="detail-label">Catatan Review</span>
                            <p class="{{ $achievement->approval_notes ? '' : 'muted-copy' }}">
                                {{ $achievement->approval_notes ?: 'Belum ada catatan review.' }}
                            </p>
                        </div>
                        <div class="supporting-card">
                            <span class="detail-label">Reviewer</span>
                            <p class="{{ $achievement->approver ? '' : 'muted-copy' }}">
                                {{ $achievement->approver->name ?? 'Belum direview' }}
                            </p>
                        </div>
                    </div>

                    @if($achievement->status !== 'approved')
                        <div class="action-row">
                            <button type="button" class="btn-approve" onclick="showApproveModal({{ $achievement->id }})">
                                <i class="bi bi-check-circle"></i> Approve
                            </button>
                            <button type="button" class="btn-reject" onclick="showRejectModal({{ $achievement->id }})">
                                <i class="bi bi-x-circle"></i> Reject
                            </button>
                        </div>
                    @endif
                </div>
            @empty
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <h4>Belum ada data prestasi</h4>
                    <p>Data prestasi mahasiswa yang diajukan untuk SKPI akan tampil di halaman ini.</p>
                </div>
            @endforelse
        </div>

        @if($achievements->hasPages())
            <div class="pagination-wrap">
                {{ $achievements->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    <div id="approveModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h4>Approve Prestasi Mahasiswa</h4>
            <form id="approveForm" method="POST">
                @csrf
                <div class="form-group">
                    <label for="approve_notes">Catatan (Opsional)</label>
                    <textarea id="approve_notes" name="approval_notes" class="form-control textarea-control" rows="4" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeApproveModal()">Batal</button>
                    <button type="submit" class="btn-approve confirm">
                        <i class="bi bi-check-circle"></i> Setujui
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="rejectModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h4>Reject Prestasi Mahasiswa</h4>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="form-group">
                    <label for="reject_notes">Alasan Penolakan *</label>
                    <textarea id="reject_notes" name="approval_notes" class="form-control textarea-control" rows="4" required placeholder="Tuliskan alasan penolakan agar mahasiswa bisa memperbaiki data..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeRejectModal()">Batal</button>
                    <button type="submit" class="btn-reject confirm">
                        <i class="bi bi-x-circle"></i> Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div>

    {{-- MODAL: Approve All --}}
    <div id="approveAllModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h4>Approve Semua Data Pending</h4>
            <p style="color:#6B7280; margin-bottom:16px;">Anda akan menyetujui <strong>{{ $stats['pending'] }}</strong> data prestasi mahasiswa yang berstatus pending sekaligus.</p>
            <form id="approveAllForm" method="POST" action="{{ route('admin.skpi.verifikasi-data.approve-all') }}">
                @csrf
                <div class="form-group">
                    <label for="approve_all_notes">Catatan (Opsional)</label>
                    <textarea id="approve_all_notes" name="approval_notes" class="form-control textarea-control" rows="3" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeApproveAllModal()">Batal</button>
                    <button type="submit" class="btn-approve confirm">
                        <i class="bi bi-check-all"></i> Ya, Approve Semua
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('css')
<style>
    .page-shell {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .hero-card,
    .content-card,
    .achievement-card,
    .alert-success,
    .empty-state {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: var(--shadow);
    }

    .hero-card {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        background: linear-gradient(135deg, #FFF7EA, #FFFFFF);
        border: 1px solid #F4E5CD;
    }

    .hero-badge {
        display: inline-flex;
        padding: 6px 12px;
        border-radius: 999px;
        background: #FFEFD4;
        color: #C46A00;
        font-size: 12px;
        font-weight: 700;
    }

    .hero-card h3 {
        margin: 10px 0 10px;
        font-size: 28px;
        font-weight: 700;
        color: #213555;
    }

    .hero-card p,
    .muted-copy,
    .empty-state p {
        margin: 0;
        color: #6B7280;
        line-height: 1.6;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(120px, 1fr));
        gap: 12px;
        width: min(560px, 100%);
    }

    .stat-card {
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid #F0E2D0;
        border-radius: 16px;
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .stat-card span {
        font-size: 12px;
        color: #8A94A6;
    }

    .stat-card strong {
        font-size: 26px;
        color: #213555;
        line-height: 1;
    }

    .stat-card.warning strong {
        color: #B76E00;
    }

    .stat-card.success strong {
        color: #1E7A44;
    }

    .stat-card.danger strong {
        color: #C23934;
    }

    .alert-success {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #1E7A44;
        background: #EDF9F0;
        border: 1px solid #D7EEDC;
    }

    .filter-form {
        display: grid;
        grid-template-columns: minmax(240px, 1.5fr) repeat(3, minmax(160px, 1fr)) auto;
        gap: 16px;
        align-items: end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .filter-group label,
    .form-group label {
        font-size: 14px;
        font-weight: 700;
        color: #374151;
    }

    .form-control {
        width: 100%;
        border: 1px solid #D9DEE8;
        border-radius: 12px;
        padding: 12px 14px;
        font-size: 14px;
        color: #1F2937;
        font-family: inherit;
        background: white;
    }

    .form-control:focus {
        outline: none;
        border-color: #D97706;
        box-shadow: 0 0 0 4px rgba(217, 119, 6, 0.12);
    }

    .filter-actions {
        display: flex;
        gap: 10px;
    }

    .btn-filter,
    .btn-reset,
    .btn-approve,
    .btn-reject,
    .btn-cancel {
        border: none;
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: 0.2s ease;
    }

    .btn-filter {
        background: #D97706;
        color: white;
    }

    .btn-filter:hover {
        background: #B86102;
    }

    .btn-reset,
    .btn-cancel {
        background: #EEF2F7;
        color: #475569;
    }

    .btn-reset:hover,
    .btn-cancel:hover {
        background: #E2E8F0;
    }

    .list-stack {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .achievement-card {
        border: 1px solid #ECE6DA;
    }

    .card-head,
    .student-meta,
    .action-row,
    .modal-actions {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: center;
    }

    .student-meta {
        justify-content: flex-start;
    }

    .card-head {
        padding-bottom: 18px;
        margin-bottom: 18px;
        border-bottom: 1px dashed #E8DED0;
        align-items: flex-start;
    }

    .card-head h4,
    .empty-state h4,
    .modal-content h4 {
        margin: 0 0 8px;
        font-size: 21px;
        font-weight: 700;
        color: #213555;
    }

    .card-head p,
    .supporting-card p {
        margin: 0;
        color: #6B7280;
    }

    .submitted-meta {
        text-align: right;
    }

    .submitted-meta span,
    .detail-label {
        display: block;
        font-size: 12px;
        color: #8A94A6;
        margin-bottom: 6px;
    }

    .submitted-meta strong,
    .detail-item strong {
        color: #213555;
        font-size: 15px;
    }

    .status-pill {
        display: inline-flex;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .status-pill.pending {
        background: #FFF1DA;
        color: #C46A00;
    }

    .status-pill.approved {
        background: #E8F7EE;
        color: #1E7A44;
    }

    .status-pill.rejected {
        background: #FDE8E7;
        color: #C23934;
    }

    .detail-grid,
    .supporting-row {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 14px;
    }

    .supporting-row {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        margin-top: 14px;
    }

    .detail-item,
    .supporting-card {
        background: #FAFBFC;
        border: 1px solid #EDF0F4;
        border-radius: 14px;
        padding: 16px;
    }

    .doc-link {
        text-decoration: none;
        color: #1D4ED8;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .action-row {
        justify-content: flex-end;
        margin-top: 18px;
        padding-top: 18px;
        border-top: 1px dashed #E8DED0;
    }

    .btn-approve {
        background: #1E7A44;
        color: white;
    }

    .btn-approve:hover {
        background: #176238;
    }

    .btn-reject {
        background: #C23934;
        color: white;
    }

    .btn-reject:hover {
        background: #A92F2B;
    }

    .pagination-wrap {
        display: flex;
        justify-content: center;
    }

    .empty-state {
        text-align: center;
        padding: 60px 24px;
    }

    .empty-state i {
        font-size: 64px;
        color: #D1D5DB;
        margin-bottom: 14px;
    }

    .modal {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 20px;
    }

    .modal-content {
        width: min(560px, 100%);
        background: white;
        border-radius: 18px;
        padding: 26px;
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.2);
    }

    .form-group {
        margin-bottom: 18px;
    }

    .textarea-control {
        min-height: 120px;
        resize: vertical;
    }

    @media (max-width: 1100px) {
        .hero-card,
        .card-head {
            flex-direction: column;
        }

        .filter-form,
        .stats-grid,
        .detail-grid,
        .supporting-row {
            grid-template-columns: 1fr;
        }

        .submitted-meta,
        .action-row {
            width: 100%;
            text-align: left;
            justify-content: flex-start;
        }
    }

    .skp-value {
        color: #1565C0 !important;
        font-size: 18px !important;
    }
</style>
@endpush

@push('scripts')
<script>
function showApproveModal(achievementId) {
    const modal = document.getElementById('approveModal');
    const form = document.getElementById('approveForm');
    form.action = `/admin/skpi/verifikasi-data/${achievementId}/approve`;
    modal.style.display = 'flex';
}

function closeApproveModal() {
    document.getElementById('approveModal').style.display = 'none';
}

function showRejectModal(achievementId) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    form.action = `/admin/skpi/verifikasi-data/${achievementId}/reject`;
    modal.style.display = 'flex';
}

function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}

function showApproveAllModal() {
    document.getElementById('approveAllModal').style.display = 'flex';
}

function closeApproveAllModal() {
    document.getElementById('approveAllModal').style.display = 'none';
}
</script>
@endpush
