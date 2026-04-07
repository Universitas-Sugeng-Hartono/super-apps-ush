@extends('admin.layouts.super-app')

@section('content')
    <!-- Page Header -->
    <div class="page-header mb-4">
        <h2 class="page-title">Dashboard Dosen PA</h2>
        <span class="semester-badge">{{ session('user_prodi') ?? 'Bisnis Digital' }}</span>
    </div>

    <!-- Charts Section -->
    <div class="charts-section">
        <div class="section-header">
            <h3>Analisis & Statistik</h3>
        </div>

        <!-- Row 1: Mahasiswa per Angkatan -->
        <div class="row g-3 mb-3">
                <div class="col-12 col-lg-4">

                <div class="chart-card">
                    <div class="chart-card-header">
                        <div>
                            <h5>Jumlah Mahasiswa per Angkatan</h5>
                            <p class="text-muted">Data mahasiswa bimbingan berdasarkan tahun angkatan</p>
                        </div>
                    </div>
                    <canvas id="batchChart"></canvas>
                </div>
            </div>
                <div class="col-12 col-lg-4">

                <div class="chart-card">
                    <div class="chart-card-header">
                        <div>
                            <h5>IPK Rata-rata per Angkatan</h5>
                            <p class="text-muted">Performa akademik mahasiswa per angkatan</p>
                        </div>
                    </div>
                    <canvas id="ipkChart"></canvas>
                </div>
            </div>
                <div class="col-12 col-lg-4">

                <div class="chart-card">
                    <div class="chart-card-header">
                        <div>
                            <h5>Distribusi per Prodi</h5>
                            <p class="text-muted">Jumlah mahasiswa per program studi</p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="prodiTable" class="table table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Program Studi</th>
                                    <th>Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($studentsByProdi ?? [] as $index => $prodi)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $prodi->program_studi ?? '-' }}</td>
                                    <td>{{ $prodi->total ?? 0 }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Menu Section -->
    <div class="menu-section">
        <div class="section-header">
            <h3>Menu Utama</h3>
            <a href="#" class="view-all">Lihat Semua</a>
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
                <a href="{{ $menu->menu_url }}"
                   target="{{ $menu->target ?? '_self' }}"
                   class="menu-card">
                    <div class="menu-icon" style="background: {{ $iconColor }};">
                        @if($menu->icon)
                            <i class="{{ $menu->icon }}"></i>
                        @else
                            <i class="bi bi-circle"></i>
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
                <a href="{{ route('admin.counseling.index') }}" class="menu-card">
                    <div class="menu-icon" style="background: linear-gradient(135deg, #FF9800, #FFB347);">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <h5>Bimbingan PA</h5>
                    <p>Kelola bimbingan mahasiswa bimbingan</p>
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

    <!-- Announcement Section -->
    <div class="announcement-section">
        <div class="section-header">
            <h3>Pengumuman Terbaru</h3>
            <a href="{{ route('admin.announcements.index') }}" class="view-all">Lihat Semua</a>
        </div>

        <div class="announcement-list">
            @if(($announcements ?? collect())->isEmpty())
                <div class="announcement-item">
                    <div class="announcement-icon">
                        <i class="bi bi-megaphone-fill"></i>
                    </div>
                    <div class="announcement-content">
                        <h5>Belum ada pengumuman</h5>
                        <p>Silakan buat pengumuman melalui menu Management Pengumuman.</p>
                        <span class="time"><i class="bi bi-info-circle"></i> Info</span>
                    </div>
                </div>
            @else
                @foreach($announcements as $a)
                    <div class="announcement-item">
                        <div class="announcement-icon">
                            <i class="bi bi-megaphone-fill"></i>
                        </div>
                        <div class="announcement-content">
                            <h5>{{ $a->title }}</h5>
                            <p>{{ \Illuminate\Support\Str::limit($a->content ?? '', 90) }}</p>
                            <span class="time">
                                <i class="bi bi-clock"></i>
                                {{ optional($a->published_at)->translatedFormat('d M Y H:i') }}
                            </span>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endsection

@push('css')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<style>
    /* Page Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .page-title {
        font-size: 28px;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0;
    }

    .semester-badge {
        background: linear-gradient(135deg, var(--primary-orange), #FFB347);
        color: white;
        padding: 8px 20px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(255, 152, 0, 0.3);
    }

    /* Menu Section */
    .menu-section {
        margin-bottom: 30px;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .section-header h3 {
        font-size: 20px;
        font-weight: 600;
        color: var(--text-dark);
        margin: 0;
    }

    .view-all {
        color: var(--primary-orange);
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: var(--transition-normal);
    }

    .view-all:hover {
        color: #FF7043;
    }

    .menu-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }

    .menu-card {
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

    .menu-card:hover {
        box-shadow: var(--shadow-hover);
        transform: translateY(-8px);
    }

    .menu-icon {
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

    .menu-card:hover .menu-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .menu-card h5 {
        font-size: 15px;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 8px;
    }

    .menu-card p {
        font-size: 12px;
        color: var(--text-gray);
        margin-bottom: 12px;
        line-height: 1.4;
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

    /* Charts Section */
    .charts-section {
        margin-bottom: 30px;
    }

    .chart-card {
        background: white;
        border-radius: 20px;
        padding: 25px;
        box-shadow: var(--shadow);
        transition: var(--transition-normal);
        border: 3px solid transparent;
    }

    .chart-card:hover {
        box-shadow: var(--shadow-hover);
        border-color: rgba(255, 152, 0, 0.3);
    }

    .chart-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
    }

    .chart-card-header h5 {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-dark);
        margin: 0 0 5px 0;
    }

    .chart-card-header p {
        font-size: 12px;
        color: var(--text-gray);
        margin: 0;
    }

    .btn-detail {
        background: linear-gradient(135deg, #5B9BD5, #7DB8E8);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition-normal);
        display: flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }

    .btn-detail:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(91, 155, 213, 0.3);
    }

    .chart-card canvas {
        max-height: 300px;
    }

    .chart-card .table-responsive {
        max-height: 300px;
        overflow-x: auto;
    }

    .chart-card table {
        font-size: 13px;
        margin-bottom: 0;
    }

    .chart-card table thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        color: var(--text-dark);
        padding: 10px 12px;
    }

    .chart-card table tbody td {
        padding: 8px 12px;
        vertical-align: middle;
    }

    .chart-card table tbody tr:hover {
        background-color: #f8f9fa;
    }

    /* Announcement Section */
    .announcement-section {
        margin-bottom: 30px;
    }

    .announcement-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .announcement-item {
        background: white;
        border-radius: 15px;
        padding: 15px;
        box-shadow: var(--shadow);
        display: flex;
        gap: 15px;
        transition: var(--transition-normal);
        cursor: pointer;
    }

    .announcement-item:hover {
        box-shadow: var(--shadow-hover);
        transform: translateX(5px);
    }

    .announcement-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--primary-orange), #FFB347);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
        flex-shrink: 0;
    }

    .announcement-content {
        flex: 1;
    }

    .announcement-content h5 {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 5px;
    }

    .announcement-content p {
        font-size: 12px;
        color: var(--text-gray);
        margin-bottom: 8px;
        line-height: 1.4;
    }

    .announcement-content .time {
        font-size: 11px;
        color: var(--text-gray);
        display: flex;
        align-items: center;
        gap: 5px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-title {
            font-size: 22px;
        }

        .semester-badge {
            font-size: 11px;
            padding: 6px 14px;
        }

        .menu-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .chart-card {
            padding: 15px;
        }

        .chart-card-header {
            flex-direction: column;
            gap: 10px;
            align-items: flex-start;
        }

        .chart-card-header h5 {
            font-size: 14px;
        }

        .chart-card-header p {
            font-size: 11px;
        }

        .btn-detail {
            padding: 6px 12px;
            font-size: 11px;
        }

        .chart-card canvas {
            max-height: 250px;
        }
    }

    @media (min-width: 769px) {
        .menu-grid {
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }
    }
