@extends('admin.layouts.super-app')

@section('content')
<div class="content-card">
    <div class="card-header">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                <h3 class="mb-0"><i class="bi bi-people-fill text-primary me-2"></i> Direktori Mahasiswa & Monitoring Akademik</h3>
                @if(!empty($activePeriod))
                    <span class="badge" style="background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; font-size: 12.5px; font-weight: 700; border-radius: 20px; padding: 5px 12px; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="bi bi-calendar-check-fill text-primary"></i> Periode Aktif: {{ $activePeriod['tahun_akademik'] ?? '-' }} (Semester {{ $activePeriod['semester'] ?? '-' }})
                    </span>
                @endif
            </div>
            <p class="text-muted mb-0" style="font-size: 13px;">Direktori lengkap mahasiswa Universitas Sugeng Hartono, filter berdasarkan program studi, angkatan, status akademik, dan capaian IPK KHS.</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('akademik.dashboard') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
            </a>
            <a href="{{ route('akademik.monitoring-beasiswa.index') }}" class="btn-outline-beasiswa">
                <i class="bi bi-award-fill"></i> Monitoring Beasiswa
            </a>
            <a href="{{ route('akademik.direktori-mahasiswa.export', request()->query()) }}" class="btn-outline-export" title="Unduh direktori mahasiswa dalam format CSV">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
            </a>
        </div>
    </div>

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

    <!-- Filter Card -->
    <div class="filter-card shadow-sm mb-4">
        <form method="GET" action="{{ route('akademik.direktori-mahasiswa.index') }}" class="filter-grid-direktori">
            <div class="filter-item search-item">
                <label>Pencarian</label>
                <div class="input-with-icon">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" class="form-control-modern" value="{{ $search }}" placeholder="Nama, NIM, atau email...">
                </div>
            </div>

            <div class="filter-item">
                <label>Status Mahasiswa</label>
                <select name="status_mahasiswa" class="form-select-modern" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="Aktif" {{ $statusMahasiswa === 'Aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="Cuti" {{ $statusMahasiswa === 'Cuti' ? 'selected' : '' }}>Cuti</option>
                    <option value="Tidak Aktif" {{ $statusMahasiswa === 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
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

            <div class="filter-item">
                <label>Program Studi</label>
                <select name="program_studi" class="form-select-modern" onchange="this.form.submit()">
                    <option value="">Semua Program Studi</option>
                    @foreach($programOptions as $prodi)
                        <option value="{{ $prodi }}" {{ $programStudi === $prodi ? 'selected' : '' }}>{{ $prodi }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-item">
                <label>Angkatan</label>
                <select name="angkatan" class="form-select-modern" onchange="this.form.submit()">
                    <option value="">Semua Angkatan</option>
                    @foreach($angkatanOptions as $year)
                        <option value="{{ $year }}" {{ (string) $angkatan === (string) $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-actions-direktori">
                @if($search || $statusMahasiswa || $statusAmbassador || $beasiswa || $programStudi || $angkatan)
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
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) auto;
        gap: 12px;
        align-items: flex-end;
    }
    .filter-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    .filter-item label {
        font-size: 12px;
        font-weight: 600;
        color: #475569;
    }
    .input-with-icon {
        position: relative;
    }
    .input-with-icon i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94A3B8;
    }
    .form-control-modern {
        width: 100%;
        padding: 9px 12px 9px 34px;
        border: 1px solid #CBD5E1;
        border-radius: 9px;
        font-size: 13px;
        background: white;
    }
    .form-select-modern {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid #CBD5E1;
        border-radius: 9px;
        font-size: 13px;
        background: white;
    }
    .filter-actions-direktori {
        display: flex;
        gap: 8px;
    }
    .btn-search-modern {
        padding: 9px 16px;
        background: #2563EB;
        color: white;
        border: none;
        border-radius: 9px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-reset-modern {
        padding: 9px 14px;
        background: #E2E8F0;
        color: #475569;
        border-radius: 9px;
        font-weight: 600;
        font-size: 13px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
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

    @media (max-width: 992px) {
        .stats-row { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 600px) {
        .stats-row { grid-template-columns: 1fr; }
    }
</style>
@endpush
