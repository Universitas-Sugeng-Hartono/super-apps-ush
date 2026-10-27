@extends('admin.layouts.super-app')

@section('content')
<div class="dashboard-kemahasiswaan-container">
    {{-- Header Banner --}}
    <div class="welcome-banner">
        <div class="banner-content">
            <div class="text-side">
                <span class="role-badge-tag"><i class="bi bi-award-fill"></i> Biro Kemahasiswaan</span>
                <h1>Selamat Datang, {{ explode(' ', auth()->user()->name ?? 'Staf')[0] }}!</h1>
                <p>Pusat verifikasi prestasi, pembinaan aktivitas mahasiswa, dan validasi data kelulusan SKPI Universitas Sugeng Hartono.</p>
                <div class="banner-badges">
                    <span class="b-badge"><i class="bi bi-shield-check"></i> Portal Institusi</span>
                    <span class="b-badge"><i class="bi bi-mortarboard-fill"></i> Seluruh Program Studi</span>
                </div>
            </div>
            <div class="icon-side">
                
            </div>
        </div>
        <div class="banner-pattern"></div>
    </div>

    {{-- KPI Stat Cards --}}
    <div class="stats-grid">
        <div class="stat-card pending">
            <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-info">
                <h3>{{ $pendingCount ?? 0 }}</h3>
                <p>Menunggu Verifikasi</p>
                <span class="stat-tag pending">Butuh Tindakan</span>
            </div>
        </div>
        <div class="stat-card approved">
            <div class="stat-icon"><i class="bi bi-check-circle-fill"></i></div>
            <div class="stat-info">
                <h3>{{ $approvedCount ?? 0 }}</h3>
                <p>Prestasi Disetujui</p>
                <span class="stat-tag approved">Terverifikasi</span>
            </div>
        </div>
        <div class="stat-card rejected">
            <div class="stat-icon"><i class="bi bi-x-circle-fill"></i></div>
            <div class="stat-info">
                <h3>{{ $rejectedCount ?? 0 }}</h3>
                <p>Ditolak / Revisi</p>
                <span class="stat-tag rejected">Perlu Perbaikan</span>
            </div>
        </div>
        <div class="stat-card total">
            <div class="stat-icon"><i class="bi bi-trophy-fill"></i></div>
            <div class="stat-info">
                <h3>{{ $totalCount ?? 0 }}</h3>
                <p>Total Data Prestasi</p>
                <span class="stat-tag total">Semua Status</span>
            </div>
        </div>
    </div>

    {{-- Layanan & Modul Utama (Menu Dinamis dari Management Menu) --}}
    <div class="menu-section">
        <div class="section-header">
            <h3><i class="bi bi-grid-fill me-2 text-warning"></i> Layanan & Modul Utama</h3>
        </div>

        <div class="menu-grid">
            @forelse($menus ?? [] as $menu)
                @php
                    $iconColors = [
                        'bi-people-fill' => 'linear-gradient(135deg, #FF9800, #FFB347)',
                        'bi-mortarboard' => 'linear-gradient(135deg, #FF5252, #FF8A80)',
                        'bi-person-badge-fill' => 'linear-gradient(135deg, #5B9BD5, #7DB8E8)',
                        'bi-person-circle' => 'linear-gradient(135deg, #FFC107, #FFD54F)',
                        'bi-person-badge' => 'linear-gradient(135deg, #5B9BD5, #7DB8E8)',
                        'bi-book-half' => 'linear-gradient(135deg, #4CAF50, #81C784)',
                        'bi-stars' => 'linear-gradient(135deg, #9C27B0, #BA68C8)',
                        'bi-megaphone-fill' => 'linear-gradient(135deg, #1E5BB6, #73C2FB)',
                        'bi-menu-button-wide' => 'linear-gradient(135deg, #607D8B, #90A4AE)',
                        'bi-award-fill' => 'linear-gradient(135deg, #FF9800, #FFB347)',
                        'bi-award' => 'linear-gradient(135deg, #FF9800, #FFB347)',
                        'bi-patch-check-fill' => 'linear-gradient(135deg, #00897B, #26A69A)',
                        'bi-patch-check' => 'linear-gradient(135deg, #00897B, #26A69A)',
                        'bi-trophy' => 'linear-gradient(135deg, #FFD54F, #FFC107)',
                        'bi-trophy-fill' => 'linear-gradient(135deg, #FFD54F, #FFC107)',
                        'bi-building' => 'linear-gradient(135deg, #FFC107, #FFD54F)',
                        'bi-envelope-paper' => 'linear-gradient(135deg, #26A69A, #80CBC4)',
                    ];
                    $defaultColor = 'linear-gradient(135deg, #00796B, #26A69A)';
                    $iconKey = trim(str_replace('bi ', '', $menu->icon ?? ''));
                    $iconColor = $iconColors[$menu->icon] ?? $iconColors[$iconKey] ?? $iconColors['bi-' . $iconKey] ?? $defaultColor;

                    $badgeClass = match($menu->badge_color) {
                        'active' => 'active',
                        'warning' => 'warning',
                        'info' => 'info',
                        'pending' => 'pending',
                        default => 'active'
                    };
                @endphp
                <a href="{{ $menu->menu_url ?? $menu->full_url }}"
                   target="{{ $menu->target ?? '_self' }}"
                   class="menu-card">
                    <div class="menu-icon" style="background: {{ $iconColor }};">
                        @if($menu->icon)
                            <i class="{{ $menu->icon }}"></i>
                        @else
                            <i class="bi bi-grid-fill"></i>
                        @endif
                    </div>
                    <h5>{{ $menu->name }}</h5>
                    @if($menu->description)
                        <p>{{ $menu->description }}</p>
                    @endif
                    @if($menu->badge_text)
                        <span class="status-badge {{ $badgeClass }}">{{ $menu->badge_text }}</span>
                    @else
                        <span class="status-badge active">Aktif</span>
                    @endif
                </a>
            @empty
                <!-- Fallback menu jika belum ada menu di database -->
                <a href="{{ route('kemahasiswaan.verifikasi-prestasi.index') }}" class="menu-card">
                    <div class="menu-icon" style="background: linear-gradient(135deg, #00897B, #26A69A);">
                        <i class="bi bi-patch-check-fill"></i>
                    </div>
                    <h5>Verifikasi Data Prestasi</h5>
                    <p>Verifikasi pengajuan prestasi dan beasiswa mahasiswa</p>
                    <span class="status-badge active">Aktif</span>
                </a>
                <a href="{{ route('kemahasiswaan.student-ambassador.index') }}" class="menu-card">
                    <div class="menu-icon" style="background: linear-gradient(135deg, #FF9800, #FFB347);">
                        <i class="bi bi-award-fill"></i>
                    </div>
                    <h5>Management Student Ambassador</h5>
                    <p>Kelola direktori mahasiswa dan status Student Ambassador</p>
                    <span class="status-badge active">Aktif</span>
                </a>
            @endforelse

            @if(auth()->user()->role === 'superadmin' || auth()->user()->role === 'masteradmin')
                <!-- Management Menu (Static - untuk akses ke menu management) -->
                <a href="{{ route('admin.management.menus.index') }}" class="menu-card">
                    <div class="menu-icon" style="background: linear-gradient(135deg, #607D8B, #90A4AE);">
                        <i class="bi bi-menu-button-wide"></i>
                    </div>
                    <h5>Management Menu</h5>
                    <p>Kelola menu dinamis untuk semua role</p>
                    <span class="status-badge active">Aktif</span>
                </a>
            @endif
        </div>
    </div>

    {{-- Recent Submissions Table --}}
    <div class="section-block">
        <div class="section-header d-flex justify-content-between align-items-center">
            <h4><i class="bi bi-clock-history me-2 text-warning"></i> Pengajuan Prestasi Terbaru</h4>
            <a href="{{ route('kemahasiswaan.verifikasi-prestasi.index') }}" class="btn-link-action">Lihat Semua Data <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="table-container">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Mahasiswa</th>
                        <th>Kategori & Kegiatan</th>
                        <th>Tingkat</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($recentAchievements ?? collect()) as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->student->nama_lengkap ?? '-' }}</strong><br>
                                <small class="text-muted">{{ $item->student->nim ?? '-' }} • {{ $item->student->program_studi ?? '-' }}</small>
                            </td>
                            <td>
                                <strong>{{ $item->event }}</strong><br>
                                <small class="text-muted">{{ ucfirst($item->category ?? 'Prestasi') }} • {{ $item->organizer ?? '-' }}</small>
                            </td>
                            <td>
                                <span class="badge-level">{{ ucfirst($item->level ?? 'Lokal') }}</span>
                            </td>
                            <td>
                                @if($item->status === 'approved')
                                    <span class="status-pill approved">Disetujui</span>
                                @elseif($item->status === 'rejected')
                                    <span class="status-pill rejected">Ditolak</span>
                                @else
                                    <span class="status-pill pending">Pending</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('kemahasiswaan.verifikasi-prestasi.index', ['search' => $item->student->nim ?? '']) }}" class="btn-detail">
                                    <i class="bi bi-search"></i> Periksa
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                Belum ada pengajuan prestasi terbaru yang menunggu verifikasi.
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
    .dashboard-kemahasiswaan-container {
        max-width: 1200px;
        margin: 0 auto;
        padding-bottom: 40px;
    }

    .welcome-banner {
        position: relative;
        background: linear-gradient(135deg, #004D40, #00796B);
        border-radius: 24px;
        padding: 40px 45px;
        color: white;
        overflow: hidden;
        margin-bottom: 30px;
        box-shadow: 0 15px 35px rgba(0, 77, 64, 0.25);
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
    .stat-card.total .stat-icon { background: #E0F2F1; color: #00796B; }

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
    .stat-tag.total { background: #B2DFDB; color: #004D40; }

    .section-block {
        background: white;
        border-radius: 20px;
        padding: 26px 30px;
        margin-bottom: 25px;
        border: 1px solid #E2E8F0;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .section-header h3,
    .section-header h4 {
        font-size: 18px;
        font-weight: 700;
        margin: 0;
        color: #1E293B;
        display: flex;
        align-items: center;
    }

    /* Menu Section & Cards (Harmonized with SuperApp Home) */
    .menu-section {
        margin-bottom: 30px;
    }

    .menu-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }

    @media (min-width: 769px) {
        .menu-grid {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
    }

    .menu-card {
        background: white;
        border-radius: 20px;
        padding: 24px 20px;
        text-align: center;
        box-shadow: var(--shadow, 0 4px 20px rgba(0, 0, 0, 0.06));
        border: 1px solid #E2E8F0;
        transition: var(--transition-normal, all 0.3s ease);
        cursor: pointer;
        position: relative;
        overflow: hidden;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: space-between;
    }

    .menu-card:hover {
        box-shadow: var(--shadow-hover, 0 10px 30px rgba(0, 0, 0, 0.12));
        transform: translateY(-8px);
        border-color: #CBD5E1;
        color: inherit;
    }

    .menu-icon {
        width: 70px;
        height: 70px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
        font-size: 32px;
        color: white;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        transition: var(--transition-normal, transform 0.3s ease);
    }

    .menu-card:hover .menu-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .menu-card h5 {
        font-size: 15px;
        font-weight: 700;
        color: var(--text-dark, #1E293B);
        margin-bottom: 8px;
        line-height: 1.35;
    }

    .menu-card p {
        font-size: 12px;
        color: var(--text-gray, #64748B);
        margin-bottom: 14px;
        line-height: 1.45;
        flex-grow: 1;
    }

    /* Status Badges */
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }

    .status-badge.active {
        background: #E8F5E9;
        color: #4CAF50;
    }

    .status-badge.warning {
        background: #FFF3E0;
        color: #FF9800;
    }

    .status-badge.pending {
        background: #E3F2FD;
        color: #2196F3;
    }

    .status-badge.info {
        background: #F3E5F5;
        color: #9C27B0;
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

    .badge-level {
        background: #E2E8F0;
        color: #334155;
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
    .status-pill.rejected { background: #FFCDD2; color: #C62828; }

    .btn-detail {
        padding: 6px 14px;
        border-radius: 8px;
        background: #00796B;
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
        background: #004D40;
        color: white;
    }

    .btn-link-action {
        color: #00796B;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
    }

    .btn-link-action:hover {
        text-decoration: underline;
    }

    @media (max-width: 992px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 768px) {
        .menu-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .menu-card {
            padding: 18px 12px;
        }

        .menu-icon {
            width: 58px;
            height: 58px;
            font-size: 26px;
            margin-bottom: 12px;
            border-radius: 15px;
        }

        .menu-card h5 {
            font-size: 14px;
        }

        .menu-card p {
            font-size: 11px;
            margin-bottom: 10px;
        }
    }

    @media (max-width: 600px) {
        .stats-grid { grid-template-columns: 1fr; }
        .welcome-banner { padding: 30px 20px; }
        .icon-side { display: none; }
    }

    @media (max-width: 480px) {
        .menu-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush
