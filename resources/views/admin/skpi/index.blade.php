@extends('admin.layouts.super-app')

@section('content')
@php

$menus = [
[
'title' => 'Daftar SKPI',
'description' => 'Lihat daftar mahasiswa/alumni yang akan diproses SKPI beserta statusnya.',
'icon' => 'bi bi-card-checklist',
'route' => 'admin.skpi.daftar-skpi.index',
'badge' => 'Daftar',
'badge_class' => 'active',
],
[
'title' => 'Input Data Akademik',
'description' => 'Lengkapi data akademik inti seperti nomor ijazah, gelar, dan masa studi.',
'icon' => 'bi bi-journal-text',
'route' => 'admin.skpi.input-data-akademi.index',
'badge' => 'Data',
'badge_class' => 'info',
],
[
'title' => 'Verifikasi Data',
'description' => 'Review dan approve data prestasi yang diajukan mahasiswa sebelum masuk SKPI.',
'icon' => 'bi bi-patch-check',
'route' => 'admin.skpi.verifikasi-data.index',
'badge' => 'Approval',
'badge_class' => 'warning',
],
[
'title' => 'Generate SKPI',
'description' => 'Preview, generate, dan siapkan dokumen SKPI final untuk dicetak.',
'icon' => 'bi bi-file-earmark-pdf',
'route' => 'admin.skpi.generate-skpi.index',
'badge' => 'Output',
'badge_class' => 'active',
],
];


@endphp


<div class="pending-section">
    <h4><i class="bi bi-journal-richtext"></i> Tahapan Utama SKPI</h4>

    <div class="pending-grid">
        @foreach($menus as $menu)
        <a href="{{ route($menu['route']) }}" class="pending-card skpi-card">
            <div class="skpi-card-head">
                <div class="skpi-icon">
                    <i class="{{ $menu['icon'] }}"></i>
                </div>
                <span class="status-badge {{ $menu['badge_class'] }}">{{ $menu['badge'] }}</span>
            </div>
            <h5>{{ $menu['title'] }}</h5>
            <p>{{ $menu['description'] }}</p>
        </a>
        @endforeach
    </div>
</div>

<!-- <div class="content-card">
        <div class="card-header">
            <div>
                <h3>Catatan Alur SKPI</h3>
                <p class="card-subtitle">Fokus utama SKPI ada pada verifikasi data dan approval akhir oleh superuser.</p>
            </div>
        </div>

    </div> -->
@endsection

@push('css')
<style>
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: linear-gradient(135deg, #ffffff, #f9fafb);
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05);
        display: flex;
        align-items: center;
        gap: 15px;
        transition: all 0.3s ease;
        border: 1px solid #f1f1f1;
    }

    .stat-card:hover {
        transform: translateY(-6px) scale(1.02);
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.08);
    }

    .stat-info h3 {
        font-size: 30px;
        font-weight: 800;
        color: #111;
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        color: white;
        flex-shrink: 0;
    }

    .stat-info h5 {
        font-size: 13px;
        color: #666;
        margin: 0 0 5px;
    }

    .stat-info h3 {
        font-size: 28px;
        font-weight: 700;
        margin: 0;
        color: #333;
        word-break: break-word;
    }

    .pending-section {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: var(--shadow);
        margin-bottom: 30px;
    }

    .pending-section h4 {
        font-size: 18px;
        font-weight: 600;
        margin: 0 0 8px;
        color: #FF9800;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section-subtitle {
        margin: 0 0 20px;
        font-size: 14px;
        color: #777;
    }

    .pending-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 15px;
    }

    .pending-card {
        background: #FFF3E0;
        border: 2px solid #FFB347;
        border-radius: 12px;
        padding: 20px;
        text-decoration: none;
        transition: all 0.3s;
    }

    .pending-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 20px rgba(255, 152, 0, 0.2);
    }

    .content-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: var(--shadow);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #F5F5F5;
    }

    .card-header h3 {
        font-size: 20px;
        font-weight: 600;
        margin: 0;
        color: #333;
    }

    .card-subtitle {
        margin: 6px 0 0;
        font-size: 13px;
        color: #777;
    }

    .skpi-card {
        display: block;
    }

    .skpi-card-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
    }

    .skpi-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: linear-gradient(135deg, var(--primary-orange), #FFB347);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        box-shadow: 0 8px 20px rgba(255, 152, 0, 0.2);
    }

    .skpi-card h5 {
        font-size: 18px;
        font-weight: 700;
        color: #333;
        margin: 0 0 10px;
    }

    .skpi-card p {
        font-size: 14px;
        color: #666;
        margin: 0;
        line-height: 1.6;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 5px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
    }

    .status-badge.active {
        background: #E8F5E9;
        color: #2E7D32;
    }

    .status-badge.info {
        background: #E3F2FD;
        color: #1565C0;
    }

    .status-badge.warning {
        background: #FFF3E0;
        color: #E65100;
    }

    .notes-list {
        display: grid;
        gap: 14px;
    }

    .note-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 16px 18px;
        border-radius: 12px;
        background: #FAFAFA;
        border: 1px solid #F0F0F0;
        color: #555;
        line-height: 1.6;
    }

    .note-item i {
        color: var(--primary-orange);
        font-size: 18px;
        margin-top: 2px;
        flex-shrink: 0;
    }

    @media (max-width: 768px) {
        .card-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }
    }
</style>
@endpush