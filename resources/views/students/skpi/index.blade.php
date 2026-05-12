@extends('students.layouts.super-app')

@section('content')

<div class="ush-dashboard">
    {{-- 1. HERO BANNER --}}
    <div class="ush-hero">
        <div class="ush-hero-inner">
            <div class="ush-hero-text">
                <div class="ush-badge-light">Dashboard Utama</div>
                <h1>Sistem SKPI</h1>
                <p>Siapkan data akademik dan portofolio prestasi sebelum diajukan untuk verifikasi Surat Keterangan Pendamping Ijazah.</p>
            </div>
            <div class="ush-hero-meta">
                <span class="ush-prodi"><i class="bi bi-mortarboard"></i> Angkatan {{ $student->angkatan ?? 'N/A' }}</span>
                @if($skpiRegistration && $skpiRegistration->status === 'approved' && $skpiRegistration->hasGeneratedDocument())
                <a href="{{ route('student.skpi.download-word') }}" class="btn-ush-primary" style="margin-top: 10px;">
                    <i class="bi bi-file-earmark-word-fill"></i> Download SKPI
                </a>
                @endif
            </div>
        </div>
    </div>

    {{-- 2. OVERLAPPING STATS --}}
    <div class="ush-overlap-container">
        <div class="ush-stats-panel">
            <div class="ush-stat-item">
                <div class="stat-icon orange"><i class="bi bi-trophy"></i></div>
                <div class="stat-info">
                    <small>Prestasi Approved</small>
                    <strong>{{ $stats['prestasi_approved'] }}/{{ $stats['prestasi_total'] }}</strong>
                </div>
            </div>
            <div class="ush-stat-divider"></div>
            <div class="ush-stat-item">
                <div class="stat-icon green"><i class="bi bi-graph-up-arrow"></i></div>
                <div class="stat-info">
                    <small>IPK</small>
                    <strong>{{ $stats['ipk'] }}</strong>
                </div>
            </div>
            <div class="ush-stat-divider"></div>
            <div class="ush-stat-item">
                <div class="stat-icon blue"><i class="bi bi-journal-bookmark"></i></div>
                <div class="stat-info">
                    <small>Total SKS</small>
                    <strong>{{ $stats['sks'] }}</strong>
                </div>
            </div>
            <div class="ush-stat-divider"></div>
            <div class="ush-stat-item">
                <div class="stat-icon purple"><i class="bi bi-folder-check"></i></div>
                <div class="stat-info">
                    <small>Dokumen SKPI</small>
                    <strong>{{ $stats['dokumen'] }}/2</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. STEP BY STEP GUIDE --}}
    <div class="ush-section-header mt-5">
        <h4>Alur Pengajuan SKPI</h4>
        <p>Ikuti langkah-langkah berikut untuk mendapatkan dokumen SKPI Anda.</p>
    </div>

    <div class="ush-stepper-horizontal">
        <div class="ush-guide-step">
            <div class="step-num">1</div>
            <div class="step-content">
                <h6>Persiapan Data</h6>
                <p>Lengkapi Foto, TTD, dan input semua Prestasi Anda.</p>
            </div>
        </div>
        <div class="step-connector"><i class="bi bi-chevron-right"></i></div>
        <div class="ush-guide-step">
            <div class="step-num">2</div>
            <div class="step-content">
                <h6>Isi Form Identitas</h6>
                <p>Isi data diri di menu Daftar SKPI sesuai ijazah terakhir.</p>
            </div>
        </div>
        <div class="step-connector"><i class="bi bi-chevron-right"></i></div>
        <div class="ush-guide-step">
            <div class="step-num">3</div>
            <div class="step-content">
                <h6>Ajukan & Verifikasi</h6>
                <p>Kirim pengajuan dan tunggu proses audit oleh Admin.</p>
            </div>
        </div>
        <div class="step-connector"><i class="bi bi-chevron-right"></i></div>
        <div class="ush-guide-step">
            <div class="step-num">4</div>
            <div class="step-content">
                <h6>Unduh Dokumen</h6>
                <p>Setelah disetujui, unduh dokumen SKPI versi digital.</p>
            </div>
        </div>
    </div>

    {{-- 4. MAIN MENU --}}
    <div class="ush-section-header mt-5">
        <h4>Menu Pendaftaran & Pengelolaan</h4>
        <p>Lengkapi data pendukung di bawah ini sebelum mendaftar SKPI.</p>
    </div>

    <div class="ush-menu-grid">
        {{-- CARD DAFTAR SKPI (Span 2) --}}
        @php
        // Mahasiswa yang sudah punya registrasi SKPI (draft/pending/approved/dll)
        // tetap bisa mengakses halaman daftar SKPI meskipun Tugas Akhir belum memenuhi syarat baru.
        // Ini mencegah mahasiswa lama terkunci setelah ada perubahan persyaratan sistem.
        $isAccessible = (bool)$tugasAkhirReady || (bool)$skpiRegistration;
        $isLockedByTA = !$isAccessible;
        @endphp

        @if($isAccessible)
        <a href="{{ route('student.skpi.daftar.index') }}" class="ush-menu-card ush-card-primary">
            @else
            <div class="ush-menu-card ush-card-primary locked" onclick="showTugasAkhirWarning()" style="cursor:pointer;">
                @endif

                <div class="card-top-bar">
                    <div class="ush-main-icon">
                        <i class="bi bi-send-check"></i>
                    </div>
                    <div>
                        @if($isLockedByTA)
                        <span class="ush-badge-danger"><i class="bi bi-lock-fill"></i> Terkunci</span>
                        @else
                        <span class="ush-badge-{{ $skpiRegistration ? 'info' : ($registrationMeta['ready'] ? 'success' : 'warning') }}">
                            {{ $skpiRegistration ? $registrationStatus['label'] : ($registrationMeta['ready'] ? 'Data Dasar Siap' : 'Mulai Draft') }}
                        </span>
                        @endif
                    </div>
                </div>

                <div class="card-body-content">
                    <div class="card-titles">
                        <h5>Daftar SKPI</h5>
                        <p>
                            @if($isLockedByTA)
                            Menu ini terkunci. Selesaikan <strong>Tugas Akhir</strong> terlebih dahulu agar bisa mengajukan pendaftaran SKPI.
                            @elseif($skpiRegistration)
                            {{ $registrationStatus['description'] }}
                            @else
                            Masuk ke halaman pendaftaran SKPI untuk mengisi data pemegang SKPI, meninjau draft, dan mengirim pengajuan ke admin.
                            @endif
                        </p>
                    </div>

                    <div class="ush-progress-box {{ $isLockedByTA ? 'is-locked' : '' }}">
                        <div class="progress-info">
                            <strong>{{ $registrationMeta['completed_count'] }}/{{ $registrationMeta['required_count'] }} Data Sistem Siap</strong>
                            <span>
                                @if($skpiRegistration)
                                Dikirim: {{ $skpiRegistration->submitted_at?->format('d M Y') ?? '-' }}
                                @else
                                {{ $registrationMeta['ready'] ? 'Lanjut isi draft' : 'Lengkapi data dulu' }}
                                @endif
                            </span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width: {{ $registrationMeta['required_count'] > 0 ? ($registrationMeta['completed_count'] / $registrationMeta['required_count']) * 100 : 0 }}%;"></div>
                        </div>
                    </div>

                    <div class="ush-mini-checklists">
                        @foreach($registrationChecklist as $item)
                        <div class="mini-check {{ $item['ready'] ? 'ready' : 'pending' }}">
                            <i class="bi {{ $item['ready'] ? 'bi-check-circle-fill' : 'bi-dash-circle' }}"></i>
                            <span>{{ $item['title'] }}</span>
                            @if(!$item['required']) <small>Opsional</small> @endif
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="card-bottom-action">
                    @if($isLockedByTA)
                    <span class="text-danger"><i class="bi bi-lock-fill"></i> Selesaikan Tugas Akhir dulu</span>
                    @else
                    <span>{{ $skpiRegistration ? 'Cek Status Pengajuan' : 'Mulai Pendaftaran SKPI' }}</span>
                    @endif
                    <i class="bi bi-arrow-right-circle action-arrow"></i>
                </div>

                @if($isAccessible)
        </a>
        @else
    </div>
    @endif

    {{-- OTHER MENUS (Span 1) --}}
    @foreach($menus as $menu)
    <a href="{{ $menu['href'] }}" class="ush-menu-card standard">
        <div class="menu-icon-circle">
            <i class="{{ $menu['icon'] }}"></i>
        </div>
        <div class="menu-text-content">
            <h5>{{ $menu['title'] }}</h5>
            <p>{{ $menu['description'] }}</p>
        </div>
        <div class="menu-footer">
            @php
            // Map badge classes from old logic to new theme
            $badgeClass = 'info';
            if(strpos($menu['badge_class'], 'danger') !== false) $badgeClass = 'danger';
            if(strpos($menu['badge_class'], 'success') !== false || strpos($menu['badge_class'], 'active') !== false) $badgeClass = 'success';
            if(strpos($menu['badge_class'], 'warning') !== false) $badgeClass = 'warning';
            if(strpos($menu['badge_class'], 'muted') !== false) $badgeClass = 'secondary';
            @endphp
            <span class="ush-badge-{{ $badgeClass }}">{{ $menu['badge'] }}</span>
        </div>
    </a>
    @endforeach