</style>
@endpush

@push('scripts')
<!-- jQuery (required for DataTables) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
// Pastikan jQuery dan DataTables sudah dimuat
(function() {
    'use strict';

    function initDataTables() {
        if (typeof jQuery !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
            const prodiTable = $('#prodiTable');
            if (prodiTable.length > 0) {
                // Cek apakah DataTable sudah diinisialisasi
                if ($.fn.DataTable.isDataTable('#prodiTable')) {
                    // Jika sudah diinisialisasi, destroy dulu
                    prodiTable.DataTable().destroy();
                }

                // Inisialisasi DataTable
                prodiTable.DataTable({
                    "pageLength": 5,
                    "lengthMenu": [[5, 10, 25, -1], [5, 10, 25, "Semua"]],
                    "language": {
                        "search": "Cari:",
                        "lengthMenu": "Tampilkan _MENU_ data",
                        "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                        "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
                        "infoFiltered": "(disaring dari _MAX_ total data)",
                        "paginate": {
                            "first": "Pertama",
                            "last": "Terakhir",
                            "next": "Selanjutnya",
                            "previous": "Sebelumnya"
                        },
                        "emptyTable": "Tidak ada data tersedia"
                    },
                    "order": [[2, 'desc']],
                    "columnDefs": [
                        { "orderable": false, "targets": 0 }
                    ]
                });
            }
        } else {
            // Jika jQuery/DataTables belum dimuat, coba lagi setelah beberapa saat
            setTimeout(initDataTables, 100);
        }
    }

document.addEventListener('DOMContentLoaded', function() {
    // Chart Colors
    const colors = {
        primary: 'rgba(255, 152, 0, 0.8)',
        primaryLight: 'rgba(255, 152, 0, 0.2)',
        blue: 'rgba(91, 155, 213, 0.8)',
        blueLight: 'rgba(91, 155, 213, 0.2)',
        green: 'rgba(76, 175, 80, 0.8)',
        greenLight: 'rgba(76, 175, 80, 0.2)',
        yellow: 'rgba(255, 193, 7, 0.8)',
        yellowLight: 'rgba(255, 193, 7, 0.2)',
        red: 'rgba(244, 67, 54, 0.8)',
        redLight: 'rgba(244, 67, 54, 0.2)',
        purple: 'rgba(156, 39, 176, 0.8)',
        purpleLight: 'rgba(156, 39, 176, 0.2)',
    };

    // Default chart options
    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true,
                position: 'bottom',
                labels: {
                    padding: 15,
                    font: {
                        size: 12,
                        family: "'Plus Jakarta Sans', sans-serif"
                    },
                    usePointStyle: true,
                    pointStyle: 'circle'
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 12,
                titleFont: {
                    size: 13,
                    weight: 'bold'
                },
                bodyFont: {
                    size: 12
                },
                cornerRadius: 8,
                displayColors: true
            }
        }
    };

    // Chart 1: Mahasiswa per Angkatan (Line Chart)
    const batchCtx = document.getElementById('batchChart');
    if (batchCtx) {
        new Chart(batchCtx, {
            type: 'line',
            data: {
                labels: @json($batchLabels ?? []),
                datasets: [{
                    label: 'Jumlah Mahasiswa',
                    data: @json($batchData ?? []),
                    borderColor: colors.primary,
                    backgroundColor: colors.primaryLight,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    pointBackgroundColor: colors.primary,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                ...defaultOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Chart 2: IPK Rata-rata per Angkatan (Bar Chart)
    const ipkCtx = document.getElementById('ipkChart');
    if (ipkCtx) {
        new Chart(ipkCtx, {
            type: 'bar',
            data: {
                labels: @json($ipkLabels ?? []),
                datasets: [{
                    label: 'IPK Rata-rata',
                    data: @json($ipkData ?? []),
                    backgroundColor: colors.blue,
                    borderColor: colors.blue,
                    borderWidth: 2,
                    borderRadius: 10,
                    borderSkipped: false
                }]
            },
            options: {
                ...defaultOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 4.0,
                        ticks: {
                            stepSize: 0.5,
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Inisialisasi DataTables setelah semua chart selesai
    initDataTables();
});

})();
</script>
@endpush
