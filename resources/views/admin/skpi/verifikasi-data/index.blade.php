@extends('admin.layouts.super-app')

@section('content')
<div class="page-shell">
    <div class="mb-3" style="padding-top: 10px;">
        <a href="{{ route('admin.skpi.index') }}" class="btn-back">
            <i class="bi bi-skip-backward-fill"></i> Kembali ke Menu Utama SKPI
        </a>
    </div>
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
        <form method="GET" action="{{ route('admin.skpi.verifikasi-data.index') }}" class="filter-form" id="filterForm">
            <div class="filter-group search-group">
                <label for="search">Cari Data</label>
                <input type="text" id="search" name="search" class="form-control" value="{{ $search }}" placeholder="Nama mahasiswa, NIM, event, prestasi, tingkat" oninput="debounceFilter()">
            </div>
            <div class="filter-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control" onchange="document.getElementById('filterForm').submit()">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="category">Jenis</label>
                <select id="category" name="category" class="form-control" onchange="document.getElementById('filterForm').submit()">
                    <option value="">Semua Jenis</option>
                    @foreach($categoryOptions as $value => $label)
                    <option value="{{ $value }}" {{ $category === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>

            </div>
            <div class="filter-group">
                <label for="program_studi">Program Studi</label>
                <select id="program_studi" name="program_studi" class="form-control" onchange="document.getElementById('filterForm').submit()">
                    <option value="">Semua Program Studi</option>
                    @foreach($studyPrograms as $studyProgram)
                    <option value="{{ $studyProgram->name }}" {{ $programStudi === $studyProgram->name ? 'selected' : '' }}>
                        {{ $studyProgram->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-actions">
                <a href="{{ route('admin.skpi.verifikasi-data.index') }}" class="btn-reset" style="margin-top: 22px;">Reset</a>
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

    @if($students->count() > 0)
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Mahasiswa</th>
                    <th>Program Studi</th>
                    <th>Total Pengajuan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $student)
                @php
                $achievementsData = $student->achievements->map(function ($ach) {
                $statusClass = match($ach->status) {
                'approved' => 'status-aktif',
                'rejected' => 'status-nonaktif',
                default => 'status-cuti',
                };
                $statusLabel = match($ach->status) {
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                default => 'Pending',
                };

                return [
                'id' => $ach->id,
                'category_raw' => $ach->category,
                'activity_type_raw' => $ach->activity_type,
                'kategori' => $ach->category_label,
                'kegiatan' => $ach->activity_type_label ?? $ach->activity_type,
                'event' => ($ach->event && $ach->event !== '-') ? $ach->event : null,
                'organizer' => $ach->organizer ?: '-',
                'event_year' => $ach->event_year ?: '-',
                'tingkat' => $ach->level,
                'peran' => $ach->participation_role ?? '-',
                'skp' => $ach->skp_points ?? 0,
                'status' => $ach->status,
                'statusClass' => $statusClass,
                'statusLabel' => $statusLabel,
                'tanggal' => $ach->created_at?->format('d M Y H:i') ?? '-',
                'file' => $ach->certificate ? asset('storage/' . $ach->certificate) : '',
                'catatan' => $ach->approval_notes ?: 'Belum ada catatan review.',
                'reviewer' => $ach->approver->name ?? 'Belum direview'
                ];
                });

                @endphp
                <tr>
                    <td>{{ $loop->iteration + ($students->currentPage() - 1) * $students->perPage() }}</td>
                    <td>
                        <strong>{{ $student->nama_lengkap ?? '-' }}</strong><br>
                        <span class="font-monospace text-muted" style="font-size: 12px;">{{ $student->nim ?? '-' }}</span>
                    </td>
                    <td>
                        <span class="badge-prodi" style="display: inline-block;">{{ $student->program_studi ?? '-' }}</span>
                    </td>
                    <td>
                        <span class="badge-count">{{ $student->achievements->count() }} Data</span>
                        @php
                        $pendingCount = $student->achievements->where('status', 'pending')->count();
                        @endphp
                        @if($pendingCount > 0)
                        <span class="status-badge status-cuti" style="font-size: 11px; padding: 4px 10px; margin-left: 4px;">
                            <i class="bi bi-clock-history"></i> {{ $pendingCount }} Pending
                        </span>
                        @endif
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button type="button" class="btn-view"
                                data-nama="{{ $student->nama_lengkap ?? '-' }}"
                                data-achievements="{{ base64_encode(json_encode($achievementsData->values())) }}"
                                onclick="showStudentAchievementsModal(this)">
                                <i class="bi bi-list-task"></i> Lihat Prestasi
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty-state">
        <i class="bi bi-inbox"></i>
        <h4>Belum ada data prestasi</h4>
        <p>Data prestasi mahasiswa yang diajukan untuk SKPI akan tampil di halaman ini.</p>
    </div>
    @endif

    @if($students->hasPages())
    <div class="pagination-wrap">
        {{ $students->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

{{-- MODAL: Detail Prestasi --}}
<div id="detailModal" class="modal" style="display: none;">
    <div class="modal-content modal-detail">
        <!-- Modal Header -->
        <div class="modal-hdr">
            <div>
                <h4 class="modal-hdr-title">Detail Prestasi Mahasiswa</h4>
                <p class="modal-hdr-sub" id="detail_nama"></p>
            </div>
            <button type="button" onclick="closeDetailModal()" class="modal-close-btn">
                <i class="bi bi-x"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body-pad">
            <div class="detail-info-grid">
                <div class="detail-info-item">
                    <span class="detail-info-label">Kategori</span>
                    <strong id="detail_kategori" class="detail-info-value"></strong>
                </div>
                <div class="detail-info-item">
                    <span class="detail-info-label">Jenis Kegiatan</span>
                    <strong id="detail_kegiatan" class="detail-info-value"></strong>
                </div>
                <div class="detail-info-item">
                    <span class="detail-info-label">Nama Acara / Kegiatan</span>
                    <strong id="detail_event" class="detail-info-value" style="color: #1D4ED8;"></strong>
                </div>
                <div class="detail-info-item">
                    <span class="detail-info-label">Diselenggarakan Oleh</span>
                    <strong id="detail_organizer" class="detail-info-value"></strong>
                </div>
                <div class="detail-info-item">
                    <span class="detail-info-label">Tahun Kegiatan</span>
                    <strong id="detail_event_year" class="detail-info-value"></strong>
                </div>
                <div class="detail-info-item">
                    <span class="detail-info-label">Tingkat</span>
                    <strong id="detail_tingkat" class="detail-info-value"></strong>
                </div>
                <div class="detail-info-item">
                    <span class="detail-info-label">Peran / Jabatan</span>
                    <strong id="detail_peran" class="detail-info-value"></strong>
                </div>
                <div class="detail-info-item">
                    <span class="detail-info-label">Poin SKP</span>
                    <strong id="detail_skp" class="detail-info-value skp-value"></strong>
                </div>
                <div class="detail-info-item">
                    <span class="detail-info-label">Tanggal Diajukan</span>
                    <strong id="detail_tanggal" class="detail-info-value"></strong>
                </div>
            </div>

            <div class="detail-section">
                <span class="detail-section-label">File Bukti / Piagam</span>
                <div id="detail_file_container" class="detail-file-box">
                    <!-- Diisi via JS -->
                </div>
            </div>

            <div class="detail-section">
                <span class="detail-section-label">Catatan Reviewer</span>
                <div class="detail-notes-box">
                    <div class="detail-notes-inner">
                        <i class="bi bi-chat-left-text detail-notes-icon"></i>
                        <div>
                            <p id="detail_catatan" class="detail-catatan-text"></p>
                            <p class="detail-reviewer-text">Reviewer: <span id="detail_reviewer" style="font-weight: 400;"></span></p>
                        </div>
                    </div>
                </div>
            </div>

            <div id="detail_action_container" class="detail-action-bar">
                <!-- Diisi via JS (Tombol Approve & Reject) -->
            </div>
        </div>
    </div>
</div>

{{-- MODAL 1: Daftar Prestasi Mahasiswa --}}
<div id="studentAchievementsModal" class="modal" style="display: none; z-index: 9998;">
    <div class="modal-content modal-achievements">
        <!-- Modal Header -->
        <div class="modal-hdr">
            <div>
                <h4 class="modal-hdr-title">Daftar Prestasi Mahasiswa</h4>
                <p class="modal-hdr-sub" id="student_achievements_nama"></p>
            </div>
            <button type="button" onclick="closeStudentAchievementsModal()" class="modal-close-btn">
                <i class="bi bi-x"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body-scroll">
            <div class="table-responsive" id="achievements_table_container">
                <!-- Diisi via JS -->
            </div>
        </div>
    </div>
</div>

<div id="approveModal" class="modal" style="display: none; z-index: 10000;">
    <div class="modal-content modal-form">
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

<div id="rejectModal" class="modal" style="display: none; z-index: 10000;">
    <div class="modal-content modal-form">
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

{{-- MODAL: Approve All --}}
<div id="approveAllModal" class="modal" style="display: none;">
    <div class="modal-content modal-form">
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

{{-- MODAL: Edit / Sunting Data Sertifikat Mahasiswa --}}
<div id="editAchievementModal" class="modal" style="display: none; z-index: 10001;">
    <div class="modal-content modal-form" style="max-width: 650px; width: 90%; max-height: 90vh; overflow-y: auto; text-align: left;">
        <div class="modal-hdr" style="padding-bottom: 12px; margin-bottom: 16px; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h4 class="modal-hdr-title" style="margin: 0; color: #1E293B; font-size: 18px; font-weight: 700; text-align: left;">
                    <i class="bi bi-pencil-square" style="color: #2563EB;"></i> Sunting / Lengkapi Data Sertifikat
                </h4>
                <p class="modal-hdr-sub" id="edit_modal_subtitle" style="margin: 4px 0 0; color: #64748B; font-size: 13px; text-align: left;"></p>
            </div>
            <button type="button" onclick="closeEditAchievementModal()" class="modal-close-btn">
                <i class="bi bi-x"></i>
            </button>
        </div>

        <form id="editAchievementForm" method="POST" enctype="multipart/form-data">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_category" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Kategori SKPI *</label>
                    <select id="edit_category" name="category" class="form-control" required onchange="onEditCategoryChange()">
                        @foreach($categoryOptions as $catVal => $catLabel)
                        <option value="{{ $catVal }}">{{ $catLabel }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_activity_type" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Jenis Kegiatan *</label>
                    <select id="edit_activity_type" name="activity_type" class="form-control" required onchange="onEditActivityTypeChange()">
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-top: 14px; margin-bottom: 0;">
                <label for="edit_event" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Nama Acara / Kegiatan *</label>
                <input type="text" id="edit_event" name="event" class="form-control" required placeholder="Contoh: Seminar Nasional Teknologi 2026">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 14px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_organizer" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Penyelenggara *</label>
                    <input type="text" id="edit_organizer" name="organizer" class="form-control" required placeholder="Contoh: BEM USH / Kemendikbud">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_event_year" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Tahun Kegiatan *</label>
                    <input type="text" id="edit_event_year" name="event_year" class="form-control" required placeholder="Contoh: 2025">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 14px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_level" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Tingkat Kegiatan *</label>
                    <select id="edit_level" name="level" class="form-control" required onchange="onEditLevelOrRoleChange()">
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_participation_role" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Peran / Posisi / Juara *</label>
                    <select id="edit_participation_role" name="participation_role" class="form-control" required onchange="onEditLevelOrRoleChange()">
                    </select>
                </div>
            </div>

            <div style="margin-top: 14px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 10px 14px; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 13px; color: #475569; font-weight: 600;">Kalkulasi Poin SKP:</span>
                <span id="edit_skp_badge" style="background: #2563EB; color: white; padding: 4px 12px; border-radius: 999px; font-weight: 700; font-size: 13px;">0 SKP</span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 14px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_status" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Status Verifikasi *</label>
                    <select id="edit_status" name="status" class="form-control" required>
                        <option value="approved">Approved (Disetujui)</option>
                        <option value="pending">Pending (Menunggu)</option>
                        <option value="rejected">Rejected (Ditolak)</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_certificate" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Ganti File Bukti / Sertifikat (Opsional)</label>
                    <input type="file" id="edit_certificate" name="certificate" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    <div id="edit_current_file_preview" style="font-size: 12px; margin-top: 4px;"></div>
                </div>
            </div>

            <div class="form-group" style="margin-top: 14px; margin-bottom: 0;">
                <label for="edit_approval_notes" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Catatan Reviewer / Catatan Revisi (Opsional)</label>
                <textarea id="edit_approval_notes" name="approval_notes" class="form-control textarea-control" rows="3" placeholder="Catatan untuk mahasiswa atau alasan perubahan data..."></textarea>
            </div>

            <div class="modal-actions" style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn-cancel" onclick="closeEditAchievementModal()">Batal</button>
                <button type="submit" class="btn-approve confirm" style="background: #2563EB;">
                    <i class="bi bi-save"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('admin/css/skpi-verifikasi-data.css') }}">
@endpush



@push('scripts')
<script>
    const SKP_DICTIONARY = @json($skpDictionary ?? []);

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

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

    let filterTimeout = null;

    function debounceFilter() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(() => {
            document.getElementById('filterForm').submit();
        }, 500);
    }

    function showStudentAchievementsModal(btn) {
        const nama = btn.dataset.nama;
        const achievementsBase64 = btn.dataset.achievements;

        let achievements = [];
        try {
            const jsonStr = decodeURIComponent(escape(atob(achievementsBase64)));
            achievements = JSON.parse(jsonStr);
        } catch (e) {
            console.error("Gagal mem-parsing data achievements", e);
            return;
        }

        document.getElementById('student_achievements_nama').textContent = nama;

        const container = document.getElementById('achievements_table_container');

        if (achievements.length === 0) {
            container.innerHTML = '<p style="text-align:center; color:#666; margin: 20px 0;">Tidak ada prestasi yang sesuai dengan filter saat ini.</p>';
        } else {
            let tableHtml = `
            <table class="data-table" style="margin-top: 0;">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Kategori & Kegiatan</th>
                        <th style="width: 120px;">Tingkat</th>
                        <th style="width: 120px;">Status</th>
                        <th style="min-width: 220px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
        `;

            achievements.forEach((ach, index) => {
                ach.nama = nama;
                const encodedData = btoa(unescape(encodeURIComponent(JSON.stringify(ach))));

                const titleText = ach.event ? ach.event : ach.kegiatan;
                const subText = ach.event ? `${ach.kegiatan} ${ach.organizer && ach.organizer !== '-' ? '• ' + ach.organizer : ''} ${ach.event_year && ach.event_year !== '-' ? '(' + ach.event_year + ')' : ''}` : ach.kategori;

                tableHtml += `
                <tr>
                    <td>${index + 1}</td>
                    <td>
                        <strong style="font-size: 13px; color: #1E293B;">${escapeHtml(titleText)}</strong><br>
                        <span style="font-size: 12px; color: #64748B;">${escapeHtml(subText)}</span>
                    </td>
                    <td>
                        <span class="badge-year">${ach.tingkat}</span>
                    </td>
                    <td>
                        <span class="status-badge ${ach.statusClass}">${ach.statusLabel}</span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                            <button type="button" class="btn-view" data-ach="${encodedData}" data-nama="${escapeHtml(nama)}" onclick="showDetailModalFromEncoded(this)" style="padding: 4px 8px; font-size: 11px;">
                                <i class="bi bi-eye"></i> Detail
                            </button>
                            <button type="button" class="btn-edit" data-ach="${encodedData}" data-nama="${escapeHtml(nama)}" onclick="showEditModalFromEncoded(this)" style="padding: 4px 8px; font-size: 11px; border-radius: 6px; background-color: #D97706; color: white; border: none; cursor: pointer;" title="Sunting Data Sertifikat">
                                <i class="bi bi-pencil-square"></i> Sunting
                            </button>
                            ${ach.status === 'approved' ? `
                                <form action="/admin/skpi/verifikasi-data/${ach.id}/unapprove" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin membatalkan approval data ini? Status akan kembali menjadi pending.');">
                                    @csrf
                                    <button type="submit" class="btn-reject" style="padding: 4px 8px; font-size: 11px; border-radius: 6px; background-color: #64748B; color: white; border: none;" title="Batal Approve">
                                        <i class="bi bi-arrow-counterclockwise"></i> Batal
                                    </button>
                                </form>
                            ` : `
                                <button type="button" class="btn-approve" onclick="showApproveModal(${ach.id})" style="padding: 4px 8px; font-size: 11px; border-radius: 6px;" title="Approve">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                <button type="button" class="btn-reject" onclick="showRejectModal(${ach.id})" style="padding: 4px 8px; font-size: 11px; border-radius: 6px;" title="Reject">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            `}
                        </div>
                    </td>
                </tr>
            `;
            });

            tableHtml += `</tbody></table>`;
            container.innerHTML = tableHtml;
        }

        document.getElementById('studentAchievementsModal').style.display = 'flex';
    }

    function closeStudentAchievementsModal() {
        document.getElementById('studentAchievementsModal').style.display = 'none';
    }

    function showDetailModalFromEncoded(btn) {
        const encodedData = btn.dataset.ach;
        const nama = btn.dataset.nama;
        let data = {};
        try {
            data = JSON.parse(decodeURIComponent(escape(atob(encodedData))));
        } catch (e) {
            console.error("Gagal decode data prestasi", e);
            return;
        }

        data.nama = nama;

        document.getElementById('detail_nama').textContent = data.nama;
        document.getElementById('detail_kategori').textContent = data.kategori;
        document.getElementById('detail_kegiatan').textContent = data.kegiatan;
        if (document.getElementById('detail_event')) document.getElementById('detail_event').textContent = data.event || '-';
        if (document.getElementById('detail_organizer')) document.getElementById('detail_organizer').textContent = data.organizer || '-';
        if (document.getElementById('detail_event_year')) document.getElementById('detail_event_year').textContent = data.event_year || '-';
        document.getElementById('detail_tingkat').textContent = data.tingkat;
        document.getElementById('detail_peran').textContent = data.peran;
        document.getElementById('detail_skp').textContent = data.skp;
        document.getElementById('detail_tanggal').textContent = data.tanggal;
        document.getElementById('detail_catatan').textContent = data.catatan;
        document.getElementById('detail_reviewer').textContent = data.reviewer;

        const fileContainer = document.getElementById('detail_file_container');
        if (data.file) {
            fileContainer.innerHTML = `<a href="${data.file}" target="_blank" style="display: inline-flex; align-items: center; gap: 8px; color: #1D4ED8; text-decoration: none; font-weight: 600; padding: 10px 20px; background: white; border-radius: 8px; border: 1px solid #CBD5E1; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.05);"><i class="bi bi-file-earmark-arrow-down" style="font-size: 16px;"></i> Lihat Dokumen / Sertifikat</a>`;
        } else {
            fileContainer.innerHTML = `<div style="color: #64748B; font-size: 13px; display: flex; flex-direction: column; align-items: center; gap: 4px;"><i class="bi bi-file-earmark-x" style="font-size: 24px; opacity: 0.5;"></i><span>Mahasiswa belum mengunggah file pendukung.</span></div>`;
        }

        const actionContainer = document.getElementById('detail_action_container');
        const encodedDataForAction = btoa(unescape(encodeURIComponent(JSON.stringify(data))));

        if (data.status === 'approved') {
            actionContainer.innerHTML = `
            <button type="button" class="btn-edit" data-ach="${encodedDataForAction}" data-nama="${escapeHtml(data.nama)}" onclick="showEditModalFromEncoded(this)" style="flex: 1; padding: 8px 16px; border-radius: 8px; font-weight: 600; border: none; cursor: pointer; background-color: #D97706; color: white; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                <i class="bi bi-pencil-square"></i> Sunting Data
            </button>
            <form action="/admin/skpi/verifikasi-data/${data.id}/unapprove" method="POST" style="display:inline; flex: 1;" onsubmit="return confirm('Yakin ingin membatalkan approval data ini? Status akan kembali menjadi pending.');">
                @csrf
                <button type="submit" class="btn-reject" style="width: 100%; padding: 8px 16px; border-radius: 8px; font-weight: 600; border: none; cursor: pointer; background-color: #64748B; color: white;">
                    <i class="bi bi-arrow-counterclockwise"></i> Batal Approve
                </button>
            </form>
            `;
            actionContainer.style.display = 'flex';
            actionContainer.style.gap = '10px';
        } else {
            actionContainer.innerHTML = `
            <button type="button" class="btn-edit" data-ach="${encodedDataForAction}" data-nama="${escapeHtml(data.nama)}" onclick="showEditModalFromEncoded(this)" style="flex: 1; padding: 8px 16px; border-radius: 8px; font-weight: 600; border: none; cursor: pointer; background-color: #D97706; color: white; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                <i class="bi bi-pencil-square"></i> Sunting Data
            </button>
            <button type="button" class="btn-approve" onclick="showApproveModal(${data.id})" style="flex: 1; padding: 8px 16px; border-radius: 8px; font-weight: 600; border: none; cursor: pointer;">
                <i class="bi bi-check-circle"></i> Approve
            </button>
            <button type="button" class="btn-reject" onclick="showRejectModal(${data.id})" style="flex: 1; padding: 8px 16px; border-radius: 8px; font-weight: 600; border: none; cursor: pointer;">
                <i class="bi bi-x-circle"></i> Reject
            </button>
            `;
            actionContainer.style.display = 'flex';
            actionContainer.style.gap = '10px';
        }

        document.getElementById('studentAchievementsModal').style.display = 'none';
        document.getElementById('detailModal').style.display = 'flex';
    }

    function closeDetailModal() {
        document.getElementById('detailModal').style.display = 'none';
        const studentName = document.getElementById('student_achievements_nama').textContent;
        if (studentName) {
            document.getElementById('studentAchievementsModal').style.display = 'flex';
        }
    }

    // ── LOGIK EDIT SERTIFIKAT / PRESTASI OLEH ADMIN ──
    function onEditCategoryChange(selectedType = null) {
        const cat = document.getElementById('edit_category').value;
        const $actType = document.getElementById('edit_activity_type');
        $actType.innerHTML = '';

        if (SKP_DICTIONARY[cat] && SKP_DICTIONARY[cat].types) {
            const types = SKP_DICTIONARY[cat].types;
            Object.entries(types).forEach(([typeKey, typeData]) => {
                const opt = document.createElement('option');
                opt.value = typeKey;
                opt.textContent = typeData.label || typeKey;
                if (selectedType && selectedType === typeKey) {
                    opt.selected = true;
                }
                $actType.appendChild(opt);
            });
        }

        onEditActivityTypeChange();
    }

    function onEditActivityTypeChange(selectedLevel = null, selectedRole = null) {
        const cat = document.getElementById('edit_category').value;
        const actType = document.getElementById('edit_activity_type').value;
        const $level = document.getElementById('edit_level');
        const $role = document.getElementById('edit_participation_role');

        $level.innerHTML = '';
        $role.innerHTML = '';

        if (SKP_DICTIONARY[cat] && SKP_DICTIONARY[cat].types && SKP_DICTIONARY[cat].types[actType]) {
            const typeData = SKP_DICTIONARY[cat].types[actType];

            if (typeData.levels) {
                Object.entries(typeData.levels).forEach(([lvlKey, lvlLabel]) => {
                    const opt = document.createElement('option');
                    opt.value = lvlKey;
                    opt.textContent = lvlLabel;
                    if (selectedLevel && selectedLevel === lvlKey) {
                        opt.selected = true;
                    }
                    $level.appendChild(opt);
                });
            }

            if (typeData.roles) {
                Object.entries(typeData.roles).forEach(([roleKey, roleLabel]) => {
                    const opt = document.createElement('option');
                    opt.value = roleKey;
                    opt.textContent = roleLabel;
                    if (selectedRole && selectedRole === roleKey) {
                        opt.selected = true;
                    }
                    $role.appendChild(opt);
                });
            }
        }

        onEditLevelOrRoleChange();
    }

    function onEditLevelOrRoleChange() {
        const cat = document.getElementById('edit_category').value;
        const actType = document.getElementById('edit_activity_type').value;
        const level = document.getElementById('edit_level').value;
        const role = document.getElementById('edit_participation_role').value;
        const $badge = document.getElementById('edit_skp_badge');

        let points = 0;
        try {
            if (SKP_DICTIONARY[cat] && SKP_DICTIONARY[cat].types[actType] && SKP_DICTIONARY[cat].types[actType].points) {
                const pTable = SKP_DICTIONARY[cat].types[actType].points;
                if (pTable[level] && pTable[level][role] !== undefined) {
                    points = pTable[level][role];
                }
            }
        } catch (e) {
            points = 0;
        }

        $badge.textContent = points + ' SKP';
    }

    function showEditModalFromEncoded(btn) {
        const encodedData = btn.dataset.ach;
        const nama = btn.dataset.nama;
        let data = {};
        try {
            data = JSON.parse(decodeURIComponent(escape(atob(encodedData))));
        } catch (e) {
            console.error("Gagal decode data achievement", e);
            return;
        }
        data.nama = nama;
        showEditModalFromData(data);
    }

    function showEditModalFromData(data) {
        document.getElementById('editAchievementForm').action = `/admin/skpi/verifikasi-data/${data.id}/update`;
        document.getElementById('edit_modal_subtitle').textContent = `Mahasiswa: ${data.nama || ''}`;

        const $cat = document.getElementById('edit_category');
        if (data.category_raw) {
            $cat.value = data.category_raw;
        }

        onEditCategoryChange(data.activity_type_raw);
        onEditActivityTypeChange(data.tingkat, data.peran);

        document.getElementById('edit_event').value = (data.event && data.event !== '-') ? data.event : '';
        document.getElementById('edit_organizer').value = (data.organizer && data.organizer !== '-') ? data.organizer : '';
        document.getElementById('edit_event_year').value = (data.event_year && data.event_year !== '-') ? data.event_year : '';
        document.getElementById('edit_status').value = data.status || 'approved';
        document.getElementById('edit_approval_notes').value = (data.catatan && data.catatan !== 'Belum ada catatan review.') ? data.catatan : '';

        const previewDiv = document.getElementById('edit_current_file_preview');
        if (data.file) {
            previewDiv.innerHTML = `<a href="${data.file}" target="_blank" style="color: #2563EB; font-weight: 600; text-decoration: none;"><i class="bi bi-file-earmark-check"></i> File saat ini (Klik untuk lihat)</a>`;
        } else {
            previewDiv.innerHTML = `<span style="color: #94A3B8;">Belum ada file diunggah.</span>`;
        }

        document.getElementById('studentAchievementsModal').style.display = 'none';
        document.getElementById('detailModal').style.display = 'none';
        document.getElementById('editAchievementModal').style.display = 'flex';
    }

    function closeEditAchievementModal() {
        document.getElementById('editAchievementModal').style.display = 'none';
        const studentName = document.getElementById('student_achievements_nama').textContent;
        if (studentName) {
            document.getElementById('studentAchievementsModal').style.display = 'flex';
        }
    }
</script>
@endpush