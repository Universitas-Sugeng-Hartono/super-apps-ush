@extends('admin.layouts.super-app')

@section('content')
@php
$menus = [
[
'step' => '01',
'title' => 'Input Data Akademik',
'description' => 'Konfigurasi data institusi, program studi, dan informasi akademik dasar.',
'icon' => 'bi bi-journal-text',
'route' => 'admin.skpi.input-data-akademi.index',
'badge' => 'Setup',
'color' => '#2196F3',
'gradient' => 'linear-gradient(135deg, #2196F3, #64B5F6)'
],
[
'step' => '02',
'title' => 'Verifikasi Data',
'description' => 'Validasi prestasi dan aktivitas mahasiswa sebelum masuk ke draf SKPI.',
'icon' => 'bi bi-patch-check',
'route' => 'admin.skpi.verifikasi-data.index',
'badge' => 'Approval',
'color' => '#FF9800',
'gradient' => 'linear-gradient(135deg, #FF9800, #FFB347)'
],
[
'step' => '03',
'title' => 'Daftar SKPI',
'description' => 'Kelola antrean pengajuan pendaftaran SKPI dan status verifikasi akhir.',
'icon' => 'bi bi-people',
'route' => 'admin.skpi.daftar-skpi.index',
'badge' => 'Queue',
'color' => '#4CAF50',
'gradient' => 'linear-gradient(135deg, #4CAF50, #81C784)'
],
[
'step' => '04',
'title' => 'Generate SKPI',
'description' => 'Proses akhir pembuatan dokumen Word/PDF dan penomoran resmi.',
'icon' => 'bi bi-file-earmark-pdf',
'route' => 'admin.skpi.generate-skpi.index',
'badge' => 'Final',
'color' => '#E91E63',
'gradient' => 'linear-gradient(135deg, #E91E63, #F06292)'
],
];
@endphp

<div class="skpi-dashboard-container">
    {{-- Header Section --}}
    <div class="welcome-banner">
        <div class="banner-content">
            <div class="text-side">
                <h1>Manajemen SKPI</h1>
                <p>Kelola seluruh tahapan penerbitan Surat Keterangan Pendamping Ijazah secara terstruktur.</p>
                <div class="banner-badges">
                    <span class="b-badge"><i class="bi bi-shield-check"></i> Verified Process</span>
                    <span class="b-badge"><i class="bi bi-lightning-charge"></i> Efficient Workflow</span>
                </div>
            </div>
            <div class="icon-side">
                <i class="bi bi-mortboard"></i>
            </div>
        </div>
        <div class="banner-pattern"></div>
    </div>

    {{-- Workflow Section --}}
    <div class="workflow-section">
        <div class="section-title">
            <div class="title-line"></div>
            <h4>Alur Penerbitan SKPI</h4>
            <div class="title-line"></div>
        </div>

        <div class="workflow-grid">
            @foreach($menus as $menu)
            <div class="workflow-item-wrapper">
                <a href="{{ route($menu['route']) }}" class="workflow-card">
                    <div class="card-step">{{ $menu['step'] }}</div>
                    <div class="card-icon" style="background: {{ $menu['gradient'] }}">
                        <i class="{{ $menu['icon'] }}"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-badge" style="color: {{ $menu['color'] }}; background: {{ $menu['color'] }}15">
                            {{ $menu['badge'] }}
                        </span>
                        <h5>{{ $menu['title'] }}</h5>
                        <p>{{ $menu['description'] }}</p>
                    </div>
                    <div class="card-arrow">
                        <i class="bi bi-arrow-right-short"></i>
                    </div>
                </a>
                @if(!$loop->last)
                <div class="workflow-connector desktop-only">
                    <i class="bi bi-chevron-right"></i>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>


</div>

@endsection

