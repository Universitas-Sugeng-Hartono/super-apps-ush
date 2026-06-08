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
            <div class="stat-box text-secondary" style="background: #f1f5f9; border-left: 4px solid #94a3b8;">
                <span class="stat-label">Draft</span>
                <span class="stat-value">{{ $stats['draft'] }}</span>
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
                    <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
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
                
                <a href="{{ route('admin.skpi.daftar-skpi.export') }}" class="btn-search-modern" style="background-color: #10b981; color: white;" title="Export Excel">
                    <i class="bi bi-file-earmark-excel"></i> Export
                </a>
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
                    $ipk = $reg->ipk ?: $s->ipk;
                    $sks = $reg->sks ?: $s->sks;
                    $judulTa = $reg->judul_ta_indo ?: optional($s->finalProject)->title;

                    $hasProfile = filled($ipk) && filled($sks) && filled($s->foto) && filled($s->ttd);
                    $hasFinalProject = filled($judulTa) || $s->is_skpi_unlocked;
                    $allReady = $hasProfile && $hasFinalProject;
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
                                <div class="prereq-badge done" 
                                     style="cursor: pointer; background: #e0f2fe; color: #05567eff; border: 1px solid #bae6fd;"
                                     onclick="showDetailDataModal({{ json_encode([
                                        'nama' => $reg->nama_lengkap,
                                        'nim' => $reg->nim,
                                        'prodi' => $s->program_studi,
                                        'tempat_lahir' => $reg->tempat_lahir,
                                        'tanggal_lahir' => $reg->tanggal_lahir ? $reg->tanggal_lahir->format('d M Y') : '-',
                                        'angkatan' => $reg->angkatan,
                                        'gelar' => $reg->gelar,
                                        'ipk' => $reg->ipk,
                                        'sks' => $reg->sks,
                                        'judul_ta' => $reg->judul_ta_indo,
                                        'periode_lulus' => $reg->periode_lulus,
                                        'lama_studi' => $reg->lama_studi,
                                        'doc_ijasah' => $reg->doc_ijasah ? asset('storage/' . $reg->doc_ijasah) : null,
                                        'doc_ktp' => $reg->doc_ktp ? asset('storage/' . $reg->doc_ktp) : null
                                     ]) }})">
                                    <i class="bi bi-card-list"></i> Detail Lengkap
                                </div>
                                @if($allReady)
                                <i class="bi bi-shield-check-fill text-success" data-bs-toggle="tooltip" title="Semua Prasyarat Terpenuhi" style="font-size: 18px;"></i>
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
                            <span class="badge-status status-{{ $reg->status ?? 'draft' }}">
                                {{ match($reg->status) {
                                    'approved' => 'Approved',
                                    'needs_revision' => 'Revision',
                                    'rejected' => 'Rejected',
                                    'pending' => 'Pending',
                                    default => 'Draft'
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
        <div class="modal-header-modern bg-primary">
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
                <button type="submit" class="btn-submit-modern bg-primary">Kirim Revisi</button>
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

{{-- Modal Detail Data --}}
<div id="detailDataModal" class="modal-backdrop-modern" style="display: none;">
    <div class="modal-dialog-modern" style="max-width: 600px;">
        <div class="modal-header-modern bg-info text-white">
            <div class="modal-icon-wrap" style="color: white;"><i class="bi bi-card-list"></i></div>
            <div class="modal-title-wrap text-white">
                <h4 style="color: white;">Detail Pendaftar SKPI</h4>
                <p style="color: rgba(255,255,255,0.8);">Menampilkan seluruh kelengkapan form pendaftaran.</p>
            </div>
        </div>
        <div class="modal-body-modern" style="max-height: 70vh; overflow-y: auto;">
            <table class="table table-borderless table-sm">
                <tbody>
                    <tr><td width="35%" class="text-muted">Nama Lengkap</td><td id="det_nama" class="fw-bold"></td></tr>
                    <tr><td class="text-muted">NIM / Angkatan</td><td><span id="det_nim" class="fw-bold"></span> / <span id="det_angkatan"></span></td></tr>
                    <tr><td class="text-muted">Program Studi</td><td id="det_prodi"></td></tr>
                    <tr><td class="text-muted">Tempat, Tgl Lahir</td><td><span id="det_tempat_lahir"></span>, <span id="det_tanggal_lahir"></span></td></tr>
                    <tr><td class="text-muted">Gelar</td><td id="det_gelar"></td></tr>
                    <tr><td class="text-muted">IPK / SKS</td><td><span id="det_ipk"></span> / <span id="det_sks"></span></td></tr>
                    <tr><td class="text-muted">Judul TA</td><td id="det_judul_ta"></td></tr>
                    <tr><td class="text-muted">Periode Lulus</td><td id="det_periode"></td></tr>
                    <tr><td class="text-muted">Lama Studi</td><td id="det_lama_studi"></td></tr>
                    <tr><td class="text-muted">Dokumen Ijazah</td><td id="det_doc_ijasah"></td></tr>
                    <tr><td class="text-muted">Dokumen KTP</td><td id="det_doc_ktp"></td></tr>
                </tbody>
            </table>
        </div>
        <div class="modal-footer-modern">
            <button type="button" class="btn-cancel-modern" onclick="closeDetailDataModal()">Tutup</button>
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

    function showDetailDataModal(data) {
        document.getElementById('det_nama').innerText = data.nama || '-';
        document.getElementById('det_nim').innerText = data.nim || '-';
        document.getElementById('det_angkatan').innerText = data.angkatan || '-';
        document.getElementById('det_prodi').innerText = data.prodi || '-';
        document.getElementById('det_tempat_lahir').innerText = data.tempat_lahir || '-';
        document.getElementById('det_tanggal_lahir').innerText = data.tanggal_lahir || '-';
        document.getElementById('det_gelar').innerText = data.gelar || '-';
        document.getElementById('det_ipk').innerText = data.ipk || '-';
        document.getElementById('det_sks').innerText = data.sks || '-';
        document.getElementById('det_judul_ta').innerText = data.judul_ta || '-';
        document.getElementById('det_periode').innerText = data.periode_lulus || '-';
        document.getElementById('det_lama_studi').innerText = data.lama_studi || '-';
        
        const docIjasah = document.getElementById('det_doc_ijasah');
        if(data.doc_ijasah) {
            docIjasah.innerHTML = `<a href="${data.doc_ijasah}" target="_blank" class="btn btn-sm btn-primary py-0" style="font-size: 12px; padding: 2px 8px;"><i class="bi bi-file-earmark-pdf"></i> Lihat File</a>`;
        } else {
            docIjasah.innerHTML = `<span class="text-muted">Belum ada</span>`;
        }
        
        const docKtp = document.getElementById('det_doc_ktp');
        if(data.doc_ktp) {
            docKtp.innerHTML = `<a href="${data.doc_ktp}" target="_blank" class="btn btn-sm btn-primary py-0" style="font-size: 12px; padding: 2px 8px;"><i class="bi bi-file-earmark-pdf"></i> Lihat File</a>`;
        } else {
            docKtp.innerHTML = `<span class="text-muted">Belum ada</span>`;
        }

        document.getElementById('detailDataModal').style.display = 'flex';
    }

    function closeDetailDataModal() {
        document.getElementById('detailDataModal').style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target.classList.contains('modal-backdrop-modern')) {
            event.target.style.display = 'none';
        }
    }

    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
</script>
@endpush