@extends('admin.layouts.super-app')

@section('content')
<div class="page-shell">
    <div class="mb-3" style="padding-top: 10px;">
        <a href="{{ route('admin.skpi.index') }}" class="text-decoration-none text-secondary" style="font-weight: 600; font-size: 15px; display: inline-flex; align-items: center; gap: 8px;">
            <i class="bi bi-arrow-left"></i> Kembali ke Menu Utama SKPI
        </a>
    </div>

    <div class="hero-card shadow-sm" style="background: #213158; color: white;">
        <div class="hero-content">
            <span class="hero-badge" style="background: rgba(255,255,255,0.2); color: white;">Verifikasi Dokumen</span>
            <h3 style="color: white;">Verifikasi Pembayaran & Naskah Publikasi</h3>
            <p style="color: rgba(255,255,255,0.9);">Review bukti pembayaran wisuda dan dokumen naskah publikasi yang diunggah oleh mahasiswa.</p>
        </div>
        <div class="stats-row">
            <div class="stat-box" style="background: rgba(255, 255, 255, 0.1); border-left: 4px solid rgba(255,255,255,0.3); color: white;">
                <span class="stat-label" style="color: rgba(255,255,255,0.8);">Total Upload</span>
                <span class="stat-value" style="color: #ffffffff;">{{ $stats['total'] }}</span>
            </div>
            <div class="stat-box" style="background: rgba(255,255,255,0.1); border-left: 4px solid #fbbf24; color: white;">
                <span class="stat-label" style="color: rgba(255,255,255,0.8);">Pending</span>
                <span class="stat-value" style="color: #ffffffff;">{{ $stats['pending'] }}</span>
            </div>
            <div class="stat-box" style="background: rgba(255,255,255,0.1); border-left: 4px solid #10b981; color: white;">
                <span class="stat-label" style="color: rgba(255,255,255,0.8);">Approved</span>
                <span class="stat-value" style="color: #ffffffff;">{{ $stats['approved'] }}</span>
            </div>
            <div class="stat-box" style="background: rgba(255,255,255,0.1); border-left: 4px solid #ef4444; color: white;">
                <span class="stat-label" style="color: rgba(255,255,255,0.8);">Rejected</span>
                <span class="stat-value" style="color: #ffffffff;">{{ $stats['rejected'] }}</span>
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
        <form method="GET" action="{{ route('admin.skpi.verifikasi-pembayaran.index') }}" class="filter-grid">
            <div class="filter-item">
                <label>Cari Mahasiswa</label>
                <div class="input-with-icon">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" class="form-control-modern" value="{{ $search }}" placeholder="Nama atau NIM...">
                </div>
            </div>
            <div class="filter-item">
                <label>Status Verifikasi</label>
                <select name="status" class="form-select-modern" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
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
                <a href="{{ route('admin.skpi.verifikasi-pembayaran.index') }}" class="btn-reset-modern" title="Reset Filter">
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
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                @if($reg->doc_pembayaran_wisuda || $reg->doc_naskah_publikasi)
                                <button class="btn btn-sm btn-outline-primary" onclick="showDetailDokumenModal('{{ $reg->doc_pembayaran_wisuda ? asset('storage/' . $reg->doc_pembayaran_wisuda) : '' }}', '{{ $reg->doc_naskah_publikasi ? asset('storage/' . $reg->doc_naskah_publikasi) : '' }}', '{{ addslashes($reg->nama_lengkap) }}')" style="font-size: 12px; width: fit-content; text-decoration: none; border-radius: 6px;">
                                    <i class="bi bi-folder2-open"></i> Lihat Dokumen
                                </button>
                                @else
                                <span class="text-muted" style="font-size: 12px;">Belum ada dokumen</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="badge-status status-{{ $reg->payment_status ?? 'pending' }}">
                                {{ match($reg->payment_status) {
                                    'approved' => 'Approved',
                                    'rejected' => 'Rejected',
                                    default => 'Pending'
                                } }}
                            </span>
                            @if($reg->payment_status === 'rejected' && $reg->payment_approval_notes)
                                <br><small class="text-danger mt-1 d-block"><i class="bi bi-info-circle"></i> {{ Str::limit($reg->payment_approval_notes, 30) }}</small>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group-modern">
                                @if($reg->payment_status !== 'approved')
                                <button class="btn-table btn-table-success" onclick="showApproveModal({{ $reg->id }}, '{{ addslashes($reg->nama_lengkap) }}')" title="Approve">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                @endif
                                <button class="btn-table btn-table-danger" onclick="showRejectModal({{ $reg->id }}, '{{ addslashes($reg->nama_lengkap) }}')" title="Tolak">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-table">
                                <img src="https://illustrations.popsy.co/amber/box.svg" alt="empty" style="width: 120px;">
                                <p>Belum ada data dokumen yang diunggah.</p>
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
                <h4>Setujui Verifikasi</h4>
                <p id="approve_target_name"></p>
            </div>
        </div>
        <form id="approveForm" method="POST">
            @csrf
            @method('PATCH')
            <div class="modal-body-modern">
                <p>Apakah Anda yakin dokumen pembayaran dan naskah publikasi atas nama mahasiswa ini sudah benar dan valid?</p>
                <div class="form-group-modern mt-3">
                    <label>Catatan (Opsional)</label>
                    <textarea name="payment_approval_notes" class="form-control-modern" rows="3" placeholder="Tulis catatan jika diperlukan..."></textarea>
                </div>
            </div>
            <div class="modal-footer-modern">
                <button type="button" class="btn-cancel-modern" onclick="closeApproveModal()">Batal</button>
                <button type="submit" class="btn-submit-modern bg-success">Ya, Setujui</button>
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
                <h4>Tolak Dokumen</h4>
                <p id="reject_target_name"></p>
            </div>
        </div>
        <form id="rejectForm" method="POST">
            @csrf
            @method('PATCH')
            <div class="modal-body-modern">
                <div class="form-group-modern">
                    <label>Alasan Penolakan <span class="text-danger">*</span></label>
                    <textarea name="payment_approval_notes" class="form-control-modern" rows="4" required placeholder="Tulis alasan penolakan yang jelas agar mahasiswa dapat memperbaikinya..."></textarea>
                </div>
            </div>
            <div class="modal-footer-modern">
                <button type="button" class="btn-cancel-modern" onclick="closeRejectModal()">Batal</button>
                <button type="submit" class="btn-submit-modern bg-danger">Tolak & Minta Perbaikan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Detail Dokumen --}}
