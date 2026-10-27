@extends('admin.layouts.super-app')

@section('content')
<div class="content-card">
    <div class="card-header">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                <h3 class="mb-0"><i class="bi bi-people-fill text-primary me-2"></i> Direktori Mahasiswa & Monitoring Akademik</h3>
            </div>
            <p class="text-muted mb-0" style="font-size: 13px;">Direktori lengkap mahasiswa Universitas Sugeng Hartono, filter berdasarkan program studi, angkatan, status akademik, dan capaian IPK KHS.</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('akademik.dashboard') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
            </a>
            <button type="button" class="btn-sync-siakad" id="btn-sync-siakad" onclick="handleSyncSiakad()" title="Tarik data mahasiswa baru dari SIAKAD (Beasiswa & Reguler)">
                <i class="bi bi-cloud-arrow-down"></i> Sinkron Siakad
            </button>
            <button type="button" class="btn-refresh-all-siakad" id="btn-refresh-all-siakad" onclick="handleRefreshAllSiakad()" title="Perbarui Semester & Nilai IPK mahasiswa penerima beasiswa dari SIAKAD">
                <i class="bi bi-arrow-clockwise"></i> Refresh Data SIAKAD
            </button>
            <a href="{{ route('akademik.monitoring-beasiswa.index') }}" class="btn-outline-beasiswa">
                <i class="bi bi-award-fill"></i> Monitoring Beasiswa
            </a>
            <a href="{{ route('akademik.direktori-mahasiswa.export', request()->query()) }}" class="btn-outline-export" title="Unduh direktori mahasiswa dalam format CSV">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
            </a>
        </div>
    </div>

    <form id="form-sync-siakad" action="{{ route('akademik.direktori-mahasiswa.sync-siakad') }}" method="POST" style="display: none;">
        @csrf
    </form>
    <form id="form-refresh-siakad" action="{{ route('akademik.direktori-mahasiswa.refresh-siakad') }}" method="POST" style="display: none;">
        @csrf
    </form>

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2 mb-3" style="border-radius: 12px; font-size: 13.5px; font-weight: 500;">
            <i class="bi bi-check-circle-fill"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" style="border-radius: 12px; font-size: 13.5px; font-weight: 500;">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <!-- Quick Summary KPI Stats -->
    <div class="stats-row">
        <div class="stat-box total">
            <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
            <div class="stat-details">
                <span class="stat-count">{{ $totalStudents ?? 0 }}</span>
                <span class="stat-label">Total Mahasiswa</span>
                <span class="stat-sub">Seluruh Mahasiswa Terdaftar</span>
            </div>
        </div>
        <div class="stat-box active">
            <div class="stat-icon"><i class="bi bi-person-check-fill"></i></div>
            <div class="stat-details">
                <span class="stat-count">{{ $activeStudentCount ?? 0 }}</span>
                <span class="stat-label">Mahasiswa Aktif</span>
                <span class="stat-sub">Status Kuliah Aktif</span>
            </div>
        </div>
        <div class="stat-box leave">
            <div class="stat-icon"><i class="bi bi-person-dash-fill"></i></div>
            <div class="stat-details">
                <span class="stat-count">{{ $leaveStudentCount ?? 0 }}</span>
                <span class="stat-label">Mahasiswa Cuti</span>
                <span class="stat-sub">Izin Cuti Akademik</span>
            </div>
        </div>
        <div class="stat-box inactive">
            <div class="stat-icon"><i class="bi bi-person-x-fill"></i></div>
            <div class="stat-details">
                <span class="stat-count">{{ $inactiveStudentCount ?? 0 }}</span>
                <span class="stat-label">Nonaktif / Lainnya</span>
                <span class="stat-sub">Status Tidak Aktif</span>
            </div>
        </div>
    </div>

    <!-- Search & Filter Card (Clean & Ringkas: Pencarian, Penetapan Ambassador, Kategori Beasiswa) -->
    <div class="filter-card shadow-sm mb-4">
        <form method="GET" action="{{ route('akademik.direktori-mahasiswa.index') }}" class="filter-grid-direktori">
            <div class="filter-item search-item">
                <label>Pencarian</label>
                <div class="input-with-icon">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" class="form-control-modern" value="{{ $search }}" placeholder="Ketik Nama, NIM, atau email...">
                </div>
            </div>

            <div class="filter-item">
                <label>Penetapan Ambassador</label>
                <select name="status_ambassador" class="form-select-modern" onchange="this.form.submit()">
                    <option value="">Semua Penetapan</option>
                    <option value="studentambassador" {{ ($statusAmbassador ?? '') === 'studentambassador' ? 'selected' : '' }}>Student Ambassador</option>
                    <option value="mahasiswa" {{ ($statusAmbassador ?? '') === 'mahasiswa' ? 'selected' : '' }}>Mahasiswa Reguler</option>
                </select>
            </div>

            <div class="filter-item">
                <label>Kategori Beasiswa</label>
                <select name="beasiswa" class="form-select-modern" onchange="this.form.submit()">
                    <option value="">Semua Beasiswa</option>
                    <option value="has_scholarship" {{ ($beasiswa ?? '') === 'has_scholarship' ? 'selected' : '' }}>Penerima Beasiswa (> 0%)</option>
                    <option value="100%" {{ ($beasiswa ?? '') === '100%' ? 'selected' : '' }}>Beasiswa 100%</option>
                    <option value="75%" {{ ($beasiswa ?? '') === '75%' ? 'selected' : '' }}>Beasiswa 75%</option>
                    <option value="50%" {{ ($beasiswa ?? '') === '50%' ? 'selected' : '' }}>Beasiswa 50%</option>
                    <option value="25%" {{ ($beasiswa ?? '') === '25%' ? 'selected' : '' }}>Beasiswa 25%</option>
                    <option value="0%" {{ ($beasiswa ?? '') === '0%' ? 'selected' : '' }}>0% / Reguler</option>
                </select>
            </div>

            <div class="filter-actions-direktori">
                @if($search || $statusAmbassador || $beasiswa)
                    <a href="{{ route('akademik.direktori-mahasiswa.index') }}" class="btn-reset-modern" title="Reset Semua Filter">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                @endif
                <button type="submit" class="btn-search-modern">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    @if($students->count() > 0)
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Mahasiswa & NIM</th>
                        <th>Program Studi & Angkatan</th>
                        <th style="text-align: center;">Semester</th>
                        <th style="text-align: center;">IPK</th>
                        <th style="text-align: center;">Beasiswa (%)</th>
                        <th>Penetapan</th>
                        <th>Dosen PA</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                        <tr>
                            <td>{{ $loop->iteration + ($students->currentPage() - 1) * $students->perPage() }}</td>
                            <td>
                                <div class="student-cell">
                                    <span class="student-name">{{ $student->nama_lengkap }}</span>
                                    <span class="student-nim font-monospace">{{ $student->nim }}</span>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <span class="prodi-text">{{ $student->program_studi ?? '-' }}</span>
                                    <span class="angkatan-badge">{{ $student->angkatan ? 'Angkatan ' . $student->angkatan : '-' }}</span>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge-sem">{{ $student->semester ? 'Sem. ' . $student->semester : '-' }}</span>
                            </td>
                            <td style="text-align: center;">
                                @if(filled($student->ipk))
                                    @php
                                        $ipkVal = (float)$student->ipk;
                                        $ipkClass = $ipkVal >= 3.5 ? 'ipk-cumlaude' : ($ipkVal >= 3.0 ? 'ipk-good' : 'ipk-fair');
                                    @endphp
                                    <span class="badge-ipk {{ $ipkClass }}">{{ number_format($ipkVal, 2) }}</span>
                                @else
                                    <span class="text-muted" style="font-size: 12px;">-</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @php
                                    $pct = trim((string)$student->beasiswa);
                                    $bClass = match($pct) {
                                        '100%' => 'badge-beasiswa-100',
                                        '75%'  => 'badge-beasiswa-75',
                                        '50%'  => 'badge-beasiswa-50',
                                        '25%'  => 'badge-beasiswa-25',
                                        default => 'badge-beasiswa-0'
                                    };
                                @endphp
                                <span class="badge-beasiswa {{ $bClass }}">{{ $pct ?: '0%' }}</span>
                            </td>
                            <td>
                                @if($student->status === 'studentambassador')
                                    <span class="status-badge-ambassador">
                                        <i class="bi bi-star-fill"></i> Ambassador
                                    </span>
                                @else
                                    <span class="status-badge-mahasiswa">
                                        <i class="bi bi-person"></i> Reguler
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="dosen-pa-text" title="{{ $student->dosenPA->name ?? ($student->id_lecturer ? 'ID: ' . $student->id_lecturer : '-') }}">
                                    {{ $student->dosenPA->name ?? ($student->id_lecturer ? 'ID: ' . $student->id_lecturer : '-') }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                @php
                                    $statusClass = match ($student->status_mahasiswa) {
                                        'Aktif' => 'status-approved',
                                        'Cuti' => 'status-pending',
                                        default => 'status-rejected',
                                    };
                                    $statusLabel = match ($student->status_mahasiswa) {
                                        'Aktif' => 'Aktif',
                                        'Cuti' => 'Cuti',
                                        default => $student->status_mahasiswa ?: 'Tidak Aktif',
                                    };
                                @endphp
                                <span class="badge-status {{ $statusClass }}">{{ $statusLabel }}</span>
                            </td>
                            <td style="text-align: right;">
                                <div class="action-buttons" style="justify-content: flex-end;">
                                    <button type="button" class="btn-view" onclick="openStudentModal({{ json_encode([
                                        'nama' => $student->nama_lengkap,
                                        'nim' => $student->nim,
                                        'prodi' => $student->program_studi ?? '-',
                                        'angkatan' => $student->angkatan ?? '-',
                                        'tanggal_masuk' => $student->tanggal_masuk ? \Carbon\Carbon::parse($student->tanggal_masuk)->translatedFormat('F Y') : '-',
                                        'semester' => $student->semester ? 'Semester ' . $student->semester : '-',
                                        'ipk' => filled($student->ipk) ? number_format((float)$student->ipk, 2) : 'Belum Ada IPK',
                                        'beasiswa' => $student->beasiswa ?: '0%',
                                        'status' => $student->status === 'studentambassador' ? 'Student Ambassador (Duta Kampus)' : 'Mahasiswa Reguler',
                                        'status_is_ambassador' => $student->status === 'studentambassador',
                                        'status_mahasiswa' => $student->status_mahasiswa ?: 'Aktif',
                                        'gender' => $student->jenis_kelamin === 'P' ? 'Perempuan' : 'Laki-laki',
                                        'dosen_pa' => $student->dosenPA->name ?? ($student->id_lecturer ? 'ID: ' . $student->id_lecturer : '-'),
                                        'telepon' => $student->no_telepon ?: '-',
                                        'email' => $student->email ?: '-',
                                        'alamat' => $student->alamat ?: '-',
                                        'notes' => $student->notes ?: 'Belum ada catatan.'
                                    ]) }})" title="Lihat Detail Profil">
                                        <i class="bi bi-eye"></i> Detail
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper mt-4">
            {{ $students->links('pagination::bootstrap-5') }}
        </div>
    @else
        <div class="empty-state">
            <i class="bi bi-mortarboard-fill"></i>
            <p>Tidak ada data mahasiswa yang cocok dengan kriteria filter.</p>
        </div>
    @endif
