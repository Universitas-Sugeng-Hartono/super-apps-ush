<div class="keuangan-payment-list">
    <div class="filter-card shadow-sm">
        <form method="GET" action="{{ $listRoute }}" class="filter-grid">
            <div class="filter-item">
                <label>Cari Mahasiswa</label>
                <div class="input-with-icon">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" class="form-control-modern" value="{{ $search }}" placeholder="Nama atau NIM">
                </div>
            </div>
            <div class="filter-item">
                <label>Status Verifikasi</label>
                <select name="status" class="form-select-modern" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="revision" {{ $status === 'revision' ? 'selected' : '' }}>Revision</option>
                    <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="filter-item">
                <label>Program Studi</label>
                <select name="study_program_id" class="form-select-modern" onchange="this.form.submit()">
                    <option value="">Semua Program Studi</option>
                    @foreach($studyPrograms as $prodi)
                        <option value="{{ $prodi->id }}" {{ (string) $studyProgramId === (string) $prodi->id ? 'selected' : '' }}>{{ $prodi->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-actions-modern">
                <a href="{{ $listRoute }}" class="btn-reset-modern" title="Reset Filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
                <button type="submit" class="btn-search-modern">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
        </form>
    </div>

    <div class="table-card shadow-sm">
        <div class="table-responsive-modern">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th>Mahasiswa & NIM</th>
                        <th>Program Studi</th>
                        <th>Dokumen Terlampir</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrations as $reg)
                        <tr>
                            <td>
                                <div class="student-profile">
                                    <div class="student-meta">
                                        <span class="student-name">{{ $reg->nama_lengkap }}</span>
                                        <span class="student-nim">{{ $reg->nim }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="prodi-tag">{{ $reg->student->program_studi ?? '-' }}</span>
                            </td>
                            <td>
                                @if($reg->doc_pembayaran_wisuda || $reg->doc_naskah_publikasi)
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="showDetailDokumenModal('{{ $reg->doc_pembayaran_wisuda ? asset('storage/' . $reg->doc_pembayaran_wisuda) : '' }}', '{{ $reg->doc_naskah_publikasi ? asset('storage/' . $reg->doc_naskah_publikasi) : '' }}', '{{ addslashes($reg->nama_lengkap) }}')">
                                        <i class="bi bi-folder2-open"></i> Lihat Dokumen
                                    </button>
                                @else
                                    <span class="text-muted">Belum ada dokumen</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge-status status-{{ $reg->payment_status ?? 'pending' }}">
                                    {{ match($reg->payment_status) {
                                        'approved' => 'Approved',
                                        'rejected' => 'Rejected',
                                        'revision' => 'Revision',
                                        default => 'Pending'
                                    } }}
                                </span>
                            </td>
                            <td class="td-action">
                                <div class="btn-group-modern">
                                    @if($reg->payment_status !== 'approved')
                                        <button type="button" class="btn-table btn-table-success" onclick="showApproveModal({{ $reg->id }}, '{{ addslashes($reg->nama_lengkap) }}')" title="Approve">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    @endif
                                    @if($reg->payment_status !== 'revision')
                                        <button type="button" class="btn-table btn-table-warning text-white" style="background-color: #f59e0b; border-color: #f59e0b;" onclick="showRevisionModal({{ $reg->id }}, '{{ addslashes($reg->nama_lengkap) }}')" title="Minta Revisi">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                    @endif
                                    <button type="button" class="btn-table btn-table-danger" onclick="showRejectModal({{ $reg->id }}, '{{ addslashes($reg->nama_lengkap) }}')" title="Tolak">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Belum ada data dokumen yang diunggah.</td>
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

<div id="approveModal" class="modal-backdrop-modern" style="display: none;">
    <div class="modal-dialog-modern">
        <div class="modal-header-modern bg-success">
            <div class="modal-title-wrap">
                <h4>Setujui Verifikasi</h4>
                <p id="approve_target_name"></p>
            </div>
        </div>
        <form id="approveForm" method="POST">
            @csrf
            @method('PATCH')
            <div class="modal-body-modern">
                <p>Apakah dokumen pembayaran dan naskah publikasi mahasiswa ini sudah valid?</p>
                <textarea name="payment_approval_notes" class="form-control-modern" rows="3" placeholder="Catatan, jika diperlukan"></textarea>
            </div>
            <div class="modal-footer-modern">
                <button type="button" class="btn-cancel-modern" onclick="closeApproveModal()">Batal</button>
                <button type="submit" class="btn-submit-modern bg-success">Ya, Setujui</button>
            </div>
        </form>
    </div>
</div>

<div id="revisionModal" class="modal-backdrop-modern" style="display: none;">
    <div class="modal-dialog-modern">
        <div class="modal-header-modern bg-warning text-dark">
            <div class="modal-title-wrap">
                <h4 style="color: #212529;">Minta Revisi Dokumen</h4>
                <p id="revision_target_name" style="color: #495057; margin-bottom: 0;"></p>
            </div>
        </div>
        <form id="revisionForm" method="POST">
            @csrf
            @method('PATCH')
            <div class="modal-body-modern">
                <p style="margin-bottom: 12px; font-size: 14px; color: #4b5563;">Status verifikasi akan diubah menjadi <strong>Revisi</strong> agar mahasiswa dapat memperbaiki atau mengunggah ulang dokumen.</p>
                <div class="form-group-modern">
                    <label style="font-weight: 600; font-size: 13px; color: #374151;">Catatan Revisi / Instruksi Perbaikan (Opsional)</label>
                    <textarea name="payment_approval_notes" class="form-control-modern" rows="3" placeholder="Tulis instruksi revisi untuk mahasiswa..."></textarea>
                </div>
            </div>
            <div class="modal-footer-modern">
                <button type="button" class="btn-cancel-modern" onclick="closeRevisionModal()">Batal</button>
                <button type="submit" class="btn-submit-modern bg-warning text-dark" style="font-weight: 600;">Kirim Revisi</button>
            </div>
        </form>
    </div>
</div>

<div id="rejectModal" class="modal-backdrop-modern" style="display: none;">
    <div class="modal-dialog-modern">
        <div class="modal-header-modern bg-danger">
            <div class="modal-title-wrap">
                <h4>Tolak Dokumen</h4>
                <p id="reject_target_name"></p>
            </div>
        </div>
        <form id="rejectForm" method="POST">
            @csrf
            @method('PATCH')
            <div class="modal-body-modern">
                <label>Alasan Penolakan</label>
                <textarea name="payment_approval_notes" class="form-control-modern" rows="4" required placeholder="Tulis alasan penolakan"></textarea>
            </div>
            <div class="modal-footer-modern">
                <button type="button" class="btn-cancel-modern" onclick="closeRejectModal()">Batal</button>
                <button type="submit" class="btn-submit-modern bg-danger">Tolak</button>
            </div>
        </form>
    </div>
</div>

<div id="detailDokumenModal" class="modal-backdrop-modern" style="display: none;">
    <div class="modal-dialog-modern" style="max-width: 420px;">
        <div class="modal-header-modern bg-primary text-white">
            <div class="modal-title-wrap">
                <h4 style="color: white;">Dokumen Terlampir</h4>
                <p id="dokumen_target_name" style="color: rgba(255,255,255,0.8);"></p>
            </div>
        </div>
        <div class="modal-body-modern">
            <p><i class="bi bi-credit-card-2-front"></i> Bukti Pembayaran Wisuda</p>
            <a href="#" id="btn_pembayaran" target="_blank" class="btn btn-sm btn-outline-primary mb-3">Lihat Bukti Pembayaran</a>
            <span id="no_pembayaran" class="text-muted" style="display:none;">Belum diunggah</span>
            <p class="mt-3"><i class="bi bi-file-earmark-text"></i> Naskah Publikasi</p>
            <a href="#" id="btn_naskah" target="_blank" class="btn btn-sm btn-outline-info">Lihat Naskah Publikasi</a>
            <span id="no_naskah" class="text-muted" style="display:none;">Belum diunggah</span>
        </div>
        <div class="modal-footer-modern">
            <button type="button" class="btn-cancel-modern" onclick="closeDetailDokumenModal()">Tutup</button>
        </div>
    </div>
</div>

@push('css')
<style>
    .btn-table-warning { background: #f59e0b; color: #fff; }
    .btn-table-warning:hover { background: #d97706; color: #fff; }
</style>
@endpush

@push('scripts')
<script>
    function showApproveModal(id, name) {
        document.getElementById('approve_target_name').innerText = name;
        document.getElementById('approveForm').action = `/keuangan/verifikasi-pembayaran/${id}/approve`;
        document.getElementById('approveModal').style.display = 'flex';
    }
    function closeApproveModal() {
        document.getElementById('approveModal').style.display = 'none';
    }
    function showRevisionModal(id, name) {
        document.getElementById('revision_target_name').innerText = name;
        document.getElementById('revisionForm').action = `/keuangan/verifikasi-pembayaran/${id}/revision`;
        document.getElementById('revisionModal').style.display = 'flex';
    }
    function closeRevisionModal() {
        document.getElementById('revisionModal').style.display = 'none';
    }
    function showRejectModal(id, name) {
        document.getElementById('reject_target_name').innerText = name;
        document.getElementById('rejectForm').action = `/keuangan/verifikasi-pembayaran/${id}/reject`;
        document.getElementById('rejectModal').style.display = 'flex';
    }
    function closeRejectModal() {
        document.getElementById('rejectModal').style.display = 'none';
    }
    function showDetailDokumenModal(urlPembayaran, urlNaskah, name) {
        document.getElementById('dokumen_target_name').innerText = name;
        const btnPembayaran = document.getElementById('btn_pembayaran');
        const noPembayaran = document.getElementById('no_pembayaran');
        btnPembayaran.style.display = urlPembayaran ? 'inline-block' : 'none';
        noPembayaran.style.display = urlPembayaran ? 'none' : 'inline-block';
        if (urlPembayaran) btnPembayaran.href = urlPembayaran;
        const btnNaskah = document.getElementById('btn_naskah');
        const noNaskah = document.getElementById('no_naskah');
        btnNaskah.style.display = urlNaskah ? 'inline-block' : 'none';
        noNaskah.style.display = urlNaskah ? 'none' : 'inline-block';
        if (urlNaskah) btnNaskah.href = urlNaskah;
        document.getElementById('detailDokumenModal').style.display = 'flex';
    }
    function closeDetailDokumenModal() {
        document.getElementById('detailDokumenModal').style.display = 'none';
    }

    window.addEventListener('click', function(event) {
        if (event.target && event.target.classList && event.target.classList.contains('modal-backdrop-modern')) {
            event.target.style.display = 'none';
        }
    });
</script>
@endpush