</div>
</div>

{{-- Toast warning tugas akhir belum selesai --}}
<div class="ush-toast" id="taWarning">
    <i class="bi bi-shield-lock-fill"></i>
    <div class="toast-body">
        <strong>Akses Ditolak</strong>
        <p>Selesaikan <strong>Tugas Akhir</strong> terlebih dahulu sebelum bisa mendaftar SKPI.</p>
    </div>
    <button class="toast-close" onclick="document.getElementById('taWarning').classList.remove('show')">
        <i class="bi bi-x-lg"></i>
    </button>
</div>

@endsection

@push('css')
<style>
    /* --- THEME VARIABLES --- */
    :root {
        --ush-navy: #29375d;
        --ush-navy-light: #3a4b7c;
        --ush-orange: #FF9800;
        --ush-orange-hover: #F57C00;
        --ush-surface: #FFFFFF;
        --ush-bg-body: #FFF5E6;
        --ush-border: rgba(41, 55, 93, 0.08);
        --ush-text-dark: #1E293B;
        --ush-text-muted: #64748B;
        --ush-shadow-sm: 0 4px 12px rgba(41, 55, 93, 0.05);
        --ush-shadow-md: 0 10px 25px rgba(41, 55, 93, 0.08);
        --ush-shadow-hover: 0 15px 35px rgba(41, 55, 93, 0.12);
        --font-main: 'Poppins', sans-serif;
    }

    .ush-dashboard {
        font-family: var(--font-main);
        color: var(--ush-text-dark);
        margin-bottom: 50px;
    }

    /* --- HERO BANNER --- */
    .ush-hero {
        background: linear-gradient(135deg, var(--ush-navy) 0%, var(--ush-navy-light) 100%);
        border-radius: 20px;
        padding: 40px 40px 90px 40px;
        /* Extra padding bottom for overlap */
        margin-bottom: -60px;
        /* Pulls the overlap container up */
        color: white;
        box-shadow: 0 10px 30px rgba(41, 55, 93, 0.15);
        position: relative;
        overflow: hidden;
    }

    .ush-hero::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        background: url('data:image/svg+xml;utf8,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="100" cy="0" r="80" fill="%23ffffff" fill-opacity="0.03"/></svg>') no-repeat top right;
        background-size: cover;
        pointer-events: none;
    }

    .ush-hero-inner {
        position: relative;
        z-index: 2;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 20px;
    }

    .ush-hero-text {
        max-width: 600px;
    }

    .ush-badge-light {
        display: inline-block;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(4px);
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 1px;
        margin-bottom: 15px;
        text-transform: uppercase;
    }

    .ush-hero h1 {
        font-size: 32px;
        font-weight: 700;
        margin: 0 0 10px 0;
        letter-spacing: -0.5px;
    }

    .ush-hero p {
        font-size: 15px;
        color: rgba(255, 255, 255, 0.8);
        margin: 0;
        line-height: 1.6;
    }

    .ush-hero-meta {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 12px;
    }

    .ush-prodi {
        background: var(--ush-orange);
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3);
    }

    .btn-ush-primary {
        background: linear-gradient(135deg, #2B5797, #4A90D9);
        color: white;
        padding: 10px 18px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: opacity 0.3s;
        box-shadow: 0 4px 12px rgba(43, 87, 151, 0.3);
    }

    .btn-ush-primary:hover {
        opacity: 0.9;
        color: white;
    }

    /* --- OVERLAPPING STATS --- */
    .ush-overlap-container {
        position: relative;
        z-index: 10;
        padding: 0 20px;
        margin-bottom: 40px;
    }

    .ush-stats-panel {
        background: var(--ush-surface);
        border-radius: 16px;
        padding: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: var(--ush-shadow-md);
        border: 1px solid var(--ush-border);
    }

    .ush-stat-item {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 0 15px;
    }

    .ush-stat-divider {
        width: 1px;
        height: 40px;
        background: var(--ush-border);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        color: white;
        flex-shrink: 0;
    }

    .stat-icon.orange {
        background: linear-gradient(135deg, #FF9800, #F57C00);
    }

    .stat-icon.green {
        background: linear-gradient(135deg, #10B981, #059669);
    }

    .stat-icon.blue {
        background: linear-gradient(135deg, #3B82F6, #2563EB);
    }

    .stat-icon.purple {
        background: linear-gradient(135deg, #8B5CF6, #6D28D9);
    }

    .stat-info small {
        display: block;
        font-size: 12px;
        color: var(--ush-text-muted);
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
        margin-bottom: 2px;
    }

    .stat-info strong {
        font-size: 22px;
        color: var(--ush-text-dark);
        line-height: 1;
    }

    /* --- SECTION HEADER --- */
    .ush-section-header {
        margin: 0 20px 20px 20px;
    }

    .ush-section-header h4 {
        margin: 0 0 5px 0;
        font-size: 20px;
        font-weight: 700;
        color: var(--ush-navy);
    }

    .ush-section-header p {
        margin: 0;
        font-size: 14px;
        color: var(--ush-text-muted);
    }

    /* --- MENU GRID --- */
    .ush-menu-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        padding: 0 20px;
    }

    .ush-menu-card {
        background: var(--ush-surface);
        border-radius: 16px;
        padding: 24px;
        text-decoration: none;
        color: var(--ush-text-dark);
        border: 1px solid var(--ush-border);
        box-shadow: var(--ush-shadow-sm);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
    }

    .ush-menu-card:hover:not(.locked) {
        transform: translateY(-5px);
        box-shadow: var(--ush-shadow-hover);
        border-color: rgba(41, 55, 93, 0.2);
    }

    .ush-menu-card.locked {
        opacity: 0.8;
        background: #FAFAFA;
        border-color: #E2E8F0;
    }

    .ush-card-primary {
        grid-column: span 3;
        background: linear-gradient(to bottom right, #FFFFFF, #F8FAFC);
        border-top: 4px solid var(--ush-orange);
    }

    /* Register Card Specifics */
    .card-top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .ush-main-icon {
        width: 56px;
        height: 56px;
        background: rgba(255, 152, 0, 0.1);
        color: var(--ush-orange);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
    }

    .card-body-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .card-titles h5 {
        margin: 0 0 8px 0;
        font-size: 18px;
        font-weight: 700;
        color: var(--ush-navy);
    }

    .card-titles p {
        margin: 0;
        font-size: 13px;
        color: var(--ush-text-muted);
        line-height: 1.6;
    }

    .ush-progress-box {
        background: white;
        border: 1px solid var(--ush-border);
        border-radius: 12px;
        padding: 16px;
    }

    .ush-progress-box.is-locked {
        background: #FEF2F2;
        border-color: #FECACA;
    }

    .progress-info {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        margin-bottom: 10px;
    }

    .progress-info strong {
        color: var(--ush-navy);
    }

    .progress-info span {
        color: var(--ush-text-muted);
    }

    .progress-track {
        height: 8px;
        background: #F1F5F9;
        border-radius: 99px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--ush-orange), #FFB347);
        border-radius: 99px;
    }

    .ush-mini-checklists {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .mini-check {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        color: var(--ush-text-dark);
    }

    .mini-check i {
        font-size: 16px;
    }

    .mini-check.ready i {
        color: #10B981;
    }

    .mini-check.pending i {
        color: #CBD5E1;
    }

    .mini-check small {
        font-size: 10px;
        background: #E2E8F0;
        padding: 2px 6px;
        border-radius: 4px;
        color: #64748B;
    }

    .card-bottom-action {
        margin-top: 25px;
        padding-top: 15px;
        border-top: 1px solid var(--ush-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 14px;
        font-weight: 600;
        color: var(--ush-orange);
    }

    .ush-menu-card:hover .action-arrow {
        transform: translateX(5px);
    }

    .action-arrow {
        transition: transform 0.3s;
        font-size: 20px;
    }

    .text-danger {
        color: #EF4444;
    }

    /* Standard Cards */
    .ush-menu-card.standard {
        align-items: center;
        text-align: center;
    }

    .menu-icon-circle {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: rgba(41, 55, 93, 0.05);
        color: var(--ush-navy);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        margin-bottom: 20px;
        transition: all 0.3s;
    }

    .ush-menu-card.standard:hover .menu-icon-circle {
        background: var(--ush-navy);
        color: white;
        transform: scale(1.05);
    }

    .menu-text-content {
        flex: 1;
        margin-bottom: 20px;
    }

    .menu-text-content h5 {
        margin: 0 0 10px 0;
        font-size: 16px;
        font-weight: 700;
        color: var(--ush-navy);
    }

    .menu-text-content p {
        margin: 0;
        font-size: 13px;
        color: var(--ush-text-muted);
        line-height: 1.5;
    }

    /* Badges */
    [class^="ush-badge-"] {
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
    }

    .ush-badge-success {
        background: #ECFDF5;
        color: #059669;
    }

    .ush-badge-warning {
        background: #FFFBEB;
        color: #D97706;
    }

    .ush-badge-danger {
        background: #FEF2F2;
        color: #DC2626;
    }

    .ush-badge-info {
        background: #EFF6FF;
        color: #2563EB;
    }

    .ush-badge-secondary {
        background: #F1F5F9;
        color: #475569;
    }

    /* --- TOAST --- */
    .ush-toast {
        position: fixed;
        bottom: 30px;
        left: 50%;
        transform: translate(-50%, 100px);
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        padding: 16px 20px;
        display: flex;
        align-items: flex-start;
        gap: 15px;
        z-index: 9999;
        min-width: 320px;
        border-left: 4px solid #EF4444;
        opacity: 0;
        transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        pointer-events: none;
    }

    .ush-toast.show {
        opacity: 1;
        transform: translate(-50%, 0);
        pointer-events: auto;
    }

    .ush-toast>i {
        font-size: 24px;
        color: #EF4444;
    }

    .toast-body {
        flex: 1;
    }

    .toast-body strong {
        display: block;
        font-size: 15px;
        color: var(--ush-text-dark);
        margin-bottom: 4px;
    }

    .toast-body p {
        margin: 0;
        font-size: 13px;
        color: var(--ush-text-muted);
        line-height: 1.4;
    }

    .toast-close {
        background: none;
        border: none;
        font-size: 18px;
        color: #94A3B8;
        cursor: pointer;
        padding: 0;
    }

    .toast-close:hover {
        color: #475569;
    }

    /* --- STEPPER HORIZONTAL GUIDE --- */
    .ush-stepper-horizontal {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 20px;
        margin-bottom: 30px;
        gap: 10px;
    }

    .ush-guide-step {
        flex: 1;
        background: white;
        padding: 20px;
        border-radius: 16px;
        border: 1px solid var(--ush-border);
        box-shadow: var(--ush-shadow-sm);
        display: flex;
        align-items: flex-start;
        gap: 15px;
        transition: all 0.3s ease;
    }

    .ush-guide-step:hover {
        border-color: var(--ush-orange);
        transform: translateY(-3px);
    }

    .step-num {
        width: 32px;
        height: 32px;
        background: var(--ush-navy);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
        flex-shrink: 0;
    }

    .step-content h6 {
        margin: 0 0 5px 0;
        font-size: 14px;
        font-weight: 700;
        color: var(--ush-navy);
    }

    .step-content p {
        margin: 0;
        font-size: 11px;
        color: var(--ush-text-muted);
        line-height: 1.4;
    }

    .step-connector {
        color: #CBD5E1;
        font-size: 20px;
    }

    /* --- RESPONSIVE --- */
    @media (max-width: 1200px) {
        .step-connector {
            display: none;
        }

        .ush-stepper-horizontal {
            flex-wrap: wrap;
            gap: 15px;
        }

        .ush-guide-step {
            min-width: calc(50% - 15px);
        }
    }

    @media (max-width: 1024px) {
        .ush-stats-panel {
            flex-wrap: wrap;
            gap: 20px;
        }

        .ush-stat-item {
            min-width: 45%;
        }

        .ush-stat-divider {
            display: none;
        }

        .ush-menu-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .ush-card-primary {
            grid-column: span 2;
        }
    }

    @media (max-width: 768px) {
        .ush-hero {
            padding: 30px 20px 80px 20px;
            text-align: center;
            border-radius: 0 0 20px 20px;
            margin-bottom: -40px;
        }

        .ush-hero-inner {
            flex-direction: column;
            align-items: center;
        }

        .ush-hero-meta {
            align-items: center;
        }

        .ush-overlap-container {
            padding: 0 15px;
        }

        .ush-stat-item {
            min-width: 100%;
            padding: 0;
        }

        .ush-stepper-horizontal {
            flex-direction: column;
            align-items: stretch;
        }

        .ush-guide-step {
            min-width: 100%;
        }

        .ush-menu-grid {
            grid-template-columns: 1fr;
            padding: 0 15px;
        }

        .ush-card-primary {
            grid-column: span 1;
        }

        .ush-mini-checklists {
            grid-template-columns: 1fr;
        }

        .ush-section-header {
            margin: 0 15px 20px 15px;
            text-align: center;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    function showTugasAkhirWarning() {
        const toast = document.getElementById('taWarning');
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 4000);
    }
</script>
@endpush