</div>

{{-- Modal Quick Detail Mahasiswa Shared Component --}}
@include('akademik.script')
@endsection

@push('css')
<style>
    .content-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid #ECEFF1;
        flex-wrap: wrap;
        gap: 12px;
    }
    .card-header h3 {
        margin: 0;
        font-size: 22px;
        font-weight: 700;
        color: #1E293B;
    }
    .header-actions {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }
    .btn-secondary {
        padding: 9px 16px;
        background: #F1F5F9;
        color: #475569;
        border-radius: 10px;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid #E2E8F0;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13.5px;
        transition: all 0.2s;
    }
    .btn-secondary:hover {
        background: #E2E8F0;
        color: #1E293B;
    }
    .btn-outline-beasiswa {
        padding: 9px 16px;
        background: #FFFBEB;
        color: #B45309;
        border: 1px solid #FDE68A;
        border-radius: 10px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13.5px;
        transition: all 0.2s;
    }
    .btn-outline-beasiswa:hover {
        background: #FEF3C7;
        color: #92400E;
    }
    .btn-outline-export {
        padding: 9px 16px;
        background: #ECFDF5;
        color: #047857;
        border: 1px solid #A7F3D0;
        border-radius: 10px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13.5px;
        transition: all 0.2s;
    }
    .btn-outline-export:hover {
        background: #059669;
        color: white;
        border-color: #059669;
    }

    /* Stats Row */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }
    .stat-box {
        background: #F8FAFC;
        border-radius: 14px;
        padding: 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        border: 1px solid #E2E8F0;
        transition: all 0.2s;
    }
    .stat-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.04);
    }
    .stat-box.total .stat-icon { background: #DBEAFE; color: #1D4ED8; }
    .stat-box.active .stat-icon { background: #DCFCE7; color: #15803D; }
    .stat-box.leave .stat-icon { background: #FEF3C7; color: #B45309; }
    .stat-box.inactive .stat-icon { background: #FEE2E2; color: #B91C1C; }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }
    .stat-details {
        display: flex;
        flex-direction: column;
    }
    .stat-count {
        font-size: 24px;
        font-weight: 800;
        color: #1E293B;
        line-height: 1.1;
    }
    .stat-label {
        font-size: 12.5px;
        font-weight: 600;
        color: #475569;
        margin-top: 2px;
    }
    .stat-sub {
        font-size: 11px;
        color: #94A3B8;
    }

    /* Filter Grid */
    .filter-card {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        padding: 18px 20px;
    }
    .filter-grid-direktori {
        display: grid;
        grid-template-columns: minmax(240px, 2.2fr) minmax(200px, 1.2fr) minmax(200px, 1.2fr) auto;
        gap: 14px;
        align-items: flex-end;
    }
    @media (max-width: 992px) {
        .filter-grid-direktori {
            grid-template-columns: 1fr 1fr;
        }
        .filter-actions-direktori {
            grid-column: 1 / -1;
            justify-content: flex-end;
        }
    }
    @media (max-width: 600px) {
        .filter-grid-direktori {
            grid-template-columns: 1fr;
        }
        .filter-actions-direktori {
            justify-content: stretch;
        }
        .btn-search-modern, .btn-reset-modern {
            flex: 1;
            justify-content: center;
        }
    }
    .filter-item {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .filter-item label {
        font-size: 12px;
        font-weight: 700;
        color: #475569;
        letter-spacing: 0.3px;
    }
    .input-with-icon {
        position: relative;
    }
    .input-with-icon i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94A3B8;
        font-size: 14px;
        pointer-events: none;
    }
    .form-control-modern {
        width: 100%;
        height: 42px;
        padding: 0 14px 0 38px;
        border: 1px solid #CBD5E1;
        border-radius: 10px;
        font-size: 13.5px;
        background: white;
        color: #1E293B;
        transition: all 0.2s ease;
    }
    .form-control-modern:focus {
        outline: none;
        border-color: #2563EB;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }
    .form-select-modern {
        width: 100%;
        height: 42px;
        padding: 0 14px;
        border: 1px solid #CBD5E1;
        border-radius: 10px;
        font-size: 13.5px;
        background: white;
        color: #1E293B;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .form-select-modern:focus {
        outline: none;
        border-color: #2563EB;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }
    .filter-actions-direktori {
        display: flex;
        gap: 8px;
    }
    .btn-search-modern {
        height: 42px;
        padding: 0 18px;
        background: #1E3A8A;
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    .btn-search-modern:hover {
        background: #1D4ED8;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(29, 78, 216, 0.2);
    }
    .btn-reset-modern {
        height: 42px;
        padding: 0 14px;
        background: #F1F5F9;
        color: #475569;
        border: 1px solid #CBD5E1;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    .btn-reset-modern:hover {
        background: #E2E8F0;
        color: #1E293B;
    }

    /* Table */
    .table-responsive {
        overflow-x: auto;
    }
    .data-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .data-table th {
        background: #F8FAFC;
        padding: 12px 14px;
        font-size: 12.5px;
        font-weight: 700;
        color: #475569;
        border-bottom: 2px solid #E2E8F0;
    }
    .data-table td {
        padding: 14px;
        border-bottom: 1px solid #F1F5F9;
        font-size: 13px;
        vertical-align: middle;
    }
    .student-cell {
        display: flex;
        flex-direction: column;
    }
    .student-name {
        font-weight: 700;
        color: #1E293B;
    }
    .student-nim {
        font-size: 12px;
        color: #64748B;
    }
    .prodi-text {
        font-weight: 600;
        color: #334155;
        display: block;
    }
    .angkatan-badge {
        font-size: 11.5px;
        color: #64748B;
    }
    .badge-sem {
        font-size: 11.5px;
        background: #F1F5F9;
        padding: 3px 8px;
        border-radius: 6px;
        font-weight: 600;
        color: #475569;
    }

    /* Badges */
    .badge-ipk {
        padding: 4px 10px;
        border-radius: 8px;
        font-weight: 800;
        font-size: 12.5px;
        display: inline-block;
    }
    .ipk-cumlaude { background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0; }
    .ipk-good { background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; }
    .ipk-fair { background: #FFFBEB; color: #B45309; border: 1px solid #FDE68A; }

    .badge-beasiswa {
        padding: 4px 10px;
        border-radius: 8px;
        font-weight: 800;
        font-size: 12px;
        display: inline-block;
    }
    .badge-beasiswa-100 { background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0; }
    .badge-beasiswa-75  { background: #E0F2FE; color: #0369A1; border: 1px solid #BAE6FD; }
    .badge-beasiswa-50  { background: #F3E8FF; color: #6B21A8; border: 1px solid #E9D5FF; }
    .badge-beasiswa-25  { background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A; }
    .badge-beasiswa-0   { background: #F1F5F9; color: #64748B; border: 1px solid #E2E8F0; }

    .status-badge-ambassador {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        background: linear-gradient(135deg, #FFF8E1, #FFECB3);
        color: #B78103;
        border: 1px solid #FFE082;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 700;
    }
    .status-badge-mahasiswa {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        background: #F1F5F9;
        color: #64748B;
        border: 1px solid #E2E8F0;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 600;
    }
    .dosen-pa-text {
        font-size: 12.5px;
        color: #475569;
        max-width: 150px;
        display: inline-block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .badge-status {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11.5px;
        font-weight: 700;
    }
    .status-approved { background: #DCFCE7; color: #166534; }
    .status-pending { background: #FEF3C7; color: #92400E; }
    .status-rejected { background: #FEE2E2; color: #991B1B; }

    .btn-view {
        padding: 6px 14px;
        background: #EFF6FF;
        border: 1px solid #BFDBFE;
        color: #1D4ED8;
        border-radius: 8px;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.2s;
    }
    .btn-view:hover {
        background: #2563EB;
        color: white;
        border-color: #2563EB;
    }

    .empty-state {
        text-align: center;
        padding: 48px 20px;
        color: #64748B;
    }
    .empty-state i {
        font-size: 40px;
        display: block;
        margin-bottom: 12px;
        color: #CBD5E1;
    }

    /* Siakad Sync Buttons */
    .btn-sync-siakad {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 16px;
        background: linear-gradient(135deg, #00796B, #004D40);
        color: white;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13.5px;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.25s ease;
        box-shadow: 0 4px 12px rgba(0, 121, 107, 0.25);
    }
    .btn-sync-siakad:hover {
        background: linear-gradient(135deg, #004D40, #00332C);
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 121, 107, 0.35);
        color: white;
    }
    .btn-sync-siakad:active {
        transform: translateY(0);
    }

    .btn-refresh-all-siakad {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 16px;
        background: linear-gradient(135deg, #0284C7, #0369A1);
        color: white;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13.5px;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.25s ease;
        box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);
    }
    .btn-refresh-all-siakad:hover {
        background: linear-gradient(135deg, #0369A1, #075985);
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(2, 132, 199, 0.35);
        color: white;
    }
    .btn-refresh-all-siakad:active {
        transform: translateY(0);
    }

    @media (max-width: 992px) {
        .stats-row { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 600px) {
        .stats-row { grid-template-columns: 1fr; }
    }
</style>
@endpush

@push('scripts')
<script>
function handleSyncSiakad() {
    Swal.fire({
        title: 'Memeriksa Data Siakad...',
        html: 'Menghubungkan ke Siakad Rekomendasi Beasiswa Termin...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
            fetch("{{ route('akademik.direktori-mahasiswa.preview-siakad') }}")
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Koneksi Siakad Gagal',
                            text: data.message || 'Tidak dapat terhubung ke API Siakad.'
                        });
                        return;
                    }

                    Swal.fire({
                        title: 'Tarik Data Mahasiswa Baru dari SIAKAD',
                        html: `
                            <div style="text-align: left; font-size: 14px;">
                                <div style="background: #E0F2FE; border-left: 4px solid #0284C7; padding: 12px; border-radius: 6px; margin-bottom: 12px; color: #0369A1;">
                                    <strong><i class="bi bi-info-circle-fill" style="color: #0284C7;"></i> Ditemukan ${data.total} mahasiswa</strong> (Angkatan 2026+) di SIAKAD.
                                </div>
                                <table style="width: 100%; font-size: 13px; margin-bottom: 8px;">
                                    <tr>
                                        <td style="padding: 5px 0; color: #64748B;">Mahasiswa Penerima Beasiswa di SIAKAD:</td>
                                        <td style="padding: 5px 0; font-weight: 700; color: #059669; text-align: right;">${data.beasiswa_count} Mahasiswa</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0; color: #64748B;">Mahasiswa Reguler (Non-Beasiswa) di SIAKAD:</td>
                                        <td style="padding: 5px 0; font-weight: 700; color: #0284C7; text-align: right;">${data.reguler_count} Mahasiswa</td>
                                    </tr>
                                    <tr style="border-top: 1px dashed #CBD5E1;">
                                        <td style="padding: 8px 0 4px; color: #1E293B; font-weight: 600;">Mahasiswa Baru Siap Ditarik:</td>
                                        <td style="padding: 8px 0 4px; font-weight: 800; color: #16A34A; text-align: right; font-size: 15px;">+${data.new_count} Mahasiswa</td>
                                    </tr>
                                    ${data.new_count > 0 ? `
                                    <tr>
                                        <td colspan="2" style="padding: 0 0 4px; font-size: 12px; color: #64748B; text-align: right;">
                                            (${data.new_beasiswa_count > 0 ? `Beasiswa: <b>${data.new_beasiswa_count}</b>, ` : ''}Reguler: <b>${data.new_reguler_count}</b>)
                                        </td>
                                    </tr>
                                    ` : ''}
                                </table>
                                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 10px; margin-top: 10px; font-size: 12px; color: #475569; line-height: 1.45;">
                                    <i class="bi bi-shield-check" style="color: #059669;"></i> <strong>Catatan:</strong> Tombol ini hanya akan mendaftarkan <b>mahasiswa baru</b> yang belum tercatat di SuperApps (agar tidak perlu input manual). Periode Masuk & Tanggal Masuk otomatis ditentukan dari SIAKAD. Data yang sudah terdaftar <b>tidak akan diubah</b>.
                                </div>
                            </div>
                        `,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#00796B',
                        cancelButtonColor: '#94A3B8',
                        confirmButtonText: '<i class="bi bi-cloud-arrow-down"></i> Ya, Tarik Mahasiswa Baru',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Sedang Menarik Data...',
                                html: 'Sistem sedang memproses penarikan mahasiswa baru dari SIAKAD...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            document.getElementById('form-sync-siakad').submit();
                        }
                    });
                })
                .catch(err => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        text: err.message || 'Gagal memuat pratinjau data Siakad.'
                    });
                });
        }
    });
}

function handleRefreshAllSiakad() {
    Swal.fire({
        title: 'Refresh Data SIAKAD',
        html: `
            <div style="text-align: left; font-size: 13.5px; color: #475569; line-height: 1.55;">
                <p>Sistem akan memperbarui data <strong>Semester, Nilai IPK, dan Periode Masuk</strong> resmi dari SIAKAD untuk <strong>seluruh mahasiswa (Beasiswa & Reguler)</strong> yang sudah terdaftar di SuperApps.</p>
                <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; padding: 10px 12px; margin-top: 10px; color: #166534; font-size: 12.5px;">
                    <i class="bi bi-shield-check"></i> <strong>Aman:</strong> Hanya menyinkronkan nilai Semester, IPK, dan periode masuk terbaru dari SIAKAD. Tidak menambah mahasiswa baru, serta nomor kontak dan data evaluasi beasiswa SuperApps tetap terjaga.
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0284C7',
        cancelButtonColor: '#6B7280',
        confirmButtonText: '<i class="bi bi-arrow-clockwise"></i> Ya, Refresh Semester & IPK',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memperbarui Data...',
                text: 'Sedang memperbarui data akademik seluruh mahasiswa dari SIAKAD...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            document.getElementById('form-refresh-siakad').submit();
        }
    });
}
</script>
@endpush
