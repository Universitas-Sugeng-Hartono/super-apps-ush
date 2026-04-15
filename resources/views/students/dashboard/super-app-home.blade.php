@extends('students.layouts.super-app')

@section('content')
    <!-- Upcoming Schedule Reminder -->
    @if(($upcomingSchedules ?? collect())->isNotEmpty())
        @foreach($upcomingSchedules as $schedule)
            <div class="schedule-reminder">
                <div class="reminder-badge-small {{ $schedule['type'] === 'Sidang' ? 'badge-red' : 'badge-blue' }}">
                    {{ $schedule['type'] }}
                </div>
                <div class="reminder-content">
                    <h4 class="reminder-title-main">{{ $schedule['type_label'] }} Mendatang</h4>
                    
                    <div class="reminder-info-row">
                        <i class="bi bi-calendar3"></i>
                        <div>
                            <strong>{{ $schedule['datetime']->translatedFormat('l, d F Y') }}</strong>
                            <span class="reminder-time-text">pukul {{ $schedule['datetime']->translatedFormat('H:i') }} WIB</span>
                        </div>
                    </div>

                    @if(!empty($schedule['title']))
                        <div class="reminder-info-row">
                            <i class="bi bi-file-earmark-text"></i>
                            <div class="reminder-text">{{ $schedule['title'] }}</div>
                        </div>
                    @endif

                    @if(!empty($schedule['approval_notes']))
                        <div class="reminder-info-box">
                            <div class="reminder-info-label">Catatan Kaprodi</div>
                            <div class="reminder-info-value">{{ $schedule['approval_notes'] }}</div>
                        </div>
                    @endif

                    <a href="{{ $schedule['url'] }}" class="reminder-link">
                        Lihat Detail
                    </a>
                </div>
            </div>
        @endforeach
    @endif

    <!-- Quick Stats Card -->
    <div class="stats-card">
        <div class="stats-header">
            <h3>Status Akademik</h3>
            <span class="semester-badge">{{ $student->angkatan ?? 'N/A' }}</span>
        </div>
        <div class="stats-content">
            <div class="stat-item">
                <div class="stat-icon" style="background: linear-gradient(135deg, #FF9800, #FFB347);">
                    <i class="bi bi-book"></i>
                </div>
                <div class="stat-info">
                    <h5>IPK</h5>
                    <p>{{ $student->ipk ?? '0.00' }}</p>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon" style="background: linear-gradient(135deg, #5B9BD5, #7DB8E8);">
                    <i class="bi bi-clipboard-check"></i>
                </div>
                <div class="stat-info">
                    <h5>SKS</h5>
                    <p>{{ $student->sks ?? '0' }}</p>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon" style="background: linear-gradient(135deg, #FFC107, #FFD54F);">
                    <i class="bi bi-trophy"></i>
                </div>
                <div class="stat-info">
                    <h5>Prestasi</h5>
                    <p>{{ $student->achievements_count ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Menu Section -->
    <div class="menu-section">
        <div class="section-header">
            <h3>Layanan Akademik</h3>
            <a href="#" class="view-all">Lihat Semua</a>
        </div>
        
        <div class="menu-grid">
            @forelse($menus ?? [] as $menu)
                @php
                    $iconColors = [
                        'bi-person-video3' => 'linear-gradient(135deg, #FF9800, #FFB347)',
                        'bi-mortarboard' => 'linear-gradient(135deg, #FF5252, #FF8A80)',
                        'bi-trophy' => 'linear-gradient(135deg, #FFD54F, #FFC107)',
                        'bi-building' => 'linear-gradient(135deg, #FFC107, #FFD54F)',
                        'bi-person-badge' => 'linear-gradient(135deg, #5B9BD5, #7DB8E8)',
                        'bi-book-half' => 'linear-gradient(135deg, #4CAF50, #81C784)',
                        'bi-stars' => 'linear-gradient(135deg, #9C27B0, #BA68C8)',
                    ];
                    $defaultColor = 'linear-gradient(135deg, #2196F3, #64B5F6)';
                    $iconColor = $iconColors[$menu->icon] ?? $defaultColor;
                    
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
                <a href="{{ route('student.counseling.show') }}" class="menu-card">
                    <div class="menu-icon" style="background: linear-gradient(135deg, #FF9800, #FFB347);">
                        <i class="bi bi-person-video3"></i>
                    </div>
                    <h5>Bimbingan PA</h5>
                    <p>Konsultasi dengan dosen pembimbing akademik</p>
                    <span class="status-badge active">Aktif</span>
                </a>
            @endforelse
        </div>
    </div>

    <!-- Announcement Section -->
    <div class="announcement-section">
        <div class="section-header">
            <h3>Pengumuman Terbaru</h3>
            <a href="{{ route('announcements.index') }}" class="view-all">Lihat Semua</a>
        </div>
        
        <div class="announcement-list">
            @forelse(($announcements ?? collect()) as $a)
                <a class="announcement-item" href="{{ route('announcements.show', $a->id) }}" style="text-decoration: none;">
                    <div class="announcement-icon">
                        <i class="bi bi-megaphone-fill"></i>
                    </div>
                    <div class="announcement-content">
                        <h5>{{ $a->title }}</h5>
                        <p>{{ \Illuminate\Support\Str::limit(strip_tags($a->content ?? ''), 90) }}</p>
                        <span class="time">
                            <i class="bi bi-clock"></i>
                            {{ ($a->published_at ?? $a->updated_at ?? $a->created_at)?->diffForHumans() ?? '' }}
                        </span>
                    </div>
                </a>
            @empty
                <div class="announcement-item" style="cursor: default;">
                    <div class="announcement-icon">
                        <i class="bi bi-info-circle-fill"></i>
                    </div>
                    <div class="announcement-content">
                        <h5>Belum ada pengumuman</h5>
                        <p>Pengumuman terbaru akan tampil di sini setelah dipublish.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@endsection

@push('css')
<style>
    /* Schedule Reminder - Natural Design */
    .schedule-reminder {
        background: #ffffff;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 25px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        position: relative;
    }

    .reminder-badge-small {
        position: absolute;
        top: 20px;
        right: 20px;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.3px;
        text-transform: uppercase;
    }

    .badge-blue {
        background: #eff6ff;
        color: #2563eb;
    }

    .badge-red {
        background: #fef2f2;
        color: #dc2626;
    }

    .reminder-content {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .reminder-title-main {
        font-size: 20px;
        font-weight: 600;
        color: #111827;
        margin: 0;
        padding-right: 80px;
    }

    .reminder-info-row {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        font-size: 14px;
        color: #374151;
    }

    .reminder-info-row i {
        color: #6b7280;
        font-size: 18px;
        margin-top: 2px;
        flex-shrink: 0;
    }

    .reminder-info-row strong {
        display: block;
        color: #111827;
        font-weight: 600;
        margin-bottom: 2px;
    }

    .reminder-time-text {
        color: #6b7280;
        font-size: 13px;
    }

    .reminder-text {
        color: #374151;
        line-height: 1.6;
        font-size: 14px;
    }

    .reminder-info-box {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px 16px;
        margin-top: 4px;
    }

    .reminder-info-label {
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }

    .reminder-info-value {
        font-size: 14px;
        color: #374151;
        line-height: 1.5;
    }

    .reminder-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 20px;
        background: #f3f4f6;
        color: #374151;
        text-decoration: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s;
        border: 1px solid #e5e7eb;
        align-self: flex-start;
        margin-top: 4px;
    }

    .reminder-link:hover {
        background: #e5e7eb;
        color: #111827;
        border-color: #d1d5db;
    }

    /* Stats Card */
    .stats-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        box-shadow: var(--shadow);
        margin-bottom: 25px;
        transition: var(--transition-normal);
    }

    .stats-card:hover {
        box-shadow: var(--shadow-hover);
        transform: translateY(-5px);
    }

    .stats-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .stats-header h3 {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-dark);
        margin: 0;
    }

    .semester-badge {
        background: linear-gradient(135deg, var(--primary-orange), #FFB347);
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .stats-content {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
    }

    .stat-item {
        text-align: center;
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-size: 28px;
        color: white;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    }

    .stat-info h5 {
        font-size: 12px;
        color: var(--text-gray);
        margin: 0 0 5px;
    }

    .stat-info p {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0;
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
        .menu-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .stats-content {
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            font-size: 24px;
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

