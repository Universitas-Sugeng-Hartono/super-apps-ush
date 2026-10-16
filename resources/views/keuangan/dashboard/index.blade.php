@extends('admin.layouts.super-app')

@section('content')
<div class="dashboard-keuangan-container">
    {{-- Header Banner --}}
    <div class="welcome-banner">
        <div class="banner-content">
            <div class="text-side">
                <span class="role-badge-tag"><i class="bi bi-cash-coin"></i> Biro Keuangan</span>
                <h1>Selamat Datang, {{ explode(' ', auth()->user()->name ?? 'Staf')[0] }}! 👋</h1>
                <p>Verifikasi bukti pembayaran wisuda, validasi naskah publikasi, dan rekonsiliasi administrasi kelulusan Universitas Sugeng Hartono.</p>
                <div class="banner-badges">
                    <span class="b-badge"><i class="bi bi-shield-check"></i> Portal Institusi</span>
                    <span class="b-badge"><i class="bi bi-wallet2"></i> Pembayaran & Wisuda</span>
                </div>
            </div>
            <div class="icon-side">
                <i class="bi bi-cash-stack"></i>
            </div>
        </div>
        <div class="banner-pattern"></div>
    </div>

    {{-- KPI Stat Cards --}}
    <div class="stats-grid">
        <div class="stat-card pending">
            <div class="stat-icon"><i class="bi bi-clock-history"></i></div>
            <div class="stat-info">
                <h3>{{ $pendingCount ?? 0 }}</h3>
                <p>Menunggu Konfirmasi</p>
                <span class="stat-tag pending">Butuh Validasi</span>
            </div>
        </div>
        <div class="stat-card approved">
            <div class="stat-icon"><i class="bi bi-check-all"></i></div>
            <div class="stat-info">
                <h3>{{ $approvedCount ?? 0 }}</h3>
                <p>Pembayaran Lunas</p>
                <span class="stat-tag approved">Terverifikasi</span>
            </div>
        </div>
        <div class="stat-card rejected">
            <div class="stat-icon"><i class="bi bi-exclamation-octagon-fill"></i></div>
            <div class="stat-info">
                <h3>{{ $rejectedCount ?? 0 }}</h3>
                <p>Perlu Revisi / Ditolak</p>
                <span class="stat-tag rejected">Koreksi Bukti</span>
            </div>
        </div>
        <div class="stat-card total">
            <div class="stat-icon"><i class="bi bi-receipt-cutoff"></i></div>
            <div class="stat-info">
                <h3>{{ $totalCount ?? 0 }}</h3>
                <p>Total Pendaftar Wisuda</p>
                <span class="stat-tag total">Semua Status</span>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @include('keuangan.dashboard._payment_table')
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('admin/css/skpi-daftar-skpi.css') }}">
<style>
    .dashboard-keuangan-container {
        max-width: 1280px;
        margin: 0 auto;
        padding-bottom: 40px;
    }

    .keuangan-payment-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .welcome-banner {
        position: relative;
        background: linear-gradient(135deg, #1B5E20, #2E7D32);
        border-radius: 24px;
        padding: 40px 45px;
        color: white;
        overflow: hidden;
        margin-bottom: 30px;
        box-shadow: 0 15px 35px rgba(27, 94, 32, 0.25);
    }

    .role-badge-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255, 255, 255, 0.2);
        padding: 5px 14px;
        border-radius: 30px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 12px;
        backdrop-filter: blur(4px);
    }

    .text-side h1 {
        font-size: 32px;
        font-weight: 800;
        margin-bottom: 10px;
    }

    .text-side p {
        font-size: 15px;
        opacity: 0.9;
        max-width: 600px;
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .banner-badges {
        display: flex;
        gap: 10px;
    }

    .b-badge {
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.25);
        padding: 5px 14px;
        border-radius: 99px;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .icon-side {
        font-size: 110px;
        opacity: 0.15;
        position: absolute;
        right: 40px;
        top: 20px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 18px;
        padding: 22px;
        display: flex;
        align-items: center;
        gap: 16px;
        border: 1px solid #E2E8F0;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    }

    .stat-icon {
        width: 55px;
        height: 55px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
    }

    .stat-card.pending .stat-icon { background: #FFF3E0; color: #E65100; }
    .stat-card.approved .stat-icon { background: #E8F5E9; color: #2E7D32; }
    .stat-card.rejected .stat-icon { background: #FFEBEE; color: #C62828; }
    .stat-card.total .stat-icon { background: #E8F5E9; color: #1B5E20; }

    .stat-info h3 {
        font-size: 26px;
        font-weight: 800;
        margin: 0;
        color: #1E293B;
    }

    .stat-info p {
        font-size: 13px;
        color: #64748B;
        margin: 2px 0 6px;
        font-weight: 500;
    }

    .stat-tag {
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 6px;
        font-weight: 700;
    }

    .stat-tag.pending { background: #FFE0B2; color: #E65100; }
    .stat-tag.approved { background: #C8E6C9; color: #2E7D32; }
    .stat-tag.rejected { background: #FFCDD2; color: #C62828; }
    .stat-tag.total { background: #A5D6A7; color: #1B5E20; }

    .section-block {
        background: white;
        border-radius: 20px;
        padding: 26px 30px;
        margin-bottom: 25px;
        border: 1px solid #E2E8F0;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    }

    .section-header h4 {
        font-size: 18px;
        font-weight: 700;
        margin: 0 0 20px;
        color: #1E293B;
    }

    .actions-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }

    .action-card {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 20px;
        border-radius: 16px;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        text-decoration: none;
        color: inherit;
        transition: all 0.25s ease;
    }

    .action-card:hover {
        background: #F0FDF4;
        border-color: #2E7D32;
        transform: translateY(-2px);
    }

    .action-card.highlight {
        background: linear-gradient(135deg, rgba(46, 125, 50, 0.06), rgba(27, 94, 32, 0.02));
        border-color: rgba(46, 125, 50, 0.25);
    }

    .action-card .card-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: #2E7D32;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }

    .action-card .card-text h5 {
        font-size: 16px;
        font-weight: 700;
        margin: 0 0 4px;
        color: #1E293B;
    }

    .action-card .card-text p {
        font-size: 13px;
        color: #64748B;
        margin: 0;
    }

    .action-card .card-arrow {
        margin-left: auto;
        color: #94A3B8;
        font-size: 20px;
        transition: transform 0.2s ease;
    }

    .action-card:hover .card-arrow {
        color: #2E7D32;
        transform: translateX(4px);
    }

    .custom-table {
        width: 100%;
        border-collapse: collapse;
    }

    .custom-table th {
        padding: 12px 16px;
        background: #F8FAFC;
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        border-bottom: 2px solid #E2E8F0;
    }

    .custom-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #F1F5F9;
        font-size: 13px;
    }

    .badge-prodi {
        background: #E3F2FD;
        color: #1976D2;
        padding: 4px 10px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 12px;
    }

    .status-pill {
        padding: 4px 10px;
        border-radius: 99px;
        font-size: 12px;
        font-weight: 700;
    }

    .status-pill.pending { background: #FFE0B2; color: #E65100; }
    .status-pill.approved { background: #C8E6C9; color: #2E7D32; }
    .status-pill.revision { background: #FFF9C4; color: #F57F17; }
    .status-pill.rejected { background: #FFCDD2; color: #C62828; }

    .btn-detail {
        padding: 6px 14px;
        border-radius: 8px;
        background: #2E7D32;
        color: white;
        text-decoration: none;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background 0.2s ease;
    }

    .btn-detail:hover {
        background: #1B5E20;
        color: white;
    }

    .btn-link-action {
        color: #2E7D32;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
    }

    .btn-link-action:hover {
        text-decoration: underline;
    }

    @media (max-width: 992px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
        .actions-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 600px) {
        .stats-grid { grid-template-columns: 1fr; }
        .welcome-banner { padding: 30px 20px; }
        .icon-side { display: none; }
    }
</style>
@endpush
