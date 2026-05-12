@extends('students.layouts.super-app')

@section('content')

<div class="ush-dashboard">
    {{-- 1. HERO BANNER --}}
    <div class="ush-hero">
        <div class="ush-hero-inner">
            <div class="ush-hero-text">
                <div class="ush-badge-light">Pengajuan SKPI</div>
                <h1>Portofolio Mahasiswa</h1>
                <p>Lengkapi profil, identitas akademik, dan kelola dokumen pendamping ijazah Anda melalui portal ini.</p>
            </div>
            <div class="ush-hero-meta">
                <span class="ush-prodi"><i class="bi bi-mortarboard"></i> {{ $student->program_studi ?? 'Program Studi' }}</span>
                <span class="ush-status {{ $registrationStatus['badge_class'] }}">{{ $registrationStatus['label'] }}</span>
            </div>
        </div>
    </div>

    {{-- 2. OVERLAPPING STEPPER --}}
    <div class="ush-stepper-container">
        <div class="ush-stepper">
            @php
                $status = $skpiRegistration->status ?? 'draft';
                $hasDoc = $skpiRegistration?->hasGeneratedDocument();
                
                $steps = [
                    ['step' => 1, 'label' => 'Pengisian Data', 'active' => true],
                    ['step' => 2, 'label' => 'Verifikasi', 'active' => in_array($status, ['pending', 'approved', 'rejected'])],
                    ['step' => 3, 'label' => 'Penerbitan', 'active' => ($status === 'approved' && $hasDoc)]
                ];
            @endphp

            @foreach($steps as $index => $step)
            <div class="ush-step {{ $step['active'] ? 'active' : '' }}">
                <div class="ush-step-indicator">
                    @if($step['active'] && $index < 2 && $steps[$index+1]['active'])
                        <i class="bi bi-check-lg"></i>
                    @else
                        {{ $step['step'] }}
                    @endif
                </div>
                <span class="ush-step-label">{{ $step['label'] }}</span>
                @if(!$loop->last)
                <div class="ush-step-line {{ $steps[$index+1]['active'] ? 'active' : '' }}"></div>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    {{-- 3. MAIN DASHBOARD GRID --}}
    <div class="ush-grid">
        
        {{-- LEFT COLUMN: Status & Overview --}}
        <div class="ush-col-main">
            
            {{-- Current Registration Status --}}
            @if($skpiRegistration)
            <div class="ush-card status-card">
                <div class="ush-card-header">
                    <h4><i class="bi bi-clock-history"></i> Detail Status Pengajuan</h4>
                    @if($skpiRegistration->status === 'approved' && $skpiRegistration->hasGeneratedDocument())
                        <a href="{{ route('student.skpi.download-word') }}" class="btn-ush-primary">
                            <i class="bi bi-cloud-arrow-down-fill"></i> Unduh Dokumen
                        </a>
                    @endif
                </div>
                
                <div class="ush-status-grid">
                    <div class="ush-status-item">
                        <small>Status Akun</small>
                        <strong class="{{ $registrationStatus['badge_class'] }}">{{ $registrationStatus['label'] }}</strong>
                    </div>
                    <div class="ush-status-item">
                        <small>Tanggal Kirim</small>
                        <strong>{{ $skpiRegistration->submitted_at?->translatedFormat('d F Y') ?? '-' }}</strong>
                    </div>
                    <div class="ush-status-item">
                        <small>Update Terakhir</small>
                        <strong>{{ $skpiRegistration->updated_at?->translatedFormat('d F Y') ?? '-' }}</strong>
                    </div>
                </div>

                @if($skpiRegistration->status === 'approved' && !$skpiRegistration->hasGeneratedDocument())
                <div class="ush-alert warning">
                    <i class="bi bi-hourglass-split"></i>
                    <div>
                        <strong>Menunggu Penerbitan</strong>
                        <p>Pengajuan Anda telah disetujui. Admin sedang memproses dokumen SKPI fisik Anda.</p>
                    </div>
                </div>
                @endif

                @if($skpiRegistration->approval_notes)
                <div class="ush-alert info">
                    <i class="bi bi-chat-left-text"></i>
                    <div>
                        <strong>Catatan Verifikator</strong>
                        <p>{{ $skpiRegistration->approval_notes }}</p>
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- Checklist Data --}}
            <div class="ush-card">
                <div class="ush-card-header">
                    <h4><i class="bi bi-list-check"></i> Prasyarat Sistem</h4>
                </div>
                <div class="ush-checklist">
                    @foreach($registrationChecklist as $item)
                    <div class="ush-check-item">
                        <div class="check-icon {{ $item['ready'] ? 'ready' : 'pending' }}">
                            <i class="bi {{ $item['ready'] ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill' }}"></i>
                        </div>
                        <div class="check-content">
                            <div class="check-title">
                                <h5>{{ $item['title'] }}</h5>
                                <span class="badge {{ $item['ready'] ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ $item['ready'] ? 'Terpenuhi' : 'Belum Terpenuhi' }}
                                </span>
                            </div>
                            <p>{{ $item['description'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>

                @if($registrationMeta['ready'] && $canEditRegistration && $skpiRegistration)
                <div style="margin-top: 20px; border-top: 1px solid var(--ush-border); padding-top: 20px;">
                    <form action="{{ route('student.skpi.daftar.submit') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-ush-primary" style="width: 100%; justify-content: center; padding: 12px; font-size: 16px;">
                            <i class="bi bi-send-fill"></i> Ajukan SKPI Sekarang
                        </button>
                    </form>
                </div>
                @elseif(!$registrationMeta['ready'] && $canEditRegistration)
                <div style="margin-top: 20px; border-top: 1px solid var(--ush-border); padding-top: 20px;">
                    <div class="ush-alert warning" style="margin-top: 0;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <div>
                            <strong>Belum Bisa Mengajukan</strong>
                            <p>Lengkapi semua Prasyarat Sistem terlebih dahulu (termasuk Form Identitas) agar tombol pengajuan aktif.</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>

        </div>

        {{-- RIGHT COLUMN: Actions & Summary --}}
        <div class="ush-col-side">
            
            {{-- Action Cards --}}
            <div class="ush-card no-padding bg-transparent shadow-none border-0">
                <h4 class="side-title">Menu Utama</h4>
                
                <a href="{{ route('student.skpi.daftar.create') }}" class="ush-action-card">
                    <div class="action-icon orange">
                        <i class="bi bi-person-vcard"></i>
                    </div>
                    <div class="action-text">
                        <h5>{{ $skpiRegistration ? ($canEditRegistration ? 'Update Form Identitas' : 'Lihat Form Identitas') : 'Isi Form Identitas' }}</h5>
                        <p>Kelola Nama, NIM, Nomor Ijazah, dan Gelar.</p>
                    </div>
                    <i class="bi bi-chevron-right action-arrow"></i>
                </a>

                <a href="{{ route('student.personal.achievements.index') }}" class="ush-action-card">
                    <div class="action-icon blue">
                        <i class="bi bi-award"></i>
                    </div>
                    <div class="action-text">
                        <h5>Manajemen Prestasi</h5>
                        <p>Input sertifikat dan aktivitas organisasi.</p>
                    </div>
                    <i class="bi bi-chevron-right action-arrow"></i>
                </a>
            </div>

            {{-- Summary Stats --}}
            <div class="ush-card bg-light-blue mt-4">
                <h4 class="side-title mb-3">Ringkasan Data</h4>
                <div class="ush-stat-list">
                    <div class="stat-row">
                        <span>Kelengkapan Dasar</span>
                        <strong>{{ $registrationMeta['completed_count'] }}/{{ $registrationMeta['required_count'] }}</strong>
                    </div>
                    <div class="stat-row">
                        <span>Form Identitas</span>
                        <strong>{{ $holderMeta['filled_count'] }}/{{ $holderMeta['total_count'] }}</strong>
                    </div>
                    <div class="stat-row">
                        <span>Prestasi Valid</span>
                        <strong>{{ $student->achievements()->where('status', 'approved')->count() }} Item</strong>
                    </div>
                </div>
            </div>

            {{-- Guide Card --}}
            <div class="ush-card mt-4" style="border-left: 4px solid var(--ush-orange);">
                <h4 class="side-title mb-2">Panduan Cepat</h4>
                <ul style="padding-left: 20px; font-size: 12px; color: var(--ush-text-muted); line-height: 1.6;">
                    <li>Pastikan Nama sesuai Ijazah.</li>
                    <li>Gelar otomatis terisi dari Prodi.</li>
                    <li>Review semua prestasi sebelum klik Ajukan.</li>
                    <li>Status akan berubah setelah divalidasi Admin.</li>
                </ul>
            </div>

        </div>
    </div>
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
        --ush-bg-body: #FFF5E6; /* Matching global */
        --ush-border: rgba(41, 55, 93, 0.08);
        --ush-text-dark: #1E293B;
        --ush-text-muted: #64748B;
        --ush-shadow-sm: 0 2px 8px rgba(41, 55, 93, 0.04);
        --ush-shadow-md: 0 8px 24px rgba(41, 55, 93, 0.08);
    }

    .ush-dashboard {
        font-family: 'Poppins', sans-serif;
        color: var(--ush-text-dark);
        margin-bottom: 40px;
    }

    /* --- HERO BANNER --- */
    .ush-hero {
        background: linear-gradient(135deg, var(--ush-navy) 0%, var(--ush-navy-light) 100%);
        border-radius: 20px;
        padding: 40px 40px 80px 40px; /* Extra padding bottom for overlapping stepper */
        margin-bottom: -50px; /* Pulls the stepper up */
        color: white;
        box-shadow: 0 10px 30px rgba(41, 55, 93, 0.15);
        position: relative;
        overflow: hidden;
    }

    .ush-hero::after {
        content: '';
        position: absolute;
        top: 0; right: 0; bottom: 0; left: 0;
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

    .ush-status {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
        background: rgba(255, 255, 255, 0.9);
        color: var(--ush-navy);
    }

    /* --- OVERLAPPING STEPPER --- */
    .ush-stepper-container {
        position: relative;
        z-index: 10;
        padding: 0 20px;
        margin-bottom: 30px;
    }

    .ush-stepper {
        background: var(--ush-surface);
        border-radius: 16px;
        padding: 25px 40px;
        display: flex;
        justify-content: space-between;
        box-shadow: var(--ush-shadow-md);
        border: 1px solid var(--ush-border);
    }

    .ush-step {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
    }

    .ush-step-indicator {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #F1F5F9;
        color: #94A3B8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 16px;
        margin-bottom: 10px;
        transition: all 0.3s;
        border: 2px solid white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        z-index: 2;
    }

    .ush-step-label {
        font-size: 13px;
        font-weight: 600;
        color: var(--ush-text-muted);
    }

    .ush-step-line {
        position: absolute;
        top: 20px;
        left: calc(50% + 20px);
        width: calc(100% - 40px);
        height: 2px;
        background: #F1F5F9;
        z-index: 1;
    }

    .ush-step.active .ush-step-indicator {
        background: var(--ush-navy);
        color: white;
        transform: scale(1.1);
        box-shadow: 0 4px 10px rgba(41, 55, 93, 0.3);
    }

    .ush-step.active .ush-step-label {
        color: var(--ush-navy);
        font-weight: 700;
    }

    .ush-step-line.active {
        background: var(--ush-navy);
    }

    /* --- GRID LAYOUT --- */
    .ush-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 25px;
        align-items: start;
    }

    /* --- CARDS --- */
    .ush-card {
        background: var(--ush-surface);
        border-radius: 16px;
        padding: 25px;
        box-shadow: var(--ush-shadow-sm);
        border: 1px solid var(--ush-border);
        margin-bottom: 25px;
    }

    .ush-card.no-padding { padding: 0; }
    .bg-transparent { background: transparent; }
    .shadow-none { box-shadow: none; }
    .border-0 { border: none; }

    .ush-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid var(--ush-border);
    }

    .ush-card-header h4 {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: var(--ush-navy);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .side-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--ush-text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0 0 15px 0;
    }

    /* --- STATUS GRID (LEFT) --- */
    .ush-status-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
        margin-bottom: 20px;
    }

    .ush-status-item {
        background: #F8FAFC;
        padding: 15px;
        border-radius: 10px;
        border: 1px solid var(--ush-border);
    }

    .ush-status-item small {
        display: block;
        font-size: 11px;
        color: var(--ush-text-muted);
        text-transform: uppercase;
        margin-bottom: 5px;
        font-weight: 600;
    }

    .ush-status-item strong {
        font-size: 15px;
        color: var(--ush-text-dark);
    }

    .status-badge-pending { color: #F59E0B; }
    .status-badge-approved { color: #10B981; }
    .status-badge-rejected { color: #EF4444; }

    /* --- ALERTS --- */
    .ush-alert {
        display: flex;
        gap: 15px;
        padding: 15px;
        border-radius: 10px;
        margin-top: 15px;
    }

    .ush-alert i { font-size: 24px; }
    .ush-alert strong { display: block; font-size: 14px; margin-bottom: 3px; }
    .ush-alert p { margin: 0; font-size: 13px; line-height: 1.5; }

    .ush-alert.warning {
        background: #FFFBEB;
        border-left: 4px solid #F59E0B;
        color: #92400E;
    }

    .ush-alert.info {
        background: #EFF6FF;
        border-left: 4px solid #3B82F6;
        color: #1E3A8A;
    }

    /* --- CHECKLIST --- */
    .ush-checklist {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .ush-check-item {
        display: flex;
        gap: 15px;
        padding: 15px;
        background: #F8FAFC;
        border-radius: 10px;
        border: 1px solid var(--ush-border);
        align-items: center;
    }

    .check-icon { font-size: 24px; }
    .check-icon.ready { color: #10B981; }
    .check-icon.pending { color: #CBD5E1; }

    .check-content { flex: 1; }
    .check-title {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 4px;
    }
    .check-title h5 { margin: 0; font-size: 14px; font-weight: 600; }
    .check-content p { margin: 0; font-size: 12px; color: var(--ush-text-muted); }

    /* --- ACTION CARDS (RIGHT) --- */
    .ush-action-card {
        display: flex;
        align-items: center;
        gap: 15px;
        background: var(--ush-surface);
        padding: 20px;
        border-radius: 12px;
        border: 1px solid var(--ush-border);
        text-decoration: none;
        color: var(--ush-text-dark);
        margin-bottom: 15px;
        transition: all 0.3s;
        box-shadow: var(--ush-shadow-sm);
    }

    .ush-action-card:hover {
        transform: translateX(5px);
        border-color: var(--ush-navy-light);
        box-shadow: var(--ush-shadow-md);
    }

    .action-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: white;
        flex-shrink: 0;
    }

    .action-icon.orange { background: var(--ush-orange); }
    .action-icon.blue { background: var(--ush-navy); }

    .action-text { flex: 1; }
    .action-text h5 { margin: 0 0 4px 0; font-size: 15px; font-weight: 700; color: var(--ush-navy); }
    .action-text p { margin: 0; font-size: 12px; color: var(--ush-text-muted); line-height: 1.4; }
    
    .action-arrow { font-size: 18px; color: var(--ush-text-muted); transition: color 0.3s; }
    .ush-action-card:hover .action-arrow { color: var(--ush-orange); }

    /* --- STATS --- */
    .bg-light-blue {
        background: #F8FAFC;
        border: 1px dashed #CBD5E1;
    }

    .ush-stat-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .stat-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 8px;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    .stat-row:last-child { border-bottom: none; padding-bottom: 0; }
    .stat-row span { font-size: 13px; color: var(--ush-text-muted); }
    .stat-row strong { font-size: 14px; color: var(--ush-navy); }

    /* --- BUTTONS --- */
    .btn-ush-primary {
        background: var(--ush-orange);
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background 0.3s;
    }

    .btn-ush-primary:hover {
        background: var(--ush-orange-hover);
        color: white;
    }

    /* --- RESPONSIVE --- */
    @media (max-width: 992px) {
        .ush-grid { grid-template-columns: 1fr; }
        .ush-hero-inner { flex-direction: column; align-items: flex-start; }
        .ush-hero-meta { align-items: flex-start; }
    }

    @media (max-width: 768px) {
        .ush-hero {
            padding: 30px 20px 70px 20px;
            border-radius: 16px;
        }
        .ush-hero h1 { font-size: 24px; }
        .ush-stepper-container { padding: 0 10px; }
        .ush-stepper { padding: 20px; }
        .ush-step-label { display: none; } /* Hide text on mobile stepper to save space */
        .ush-status-grid { grid-template-columns: 1fr; }
        .ush-card-header { flex-direction: column; align-items: flex-start; gap: 10px; }
    }
</style>
@endpush