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
                'kategori' => $ach->category_label,
                'kegiatan' => $ach->activity_type_label ?? $ach->activity_type,
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
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('admin/css/skpi-verifikasi-data.css') }}">
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
            // Decode base64 dengan dukungan UTF-8
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
                        <th style="min-width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
        `;

            achievements.forEach((ach, index) => {
                // Karena data ini akan dilempar ke detailModal, kita harus mengubahnya menjadi string JSON lagi yang aman,
                // atau menggunakan data-* attributes di tombol. 
                // Untuk amannya, kita akan simpan object json-nya di window atau menggunakan HTML encoding

                // Encode data ke base64 agar aman ditaruh di dalam HTML attribute
                const encodedData = btoa(unescape(encodeURIComponent(JSON.stringify(ach))));

                tableHtml += `
                <tr>
                    <td>${index + 1}</td>
                    <td>
                        <strong style="font-size: 13px;">${ach.kategori}</strong><br>
                        <span style="font-size: 12px; color: #666;">${ach.kegiatan}</span>
                    </td>
                    <td>
                        <span class="badge-year">${ach.tingkat}</span>
                    </td>
                    <td>
                        <span class="status-badge ${ach.statusClass}">${ach.statusLabel}</span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px;">
                            <button type="button" class="btn-view" data-ach="${encodedData}" data-nama="${nama}" onclick="showDetailModalFromEncoded(this)" style="padding: 4px 8px; font-size: 11px;">
                                <i class="bi bi-eye"></i> Detail
                            </button>
                            ${ach.status !== 'approved' ? `
                                <button type="button" class="btn-approve" onclick="showApproveModal(${ach.id})" style="padding: 4px 8px; font-size: 11px; border-radius: 6px;">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                <button type="button" class="btn-reject" onclick="showRejectModal(${ach.id})" style="padding: 4px 8px; font-size: 11px; border-radius: 6px;">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            ` : ''}
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

        data.nama = nama; // Tambahkan nama mahasiswa

        document.getElementById('detail_nama').textContent = data.nama;
        document.getElementById('detail_kategori').textContent = data.kategori;
        document.getElementById('detail_kegiatan').textContent = data.kegiatan;
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

        // Konfigurasi tombol Approve & Reject
        const actionContainer = document.getElementById('detail_action_container');
        if (data.status !== 'approved') {
            actionContainer.innerHTML = `
            <button type="button" class="btn-approve" onclick="showApproveModal(${data.id})" style="padding: 8px 16px; border-radius: 8px; font-weight: 600; border: none; cursor: pointer;">
                <i class="bi bi-check-circle"></i> Approve
            </button>
            <button type="button" class="btn-reject" onclick="showRejectModal(${data.id})" style="padding: 8px 16px; border-radius: 8px; font-weight: 600; border: none; cursor: pointer;">
                <i class="bi bi-x-circle"></i> Reject
            </button>
        `;
            actionContainer.style.display = 'flex';
        } else {
            actionContainer.style.display = 'none';
        }

        // Sembunyikan Modal 1 sementara, munculkan Modal 2
        document.getElementById('studentAchievementsModal').style.display = 'none';
        document.getElementById('detailModal').style.display = 'flex';
    }

    function showDetailModal(btn) {
        // Fungsi lama untuk compatibility, biarkan kosong atau arahkan
    }

    function closeDetailModal() {
        document.getElementById('detailModal').style.display = 'none';
        // Munculkan kembali Modal 1 (Daftar Prestasi) jika sedang ada mahasiswa yang di-view
        const studentName = document.getElementById('student_achievements_nama').textContent;
        if (studentName) {
            document.getElementById('studentAchievementsModal').style.display = 'flex';
        }
    }
</script>
@endpush