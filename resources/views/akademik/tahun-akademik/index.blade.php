@extends('admin.layouts.super-app')

@section('content')
<div class="content-card">
    {{-- Page Header --}}
    <div class="card-header">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                <h3 class="mb-0">Tahun Akademik &amp; Semester</h3>
                <span class="badge-api-status">
                    Terhubung ke SIAKAD REST API
                </span>
            </div>
            <p class="text-muted mb-0" style="font-size: 13.5px;">Informasi sinkronisasi periode perkuliahan, tahun akademik, dan status semester aktif dari sistem SIAKAD USH.</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('akademik.dashboard') }}" class="btn-clean-secondary">
                Kembali ke Dashboard
            </a>
            <form action="{{ route('akademik.tahun-akademik.sync') }}" method="POST" class="d-inline m-0 p-0">
                @csrf
                <button type="submit" class="btn-clean-primary" title="Tarik ulang data periode akademik terbaru dari SIAKAD REST API">
                    Sinkronkan dari SIAKAD
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="clean-alert-success mt-3">
            {{ session('success') }}
        </div>
    @endif

    {{-- Highlight Card Periode Aktif --}}
    <div class="period-clean-banner mt-4">
        <div class="banner-top-label">PERIODE PERKULIAHAN BERJALAN</div>
        <h2>Tahun Akademik {{ $activePeriod['tahun_akademik'] ?? '-' }}</h2>
        <div class="banner-meta-grid">
            <div class="meta-item">
                <span class="meta-label">Semester</span>
                <span class="meta-value">{{ $activePeriod['semester'] ?? '-' }}</span>
            </div>
            <div class="meta-divider"></div>
            <div class="meta-item">
                <span class="meta-label">Kode Semester</span>
                <span class="meta-value">{{ $activePeriod['code'] ?? '-' }}</span>
            </div>
            <div class="meta-divider"></div>
            <div class="meta-item">
                <span class="meta-label">Status SIAKAD</span>
                <span class="meta-value text-success-accent">Aktif (Sedang Berjalan)</span>
            </div>
            <div class="meta-divider"></div>
            <div class="meta-item">
                <span class="meta-label">Protokol</span>
                <span class="meta-value">REST API Gateway</span>
            </div>
        </div>
    </div>

    {{-- Quick KPI Stats (Clear & Tanpa Icons) --}}
    <div class="clean-stats-row mt-4">
        <div class="clean-stat-box">
            <span class="stat-category">TAHUN AKADEMIK</span>
            <div class="stat-main-number">{{ $activePeriod['tahun_akademik'] ?? '-' }}</div>
            <span class="stat-footer-text">Periode Aktif Saat Ini</span>
        </div>
        <div class="clean-stat-box">
            <span class="stat-category">SEMESTER</span>
            <div class="stat-main-number">{{ $activePeriod['semester'] ?? '-' }}</div>
            <span class="stat-footer-text">Semester Berjalan</span>
        </div>
        <div class="clean-stat-box">
            <span class="stat-category">TOTAL PERIODE</span>
            <div class="stat-main-number">{{ count($academicPeriods ?? []) }}</div>
            <span class="stat-footer-text">Tercatat di Basis Data</span>
        </div>
        <div class="clean-stat-box">
            <span class="stat-category">INTEGRASI</span>
            <div class="stat-main-number">REST API</div>
            <span class="stat-footer-text">X-SIAKAD-API-KEY</span>
        </div>
    </div>

    {{-- Table Riwayat Periode Akademik (Clear & Tanpa Icons) --}}
    <div class="clean-table-card mt-4">
        <div class="clean-table-header">
            <h5>Daftar Riwayat Periode Akademik SIAKAD</h5>
            <p class="text-muted mb-0" style="font-size: 13px;">Daftar lengkap tahun akademik dan semester yang tercatat resmi pada sistem SIAKAD USH.</p>
        </div>
        <div class="table-responsive">
            <table class="table clean-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px;" class="text-center">No</th>
                        <th>Tahun Akademik</th>
                        <th>Semester</th>
                        <th>Kode Periode</th>
                        <th>Status di SIAKAD</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($academicPeriods ?? [] as $index => $period)
                        <tr class="{{ !empty($period['is_active']) ? 'row-active-highlight' : '' }}">
                            <td class="text-center text-muted" style="font-weight: 600;">{{ $index + 1 }}</td>
                            <td>
                                <span class="ta-name-text">{{ $period['tahun_akademik'] ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="badge-semester {{ ($period['semester'] ?? '') === 'Ganjil' ? 'sem-ganjil' : (($period['semester'] ?? '') === 'Genap' ? 'sem-genap' : 'sem-default') }}">
                                    {{ $period['semester'] ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <span class="code-period-box">{{ $period['code'] ?? '-' }}</span>
                            </td>
                            <td>
                                @if(!empty($period['is_active']))
                                    <span class="badge-status-active">
                                        Sedang Aktif
                                    </span>
                                @else
                                    <span class="badge-status-archive">
                                        Arsip
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                Tidak ada data periode akademik yang ditemukan dari SIAKAD.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
    /* Buttons */
    .btn-clean-secondary {
        display: inline-flex;
        align-items: center;
        background: #F8FAFC;
        border: 1px solid #CBD5E1;
        color: #334155;
        padding: 8px 18px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .btn-clean-secondary:hover {
        background: #F1F5F9;
        border-color: #94A3B8;
        color: #0F172A;
    }
    .btn-clean-primary {
        display: inline-flex;
        align-items: center;
        background: #0284C7;
        border: 1px solid #0284C7;
        color: #ffffff;
        padding: 8px 18px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-clean-primary:hover {
        background: #0369A1;
        border-color: #0369A1;
        color: #ffffff;
    }

    /* Badge API Status */
    .badge-api-status {
        display: inline-block;
        background: #ECFDF5;
        color: #065F46;
        border: 1px solid #A7F3D0;
        font-size: 12px;
        font-weight: 600;
        border-radius: 6px;
        padding: 3px 10px;
    }

    /* Alert */
    .clean-alert-success {
        background: #F0FDF4;
        border: 1px solid #BBF7D0;
        border-left: 4px solid #16A34A;
        color: #166534;
        padding: 12px 18px;
        border-radius: 8px;
        font-size: 13.5px;
        font-weight: 500;
    }

    /* Highlight Card Periode Aktif */
    .period-clean-banner {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-left: 5px solid #0284C7;
        border-radius: 14px;
        padding: 24px 28px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    }
    .period-clean-banner .banner-top-label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.8px;
        color: #0284C7;
        margin-bottom: 6px;
    }
    .period-clean-banner h2 {
        font-size: 26px;
        font-weight: 800;
        color: #0F172A;
        margin: 0 0 16px 0;
        line-height: 1.2;
    }
    .period-clean-banner .banner-meta-grid {
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
        padding-top: 14px;
        border-top: 1px solid #F1F5F9;
    }
    .period-clean-banner .meta-item {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .period-clean-banner .meta-label {
        font-size: 11.5px;
        color: #64748B;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .period-clean-banner .meta-value {
        font-size: 14px;
        color: #1E293B;
        font-weight: 700;
    }
    .period-clean-banner .meta-divider {
        width: 1px;
        height: 28px;
        background: #E2E8F0;
    }
    .text-success-accent {
        color: #059669 !important;
    }

    /* Clean Stats Row (Tanpa Icons) */
    .clean-stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
    }
    .clean-stat-box {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 18px 20px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.02);
    }
    .clean-stat-box .stat-category {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: #64748B;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }
    .clean-stat-box .stat-main-number {
        font-size: 22px;
        font-weight: 800;
        color: #0F172A;
        line-height: 1.2;
        margin-bottom: 4px;
    }
    .clean-stat-box .stat-footer-text {
        font-size: 12px;
        color: #94A3B8;
        font-weight: 500;
    }

    /* Table Card */
    .clean-table-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.02);
    }
    .clean-table-header {
        padding: 18px 24px;
        border-bottom: 1px solid #E2E8F0;
        background: #FAFAFA;
    }
    .clean-table-header h5 {
        font-size: 16px;
        font-weight: 700;
        color: #0F172A;
        margin: 0 0 2px 0;
    }
    .clean-table {
        margin: 0;
        width: 100%;
        border-collapse: collapse;
    }
    .clean-table thead th {
        background: #F8FAFC;
        color: #475569;
        font-size: 12.5px;
        font-weight: 700;
        padding: 12px 18px;
        border-bottom: 1px solid #E2E8F0;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .clean-table tbody td {
        padding: 14px 18px;
        font-size: 13.5px;
        color: #334155;
        border-bottom: 1px solid #F1F5F9;
        vertical-align: middle;
    }
    .clean-table tbody tr:last-child td {
        border-bottom: none;
    }
    .clean-table tbody tr:hover {
        background: #F8FAFC;
    }
    .row-active-highlight {
        background: #F0FDF4 !important;
    }
    .row-active-highlight td {
        border-bottom-color: #DCFCE7 !important;
    }
    .ta-name-text {
        font-weight: 700;
        color: #0F172A;
    }

    /* Semester Badges */
    .badge-semester {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
    }
    .sem-ganjil {
        background: #EFF6FF;
        color: #1D4ED8;
        border: 1px solid #BFDBFE;
    }
    .sem-genap {
        background: #F0FDF4;
        color: #15803D;
        border: 1px solid #BBF7D0;
    }
    .sem-default {
        background: #F1F5F9;
        color: #475569;
        border: 1px solid #CBD5E1;
    }

    .code-period-box {
        display: inline-block;
        background: #F1F5F9;
        color: #475569;
        font-family: monospace;
        font-size: 12px;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 4px;
        border: 1px solid #E2E8F0;
    }

    /* Status Badges */
    .badge-status-active {
        display: inline-block;
        background: #ECFDF5;
        color: #065F46;
        border: 1px solid #A7F3D0;
        font-size: 11.5px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 6px;
    }
    .badge-status-archive {
        display: inline-block;
        background: #F8FAFC;
        color: #64748B;
        border: 1px solid #E2E8F0;
        font-size: 11.5px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 6px;
    }

    @media (max-width: 992px) {
        .clean-stats-row {
            grid-template-columns: repeat(2, 1fr);
        }
        .period-clean-banner .meta-divider {
            display: none;
        }
    }
    @media (max-width: 576px) {
        .clean-stats-row {
            grid-template-columns: 1fr;
        }
        .period-clean-banner {
            padding: 18px 20px;
        }
    }
</style>
@endpush
