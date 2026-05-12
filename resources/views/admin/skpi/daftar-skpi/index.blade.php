@extends('admin.layouts.super-app')

@section('content')
<div class="page-shell">
    <div class="mb-3" style="padding-top: 10px;">
        <a href="{{ route('admin.skpi.index') }}" class="text-decoration-none text-secondary" style="font-weight: 600; font-size: 15px; display: inline-flex; align-items: center; gap: 8px;">
            <i class="bi bi-arrow-left"></i> Kembali ke Menu Utama SKPI
        </a>
    </div>

    <div class="hero-card shadow-sm">
        <div class="hero-content">
            <span class="hero-badge">Manajemen Pendaftaran</span>
            <h3>Daftar Pengajuan SKPI</h3>
            <p>Review pendaftar SKPI, kelola nomor ijazah, dan pantau status verifikasi mahasiswa secara terpusat.</p>
        </div>
        <div class="stats-row">
            <div class="stat-box">
                <span class="stat-label">Total</span>
                <span class="stat-value">{{ $stats['total'] }}</span>
            </div>
            <div class="stat-box warning">
                <span class="stat-label">Pending</span>
                <span class="stat-value">{{ $stats['pending'] }}</span>
            </div>
            <div class="stat-box success">
                <span class="stat-label">Approved</span>
                <span class="stat-value">{{ $stats['approved'] }}</span>
            </div>
            <div class="stat-box info">
                <span class="stat-label">Revision</span>
                <span class="stat-value">{{ $stats['needs_revision'] }}</span>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert-modern alert-success-modern animate__animated animate__fadeIn">
        <i class="bi bi-check-circle-fill"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="alert-modern alert-danger-modern animate__animated animate__shakeX">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <div>
            <strong>Terjadi Kesalahan:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <div class="filter-card shadow-sm">
        <form method="GET" action="{{ route('admin.skpi.daftar-skpi.index') }}" class="filter-grid">
            <div class="filter-item">
                <label>Cari Mahasiswa</label>
                <div class="input-with-icon">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" class="form-control-modern" value="{{ $search }}" placeholder="Nama, NIM, atau Prodi...">
                </div>
            </div>
            <div class="filter-item">
                <label>Status</label>
                <select name="status" class="form-select-modern" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="needs_revision" {{ $status === 'needs_revision' ? 'selected' : '' }}>Need Revision</option>
                    <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="filter-item">
                <label>Program Studi</label>
                <select name="study_program_id" class="form-select-modern" onchange="this.form.submit()">
                    <option value="">Semua Program Studi</option>
                    @foreach($studyPrograms as $prodi)
                    <option value="{{ $prodi->id }}" {{ $studyProgramId == $prodi->id ? 'selected' : '' }}>{{ $prodi->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-actions-modern">
                <a href="{{ route('admin.skpi.daftar-skpi.index') }}" class="btn-reset-modern" title="Reset Filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
                <button type="submit" class="btn-search-modern">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
        </form>
    </div>

    @if($stats['pending'] > 0)
    <div class="quick-action-bar animate__animated animate__fadeInUp">
        <div class="quick-info">
            <div class="pulse-icon"><i class="bi bi-lightning-fill"></i></div>
            <div>
                <strong>Terdapat {{ $stats['pending'] }} antrean pending</strong>
                <p>Gunakan fitur approve massal jika data sudah tervalidasi semua.</p>
            </div>
        </div>
        <button type="button" class="btn-bulk-approve" onclick="showApproveAllModal()">
            <i class="bi bi-check-all"></i> Approve Semua Pending
        </button>
    </div>
    @endif

    <div class="table-card shadow-sm">
        <div class="table-responsive-modern">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th>Mahasiswa & NIM</th>
                        <th>Program Studi</th>
                        <th>Kelengkapan</th>
                        <th>Nomor Ijazah</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrations as $reg)
                    @php
                    $s = $reg->student;
                    $hasIpkSks = filled($s->ipk) && filled($s->sks);
                    $hasFinalProject = filled(optional($s->finalProject)->title);
                    $hasFoto = filled($s->foto);
                    $hasTtd = filled($s->ttd);
                    $allReady = $hasIpkSks && $hasFinalProject && $hasFoto && $hasTtd;
                    @endphp
                    <tr>
                        <td>
                            <div class="student-profile">
                                <div class="student-meta">
                                    <span class="student-name">{{ $reg->nama_lengkap }}</span>
                                    <span class="student-nim">{{ $reg->nim }} <span class="divider"></span> Angkatan {{ $reg->angkatan }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="prodi-tag">{{ $s->program_studi ?? '-' }}</span>
                        </td>
                        <td>
                            <div class="prereq-container">
                                <div class="prereq-item {{ $hasIpkSks ? 'done' : 'missing' }}" title="IPK/SKS">I</div>
                                <div class="prereq-item {{ $hasFinalProject ? 'done' : 'missing' }}" title="Tugas Akhir">T</div>
                                <div class="prereq-item {{ $hasFoto ? 'done' : 'missing' }}" title="Foto">F</div>
                                <div class="prereq-item {{ $hasTtd ? 'done' : 'missing' }}" title="TTD">S</div>
                                @if($allReady)
                                <i class="bi bi-shield-check-fill text-success ms-2" title="Semua Prasyarat Terpenuhi"></i>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($reg->nomor_ijazah)
                            <div class="ijazah-box">
                                <code>{{ $reg->nomor_ijazah }}</code>
                                <button class="btn-edit-inline" onclick="showEditIjazahModal({{ $reg->id }}, '{{ addslashes($reg->nama_lengkap) }}', '{{ $reg->nomor_ijazah }}')" title="Edit Nomor Ijazah">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                            </div>
                            @else
                            <span class="empty-text">Belum diinput</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge-status status-{{ $reg->status }}">
                                {{ match($reg->status) {
                                    'approved' => 'Approved',
                                    'needs_revision' => 'Revision',
                                    'rejected' => 'Rejected',
                                    default => 'Pending'
                                } }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group-modern">
                                @if($reg->status !== 'approved')
                                <button class="btn-table btn-table-success" onclick="showApproveModal({{ $reg->id }}, '{{ addslashes($reg->nama_lengkap) }}')" title="Approve">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                @endif
                                <button class="btn-table btn-table-primary" onclick="showRevisionModal({{ $reg->id }}, '{{ addslashes($reg->nama_lengkap) }}')" title="Minta Revisi">
                                    <i class="bi bi-arrow-repeat"></i>
                                </button>
                                <button class="btn-table btn-table-danger" onclick="showRejectModal({{ $reg->id }}, '{{ addslashes($reg->nama_lengkap) }}')" title="Tolak">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-table">
                                <img src="https://illustrations.popsy.co/amber/box.svg" alt="empty" style="width: 120px;">
                                <p>Data pendaftaran tidak ditemukan</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($registrations->hasPages())
    <div class="pagination-container">
        {{ $registrations->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

{{-- Modal Approve --}}
<div id="approveModal" class="modal-backdrop-modern" style="display: none;">
    <div class="modal-dialog-modern">
        <div class="modal-header-modern bg-success">
            <div class="modal-icon-wrap"><i class="bi bi-check-circle-fill"></i></div>
            <div class="modal-title-wrap">
                <h4>Setujui Pendaftaran</h4>
                <p id="approve_target_name"></p>
            </div>
        </div>
        <form id="approveForm" method="POST">
            @csrf
            @method('PATCH')
            <div class="modal-body-modern">
                <div class="form-group-modern">
                    <label>Nomor Ijazah <span class="text-danger">*</span></label>
                    <input type="text" name="nomor_ijazah" class="form-control-modern" placeholder="Contoh: 12345/UN/2026" required autofocus>
                    <small class="helper-text">Pastikan nomor ijazah sudah sesuai dengan fisik ijazah.</small>
                </div>
                <div class="form-group-modern mt-3">
                    <label>Catatan (Opsional)</label>
                    <textarea name="approval_notes" class="form-control-modern" rows="3" placeholder="Tulis catatan jika diperlukan..."></textarea>
                </div>
            </div>
            <div class="modal-footer-modern">
                <button type="button" class="btn-cancel-modern" onclick="closeApproveModal()">Batal</button>
                <button type="submit" class="btn-submit-modern bg-success">Setujui & Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Ijazah (Koreksi) --}}
<div id="editIjazahModal" class="modal-backdrop-modern" style="display: none;">
    <div class="modal-dialog-modern">
        <div class="modal-header-modern bg-primary">
            <div class="modal-icon-wrap"><i class="bi bi-pencil-fill"></i></div>
            <div class="modal-title-wrap">
                <h4>Koreksi Nomor Ijazah</h4>
                <p id="edit_ijazah_target_name"></p>
            </div>
        </div>
        <form id="editIjazahForm" method="POST">
            @csrf
            @method('PATCH')
            <div class="modal-body-modern">
                <div class="form-group-modern">
                    <label>Nomor Ijazah Baru <span class="text-danger">*</span></label>
                    <input type="text" id="edit_nomor_ijazah_input" name="nomor_ijazah" class="form-control-modern" placeholder="Masukkan nomor ijazah yang benar..." required>
                    <small class="helper-text">Perubahan ini hanya memperbarui data tanpa mengubah status approval.</small>
                </div>
            </div>
            <div class="modal-footer-modern">
                <button type="button" class="btn-cancel-modern" onclick="closeEditIjazahModal()">Batal</button>
                <button type="submit" class="btn-submit-modern bg-primary">Update Nomor</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Revision --}}
<div id="revisionModal" class="modal-backdrop-modern" style="display: none;">
    <div class="modal-dialog-modern">
        <div class="modal-header-modern bg-warning">
            <div class="modal-icon-wrap"><i class="bi bi-arrow-repeat"></i></div>
            <div class="modal-title-wrap">
                <h4>Minta Revisi</h4>
                <p id="revision_target_name"></p>
            </div>
        </div>
        <form id="revisionForm" method="POST">
            @csrf
            @method('PATCH')
            <div class="modal-body-modern">
                <div class="form-group-modern">
                    <label>Catatan Revisi <span class="text-danger">*</span></label>
                    <textarea name="approval_notes" class="form-control-modern" rows="4" required placeholder="Jelaskan detail data yang harus diperbaiki mahasiswa..."></textarea>
                </div>
            </div>
            <div class="modal-footer-modern">
                <button type="button" class="btn-cancel-modern" onclick="closeRevisionModal()">Batal</button>
                <button type="submit" class="btn-submit-modern bg-warning">Kirim Revisi</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Reject --}}