@push('css')
<style>
    :root {
        --skpi-primary: #29375d;
        --skpi-accent: #FF9800;
        --skpi-bg: #F8FAFC;
        --skpi-card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
    }

    .skpi-dashboard-container {
        max-width: 1200px;
        margin: 0 auto;
        padding-bottom: 40px;
    }

    /* Welcome Banner */
    .welcome-banner {
        position: relative;
        background: var(--skpi-primary);
        border-radius: 24px;
        padding: 45px 50px;
        color: white;
        overflow: hidden;
        margin-bottom: 40px;
        box-shadow: 0 20px 40px -10px rgba(41, 55, 93, 0.3);
    }

    .banner-content {
        position: relative;
        z-index: 2;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .text-side h1 {
        font-size: 36px;
        font-weight: 800;
        margin-bottom: 12px;
        letter-spacing: -0.5px;
    }

    .text-side p {
        font-size: 17px;
        opacity: 0.85;
        max-width: 500px;
        line-height: 1.6;
        margin-bottom: 25px;
    }

    .banner-badges {
        display: flex;
        gap: 12px;
    }

    .b-badge {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 6px 16px;
        border-radius: 99px;
        font-size: 13px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .icon-side {
        font-size: 120px;
        opacity: 0.15;
        transform: rotate(-15deg);
    }

    .banner-pattern {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        background-image: radial-gradient(rgba(255, 255, 255, 0.1) 1px, transparent 1px);
        background-size: 30px 30px;
        opacity: 0.5;
    }

    /* Workflow Section */
    .workflow-section {
        margin-bottom: 40px;
    }

    .section-title {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 35px;
    }

    .title-line {
        flex: 1;
        height: 1px;
        background: #E2E8F0;
    }

    .section-title h4 {
        margin: 0;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: #94A3B8;
        font-weight: 700;
    }

    .workflow-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
    }

    .workflow-item-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .workflow-card {
        flex: 1;
        background: white;
        border-radius: 20px;
        padding: 30px 24px;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid #F1F5F9;
        display: flex;
        flex-direction: column;
        position: relative;
        height: 100%;
        box-shadow: var(--skpi-card-shadow);
    }

    .workflow-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
        border-color: var(--skpi-accent);
    }

    .card-step {
        position: absolute;
        top: 20px;
        right: 20px;
        font-size: 40px;
        font-weight: 900;
        color: #F1F5F9;
        line-height: 1;
        z-index: 0;
    }

    .card-icon {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        color: white;
        margin-bottom: 24px;
        position: relative;
        z-index: 1;
        box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.2);
    }

    .card-content {
        position: relative;
        z-index: 1;
    }

    .card-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 99px;
        font-size: 11px;
        font-weight: 700;
        margin-bottom: 12px;
        text-transform: uppercase;
    }

    .card-content h5 {
        color: var(--skpi-primary);
        font-size: 19px;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .card-content p {
        color: #64748B;
        font-size: 14px;
        line-height: 1.6;
        margin-bottom: 0;
    }

    .card-arrow {
        margin-top: 25px;
        font-size: 24px;
        color: #CBD5E1;
        transition: transform 0.3s ease;
    }

    .workflow-card:hover .card-arrow {
        color: var(--skpi-accent);
        transform: translateX(5px);
    }

    .workflow-connector {
        font-size: 24px;
        color: #E2E8F0;
        margin: 0 -10px;
        z-index: 2;
    }

    /* Footer Actions */
    .footer-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: white;
        padding: 24px 32px;
        border-radius: 20px;
        border: 1px solid #F1F5F9;
        box-shadow: var(--skpi-card-shadow);
    }

    .footer-info {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #64748B;
        font-size: 14px;
        font-weight: 500;
    }

    .footer-info i {
        font-size: 18px;
        color: #94A3B8;
    }

    .btn-bulk-download {
        background: linear-gradient(135deg, #D97706, #F59E0B);
        color: white;
        border: none;
        padding: 14px 28px;
        border-radius: 14px;
        font-weight: 700;
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 10px 20px -5px rgba(217, 119, 6, 0.3);
    }

    .btn-bulk-download:hover {
        transform: scale(1.03);
        box-shadow: 0 15px 30px -5px rgba(217, 119, 6, 0.4);
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .workflow-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
        }

        .desktop-only {
            display: none;
        }
    }

    @media (max-width: 640px) {
        .welcome-banner {
            padding: 30px 25px;
        }

        .text-side h1 {
            font-size: 28px;
        }

        .workflow-grid {
            grid-template-columns: 1fr;
        }

        .footer-actions {
            flex-direction: column;
            gap: 20px;
            text-align: center;
        }
    }
</style>
@endpush