<div id="detailDokumenModal" class="modal-backdrop-modern" style="display: none;">
    <div class="modal-dialog-modern" style="max-width: 400px;">
        <div class="modal-header-modern bg-primary text-white">
            <div class="modal-icon-wrap" style="color: white;"><i class="bi bi-folder2-open"></i></div>
            <div class="modal-title-wrap text-white">
                <h4 style="color: white;">Dokumen Terlampir</h4>
                <p style="color: rgba(255,255,255,0.8);" id="dokumen_target_name"></p>
            </div>
        </div>
        <div class="modal-body-modern">
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <div id="btn_pembayaran_container">
                    <p style="margin-bottom: 8px; font-weight: 600; font-size: 14px;"><i class="bi bi-credit-card-2-front" style="color: #4facfe;"></i> Bukti Pembayaran Wisuda</p>
                    <a href="#" id="btn_pembayaran" target="_blank" class="btn btn-sm btn-outline-primary" style="width: 100%; text-align: left; padding: 8px 12px;">
                        <i class="bi bi-file-earmark-pdf"></i> Lihat Bukti Pembayaran
                    </a>
                    <span id="no_pembayaran" class="text-muted" style="display:none; font-size: 13px;">Belum diunggah</span>
                </div>
                
                <div id="btn_naskah_container" style="border-top: 1px solid #eee; padding-top: 16px;">
                    <p style="margin-bottom: 8px; font-weight: 600; font-size: 14px;"><i class="bi bi-file-earmark-text" style="color: #4facfe;"></i> Naskah Publikasi</p>
                    <a href="#" id="btn_naskah" target="_blank" class="btn btn-sm btn-outline-info" style="width: 100%; text-align: left; padding: 8px 12px;">
                        <i class="bi bi-file-earmark-text"></i> Lihat Naskah Publikasi
                    </a>
                    <span id="no_naskah" class="text-muted" style="display:none; font-size: 13px;">Belum diunggah</span>
                </div>
            </div>
        </div>
        <div class="modal-footer-modern">
            <button type="button" class="btn-cancel-modern" onclick="closeDetailDokumenModal()">Tutup</button>
        </div>
    </div>
</div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('admin/css/skpi-daftar-skpi.css') }}">
@endpush

@push('scripts')
<script>
    function showApproveModal(id, name) {
        document.getElementById('approve_target_name').innerText = name;
        document.getElementById('approveForm').action = `/admin/skpi/verifikasi-pembayaran/${id}/approve`;
        document.getElementById('approveModal').style.display = 'flex';
    }

    function closeApproveModal() {
        document.getElementById('approveModal').style.display = 'none';
    }

    function showRejectModal(id, name) {
        document.getElementById('reject_target_name').innerText = name;
        document.getElementById('rejectForm').action = `/admin/skpi/verifikasi-pembayaran/${id}/reject`;
        document.getElementById('rejectModal').style.display = 'flex';
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').style.display = 'none';
    }

    function showDetailDokumenModal(urlPembayaran, urlNaskah, name) {
        document.getElementById('dokumen_target_name').innerText = name;
        
        let btnPembayaran = document.getElementById('btn_pembayaran');
        let noPembayaran = document.getElementById('no_pembayaran');
        if(urlPembayaran) {
            btnPembayaran.href = urlPembayaran;
            btnPembayaran.style.display = 'inline-block';
            noPembayaran.style.display = 'none';
        } else {
            btnPembayaran.style.display = 'none';
            noPembayaran.style.display = 'inline-block';
        }

        let btnNaskah = document.getElementById('btn_naskah');
        let noNaskah = document.getElementById('no_naskah');
        if(urlNaskah) {
            btnNaskah.href = urlNaskah;
            btnNaskah.style.display = 'inline-block';
            noNaskah.style.display = 'none';
        } else {
            btnNaskah.style.display = 'none';
            noNaskah.style.display = 'inline-block';
        }

        document.getElementById('detailDokumenModal').style.display = 'flex';
    }

    function closeDetailDokumenModal() {
        document.getElementById('detailDokumenModal').style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target.classList.contains('modal-backdrop-modern')) {
            event.target.style.display = 'none';
        }
    }
</script>
@endpush
