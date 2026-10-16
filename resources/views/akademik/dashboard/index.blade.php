@extends('admin.layouts.super-app')

@section('content')
<div class="dashboard-akademik-container">
    <div class="welcome-banner">
        <div class="banner-content">
            <div class="text-side">
                <span class="role-badge-tag"><i class="bi bi-mortarboard-fill"></i> Biro Akademik</span>
                <h1>Selamat Datang, {{ explode(' ', auth()->user()->name ?? 'Staf')[0] }}!</h1>
                <p>Pusat layanan akademik Universitas Sugeng Hartono untuk data mahasiswa, program studi, dan pengumuman.</p>
                <div class="banner-badges">
                    <span class="b-badge"><i class="bi bi-shield-check"></i> Portal Institusi</span>
                    <span class="b-badge"><i class="bi bi-buildings"></i> Seluruh Program Studi</span>
                </div>
            </div>
            <div class="icon-side">
                <i class="bi bi-journal-bookmark"></i>
            </div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card students">
            <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
            <div class="stat-info">
                <h3>{{ $studentCount ?? 0 }}</h3>
                <p>Total Mahasiswa</p>
                <span class="stat-tag students">Semua Angkatan</span>
            </div>
        </div>
        <div class="stat-card active-students">
            <div class="stat-icon"><i class="bi bi-person-check-fill"></i></div>
            <div class="stat-info">
                <h3>{{ $activeStudentCount ?? 0 }}</h3>
                <p>Mahasiswa Aktif</p>
                <span class="stat-tag active-students">Status Aktif</span>
            </div>
        </div>
        <div class="stat-card leave-students">
            <div class="stat-icon"><i class="bi bi-person-dash-fill"></i></div>
            <div class="stat-info">
                <h3>{{ $leaveStudentCount ?? 0 }}</h3>
                <p>Mahasiswa Cuti</p>
                <span class="stat-tag leave-students">Status Cuti</span>
            </div>
        </div>
        <div class="stat-card inactive-students">
            <div class="stat-icon"><i class="bi bi-person-x-fill"></i></div>
            <div class="stat-info">
                <h3>{{ $inactiveStudentCount ?? 0 }}</h3>
                <p>Mahasiswa Tidak Aktif</p>
                <span class="stat-tag inactive-students">Nonaktif</span>
            </div>
        </div>
        <div class="stat-card prodi">
            <div class="stat-icon"><i class="bi bi-diagram-3-fill"></i></div>
            <div class="stat-info">
                <h3>{{ $prodiCount ?? 0 }}</h3>
                <p>Program Studi Aktif</p>
                <span class="stat-tag prodi">Terdaftar</span>
            </div>
        </div>
        <div class="stat-card lecturers">
            <div class="stat-icon"><i class="bi bi-person-badge-fill"></i></div>
            <div class="stat-info">
                <h3>{{ $lecturerCount ?? 0 }}</h3>
                <p>Dosen</p>
                <span class="stat-tag lecturers">Pembimbing</span>
            </div>
        </div>
        <div class="stat-card announcements">
            <div class="stat-icon"><i class="bi bi-megaphone-fill"></i></div>
            <div class="stat-info">
                <h3>{{ $announcementCount ?? 0 }}</h3>
                <p>Pengumuman Terbit</p>
                <span class="stat-tag announcements">Aktif</span>
            </div>
        </div>
    </div>

    <div class="akademik-student-list">
        <div class="filter-card shadow-sm">
            <form method="GET" action="{{ $listRoute }}" class="filter-grid">
                <div class="filter-item">
                    <label>Cari Mahasiswa</label>
                    <div class="input-with-icon">
                        <i class="bi bi-search"></i>
                        <input type="text" name="search" class="form-control-modern" value="{{ $search }}" placeholder="Nama atau NIM">
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
                <div class="filter-actions-modern">
                    <a href="{{ $listRoute }}" class="btn-reset-modern" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                    <button type="submit" class="btn-search-modern">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <div class="table-card shadow-sm">
            <div class="table-responsive-modern">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Mahasiswa & NIM</th>
                            <th>Program Studi</th>
                            <th>Angkatan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                            <tr>
                                <td>
                                    <div class="student-profile">
                                        <div class="student-meta">
                                            <span class="student-name">{{ $student->nama_lengkap }}</span>
                                            <span class="student-nim">{{ $student->nim }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="prodi-tag">{{ $student->program_studi ?? '-' }}</span></td>
                                <td>{{ $student->angkatan ?? '-' }}</td>
                                <td>
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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Belum ada mahasiswa yang cocok dengan filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($students->hasPages())
            <div class="pagination-container">
                {{ $students->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    <div class="menu-section">
        <div class="section-header">
            <h3>Menu Utama</h3>
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
                    ];
                    $defaultColor = 'linear-gradient(135deg, #2196F3, #64B5F6)';
                    $iconKey = trim(str_replace('bi ', '', $menu->icon ?? ''));
                    $iconColor = $iconColors[$iconKey] ?? $iconColors[$menu->icon] ?? $defaultColor;
                    $badgeClass = match($menu->badge_color) {
                        'active' => 'active',
                        'warning' => 'warning',
                        'info' => 'info',
                        'pending' => 'pending',
                        default => 'active'
                    };
                @endphp
                <a href="{{ $menu->menu_url }}" target="{{ $menu->target ?? '_self' }}" class="menu-card">
                    <div class="menu-icon" style="background: {{ $iconColor }};">
                        <i class="{{ $menu->icon ?: 'bi bi-circle' }}"></i>
                    </div>
                    <h5>{{ $menu->name }}</h5>
                    @if($menu->description)
                        <p>{{ $menu->description }}</p>
                    @endif
                    <span class="status-badge {{ $badgeClass }}">{{ $menu->badge_text ?: 'Aktif' }}</span>
                </a>
            @empty
                <div class="empty-menu">
                    Belum ada menu yang ditambahkan untuk Akademik. Menu baru dapat ditambahkan dari Management Menu Superuser.
                </div>
            @endforelse
        </div>
    </div>

    <div class="section-block">
        <div class="section-header d-flex justify-content-between align-items-center">
            <h4><i class="bi bi-megaphone me-2"></i> Pengumuman Terbaru</h4>
            <a href="{{ route('admin.announcements.index') }}" class="btn-link-action">Kelola Pengumuman <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="table-container">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Terbit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($recentAnnouncements ?? collect()) as $item)
                        <tr>
                            <td><strong>{{ $item->title }}</strong></td>
                            <td>{{ optional($item->published_at)->translatedFormat('d M Y') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center py-4 text-muted">
                                Belum ada pengumuman yang diterbitkan.
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
<link rel="stylesheet" href="{{ asset('admin/css/skpi-daftar-skpi.css') }}">
<style>
    .akademik-student-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
        margin-bottom: 30px;
    }

    .akademik-student-list .filter-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr)) auto;
    }

    @media (max-width: 1100px) {
        .akademik-student-list .filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    .dashboard-akademik-container {
        max-width: 1200px;
        margin: 0 auto;
        padding-bottom: 40px;
    }

    .dashboard-akademik-container .welcome-banner {
        position: relative;
        background: linear-gradient(135deg, #1E3A8A, #2563EB);
        border-radius: 24px;
        padding: 40px 45px;
        color: white;
        overflow: hidden;
        margin-bottom: 30px;
        box-shadow: 0 15px 35px rgba(30, 58, 138, 0.25);
    }

    .dashboard-akademik-container .role-badge-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255, 255, 255, 0.2);
        padding: 5px 14px;
        border-radius: 30px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 12px;
    }

    .dashboard-akademik-container .text-side h1 {
        font-size: 32px;
        font-weight: 800;
        margin-bottom: 10px;
    }

    .dashboard-akademik-container .text-side p {
        font-size: 15px;
        opacity: 0.9;
        max-width: 600px;
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .dashboard-akademik-container .banner-badges {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .dashboard-akademik-container .b-badge {
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

    .dashboard-akademik-container .icon-side {
        font-size: 110px;
        opacity: 0.15;
        position: absolute;
        right: 40px;
        top: 20px;
    }

    .dashboard-akademik-container .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .dashboard-akademik-container .stat-card {
        background: white;
        border-radius: 18px;
        padding: 22px;
        display: flex;
        align-items: center;
        gap: 16px;
        border: 1px solid #E2E8F0;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
    }

    .dashboard-akademik-container .stat-icon {
        width: 55px;
        height: 55px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
    }

    .dashboard-akademik-container .stat-card.students .stat-icon { background: #DBEAFE; color: #1D4ED8; }
    .dashboard-akademik-container .stat-card.active-students .stat-icon { background: #DCFCE7; color: #15803D; }
    .dashboard-akademik-container .stat-card.leave-students .stat-icon { background: #FEF3C7; color: #B45309; }
    .dashboard-akademik-container .stat-card.inactive-students .stat-icon { background: #FEE2E2; color: #B91C1C; }
    .dashboard-akademik-container .stat-card.prodi .stat-icon { background: #E0E7FF; color: #4338CA; }
    .dashboard-akademik-container .stat-card.lecturers .stat-icon { background: #E0F2FE; color: #0369A1; }
    .dashboard-akademik-container .stat-card.announcements .stat-icon { background: #FEF3C7; color: #B45309; }

    .dashboard-akademik-container .stat-info h3 {
        font-size: 26px;
        font-weight: 800;
        margin: 0;
        color: #1E293B;
    }

    .dashboard-akademik-container .stat-info p {
        font-size: 13px;
        color: #64748B;
        margin: 2px 0 6px;
        font-weight: 500;
    }

    .dashboard-akademik-container .stat-tag {
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 6px;
        font-weight: 700;
    }

    .dashboard-akademik-container .stat-tag.students { background: #DBEAFE; color: #1E3A8A; }
    .dashboard-akademik-container .stat-tag.active-students { background: #DCFCE7; color: #166534; }
    .dashboard-akademik-container .stat-tag.leave-students { background: #FEF3C7; color: #92400E; }
    .dashboard-akademik-container .stat-tag.inactive-students { background: #FEE2E2; color: #991B1B; }
    .dashboard-akademik-container .stat-tag.prodi { background: #E0E7FF; color: #3730A3; }
    .dashboard-akademik-container .stat-tag.lecturers { background: #E0F2FE; color: #0C4A6E; }
    .dashboard-akademik-container .stat-tag.announcements { background: #FDE68A; color: #92400E; }

    .dashboard-akademik-container .section-block {
        background: white;
        border-radius: 20px;
        padding: 26px 30px;
        margin-bottom: 25px;
        border: 1px solid #E2E8F0;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    }

    .dashboard-akademik-container .menu-section {
        margin-bottom: 30px;
    }

    .dashboard-akademik-container .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .dashboard-akademik-container .section-header h3,
    .dashboard-akademik-container .section-header h4 {
        font-size: 20px;
        font-weight: 600;
        margin: 0;
        color: var(--text-dark);
    }

    .dashboard-akademik-container .menu-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }

    .dashboard-akademik-container .menu-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        text-align: center;
        box-shadow: var(--shadow);
        transition: var(--transition-normal);
        cursor: pointer;
        position: relative;
        overflow: hidden;
        text-decoration: none;
        display: block;
    }

    .dashboard-akademik-container .menu-card:hover {
        box-shadow: var(--shadow-hover);
        transform: translateY(-8px);
    }

    .dashboard-akademik-container .menu-icon {
        width: 70px;
        height: 70px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        font-size: 35px;
        color: white;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        transition: var(--transition-normal);
    }

    .dashboard-akademik-container .menu-card:hover .menu-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .dashboard-akademik-container .menu-card h5 {
        font-size: 15px;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 8px;
    }

    .dashboard-akademik-container .menu-card p {
        font-size: 12px;
        color: var(--text-gray);
        margin-bottom: 12px;
        line-height: 1.4;
    }

    .dashboard-akademik-container .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }

    .dashboard-akademik-container .status-badge.active {
        background: #E8F5E9;
        color: #4CAF50;
    }

    .dashboard-akademik-container .status-badge.warning {
        background: #FFF3E0;
        color: #FF9800;
    }

    .dashboard-akademik-container .status-badge.pending {
        background: #E3F2FD;
        color: #2196F3;
    }

    .dashboard-akademik-container .status-badge.info {
        background: #F3E5F5;
        color: #9C27B0;
    }

    .dashboard-akademik-container .empty-menu {
        grid-column: 1 / -1;
        text-align: center;
        color: #64748B;
        padding: 24px 12px;
        background: white;
        border-radius: 20px;
    }

    .dashboard-akademik-container .empty-menu i {
        display: block;
        font-size: 28px;
        margin-bottom: 8px;
    }

    .dashboard-akademik-container .custom-table {
        width: 100%;
        border-collapse: collapse;
    }

    .dashboard-akademik-container .custom-table th {
        padding: 12px 16px;
        background: #F8FAFC;
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        border-bottom: 2px solid #E2E8F0;
    }

    .dashboard-akademik-container .custom-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #F1F5F9;
        font-size: 13px;
    }

    .dashboard-akademik-container .btn-link-action {
        color: #1D4ED8;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
    }

    @media (min-width: 769px) {
        .dashboard-akademik-container .menu-grid {
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }
    }

    @media (max-width: 992px) {
        .dashboard-akademik-container .stats-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 600px) {
        .dashboard-akademik-container .stats-grid { grid-template-columns: 1fr; }
        .dashboard-akademik-container .welcome-banner { padding: 30px 20px; }
        .dashboard-akademik-container .icon-side { display: none; }
    }
</style>
@endpush