<div id="rejectModal" class="modal-backdrop-modern" style="display: none;">
    <div class="modal-dialog-modern">
        <div class="modal-header-modern bg-danger">
            <div class="modal-icon-wrap"><i class="bi bi-x-circle-fill"></i></div>
            <div class="modal-title-wrap">
                <h4>Tolak Pendaftaran</h4>
                <p id="reject_target_name"></p>
            </div>
        </div>
        <form id="rejectForm" method="POST">
            @csrf
            @method('PATCH')
            <div class="modal-body-modern">
                <div class="form-group-modern">
                    <label>Alasan Penolakan <span class="text-danger">*</span></label>
                    <textarea name="approval_notes" class="form-control-modern" rows="4" required placeholder="Tulis alasan penolakan yang jelas..."></textarea>
                </div>
            </div>
            <div class="modal-footer-modern">
                <button type="button" class="btn-cancel-modern" onclick="closeRejectModal()">Batal</button>
                <button type="submit" class="btn-submit-modern bg-danger">Tolak Permanen</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Approve Semua --}}
<div id="approveAllModal" class="modal-backdrop-modern" style="display: none;">
    <div class="modal-dialog-modern">
        <div class="modal-header-modern bg-success">
            <div class="modal-icon-wrap"><i class="bi bi-check-all"></i></div>
            <div class="modal-title-wrap">
                <h4>Approve Semua Pending</h4>
                <p>Menyetujui {{ $stats['pending'] }} pengajuan sekaligus.</p>
            </div>
        </div>
        <form id="approveAllForm" method="POST" action="{{ route('admin.skpi.daftar-skpi.approve-all') }}">
            @csrf
            <div class="modal-body-modern">
                <div class="alert-info-modern">
                    <i class="bi bi-info-circle"></i>
                    <span>Fitur ini untuk approval cepat. Nomor ijazah bisa dikoreksi secara manual nanti jika diperlukan.</span>
                </div>
                <div class="form-group-modern mt-3">
                    <label>Catatan Massal (Opsional)</label>
                    <textarea name="approval_notes" class="form-control-modern" rows="3" placeholder="Catatan ini akan muncul di semua pengajuan..."></textarea>
                </div>
            </div>
            <div class="modal-footer-modern">
                <button type="button" class="btn-cancel-modern" onclick="closeApproveAllModal()">Batal</button>
                <button type="submit" class="btn-submit-modern bg-success">Approve Massal</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('css')
<style>
    :root {
        --primary: #2563EB;
        --success: #10B981;
        --warning: #F59E0B;
        --danger: #EF4444;
        --slate-50: #F8FAFC;
        --slate-200: #E2E8F0;
        --slate-700: #334155;
    }

    .page-shell {
        display: flex;
        flex-direction: column;
        gap: 20px;
        padding-bottom: 50px;
    }

    /* Hero Card */
    .hero-card {
        background: #213158;
        border-radius: 20px;
        padding: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
        border: 1px solid var(--slate-200);
    }

    .hero-content h3 {
        font-size: 26px;
        font-weight: 800;
        color: #d5b228;
        margin: 8px 0;
    }

    .hero-content p {
        color: #ffffffff;
        margin: 0;
        font-size: 15px;
    }

    .hero-badge {
        background: #EEF2FF;
        color: var(--primary);
        padding: 5px 12px;
        border-radius: 99px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stats-row {
        display: flex;
        gap: 12px;
    }

    .stat-box {
        background: var(--slate-50);
        padding: 12px 20px;
        border-radius: 16px;
        border: 1px solid var(--slate-200);
        display: flex;
        flex-direction: column;
        min-width: 90px;
    }

    .stat-label {
        font-size: 11px;
        color: #94A3B8;
        font-weight: 700;
        text-transform: uppercase;
    }

    .stat-value {
        font-size: 22px;
        font-weight: 800;
        color: var(--slate-700);
    }

    .stat-box.warning .stat-value {
        color: var(--warning);
    }

    .stat-box.success .stat-value {
        color: var(--success);
    }

    .stat-box.info .stat-value {
        color: var(--primary);
    }

    /* Filter Grid - FIXED Layout */
    .filter-card {
        background: white;
        padding: 24px;
        border-radius: 20px;
        border: 1px solid var(--slate-200);
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr) auto;
        gap: 20px;
        align-items: flex-end;
    }

    .filter-item {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .filter-item label {
        font-size: 13px;
        font-weight: 700;
        color: var(--slate-700);
    }

    .form-control-modern,
    .form-select-modern {
        height: 46px;
        border-radius: 12px;
        border: 1px solid var(--slate-200);
        padding: 0 15px;
        font-size: 14px;
        background: #fff;
        transition: all 0.2s;
    }

    .form-control-modern:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        outline: none;
    }

    .input-with-icon {
        position: relative;
    }

    .input-with-icon i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #94A3B8;
    }

    .input-with-icon input {
        padding-left: 40px;
        width: 100%;
    }

    .filter-actions-modern {
        display: flex;
        gap: 10px;
    }

    .btn-search-modern {
        height: 46px;
        background: var(--slate-700);
        color: white;
        border: none;
        padding: 0 25px;
        border-radius: 12px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .btn-reset-modern {
        height: 46px;
        width: 46px;
        background: var(--slate-50);
        color: #64748B;
        border: 1px solid var(--slate-200);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        text-decoration: none;
    }

    /* Quick Action Bar */
    .quick-action-bar {
        background: linear-gradient(135deg, #FFF7ED, #FFEDD5);
        border: 1px solid #FED7AA;
        border-radius: 18px;
        padding: 15px 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .quick-info {
        display: flex;
        gap: 15px;
        align-items: center;
    }

    .pulse-icon {
        width: 40px;
        height: 40px;
        background: #FB923C;
        color: white;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        animation: pulse-orange 2s infinite;
    }

    .quick-info strong {
        color: #9A3412;
        font-size: 16px;
    }

    .quick-info p {
        margin: 0;
        color: #C2410C;
        font-size: 13px;
    }

    .btn-bulk-approve {
        background: #15803D;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 12px;
        font-weight: 700;
        cursor: pointer;
    }

    /* Table */
    .table-card {
        background: white;
        border-radius: 20px;
        border: 1px solid var(--slate-200);
        overflow: hidden;
    }

    .table-modern {
        width: 100%;
        border-collapse: collapse;
    }

    .table-modern th {
        background: #213158;
        padding: 18px 20px;
        text-align: left;
        font-size: 12px;
        font-weight: 800;
        color: #ffffffff;
        text-transform: uppercase;
        border-bottom: 1px solid var(--slate-200);
    }

    .table-modern td {
        padding: 18px 20px;
        border-bottom: 1px solid #F1F5F9;
        vertical-align: middle;
    }

    .table-modern tr:hover {
        background: #FBFDFF;
    }

    .student-profile {
        display: flex;
        flex-direction: column;
    }

    .student-name {
        font-weight: 700;
        color: var(--slate-700);
        font-size: 15px;
    }

    .student-nim {
        font-size: 12px;
        color: #94A3B8;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .divider {
        width: 4px;
        height: 4px;
        background: #CBD5E1;
        border-radius: 50%;
    }

    .prodi-tag {
        background: #F1F5F9;
        color: #475569;
        padding: 4px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
    }

    .prereq-container {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .prereq-item {
        width: 24px;
        height: 24px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 800;
        color: white;
        cursor: default;
    }

    .prereq-item.done {
        background: var(--success);
    }

    .prereq-item.missing {
        background: #FEE2E2;
        color: #EF4444;
    }

    .ijazah-box {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #F8FAFC;
        padding: 6px 12px;
        border-radius: 8px;
        border: 1px dashed var(--slate-200);
        width: fit-content;
    }

    .ijazah-box code {
        color: #334155;
        font-weight: 700;
    }

    .btn-edit-inline {
        background: none;
        border: none;
        color: var(--primary);
        cursor: pointer;
        padding: 0;
        font-size: 14px;
        display: flex;
        align-items: center;
    }

    .badge-status {
        padding: 6px 14px;
        border-radius: 99px;
        font-size: 11px;
        font-weight: 800;
    }

    .status-pending {
        background: #FFF7ED;
        color: #C2410C;
    }

    .status-approved {
        background: #ECFDF5;
        color: #059669;
    }

    .status-needs_revision {
        background: #EFF6FF;
        color: #2563EB;
    }

    .status-rejected {
        background: #FEF2F2;
        color: #DC2626;
    }

    .btn-group-modern {
        display: flex;
        gap: 8px;
        justify-content: center;
    }

    .btn-table {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-table-success {
        background: var(--success);
    }

    .btn-table-primary {
        background: var(--primary);
    }

    .btn-table-danger {
        background: var(--danger);
    }

    .btn-table:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    /* Modals - FIXED Style */
    .modal-backdrop-modern {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 20px;
    }

    .modal-dialog-modern {
        background: white;
        width: 100%;
        max-width: 550px;
        border-radius: 28px;
        overflow: hidden;
        box-shadow: 0 25px 70px -12px rgba(0, 0, 0, 0.3);
        animation: modal-slide-in 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .modal-header-modern {
        padding: 30px;
        display: flex;
        gap: 20px;
        align-items: center;
        color: white;
    }

    .modal-icon-wrap {
        width: 54px;
        height: 54px;
        background: rgba(255, 255, 255, 0.25);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        flex-shrink: 0;
    }

    .modal-title-wrap h4 {
        margin: 0;
        font-size: 20px;
        font-weight: 800;
        line-height: 1.2;
    }

    .modal-title-wrap p {
        margin: 4px 0 0;
        opacity: 0.85;
        font-size: 13.5px;
    }

    .modal-body-modern {
        padding: 30px;
    }

    .modal-footer-modern {
        padding: 20px 30px;
        background: #F8FAFC;
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        border-top: 1px solid #F1F5F9;
    }

    .btn-cancel-modern {
        background: white;
        border: 1px solid #E2E8F0;
        padding: 12px 24px;
        border-radius: 14px;
        font-weight: 700;
        color: #64748B;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-cancel-modern:hover {
        background: #F1F5F9;
        border-color: #CBD5E1;
        color: #334155;
    }

    .btn-submit-modern {
        border: none;
        padding: 12px 28px;
        border-radius: 14px;
        font-weight: 700;
        color: white;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .btn-submit-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        filter: brightness(1.1);
    }

    .form-group-modern {
        display: flex;
        flex-direction: column;
        gap: 10px;
        width: 100%;
        margin-bottom: 20px;
    }

    .form-group-modern:last-child {
        margin-bottom: 0;
    }

    .form-group-modern label {
        font-size: 14px;
        font-weight: 700;
        color: #334155;
        display: block;
        text-align: left;
    }

    .form-control-modern {
        width: 100% !important;
        display: block;
        border-radius: 14px;
        border: 1.5px solid #E2E8F0;
        padding: 12px 16px;
        font-size: 14.5px;
        transition: all 0.2s;
        background: #FFF;
        box-sizing: border-box;
    }

    .form-control-modern:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        outline: none;
    }

    textarea.form-control-modern {
        resize: none;
        min-height: 120px;
        line-height: 1.6;
    }

    .alert-modern {
        padding: 16px 20px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
        font-weight: 600;
    }

    .alert-success-modern {
        background: #ECFDF5;
        border: 1px solid #D1FAE5;
        color: #065F46;
    }

    .alert-danger-modern {
        background: #FEF2F2;
        border: 1px solid #FEE2E2;
        color: #991B1B;
    }

    .helper-text {
        display: block;
        margin-top: 5px;
        color: #94A3B8;
        font-size: 12px;
    }

    .alert-info-modern {
        display: flex;
        gap: 12px;
        padding: 12px;
        background: #EFF6FF;
        border-radius: 12px;
        color: #1E40AF;
        font-size: 13px;
    }

    @keyframes pulse-orange {
        0% {
            box-shadow: 0 0 0 0 rgba(251, 146, 60, 0.7);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(251, 146, 60, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(251, 146, 60, 0);
        }
    }

    @keyframes modal-slide-in {
        from {
            opacity: 0;
            transform: translateY(20px) scale(0.98);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
</style>
@endpush

@push('scripts')
<script>
    function showApproveModal(id, name) {
        document.getElementById('approve_target_name').innerText = name;
        document.getElementById('approveForm').action = `/admin/skpi/daftar-skpi/${id}/approve`;
        document.getElementById('approveModal').style.display = 'flex';
    }

    function closeApproveModal() {
        document.getElementById('approveModal').style.display = 'none';
    }

    function showEditIjazahModal(id, name, currentNumber) {
        document.getElementById('edit_ijazah_target_name').innerText = name;
        document.getElementById('edit_nomor_ijazah_input').value = currentNumber;
        document.getElementById('editIjazahForm').action = `/admin/skpi/daftar-skpi/${id}/update-ijazah`;
        document.getElementById('editIjazahModal').style.display = 'flex';
    }

    function closeEditIjazahModal() {
        document.getElementById('editIjazahModal').style.display = 'none';
    }

    function showRevisionModal(id, name) {
        document.getElementById('revision_target_name').innerText = name;
        document.getElementById('revisionForm').action = `/admin/skpi/daftar-skpi/${id}/revision`;
        document.getElementById('revisionModal').style.display = 'flex';
    }

    function closeRevisionModal() {
        document.getElementById('revisionModal').style.display = 'none';
    }

    function showRejectModal(id, name) {
        document.getElementById('reject_target_name').innerText = name;
        document.getElementById('rejectForm').action = `/admin/skpi/daftar-skpi/${id}/reject`;
        document.getElementById('rejectModal').style.display = 'flex';
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

    window.onclick = function(event) {
        if (event.target.classList.contains('modal-backdrop-modern')) {
            event.target.style.display = 'none';
        }
    }
</script>
@endpush