@extends('admin.layouts.super-app')

@section('content')
<div class="tahun-akademik-container">
    {{-- Top Header Card --}}
    <div class="top-header-card">
        <div class="header-main-row">
            <div class="header-left">
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                    <h3 class="page-title mb-0">Tahun Akademik &amp; Semester</h3>
                    
                </div>
                <p class="page-desc">Daftar seluruh tahun akademik dan semester yang tercatat resmi pada sistem SIAKAD USH.</p>
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

        {{-- Active Period Summary Card inside Header --}}
        @if(!empty($activePeriod))
            <div class="active-period-card-bar">
                <div class="active-bar-content">
                    <div class="active-bar-left">
                        <span class="active-pill-label">PERIODE AKTIF</span>
                        <h4 class="active-ta-heading">Tahun Akademik {{ $activePeriod['tahun_akademik'] ?? '-' }} &bull; Semester {{ $activePeriod['semester'] ?? '-' }}</h4>
                    </div>
                    
                </div>
            </div>
        @endif
    </div>

    @if(session('success'))
        <div class="clean-alert-success mb-3">
            {{ session('success') }}
        </div>
    @endif

    {{-- Tabel Periode Akademik Card --}}
    <div class="clean-table-card">
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
    .tahun-akademik-container {
        max-width: 1240px;
        margin: 0 auto;
        padding-bottom: 40px;
    }

    /* Top Header Card */
    .top-header-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 24px 28px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        margin-bottom: 22px;
    }

    .header-main-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }

    .page-title {
        font-size: 22px;
        font-weight: 800;
        color: #0F172A;
        letter-spacing: -0.3px;
    }

    .page-desc {
        font-size: 13.5px;
        color: #64748B;
        margin-top: 4px;
        margin-bottom: 0;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    /* Buttons */
    .btn-clean-secondary {
        display: inline-flex;
        align-items: center;
        background: #F8FAFC;
        border: 1px solid #CBD5E1;
        color: #334155;
        padding: 9px 18px;
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
        padding: 9px 18px;
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
        font-weight: 700;
        border-radius: 6px;
        padding: 3px 10px;
    }

    /* Active Period Card Bar */
    .active-period-card-bar {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-left: 4px solid #0284C7;
        border-radius: 10px;
        padding: 14px 18px;
        margin-top: 18px;
    }
    .active-bar-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }
    .active-bar-left {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .active-pill-label {
        font-size: 11px;
        font-weight: 700;
        color: #0284C7;
        background: #E0F2FE;
        border: 1px solid #BAE6FD;
        padding: 3px 8px;
        border-radius: 6px;
        letter-spacing: 0.5px;
    }
    .active-ta-heading {
        font-size: 15.5px;
        font-weight: 700;
        color: #0F172A;
        margin: 0;
    }
    .active-bar-right {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .code-box {
        display: inline-block;
        background: #FFFFFF;
        color: #475569;
        font-family: monospace;
        font-size: 12px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 6px;
        border: 1px solid #CBD5E1;
    }
    .status-live-pill {
        display: inline-block;
        background: #ECFDF5;
        color: #065F46;
        border: 1px solid #A7F3D0;
        font-size: 12px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 6px;
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

    /* Table Card */
    .clean-table-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
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
        padding: 14px 18px;
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

    @media (max-width: 768px) {
        .top-header-card {
            padding: 20px;
        }
        .active-bar-content {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>
@endpush
