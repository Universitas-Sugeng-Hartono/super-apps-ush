@extends('admin.layouts.super-app')

@section('content')
<div class="dashboard-akademik-container">
    {{-- Header Banner --}}
    <div class="welcome-banner">
        @if(!empty($activePeriod))
            <a href="{{ route('akademik.tahun-akademik.index') }}" class="banner-top-ta-badge" title="Klik untuk melihat detail Tahun Akademik & Semester">
                <span class="ta-indicator-dot"></span>
                TA: {{ $activePeriod['tahun_akademik'] ?? '-' }} {{ !empty($activePeriod['semester']) ? '• ' . $activePeriod['semester'] : '' }}
            </a>
        @endif
        <div class="banner-content">
            <div class="text-side">
                <span class="role-badge-tag"><i class="bi bi-mortarboard-fill"></i> Biro Akademik</span>
                <h1>Selamat Datang, {{ explode(' ', auth()->user()->name ?? 'Staf')[0] }}!</h1>
                <p>Pusat layanan akademik Universitas Sugeng Hartono untuk direktori mahasiswa, capaian IPK KHS, monitoring beasiswa, dan pengumuman kampus.</p>
                <div class="banner-badges">
                    <span class="b-badge"><i class="bi bi-shield-check"></i> Portal Institusi</span>
                    <span class="b-badge"><i class="bi bi-buildings"></i> {{ $prodiCount ?? 0 }} Program Studi</span>
                    <span class="b-badge"><i class="bi bi-person-badge-fill"></i> {{ $lecturerCount ?? 0 }} Dosen Pembimbing</span>
                </div>
            </div>
            <div class="icon-side">
                
            </div>
        </div>
        <div class="banner-pattern"></div>
    </div>

    {{-- Primary KPI Stat Cards (4 Cards in 1 Clean Row) --}}
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
        <div class="stat-card ambassador">
            <div class="stat-icon"><i class="bi bi-award-fill"></i></div>
            <div class="stat-info">
                <h3>{{ $ambassadorCount ?? 0 }}</h3>
                <p>Student Ambassador</p>
                <span class="stat-tag ambassador">Duta Kampus USH</span>
            </div>
        </div>
        <div class="stat-card scholarship">
            <div class="stat-icon"><i class="bi bi-patch-check-fill"></i></div>
            <div class="stat-info">
                <h3>{{ $scholarshipCount ?? 0 }}</h3>
                <p>Penerima Beasiswa</p>
                <span class="stat-tag scholarship">Beasiswa > 0%</span>
            </div>
        </div>
    </div>

    {{-- Secondary Metric Badges Bar --}}
    <div class="sub-stats-bar mb-4">
        <div class="sub-stat-item">
            <span class="sub-stat-label"><i class="bi bi-person-dash text-warning me-1"></i> Mahasiswa Cuti:</span>
            <span class="sub-stat-value">{{ $leaveStudentCount ?? 0 }}</span>
        </div>
        <div class="sub-stat-divider"></div>
        <div class="sub-stat-item">
            <span class="sub-stat-label"><i class="bi bi-person-x text-danger me-1"></i> Mahasiswa Nonaktif:</span>
            <span class="sub-stat-value">{{ $inactiveStudentCount ?? 0 }}</span>
        </div>
        <div class="sub-stat-divider"></div>
        <div class="sub-stat-item">
            <span class="sub-stat-label"><i class="bi bi-diagram-3 text-primary me-1"></i> Program Studi:</span>
            <span class="sub-stat-value">{{ $prodiCount ?? 0 }} Terdaftar</span>
        </div>
        <div class="sub-stat-divider"></div>
        <div class="sub-stat-item">
            <span class="sub-stat-label"><i class="bi bi-megaphone text-info me-1"></i> Pengumuman Terbit:</span>
            <span class="sub-stat-value">{{ $announcementCount ?? 0 }} Aktif</span>
        </div>
    </div>

    {{-- Layanan & Modul Utama (Menu Grid - Bersih & Rapi Seperti Dashboard Lain) --}}
    <div class="menu-section">
        <div class="section-header">
            <h3><i class="bi bi-grid-fill me-2 text-primary"></i> Layanan & Modul Utama</h3>
        </div>

        <div class="menu-grid">
            @forelse($menus ?? [] as $menu)
                @php
                    $iconColors = [
                        'bi-people-fill' => 'linear-gradient(135deg, #1E3A8A, #3B82F6)',
                        'bi-mortarboard' => 'linear-gradient(135deg, #1E3A8A, #3B82F6)',
                        'bi-person-badge-fill' => 'linear-gradient(135deg, #0284C7, #38BDF8)',
                        'bi-person-circle' => 'linear-gradient(135deg, #FFC107, #FFD54F)',
                        'bi-book-half' => 'linear-gradient(135deg, #4CAF50, #81C784)',
                        'bi-stars' => 'linear-gradient(135deg, #9C27B0, #BA68C8)',
                        'bi-megaphone-fill' => 'linear-gradient(135deg, #0284C7, #38BDF8)',
                        'bi-menu-button-wide' => 'linear-gradient(135deg, #475569, #64748B)',
                        'bi-award-fill' => 'linear-gradient(135deg, #FF9800, #FFB347)',
                        'bi-patch-check-fill' => 'linear-gradient(135deg, #00897B, #26A69A)',
                    ];
                    $defaultColor = 'linear-gradient(135deg, #1E3A8A, #3B82F6)';
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
                    @if(str_contains(strtolower($menu->name), 'tahun akademik'))
                        <div class="menu-period-chip mt-1 mb-2">
                            <span class="period-chip-tag">
                                <i class="bi bi-clock-history me-1 text-primary"></i> {{ $activePeriod['tahun_akademik'] ?? '-' }} &bull; Semester {{ $activePeriod['semester'] ?? '-' }}
                            </span>
                        </div>
                    @endif
                    @if($menu->description)
                        <p>{{ $menu->description }}</p>
                    @endif
                    <span class="status-badge {{ $badgeClass }}">{{ (str_contains(strtolower($menu->name), 'tahun akademik') && !empty($activePeriod['semester'])) ? ($activePeriod['semester'] . ' (Aktif)') : ($menu->badge_text ?: 'Aktif') }}</span>
                </a>
            @empty
                <a href="{{ route('akademik.direktori-mahasiswa.index') }}" class="menu-card">
                    <div class="menu-icon" style="background: linear-gradient(135deg, #1E3A8A, #3B82F6);">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <h5>Direktori Mahasiswa</h5>
                    <p>Direktori lengkap mahasiswa, status akademik, dan capaian IPK</p>
                    <span class="status-badge active">Utama</span>
                </a>
                <a href="{{ route('akademik.monitoring-beasiswa.index') }}" class="menu-card">
                    <div class="menu-icon" style="background: linear-gradient(135deg, #FF9800, #FFB347);">
                        <i class="bi bi-award-fill"></i>
                    </div>
                    <h5>Monitoring Beasiswa & Ambassador</h5>
                    <p>Monitoring data mahasiswa penerima beasiswa dan status Student Ambassador</p>
                    <span class="status-badge active">Utama</span>
                </a>
                <a href="{{ route('akademik.tahun-akademik.index') }}" class="menu-card">
                    <div class="menu-icon" style="background: linear-gradient(135deg, #0284C7, #38BDF8);">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                    <h5>Tahun Akademik &amp; Semester</h5>
                    <div class="menu-period-chip mt-1 mb-2">
                        <span class="period-chip-tag">
                            <i class="bi bi-clock-history me-1 text-primary"></i> {{ $activePeriod['tahun_akademik'] ?? '-' }} &bull; Semester {{ $activePeriod['semester'] ?? '-' }}
                        </span>
                    </div>
                    <p>Informasi tahun akademik dan semester aktif dari SIAKAD</p>
                    <span class="status-badge active">{{ $activePeriod['semester'] ?? 'Aktif' }}</span>
                </a>
                <a href="{{ route('admin.announcements.index') }}" class="menu-card">
                    <div class="menu-icon" style="background: linear-gradient(135deg, #0284C7, #38BDF8);">
                        <i class="bi bi-megaphone-fill"></i>
                    </div>
                    <h5>Pengumuman Kampus</h5>
                    <p>Kelola dan pantau pengumuman resmi kampus</p>
                    <span class="status-badge active">Aktif</span>
                </a>
            @endforelse

            @if(auth()->user()->role === 'superadmin' || auth()->user()->role === 'masteradmin')
                <a href="{{ route('admin.management.menus.index') }}" class="menu-card">
                    <div class="menu-icon" style="background: linear-gradient(135deg, #475569, #64748B);">
                        <i class="bi bi-menu-button-wide"></i>
                    </div>
                    <h5>Management Menu</h5>
                    <p>Kelola menu dinamis untuk semua role pengguna</p>
                    <span class="status-badge active">Superuser</span>
                </a>
            @endif
        </div>
    </div>

    {{-- Section Ringkasan Mahasiswa Terdaftar Terbaru --}}
    <div class="section-block">
        <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4><i class="bi bi-clock-history me-2 text-primary"></i> Mahasiswa Terbaru Terdaftar</h4>
                <p class="text-muted mb-0" style="font-size: 13px;">5 mahasiswa yang baru diperbarui datanya di sistem.</p>
            </div>
            <a href="{{ route('akademik.direktori-mahasiswa.index') }}" class="btn-link-action">
                Buka Direktori Lengkap <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Mahasiswa & NIM</th>
                        <th>Program Studi & Angkatan</th>
                        <th style="text-align: center;">Semester</th>
                        <th style="text-align: center;">IPK</th>
                        <th style="text-align: center;">Beasiswa (%)</th>
                        <th>Penetapan</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentStudents as $student)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="student-profile">
                                    <div class="student-meta">
                                        <span class="student-name">{{ $student->nama_lengkap }}</span>
                                        <span class="student-nim font-monospace">{{ $student->nim }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="prodi-tag">{{ $student->program_studi ?? '-' }}</span>
                                <span class="text-muted d-block" style="font-size: 11.5px; margin-top: 2px;">{{ $student->angkatan ? 'Angkatan ' . $student->angkatan : '-' }}</span>
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
                                    <span class="status-badge-ambassador" title="Student Ambassador (Duta Kampus)">
                                        <i class="bi bi-star-fill"></i> Ambassador
                                    </span>
                                @else
                                    <span class="status-badge-mahasiswa" title="Mahasiswa Reguler">
                                        <i class="bi bi-person"></i> Reguler
                                    </span>
                                @endif
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
                                <button type="button" class="btn-view-detail" onclick="openStudentModal({{ json_encode([
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
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">Belum ada data mahasiswa yang terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Section Pengumuman Terbaru --}}
    <div class="section-block">
        <div class="section-header d-flex justify-content-between align-items-center">
            <h4><i class="bi bi-megaphone me-2 text-primary"></i> Pengumuman Terbaru</h4>
            <a href="{{ route('admin.announcements.index') }}" class="btn-link-action">Kelola Pengumuman <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="table-container">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Judul Pengumuman</th>
                        <th style="width: 200px;">Tanggal Terbit</th>
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

{{-- Modal Quick Detail Mahasiswa Shared Component --}}
@include('akademik.script')
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('admin/css/skpi-daftar-skpi.css') }}">
<style>
    .dashboard-akademik-container {
        max-width: 1240px;
        margin: 0 auto;
        padding-bottom: 40px;
    }

    /* Welcome Banner */
    .dashboard-akademik-container .welcome-banner {
        position: relative;
        background: linear-gradient(135deg, #1E3A8A, #2563EB);
        border-radius: 24px;
        padding: 40px 45px;
        color: white;
        overflow: hidden;
        margin-bottom: 25px;
        box-shadow: 0 15px 35px rgba(30, 58, 138, 0.22);
    }

    /* Header Banner Top-Right Academic Period Badge */
    .banner-top-ta-badge {
        position: absolute;
        top: 24px;
        right: 32px;
        z-index: 10;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.38);
        color: #ffffff;
        padding: 6px 16px;
        border-radius: 99px;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.3px;
        backdrop-filter: blur(8px);
        text-decoration: none;
        transition: all 0.2s ease;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
    }
    .banner-top-ta-badge:hover {
        background: rgba(255, 255, 255, 0.32);
        border-color: rgba(255, 255, 255, 0.6);
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.14);
    }
    .banner-top-ta-badge .ta-indicator-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #34D399;
        display: inline-block;
        animation: pulseLiveDot 2s infinite;
    }
    @keyframes pulseLiveDot {
        0% { transform: scale(0.95); opacity: 0.8; }
        50% { transform: scale(1.3); opacity: 1; }
        100% { transform: scale(0.95); opacity: 0.8; }
    }
    .period-chip-tag {
        display: inline-flex;
        align-items: center;
        background: #EFF6FF;
        border: 1px solid #BFDBFE;
        color: #1D4ED8;
        font-size: 11.5px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 6px;
    }
    @media (max-width: 768px) {
        .banner-top-ta-badge {
            position: static;
            display: inline-flex;
            margin-bottom: 14px;
        }
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
        backdrop-filter: blur(4px);
    }

    .dashboard-akademik-container .text-side h1 {
        font-size: 32px;
        font-weight: 800;
        margin-bottom: 10px;
    }

    .dashboard-akademik-container .text-side p {
        font-size: 15px;
        opacity: 0.9;
        max-width: 620px;
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .dashboard-akademik-container .banner-badges {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }

    .dashboard-akademik-container .b-badge {
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.25);
        padding: 6px 14px;
        border-radius: 99px;
        font-size: 12.5px;
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

    /* Primary KPI 4 Cards Grid */
    .dashboard-akademik-container .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 18px;
        margin-bottom: 18px;
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
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .dashboard-akademik-container .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
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
    .dashboard-akademik-container .stat-card.ambassador .stat-icon { background: #FEF3C7; color: #B45309; }
    .dashboard-akademik-container .stat-card.scholarship .stat-icon { background: #D1FAE5; color: #047857; }

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
    .dashboard-akademik-container .stat-tag.ambassador { background: #FEF3C7; color: #92400E; }
    .dashboard-akademik-container .stat-tag.scholarship { background: #DCFCE7; color: #166534; }

    /* Secondary Metric Badges Bar */
    .sub-stats-bar {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 10px 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }
    .sub-stat-item {
        font-size: 12.5px;
        color: #475569;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .sub-stat-label { font-weight: 600; }
    .sub-stat-value {
        font-weight: 700;
        color: #1E293B;
        background: white;
        padding: 2px 8px;
        border-radius: 6px;
        border: 1px solid #CBD5E1;
        font-size: 12px;
    }
    .sub-stat-divider {
        width: 1px;
        height: 16px;
        background: #CBD5E1;
    }

    /* Menu Section & Cards (Harmonized with Kemahasiswaan & SuperApps) */
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
        font-size: 18px;
        font-weight: 700;
        margin: 0;
        color: #1E293B;
        display: flex;
        align-items: center;
    }

    .dashboard-akademik-container .menu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 20px;
    }

    .dashboard-akademik-container .menu-card {
        background: white;
        border-radius: 20px;
        padding: 24px 20px;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        border: 1px solid #E2E8F0;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: space-between;
    }

    .dashboard-akademik-container .menu-card:hover {
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transform: translateY(-6px);
        border-color: #CBD5E1;
        color: inherit;
    }

    .dashboard-akademik-container .menu-icon {
        width: 70px;
        height: 70px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
        font-size: 32px;
        color: white;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
        transition: transform 0.3s ease;
    }

    .dashboard-akademik-container .menu-card:hover .menu-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .dashboard-akademik-container .menu-card h5 {
        font-size: 15px;
        font-weight: 700;
        color: #1E293B;
        margin-bottom: 8px;
        line-height: 1.35;
    }

    .dashboard-akademik-container .menu-card p {
        font-size: 12px;
        color: #64748B;
        margin-bottom: 14px;
        line-height: 1.45;
    }

    .dashboard-akademik-container .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }
    .dashboard-akademik-container .status-badge.active { background: #E8F5E9; color: #4CAF50; }
    .dashboard-akademik-container .status-badge.warning { background: #FFF3E0; color: #FF9800; }
    .dashboard-akademik-container .status-badge.info { background: #F3E5F5; color: #9C27B0; }

    /* Section Block (Card Container) */
    .dashboard-akademik-container .section-block {
        background: white;
        border-radius: 20px;
        padding: 26px 30px;
        margin-bottom: 30px;
        border: 1px solid #E2E8F0;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    }

    /* Table */
    .dashboard-akademik-container .table-responsive {
        overflow-x: auto;
    }

    .dashboard-akademik-container .table-modern {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .dashboard-akademik-container .table-modern th {
        background: #F8FAFC;
        padding: 12px 14px;
        font-size: 12.5px;
        font-weight: 700;
        color: #475569;
        border-bottom: 2px solid #E2E8F0;
    }

    .dashboard-akademik-container .table-modern td {
        padding: 14px;
        border-bottom: 1px solid #F1F5F9;
        font-size: 13px;
        vertical-align: middle;
    }

    .student-profile { display: flex; flex-direction: column; }
    .student-name { font-weight: 700; color: #1E293B; }
    .student-nim { font-size: 12px; color: #64748B; }

    .prodi-tag {
        font-weight: 600;
        color: #334155;
    }

    .badge-sem {
        font-size: 11.5px;
        background: #F1F5F9;
        padding: 3px 8px;
        border-radius: 6px;
        font-weight: 600;
        color: #475569;
    }

    .badge-ipk {
        padding: 3px 9px;
        border-radius: 7px;
        font-weight: 800;
        font-size: 12px;
        display: inline-block;
    }
    .ipk-cumlaude { background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0; }
    .ipk-good { background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; }
    .ipk-fair { background: #FFFBEB; color: #B45309; border: 1px solid #FDE68A; }

    .badge-beasiswa {
        padding: 3px 9px;
        border-radius: 7px;
        font-weight: 800;
        font-size: 11.5px;
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
        gap: 4px;
        padding: 3px 9px;
        background: linear-gradient(135deg, #FFF8E1, #FFECB3);
        color: #B78103;
        border: 1px solid #FFE082;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
    }
    .status-badge-mahasiswa {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 9px;
        background: #F1F5F9;
        color: #64748B;
        border: 1px solid #E2E8F0;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
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

    .btn-view-detail {
        padding: 5px 12px;
        background: #EFF6FF;
        border: 1px solid #BFDBFE;
        color: #1D4ED8;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.2s;
    }
    .btn-view-detail:hover {
        background: #2563EB;
        color: white;
        border-color: #2563EB;
    }

    .btn-link-action {
        color: #1D4ED8;
        font-size: 13.5px;
        font-weight: 600;
        text-decoration: none;
    }
    .btn-link-action:hover {
        text-decoration: underline;
    }

    /* Announcements Table */
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

    @media (max-width: 992px) {
        .dashboard-akademik-container .stats-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 600px) {
        .dashboard-akademik-container .stats-grid { grid-template-columns: 1fr; }
        .dashboard-akademik-container .welcome-banner { padding: 30px 20px; }
        .dashboard-akademik-container .icon-side { display: none; }
        .sub-stats-bar { flex-direction: column; align-items: flex-start; gap: 8px; }
        .sub-stat-divider { display: none; }
    }
</style>
@endpush
