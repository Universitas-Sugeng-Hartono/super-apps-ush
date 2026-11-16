@extends('admin.layouts.super-app')

@section('content')
<div class="monitoring-kipk-container">

    {{-- Header Page & Navigation --}}
    <div class="page-header">
        <a href="{{ route('kemahasiswaan.dashboard') }}" class="btn-back">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard Kemahasiswaan
        </a>
        <div class="header-main-box">
            <div class="header-info">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge-role-tag"><i class="bi bi-mortarboard-fill"></i> Biro Kemahasiswaan</span>
                    <span class="badge-source-tag"><i class="bi bi-hdd-network"></i> Sinkronisasi SIAKAD</span>
                </div>
                <h1>Monitoring Beasiswa KIP-K</h1>
                <p>Pemantauan data mahasiswa penerima KIP-Kuliah, perolehan nilai IPS & IPK per semester, serta status keaktifan mahasiswa sesuai data resmi SIAKAD.</p>
            </div>
            <div class="header-action-btns">
                <a href="{{ $hasSelectedPeriod ? route('kemahasiswaan.monitoring-kipk.export.csv', request()->query()) : 'javascript:void(0)' }}"
                   class="btn-export-csv {{ !$hasSelectedPeriod ? 'btn-disabled' : '' }}"
                   id="btnExportCsv"
                   title="{{ $hasSelectedPeriod ? 'Unduh data dalam format CSV' : 'Pilih Tahun Akademik dan Periode terlebih dahulu' }}">
                    <i class="bi bi-file-earmark-spreadsheet-fill"></i> Ekspor CSV
                </a>
            </div>
        </div>
    </div>

    {{-- Error Banner jika API SIAKAD offline / kendala --}}
    @if(!$isApiSuccess)
    <div class="api-alert-banner">
        <div class="alert-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
        <div class="alert-body">
            <strong>Peringatan Koneksi SIAKAD</strong>
            <p>{{ $apiErrorMessage ?? 'Sistem tidak dapat terhubung ke SIAKAD REST API secara langsung. Pastikan server SIAKAD aktif di port 8090.' }}</p>
        </div>
        <button type="button" class="btn-retry" onclick="window.location.reload()">
            <i class="bi bi-arrow-clockwise"></i> Coba Lagi
        </button>
    </div>
    @endif

    {{-- Summary Statistics Cards --}}
    <div class="stats-row">
        <div class="stat-box primary">
            <div class="stat-icon-wrapper"><i class="bi bi-people-fill"></i></div>
            <div class="stat-content">
                <span class="stat-label">Total Mahasiswa KIP-K</span>
                <h3 id="stat_total">{{ $hasSelectedPeriod ? number_format($statistics['total_mahasiswa'] ?? 0) : '-' }}</h3>
                <span class="stat-sub" id="stat_sub_total">{{ $hasSelectedPeriod ? 'Periode Terpilih' : 'Pilih Tahun & Periode' }}</span>
            </div>
        </div>

        <div class="stat-box success">
            <div class="stat-icon-wrapper"><i class="bi bi-check-circle-fill"></i></div>
            <div class="stat-content">
                <span class="stat-label">Mahasiswa Aktif</span>
                <h3 id="stat_aktif">{{ $hasSelectedPeriod ? number_format($statistics['aktif_count'] ?? 0) : '-' }}</h3>
                <span class="stat-sub" id="stat_sub_aktif">{{ $hasSelectedPeriod ? 'Status SIAKAD: AKTIF' : 'Menunggu Filter' }}</span>
            </div>
        </div>

        <div class="stat-box warning">
            <div class="stat-icon-wrapper"><i class="bi bi-pause-circle-fill"></i></div>
            <div class="stat-content">
                <span class="stat-label">Mahasiswa Cuti / Lainnya</span>
                <h3 id="stat_cuti">{{ $hasSelectedPeriod ? number_format(($statistics['cuti_count'] ?? 0) + ($statistics['lainnya_count'] ?? 0)) : '-' }}</h3>
                <span class="stat-sub" id="stat_sub_cuti">{{ $hasSelectedPeriod ? ('Cuti: ' . ($statistics['cuti_count'] ?? 0) . ' • Lainnya: ' . ($statistics['lainnya_count'] ?? 0)) : 'Menunggu Filter' }}</span>
            </div>
        </div>

        <div class="stat-box info">
            <div class="stat-icon-wrapper"><i class="bi bi-mortarboard-fill"></i></div>
            <div class="stat-content">
                <span class="stat-label">Rata-rata IPK</span>
                <h3 id="stat_ipk">{{ $hasSelectedPeriod ? number_format((float) ($statistics['avg_ipk'] ?? 0), 2) : '-' }}</h3>
                <span class="stat-sub" id="stat_sub_ips">Rata-rata IPS: <b id="stat_ips">{{ $hasSelectedPeriod ? number_format((float) ($statistics['avg_ips'] ?? 0), 2) : '-' }}</b></span>
            </div>
        </div>
    </div>

    {{-- Filter Toolbar Card --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('kemahasiswaan.monitoring-kipk.index') }}" id="filterForm" class="filter-grid">
            {{-- Tahun Akademik --}}
            <div class="form-group">
                <label for="id_by"><i class="bi bi-calendar-check me-1"></i> Tahun Akademik</label>
                <select name="id_by" id="id_by" class="form-select custom-select">
                    <option value="">-- Pilih Tahun Akademik --</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year['id'] }}" {{ (string) $selectedBy === (string) $year['id'] ? 'selected' : '' }}>
                            {{ $year['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Periode Tahun Akademik (Ganjil / Genap) --}}
            <div class="form-group">
                <label for="id_sub_by"><i class="bi bi-clock-history me-1"></i> Periode Semester</label>
                <select name="id_sub_by" id="id_sub_by" class="form-select custom-select" {{ empty($subBatchYears) ? 'disabled' : '' }}>
                    <option value="">-- Pilih Periode Semester --</option>
                    @foreach($subBatchYears as $sub)
                        <option value="{{ $sub['id'] }}" {{ (string) $selectedSubBy === (string) $sub['id'] ? 'selected' : '' }}>
                            Semester {{ $sub['name'] }} ({{ $sub['code'] ?? '-' }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Status Mahasiswa (Aktif, Cuti, dsb.) Sesuai Request User --}}
            <div class="form-group">
                <label for="status"><i class="bi bi-person-check me-1"></i> Status Mahasiswa</label>
                <select name="status" id="status" class="form-select custom-select">
                    <option value="">Semua Status Mahasiswa</option>
                    @foreach($statuses as $st)
                        @if(!empty($st))
                            <option value="{{ $st }}" {{ (string) ($filters['status'] ?? '') === (string) $st ? 'selected' : '' }}>
                                {{ strtoupper($st) }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            {{-- Program Studi --}}
            <div class="form-group">
                <label for="prodi"><i class="bi bi-building me-1"></i> Program Studi</label>
                <select name="prodi" id="prodi" class="form-select custom-select">
                    <option value="">Semua Program Studi</option>
                    @foreach($prodis as $p)
                        <option value="{{ $p['name'] }}" {{ (string) ($filters['prodi'] ?? '') === (string) $p['name'] ? 'selected' : '' }}>
                            {{ $p['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Pencarian Cepat --}}
            <div class="form-group search-group">
                <label for="search"><i class="bi bi-search me-1"></i> Cari Mahasiswa</label>
                <div class="input-with-icon">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" id="search" class="form-control custom-input" placeholder="Ketik NIM atau Nama..." value="{{ $filters['search'] ?? '' }}">
                </div>
            </div>

            {{-- Tombol Aksi Filter --}}
            <div class="form-group action-group">
                <label class="d-none d-md-block">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn-filter-submit" title="Terapkan Filter">
                        <i class="bi bi-funnel-fill"></i> Filter
                    </button>
                    <a href="{{ route('kemahasiswaan.monitoring-kipk.index') }}" class="btn-filter-reset" title="Reset Semua Filter">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Table Card --}}
    <div class="table-card">
        <div class="table-card-header">
            <div class="d-flex align-items-center gap-2">
                <h4 class="mb-0"><i class="bi bi-table text-warning me-1"></i> Daftar Mahasiswa KIP-K</h4>
                <span class="badge-count" id="badge_count">{{ $hasSelectedPeriod ? count($students) : 0 }} Data</span>
            </div>
            <div class="table-meta text-muted" id="table_meta_info">
                @if($hasSelectedPeriod)
                    Tahun: <b>{{ $statistics['active_batch_year'] ?? '-' }}</b> • Periode: <b>{{ $statistics['active_sub_by'] ?? '-' }}</b>
                @else
                    Silakan pilih <b>Tahun Akademik</b> & <b>Periode</b>
                @endif
            </div>
        </div>

        {{-- Loading Overlay --}}
        <div class="table-wrapper position-relative">
            <div class="loading-overlay d-none" id="tableLoadingOverlay">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Memuat data...</span>
                </div>
                <span class="mt-2 text-muted fw-semibold">Memuat data dari SIAKAD...</span>
            </div>

            <div class="table-responsive">
                <table class="custom-table" id="kipkTable">
                    <thead>
                        <tr>
                            <th style="width: 50px;" class="text-center">No</th>
                            <th style="width: 130px;">NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th>Program Studi</th>
                            <th style="width: 80px;" class="text-center">Semester</th>
                            <th style="width: 75px;" class="text-center">IPS</th>
                            <th style="width: 75px;" class="text-center">IPK</th>
                            <th style="width: 130px;" class="text-center">Status Mahasiswa</th>
                            <th style="width: 100px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @if(!$hasSelectedPeriod)
                            <tr id="rowInitialState">
                                <td colspan="9" class="text-center py-5">
                                    <div class="empty-state-box">
                                        <i class="bi bi-calendar-event text-primary fs-1 d-block mb-3"></i>
                                        <h5 class="text-dark fw-bold mb-1">Silakan Pilih Tahun Akademik & Periode</h5>
                                        <p class="text-muted mb-0">Pilih <strong>Tahun Akademik</strong> dan <strong>Periode Semester</strong> pada filter di atas untuk memuat data mahasiswa KIP-K.</p>
                                    </div>
                                </td>
                            </tr>
                        @else
                            @forelse($students as $index => $item)
                                @php
                                    $statusRaw = strtoupper(trim((string) ($item['status'] ?? 'AKTIF')));
                                    $ipkVal = (float) ($item['ipk'] ?? 0);
                                    $ipsVal = (float) ($item['ips'] ?? 0);
                                @endphp
                                <tr>
                                    <td class="text-center text-muted fw-semibold">{{ $index + 1 }}</td>
                                    <td>
                                        <span class="fw-bold text-dark font-monospace">{{ $item['nim'] ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <div class="student-name-box">
                                            <span class="student-name">{{ $item['name'] ?? '-' }}</span>
                                            @if(!empty($item['email']) && $item['email'] !== '-')
                                                <small class="text-muted d-block">{{ $item['email'] }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-prodi">{{ $item['prodi'] ?? '-' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-semester">Sem {{ $item['semester'] ?? '-' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="val-score">{{ number_format($ipsVal, 2) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="val-score ipk {{ $ipkVal >= 3.5 ? 'high' : ($ipkVal >= 3.0 ? 'good' : 'warning') }}">
                                            {{ number_format($ipkVal, 2) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($statusRaw === 'AKTIF')
                                            <span class="status-pill status-aktif">
                                                <i class="bi bi-check-circle-fill"></i> AKTIF
                                            </span>
                                        @elseif($statusRaw === 'CUTI')
                                            <span class="status-pill status-cuti">
                                                <i class="bi bi-pause-circle-fill"></i> CUTI
                                            </span>
                                        @elseif(str_contains($statusRaw, 'MUNDUR'))
                                            <span class="status-pill status-mundur">
                                                <i class="bi bi-x-circle-fill"></i> MUNDUR
                                            </span>
                                        @else
                                            <span class="status-pill status-lain">
                                                <i class="bi bi-info-circle-fill"></i> {{ $statusRaw }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button type="button"
                                                class="btn-detail-riwayat"
                                                data-id="{{ $item['id'] }}"
                                                data-name="{{ $item['name'] ?? '-' }}"
                                                data-nim="{{ $item['nim'] ?? '-' }}"
                                                data-prodi="{{ $item['prodi'] ?? '-' }}"
                                                title="Lihat Riwayat Semester Mahasiswa">
                                            <i class="bi bi-clock-history"></i> Riwayat
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr id="rowEmptyState">
                                    <td colspan="9" class="text-center py-5">
                                        <div class="empty-state-box">
                                            <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                                            <h5 class="text-dark mb-1">Tidak ada data mahasiswa KIP-K ditemukan</h5>
                                            <p class="text-muted mb-0">Coba ubah filter Status, Program Studi, atau kata kunci pencarian.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Riwayat Semester Mahasiswa (Custom Modal Backdrop Escaping Stacking Context) --}}
    <div class="custom-history-backdrop" id="modalRiwayatSemester" style="display: none;" role="dialog" aria-hidden="true" aria-labelledby="modalRiwayatSemesterLabel">
        <div class="custom-history-dialog">
            <div class="custom-history-content">
                <div class="custom-history-header">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge-modal-tag"><i class="bi bi-mortarboard-fill"></i> Riwayat Akademik SIAKAD</span>
                        </div>
                        <h4 class="modal-title fw-bold text-white mb-0" id="modalRiwayatSemesterLabel">
                            Riwayat Semester: <span id="mhsModalName">-</span>
                        </h4>
                        <small class="text-white-50" id="mhsModalSub">-</small>
                    </div>
                    <button type="button" class="btn-history-close" id="btnCloseHistoryModalTop" aria-label="Tutup">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="custom-history-body">
                    {{-- Loading State --}}
                    <div id="modalLoading" class="text-center py-5">
                        <div class="spinner-border" style="color: #00796B;" role="status">
                            <span class="visually-hidden">Memuat riwayat...</span>
                        </div>
                        <p class="text-muted mt-2 fw-semibold">Mengambil riwayat nilai semester dari SIAKAD...</p>
                    </div>

                    {{-- Error State --}}
                    <div id="modalError" class="alert alert-danger d-none" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <span id="modalErrorMsg">Gagal memuat data riwayat semester.</span>
                    </div>

                    {{-- Content State --}}
                    <div id="modalDataContent" class="d-none">
                        {{-- Mini Stats Bar --}}
                        <div class="history-metrics-grid mb-4">
                            <div class="metric-card">
                                <span class="metric-label">IPK Terakhir</span>
                                <h3 id="metricIpk" class="text-primary">-</h3>
                                <small class="text-muted">Kumulatif Selesai</small>
                            </div>
                            <div class="metric-card">
                                <span class="metric-label">Total SKS Diambil</span>
                                <h3 id="metricSks" class="text-success">-</h3>
                                <small class="text-muted">SKS Kumulatif</small>
                            </div>
                            <div class="metric-card">
                                <span class="metric-label">Semester Aktif</span>
                                <h3 id="metricSem" class="text-dark">-</h3>
                                <small class="text-muted" id="metricStatus">Status: AKTIF</small>
                            </div>
                            <div class="metric-card">
                                <span class="metric-label">Standar KIP-K</span>
                                <div id="metricKipkStatus" class="mt-1">
                                    <span class="badge bg-success-subtle text-success fw-bold">Memenuhi</span>
                                </div>
                                <small class="text-muted">Min. IPK 3.00</small>
                            </div>
                        </div>

                        {{-- Table Riwayat --}}
                        <div class="table-responsive rounded-3 border history-table-wrapper">
                            <table class="table table-hover align-middle mb-0 history-table" id="tableHistoryContent">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 130px;">Semester</th>
                                        <th class="text-center" style="width: 90px;">SKS Sem.</th>
                                        <th class="text-center" style="width: 95px;">SKS Kum.</th>
                                        <th class="text-center" style="width: 85px;">IPS</th>
                                        <th class="text-center" style="width: 95px;">IPK</th>
                                        <th class="text-center" style="width: 120px;">Tren IPK</th>
                                        <th class="text-center">Status Standar</th>
                                    </tr>
                                </thead>
                                <tbody id="tableHistoryBody">
                                    {{-- Dynamically filled via JS --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="custom-history-footer">
                    <button type="button" class="btn btn-secondary btn-sm px-4 fw-bold" id="btnCloseHistoryModalBottom">Tutup</button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('css')
<style>
    .monitoring-kipk-container {
        max-width: 1300px;
        margin: 0 auto;
        padding-bottom: 50px;
    }

    /* Page Header */
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #00796B;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        margin-bottom: 12px;
        transition: all 0.2s ease;
    }
    .btn-back:hover {
        color: #004D40;
        transform: translateX(-3px);
    }

    .header-main-box {
        background: linear-gradient(135deg, #004D40 0%, #00695C 60%, #00796B 100%);
        border-radius: 20px;
        padding: 30px 35px;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        box-shadow: 0 12px 30px rgba(0, 77, 64, 0.2);
    }
    .header-info h1 {
        font-size: 26px;
        font-weight: 800;
        margin: 0 0 6px 0;
    }
    .header-info p {
        font-size: 14px;
        opacity: 0.9;
        margin: 0;
        max-width: 750px;
        line-height: 1.5;
    }

    .badge-role-tag {
        background: rgba(255, 255, 255, 0.2);
        padding: 4px 12px;
        border-radius: 99px;
        font-size: 12px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .badge-source-tag {
        background: rgba(255, 193, 7, 0.25);
        color: #FFE082;
        padding: 4px 12px;
        border-radius: 99px;
        font-size: 12px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .btn-export-csv {
        background: white;
        color: #004D40;
        padding: 10px 20px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.15);
        transition: all 0.2s ease;
    }
    .btn-export-csv:hover {
        background: #F0FDF4;
        color: #00796B;
        transform: translateY(-2px);
    }

    /* API Alert Banner */
    .api-alert-banner {
        background: #FEF2F2;
        border: 1px solid #FCA5A5;
        border-radius: 14px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 25px;
    }
    .api-alert-banner .alert-icon {
        font-size: 26px;
        color: #DC2626;
    }
    .api-alert-banner .alert-body {
        flex: 1;
    }
    .api-alert-banner .alert-body strong {
        color: #991B1B;
        display: block;
        margin-bottom: 2px;
    }
    .api-alert-banner .alert-body p {
        color: #B91C1C;
        margin: 0;
        font-size: 13px;
    }
    .btn-retry {
        background: #DC2626;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
    }

    /* Statistics Row */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 18px;
        margin-bottom: 25px;
    }
    .stat-box {
        background: white;
        border-radius: 16px;
        padding: 20px 22px;
        display: flex;
        align-items: center;
        gap: 16px;
        border: 1px solid #E2E8F0;
        box-shadow: 0 4px 16px rgba(0,0,0,0.03);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.06);
    }
    .stat-icon-wrapper {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    .stat-box.primary .stat-icon-wrapper {
        background: #E0F2FE;
        color: #0284C7;
    }
    .stat-box.success .stat-icon-wrapper {
        background: #DCFCE7;
        color: #16A34A;
    }
    .stat-box.warning .stat-icon-wrapper {
        background: #FEF3C7;
        color: #D97706;
    }
    .stat-box.info .stat-icon-wrapper {
        background: #EDE9FE;
        color: #7C3AED;
    }

    .stat-content {
        flex: 1;
    }
    .stat-label {
        font-size: 12px;
        font-weight: 700;
        color: #64748B;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: block;
        margin-bottom: 4px;
    }
    .stat-content h3 {
        font-size: 24px;
        font-weight: 800;
        color: #0F172A;
        margin: 0 0 2px 0;
    }
    .stat-sub {
        font-size: 11px;
        color: #94A3B8;
        display: block;
    }

    /* Filter Card */
    .filter-card {
        background: white;
        border-radius: 16px;
        padding: 22px 24px;
        border: 1px solid #E2E8F0;
        box-shadow: 0 4px 16px rgba(0,0,0,0.03);
        margin-bottom: 25px;
    }
    .filter-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr) 1.5fr auto;
        gap: 14px;
        align-items: flex-end;
    }
    .form-group label {
        font-size: 12px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 6px;
        display: block;
    }
    .custom-select, .custom-input {
        height: 42px;
        border-radius: 10px;
        border: 1px solid #CBD5E1;
        font-size: 13px;
        color: #1E293B;
        padding: 8px 12px;
        background-color: #F8FAFC;
        transition: border-color 0.2s ease;
    }
    .custom-select:focus, .custom-input:focus {
        border-color: #00796B;
        background-color: #fff;
        box-shadow: 0 0 0 3px rgba(0, 121, 107, 0.15);
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
        font-size: 14px;
    }
    .input-with-icon input {
        padding-left: 36px;
    }
    .btn-filter-submit {
        height: 42px;
        background: #00796B;
        color: white;
        border: none;
        padding: 0 18px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background 0.2s ease;
    }
    .btn-filter-submit:hover {
        background: #004D40;
    }
    .btn-filter-reset {
        height: 42px;
        background: #F1F5F9;
        color: #475569;
        border: 1px solid #CBD5E1;
        padding: 0 14px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
    }
    .btn-filter-reset:hover {
        background: #E2E8F0;
        color: #1E293B;
    }

    /* Table Card */
    .table-card {
        background: white;
        border-radius: 16px;
        border: 1px solid #E2E8F0;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    .btn-export-csv.btn-disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background: #F1F5F9;
        color: #94A3B8;
        box-shadow: none;
        pointer-events: none;
    }
    .table-card-header {
        padding: 20px 24px;
        border-bottom: 1px solid #F1F5F9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .table-card-header h4 {
        font-size: 18px;
        font-weight: 800;
        color: #0F172A;
    }
    .badge-count {
        background: #E2E8F0;
        color: #334155;
        padding: 4px 10px;
        border-radius: 99px;
        font-size: 12px;
        font-weight: 700;
    }
    .table-meta {
        font-size: 13px;
    }

    /* Custom Table */
    .custom-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }
    .custom-table thead th {
        background: #F8FAFC;
        color: #475569;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 14px 16px;
        border-bottom: 1px solid #E2E8F0;
    }
    .custom-table tbody tr {
        transition: background-color 0.15s ease;
    }
    .custom-table tbody tr:hover {
        background-color: #F8FAFC;
    }
    .custom-table tbody td {
        padding: 14px 16px;
        font-size: 13px;
        vertical-align: middle;
        border-bottom: 1px solid #F1F5F9;
    }

    .student-name-box .student-name {
        font-weight: 700;
        color: #0F172A;
        display: block;
    }
    .student-name-box small {
        font-size: 11px;
    }

    .badge-prodi {
        background: #F1F5F9;
        color: #334155;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        display: inline-block;
    }
    .badge-semester {
        background: #EFF6FF;
        color: #1D4ED8;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
    }

    .val-score {
        font-weight: 700;
        font-size: 13px;
        color: #334155;
    }
    .val-score.ipk {
        padding: 3px 8px;
        border-radius: 6px;
        font-weight: 800;
    }
    .val-score.ipk.high {
        background: #DCFCE7;
        color: #15803D;
    }
    .val-score.ipk.good {
        background: #EFF6FF;
        color: #1D4ED8;
    }
    .val-score.ipk.warning {
        background: #FEF3C7;
        color: #B45309;
    }

    /* Status Pills */
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        border-radius: 99px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.3px;
    }
    .status-pill.status-aktif {
        background: #DCFCE7;
        color: #166534;
        border: 1px solid #BBF7D0;
    }
    .status-pill.status-cuti {
        background: #FEF3C7;
        color: #92400E;
        border: 1px solid #FDE68A;
    }
    .status-pill.status-mundur {
        background: #FEE2E2;
        color: #991B1B;
        border: 1px solid #FECACA;
    }
    .status-pill.status-lain {
        background: #F1F5F9;
        color: #475569;
        border: 1px solid #E2E8F0;
    }

    /* Loading Overlay */
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.85);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 10;
        backdrop-filter: blur(2px);
    }

    /* Button Detail Riwayat */
    .btn-detail-riwayat {
        background: #F0FDF4;
        color: #166534;
        border: 1px solid #BBF7D0;
        padding: 5px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .btn-detail-riwayat:hover {
        background: #DCFCE7;
        color: #14532D;
        border-color: #86EFAC;
        transform: translateY(-1px);
    }

    /* =========================================================================
       CUSTOM MODAL RIWAYAT (Escapes Stacking Context & Navbar z-index)
       ========================================================================= */
    .custom-history-backdrop {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background: rgba(15, 23, 42, 0.72) !important;
        backdrop-filter: blur(5px) !important;
        -webkit-backdrop-filter: blur(5px) !important;
        z-index: 999999 !important; /* Di atas header-section app (z-index 9998) */
        display: none;
        align-items: center;
        justify-content: center;
        padding: 40px 20px !important;
        box-sizing: border-box !important;
        overflow-y: auto !important;
    }
    .custom-history-dialog {
        width: 100% !important;
        max-width: 860px !important;
        margin: auto !important;
        display: flex !important;
        max-height: calc(100vh - 80px) !important;
        animation: modalZoomIn 0.22s cubic-bezier(0.16, 1, 0.3, 1) !important;
        position: relative !important;
        z-index: 1000000 !important;
    }
    @keyframes modalZoomIn {
        from { opacity: 0; transform: translateY(20px) scale(0.97); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    .custom-history-content {
        background: #ffffff !important; /* Mencegah tembus pandang */
        border-radius: 20px !important;
        width: 100% !important;
        max-height: calc(100vh - 80px) !important;
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
        box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.45) !important;
        border: 1px solid rgba(226, 232, 240, 0.9) !important;
    }
    .custom-history-header {
        background: linear-gradient(135deg, #004D40 0%, #00796B 100%) !important;
        padding: 20px 26px !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        color: #ffffff !important;
        flex-shrink: 0 !important;
    }
    .btn-history-close {
        background: rgba(255, 255, 255, 0.15) !important;
        border: none !important;
        color: #ffffff !important;
        width: 34px !important;
        height: 34px !important;
        border-radius: 50% !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 16px !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
    }
    .btn-history-close:hover {
        background: rgba(255, 255, 255, 0.3) !important;
        transform: rotate(90deg) scale(1.05) !important;
    }
    .custom-history-body {
        background: #ffffff !important; /* Solid background */
        padding: 24px 26px !important;
        overflow-y: auto !important;
        flex: 1 1 auto !important;
    }
    .custom-history-footer {
        background: #F8FAFC !important;
        border-top: 1px solid #E2E8F0 !important;
        padding: 14px 26px !important;
        display: flex !important;
        justify-content: flex-end !important;
        align-items: center !important;
        flex-shrink: 0 !important;
    }

    .badge-modal-tag {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        padding: 3px 10px;
        border-radius: 99px;
        font-size: 11px;
        font-weight: 700;
    }
    .history-metrics-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }
    .metric-card {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 14px 16px;
        text-align: center;
    }
    .metric-label {
        font-size: 11px;
        font-weight: 700;
        color: #64748B;
        text-transform: uppercase;
        display: block;
        margin-bottom: 2px;
    }
    .metric-card h3 {
        font-size: 20px;
        font-weight: 800;
        margin: 0;
    }
    .history-table-wrapper {
        background: #ffffff !important;
    }
    .history-table {
        background: #ffffff !important;
    }
    .history-table th,
    .history-table td {
        background-color: transparent;
    }
    .trend-pill {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        font-size: 11px;
        font-weight: 800;
        padding: 3px 8px;
        border-radius: 99px;
    }
    .trend-pill.trend-up {
        background: #DCFCE7;
        color: #15803D;
    }
    .trend-pill.trend-down {
        background: #FEE2E2;
        color: #B91C1C;
    }
    .trend-pill.trend-same {
        background: #F1F5F9;
        color: #64748B;
    }

    @media (max-width: 992px) {
        .filter-grid {
            grid-template-columns: 1fr 1fr;
        }
        .stats-row {
            grid-template-columns: repeat(2, 1fr);
        }
        .header-main-box {
            flex-direction: column;
            align-items: flex-start;
            gap: 16px;
        }
    }
    @media (max-width: 576px) {
        .filter-grid {
            grid-template-columns: 1fr;
        }
        .stats-row {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const bySelect = document.getElementById('id_by');
        const subBySelect = document.getElementById('id_sub_by');
        const statusSelect = document.getElementById('status');
        const prodiSelect = document.getElementById('prodi');
        const searchInput = document.getElementById('search');
        const filterForm = document.getElementById('filterForm');
        const tableBody = document.getElementById('tableBody');
        const loadingOverlay = document.getElementById('tableLoadingOverlay');
        const btnExportCsv = document.getElementById('btnExportCsv');

        // Dynamic change of sub_by when Tahun Akademik changes
        bySelect.addEventListener('change', function () {
            const idBy = this.value;
            if (!idBy) {
                subBySelect.innerHTML = '<option value="">-- Pilih Periode Semester --</option>';
                subBySelect.disabled = true;
                setEmptyInitialState();
                return;
            }

            showLoading(true);
            fetch(`{{ url('kemahasiswaan/monitoring-kipk/sub-by') }}/${idBy}`)
                .then(res => res.json())
                .then(data => {
                    showLoading(false);
                    subBySelect.innerHTML = '<option value="">-- Pilih Periode Semester --</option>';
                    if (data.sub_batch_years && data.sub_batch_years.length > 0) {
                        subBySelect.disabled = false;
                        data.sub_batch_years.forEach((sub) => {
                            const opt = document.createElement('option');
                            opt.value = sub.id;
                            opt.textContent = `Semester ${sub.name} (${sub.code || '-'})`;
                            subBySelect.appendChild(opt);
                        });
                    } else {
                        subBySelect.disabled = true;
                    }
                    // Tetap kosongkan data sampai user memilih periode semester
                    setEmptyInitialState();
                })
                .catch(err => {
                    console.error('Error fetching sub_by:', err);
                    showLoading(false);
                });
        });

        // Trigger filter saat periode berubah
        subBySelect.addEventListener('change', function () {
            if (!this.value) {
                setEmptyInitialState();
            } else {
                triggerDataRefresh();
            }
        });

        // Trigger filter otomatis saat dropdown status atau prodi berubah
        [statusSelect, prodiSelect].forEach(elem => {
            elem.addEventListener('change', function () {
                if (bySelect.value && subBySelect.value) {
                    triggerDataRefresh();
                }
            });
        });

        // Search debouncing
        let searchTimeout = null;
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (bySelect.value && subBySelect.value) {
                    triggerDataRefresh();
                }
            }, 400);
        });

        // Form submit override untuk AJAX
        filterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            if (bySelect.value && subBySelect.value) {
                triggerDataRefresh();
            } else {
                alert('Silakan pilih Tahun Akademik dan Periode Semester terlebih dahulu.');
            }
        });

        function setEmptyInitialState() {
            document.getElementById('stat_total').textContent = '-';
            document.getElementById('stat_aktif').textContent = '-';
            document.getElementById('stat_cuti').textContent = '-';
            document.getElementById('stat_ipk').textContent = '-';
            document.getElementById('stat_ips').textContent = '-';
            const subTotal = document.getElementById('stat_sub_total');
            if (subTotal) subTotal.textContent = 'Pilih Tahun & Periode';
            const subAktif = document.getElementById('stat_sub_aktif');
            if (subAktif) subAktif.textContent = 'Menunggu Filter';
            const subCuti = document.getElementById('stat_sub_cuti');
            if (subCuti) subCuti.textContent = 'Menunggu Filter';

            document.getElementById('badge_count').textContent = '0 Data';
            document.getElementById('table_meta_info').innerHTML = 'Silakan pilih <b>Tahun Akademik</b> & <b>Periode</b>';

            if (btnExportCsv) {
                btnExportCsv.href = 'javascript:void(0)';
                btnExportCsv.classList.add('btn-disabled');
                btnExportCsv.title = 'Pilih Tahun Akademik dan Periode terlebih dahulu';
            }

            tableBody.innerHTML = `
                <tr id="rowInitialState">
                    <td colspan="9" class="text-center py-5">
                        <div class="empty-state-box">
                            <i class="bi bi-calendar-event text-primary fs-1 d-block mb-3"></i>
                            <h5 class="text-dark fw-bold mb-1">Silakan Pilih Tahun Akademik & Periode</h5>
                            <p class="text-muted mb-0">Pilih <strong>Tahun Akademik</strong> dan <strong>Periode Semester</strong> pada filter di atas untuk memuat data mahasiswa KIP-K.</p>
                        </div>
                    </td>
                </tr>
            `;
        }

        function showLoading(show) {
            if (show) {
                loadingOverlay.classList.remove('d-none');
            } else {
                loadingOverlay.classList.add('d-none');
            }
        }

        function triggerDataRefresh() {
            if (!bySelect.value || !subBySelect.value) {
                setEmptyInitialState();
                return;
            }

            showLoading(true);

            const params = new URLSearchParams({
                id_by: bySelect.value || '',
                id_sub_by: subBySelect.value || '',
                status: statusSelect.value || '',
                prodi: prodiSelect.value || '',
                search: searchInput.value || ''
            });

            // Update URL Export CSV
            if (btnExportCsv) {
                btnExportCsv.href = `{{ route('kemahasiswaan.monitoring-kipk.export.csv') }}?${params.toString()}`;
                btnExportCsv.classList.remove('btn-disabled');
                btnExportCsv.title = 'Unduh data dalam format CSV';
            }

            fetch(`{{ route('kemahasiswaan.monitoring-kipk.data') }}?${params.toString()}`)
                .then(res => res.json())
                .then(res => {
                    showLoading(false);
                    if (!res.success) {
                        alert(res.message || 'Gagal memuat data dari SIAKAD.');
                        return;
                    }

                    // Update Statistics Cards
                    const stats = res.statistics || {};
                    document.getElementById('stat_total').textContent = (stats.total_mahasiswa || 0).toLocaleString();
                    document.getElementById('stat_aktif').textContent = (stats.aktif_count || 0).toLocaleString();
                    document.getElementById('stat_cuti').textContent = ((stats.cuti_count || 0) + (stats.lainnya_count || 0)).toLocaleString();
                    document.getElementById('stat_ipk').textContent = parseFloat(stats.avg_ipk || 0).toFixed(2);
                    document.getElementById('stat_ips').textContent = parseFloat(stats.avg_ips || 0).toFixed(2);

                    document.getElementById('badge_count').textContent = `${res.total || 0} Data`;
                    document.getElementById('table_meta_info').innerHTML = `Tahun: <b>${stats.active_batch_year || '-'}</b> • Periode: <b>${stats.active_sub_by || '-'}</b>`;

                    // Render Table Rows
                    renderTable(res.data || []);
                })
                .catch(err => {
                    console.error('Refresh error:', err);
                    showLoading(false);
                });
        }

        function renderTable(data) {
            tableBody.innerHTML = '';

            if (data.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <div class="empty-state-box">
                                <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                                <h5 class="text-dark mb-1">Tidak ada data mahasiswa KIP-K ditemukan</h5>
                                <p class="text-muted mb-0">Coba ubah filter Tahun Akademik, Periode, Status, atau kata kunci pencarian.</p>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }

            data.forEach((item, index) => {
                const statusRaw = (item.status || 'AKTIF').toUpperCase().trim();
                const ipkVal = parseFloat(item.ipk || 0);
                const ipsVal = parseFloat(item.ips || 0);

                let ipkClass = 'warning';
                if (ipkVal >= 3.5) ipkClass = 'high';
                else if (ipkVal >= 3.0) ipkClass = 'good';

                let statusBadge = '';
                if (statusRaw === 'AKTIF') {
                    statusBadge = `<span class="status-pill status-aktif"><i class="bi bi-check-circle-fill"></i> AKTIF</span>`;
                } else if (statusRaw === 'CUTI') {
                    statusBadge = `<span class="status-pill status-cuti"><i class="bi bi-pause-circle-fill"></i> CUTI</span>`;
                } else if (statusRaw.includes('MUNDUR')) {
                    statusBadge = `<span class="status-pill status-mundur"><i class="bi bi-x-circle-fill"></i> MUNDUR</span>`;
                } else {
                    statusBadge = `<span class="status-pill status-lain"><i class="bi bi-info-circle-fill"></i> ${escapeHtml(statusRaw)}</span>`;
                }

                const emailHtml = (item.email && item.email !== '-')
                    ? `<small class="text-muted d-block">${escapeHtml(item.email)}</small>`
                    : '';

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="text-center text-muted fw-semibold">${index + 1}</td>
                    <td><span class="fw-bold text-dark font-monospace">${escapeHtml(item.nim || '-')}</span></td>
                    <td>
                        <div class="student-name-box">
                            <span class="student-name">${escapeHtml(item.name || '-')}</span>
                            ${emailHtml}
                        </div>
                    </td>
                    <td><span class="badge-prodi">${escapeHtml(item.prodi || '-')}</span></td>
                    <td class="text-center"><span class="badge-semester">Sem ${item.semester || '-'}</span></td>
                    <td class="text-center"><span class="val-score">${ipsVal.toFixed(2)}</span></td>
                    <td class="text-center"><span class="val-score ipk ${ipkClass}">${ipkVal.toFixed(2)}</span></td>
                    <td class="text-center">${statusBadge}</td>
                    <td class="text-center">
                        <button type="button"
                                class="btn-detail-riwayat"
                                data-id="${item.id}"
                                data-name="${escapeHtml(item.name || '-')}"
                                data-nim="${escapeHtml(item.nim || '-')}"
                                data-prodi="${escapeHtml(item.prodi || '-')}"
                                title="Lihat Riwayat Semester Mahasiswa">
                            <i class="bi bi-clock-history"></i> Riwayat
                        </button>
                    </td>
                `;
                tableBody.appendChild(tr);
            });
        }

        // Handle Klik Tombol Riwayat Mahasiswa (Modal Popup)
        tableBody.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-detail-riwayat');
            if (!btn) return;

            const mhsId = btn.dataset.id;
            const mhsName = btn.dataset.name;
            const mhsNim = btn.dataset.nim;
            const mhsProdi = btn.dataset.prodi;

            openHistoryModal(mhsId, mhsName, mhsNim, mhsProdi);
        });

        const modalBackdrop = document.getElementById('modalRiwayatSemester');
        const btnCloseTop = document.getElementById('btnCloseHistoryModalTop');
        const btnCloseBottom = document.getElementById('btnCloseHistoryModalBottom');

        function openHistoryModal(id, name, nim, prodi) {
            document.getElementById('mhsModalName').textContent = name;
            document.getElementById('mhsModalSub').textContent = `${nim} • ${prodi}`;

            const loadingEl = document.getElementById('modalLoading');
            const contentEl = document.getElementById('modalDataContent');
            const errorEl = document.getElementById('modalError');
            const errorMsgEl = document.getElementById('modalErrorMsg');
            const historyBody = document.getElementById('tableHistoryBody');

            loadingEl.classList.remove('d-none');
            contentEl.classList.add('d-none');
            errorEl.classList.add('d-none');
            historyBody.innerHTML = '';

            // Tampilkan custom backdrop
            modalBackdrop.style.display = 'flex';
            document.body.style.overflow = 'hidden';

            fetch(`{{ url('kemahasiswaan/monitoring-kipk/student-history') }}/${id}`)
                .then(res => res.json())
                .then(res => {
                    loadingEl.classList.add('d-none');
                    if (!res.success) {
                        errorMsgEl.textContent = res.message || 'Gagal memuat riwayat nilai.';
                        errorEl.classList.remove('d-none');
                        return;
                    }

                    const st = res.student || {};
                    const history = res.history || [];

                    document.getElementById('metricIpk').textContent = parseFloat(st.latest_ipk || 0).toFixed(2);
                    document.getElementById('metricSks').textContent = (st.total_sks || 0) + ' SKS';
                    document.getElementById('metricSem').textContent = 'Sem ' + (st.semester_aktif || '-');
                    document.getElementById('metricStatus').textContent = 'Status: ' + (st.status || 'AKTIF');

                    const isKipkPassed = parseFloat(st.latest_ipk || 0) >= 3.00;
                    document.getElementById('metricKipkStatus').innerHTML = isKipkPassed
                        ? '<span class="badge bg-success-subtle text-success fw-bold px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i> Memenuhi (≥ 3.00)</span>'
                        : '<span class="badge bg-warning-subtle text-warning fw-bold px-2 py-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Perlu Pembinaan (< 3.00)</span>';

                    if (history.length === 0) {
                        historyBody.innerHTML = `
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox d-block fs-3 mb-1"></i>
                                    Belum ada data nilai semester yang tersimpan di SIAKAD.
                                </td>
                            </tr>
                        `;
                    } else {
                        history.forEach(row => {
                            const isOngoing = !!row.is_ongoing;
                            const ipsVal = row.ips !== null && row.ips !== undefined ? parseFloat(row.ips) : null;
                            const ipkVal = row.ipk !== null && row.ipk !== undefined ? parseFloat(row.ipk) : null;

                            let semDisplay = `<span class="fw-bold text-dark">Semester ${row.semester}</span>`;
                            let ipsDisplay = ipsVal !== null ? ipsVal.toFixed(2) : '<span class="text-muted fw-bold">-</span>';
                            let ipkDisplay = '-';
                            let trendHtml = '<span class="trend-pill trend-same"><i class="bi bi-dash"></i> Tetap</span>';
                            let evalBadge = '';

                            if (isOngoing) {
                                semDisplay = `
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size:10px;">Aktif</span>
                                        <span class="fw-bold text-dark">Semester ${row.semester}</span>
                                    </div>
                                `;
                                ipsDisplay = '<span class="text-muted fw-bold">-</span><small class="text-muted d-block" style="font-size:10px;">(berjalan)</small>';
                                if (ipkVal !== null) {
                                    ipkDisplay = `<span class="val-score ipk text-muted">${ipkVal.toFixed(2)}*</span><small class="text-muted d-block" style="font-size:10px;">(sementara)</small>`;
                                }
                                trendHtml = '<span class="trend-pill trend-same"><i class="bi bi-clock"></i> Berjalan</span>';
                                evalBadge = '<span class="badge bg-info-subtle text-info fw-bold px-2 py-1"><i class="bi bi-hourglass-split me-1"></i> Sedang Berjalan</span>';
                            } else {
                                if (row.trend) {
                                    if (row.trend.direction === 'up') {
                                        trendHtml = `<span class="trend-pill trend-up"><i class="bi bi-arrow-up-right"></i> +${row.trend.diff.toFixed(2)}</span>`;
                                    } else if (row.trend.direction === 'down') {
                                        trendHtml = `<span class="trend-pill trend-down"><i class="bi bi-arrow-down-right"></i> ${row.trend.diff.toFixed(2)}</span>`;
                                    }
                                }

                                evalBadge = (ipkVal !== null && ipkVal >= 3.00)
                                    ? '<span class="badge bg-success-subtle text-success px-2 py-1"><i class="bi bi-check-circle me-1"></i> Standar Terpenuhi</span>'
                                    : '<span class="badge bg-warning-subtle text-warning px-2 py-1"><i class="bi bi-exclamation-circle me-1"></i> Di Bawah Standar</span>';

                                let ipkColorClass = 'text-warning';
                                if (ipkVal !== null) {
                                    if (ipkVal >= 3.50) ipkColorClass = 'text-success fw-bold';
                                    else if (ipkVal >= 3.00) ipkColorClass = 'text-primary fw-bold';
                                    ipkDisplay = `<span class="val-score ipk ${ipkColorClass}">${ipkVal.toFixed(2)}</span>`;
                                }
                            }

                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td class="text-center">${semDisplay}</td>
                                <td class="text-center">${row.sks_semester || 0} SKS</td>
                                <td class="text-center text-muted">${row.sks_kumulatif || 0} SKS</td>
                                <td class="text-center fw-semibold">${ipsDisplay}</td>
                                <td class="text-center">${ipkDisplay}</td>
                                <td class="text-center">${trendHtml}</td>
                                <td class="text-center">${evalBadge}</td>
                            `;
                            historyBody.appendChild(tr);
                        });
                    }

                    contentEl.classList.remove('d-none');
                })
                .catch(err => {
                    console.error('Error fetching history:', err);
                    loadingEl.classList.add('d-none');
                    errorMsgEl.textContent = 'Terjadi kesalahan saat menghubungi server SIAKAD.';
                    errorEl.classList.remove('d-none');
                });
        }

        function closeHistoryModal() {
            modalBackdrop.style.display = 'none';
            document.body.style.overflow = '';
        }

        if (btnCloseTop) btnCloseTop.addEventListener('click', closeHistoryModal);
        if (btnCloseBottom) btnCloseBottom.addEventListener('click', closeHistoryModal);

        modalBackdrop.addEventListener('click', function (e) {
            if (e.target === modalBackdrop) {
                closeHistoryModal();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modalBackdrop.style.display === 'flex') {
                closeHistoryModal();
            }
        });

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    });
</script>
@endpush
