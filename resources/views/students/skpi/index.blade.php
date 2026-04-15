@extends('students.layouts.super-app')

@section('content')
    <div class="stats-card">
        <div class="stats-header">
            <div>
                <h3>SKPI</h3>
                <p class="header-subtitle">Siapkan data akademik dan prestasi sebelum diajukan untuk verifikasi SKPI.</p>
            </div>
            <div style="display:flex; gap:10px; align-items:center;">
                @if($skpiRegistration && $skpiRegistration->status === 'approved')
                    <a href="{{ route('student.skpi.download-pdf') }}" class="pdf-btn">
                        <i class="bi bi-file-earmark-pdf-fill me-1"></i> Download SKPI
                    </a>
                @endif
                <span class="semester-badge">{{ $student->angkatan ?? 'N/A' }}</span>
            </div>
        </div>
    </div>

    <div class="stats-card">
        <div class="stats-header">
            <div>
                <h3>Ringkasan SKPI</h3>
            </div>
        </div>
        <div class="stats-content">
            <div class="stat-item">
                <div class="stat-icon" style="background: linear-gradient(135deg, #FF9800, #FFB347);">
                    <i class="bi bi-trophy"></i>
                </div>
                <div class="stat-info">
                    <h5>Prestasi Approved</h5>
                    <p>{{ $stats['prestasi_approved'] }}/{{ $stats['prestasi_total'] }}</p>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon" style="background: linear-gradient(135deg, #4CAF50, #81C784);">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div class="stat-info">
                    <h5>IPK</h5>
                    <p>{{ $stats['ipk'] }}</p>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon" style="background: linear-gradient(135deg, #2196F3, #64B5F6);">
                    <i class="bi bi-journal-bookmark"></i>
                </div>
                <div class="stat-info">
                    <h5>SKS</h5>
                    <p>{{ $stats['sks'] }}</p>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon" style="background: linear-gradient(135deg, #9C27B0, #BA68C8);">
                    <i class="bi bi-folder-check"></i>
                </div>
                <div class="stat-info">
                    <h5>Dokumen</h5>
                    <p>{{ $stats['dokumen'] }}/2</p>
                </div>
            </div>
        </div>
    </div>

    <div class="menu-section">
        <div class="section-header">
            <div>
                <h3>Menu Utama</h3>
                <p class="section-subtitle">Lengkapi data pendukung lalu lanjutkan ke menu pendaftaran SKPI.</p>
            </div>
        </div>

        <div class="menu-grid">

            {{-- ===== CARD DAFTAR SKPI (span 2 kolom) ===== --}}
            @if($tugasAkhirReady)
                <a href="{{ route('student.skpi.daftar.index') }}" class="menu-card register-card">
            @else
                <div class="menu-card register-card disabled-card"
                     onclick="showTugasAkhirWarning()" style="cursor:pointer;">
            @endif

                <div class="register-card-top">
                    <div class="menu-icon register-icon">
                        <i class="bi bi-send-check"></i>
                    </div>
                    <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                        @if(!$tugasAkhirReady)
                            <span class="status-badge danger">
                                <i class="bi bi-lock-fill me-1"></i> Tugas Akhir Belum Selesai
                            </span>
                        @else
                            <span class="status-badge {{ $skpiRegistration ? $registrationStatus['badge_class'] : ($registrationMeta['ready'] ? 'active' : 'info') }}">
                                {{ $skpiRegistration ? $registrationStatus['label'] : ($registrationMeta['ready'] ? 'Data Dasar Siap' : 'Mulai Draft') }}
                            </span>
                        @endif

                    </div>
                </div>

                <div class="register-card-content">
                    <div>
                        <h5>Daftar SKPI</h5>
                        <p>
                            @if(!$tugasAkhirReady)
                                Menu ini terkunci. Selesaikan <strong>Tugas Akhir</strong> terlebih dahulu agar bisa mengajukan pendaftaran SKPI.
                            @elseif($skpiRegistration)
                                {{ $registrationStatus['description'] }}
                            @else
                                Masuk ke halaman pendaftaran SKPI untuk mengisi data pemegang SKPI, meninjau draft, dan mengirim pengajuan ke superuser.
                            @endif
                        </p>
                    </div>

                    <div class="register-progress-box {{ !$tugasAkhirReady ? 'locked' : '' }}">
                        <div class="register-progress-head">
                            <strong>{{ $registrationMeta['completed_count'] }}/{{ $registrationMeta['required_count'] }} data sistem siap</strong>
                            <span>
                                @if($skpiRegistration)
                                    Terakhir dikirim {{ $skpiRegistration->submitted_at?->format('d M Y H:i') ?? '-' }}
                                @else
                                    {{ $registrationMeta['ready'] ? 'Lanjut isi draft' : 'Cek data dulu' }}
                                @endif
                            </span>
                        </div>
                        <div class="register-progress-bar">
                            <span style="width: {{ $registrationMeta['required_count'] > 0 ? ($registrationMeta['completed_count'] / $registrationMeta['required_count']) * 100 : 0 }}%;"></span>
                        </div>
                    </div>

                    <div class="register-checklist-preview">
                        @foreach($registrationChecklist as $item)
                            <div class="register-check-item {{ $item['ready'] ? 'is-ready' : 'is-pending' }}">
                                <i class="bi {{ $item['ready'] ? 'bi-check-circle-fill' : 'bi-dash-circle' }}"></i>
                                <span>{{ $item['title'] }}</span>
                                @if(!$item['required'])
                                    <small>Opsional</small>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="register-card-footer">
                        @if(!$tugasAkhirReady)
                            <span style="color:#C62828;">
                                <i class="bi bi-lock-fill me-1"></i>
                                Selesaikan Tugas Akhir dulu
                            </span>
                        @else
                            <span>{{ $skpiRegistration ? 'Lihat status pendaftaran' : 'Buka menu daftar SKPI' }}</span>
                        @endif
                        <i class="bi bi-arrow-right-circle"></i>
                    </div>
                </div>

            @if($tugasAkhirReady)
                </a>
            @else
                </div>
            @endif

            {{-- ===== 4 MENU LAINNYA (masing-masing 1 kolom) ===== --}}
            @foreach($menus as $menu)
                <a href="{{ $menu['href'] }}" class="menu-card">
                    <div class="menu-icon">
                        <i class="{{ $menu['icon'] }}"></i>
                    </div>
                    <h5>{{ $menu['title'] }}</h5>
                    <p>{{ $menu['description'] }}</p>
                    <span class="status-badge {{ $menu['badge_class'] }}">{{ $menu['badge'] }}</span>
                </a>
            @endforeach

        </div>
    </div>

    {{-- Toast warning tugas akhir belum selesai --}}
    <div class="toast-warning" id="taWarning">
        <i class="bi bi-lock-fill"></i>
        <div>
            <strong>Akses Ditolak</strong>
            <p>Selesaikan <strong>Tugas Akhir</strong> terlebih dahulu sebelum bisa mendaftar SKPI.</p>
        </div>
        <button onclick="document.getElementById('taWarning').classList.remove('show')">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

@endsection

@push('css')
<style>
    .stats-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        box-shadow: var(--shadow);
        margin-bottom: 25px;
    }

    .stats-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .stats-header h3 {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-dark);
        margin: 0;
    }

    .header-subtitle,
    .section-subtitle {
        margin: 6px 0 0;
        font-size: 14px;
        color: #666;
    }

    .semester-badge {
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        color: white;
        background: linear-gradient(135deg, var(--primary-orange), #FFB347);
    }

    .stats-content {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
    }

    .stat-item { text-align: center; }

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
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    }

    .stat-info h5 { font-size: 12px; color: var(--text-gray); margin: 0 0 5px; }
    .stat-info p  { font-size: 18px; font-weight: 700; color: var(--text-dark); margin: 0; }

    /* ---- MENU SECTION ---- */
    .menu-section { margin-bottom: 30px; }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .section-header h3 { font-size: 20px; font-weight: 600; color: var(--text-dark); margin: 0; }

    /* Grid: 4 kolom di desktop, register card span 2 */
    .menu-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        align-items: start;
    }

    .register-card {
        grid-column: span 2;
        text-align: left;
        background: linear-gradient(145deg, #FFF8EE, #FFFFFF);
        border: 1px solid rgba(255, 152, 0, 0.15);
    }

    .menu-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        text-decoration: none;
        transition: var(--transition-normal);
        box-shadow: var(--shadow);
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        height: 100%;
    }

    .menu-card:hover { box-shadow: var(--shadow-hover); transform: translateY(-6px); }

    .disabled-card {
        opacity: 0.72;
        position: relative;
    }

    .disabled-card::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 20px;
        background: repeating-linear-gradient(
            -45deg,
            transparent,
            transparent 8px,
            rgba(220, 53, 69, 0.04) 8px,
            rgba(220, 53, 69, 0.04) 16px
        );
        pointer-events: none;
    }

    .menu-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 14px;
        border-radius: 18px;
        background: linear-gradient(135deg, var(--primary-orange), #FFB347);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.13);
        flex-shrink: 0;
    }

    .register-icon { margin: 0; background: linear-gradient(135deg, #FF9800, #FF7043); }

    .menu-card h5 { font-size: 14px; font-weight: 700; color: var(--text-dark); margin: 0 0 8px; }
    .menu-card p  { font-size: 12px; color: var(--text-gray); margin: 0 0 12px; flex: 1; }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }

    .status-badge.info    { background: #E3F2FD; color: #2196F3; }
    .status-badge.warning { background: #FFF3E0; color: #FF9800; }
    .status-badge.active  { background: #E8F5E9; color: #4CAF50; }
    .status-badge.danger  { background: #FFEBEE; color: #C62828; }
    .status-badge.muted   { background: #F5F5F5; color: #757575; }

    /* PDF button badge */
    .pdf-btn {
        background: linear-gradient(135deg, #1565C0, #42A5F5) !important;
        color: white !important;
        text-decoration: none;
        padding: 6px 14px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        transition: opacity 0.2s;
    }
    .pdf-btn:hover { opacity: 0.85; color: white; }

    /* Register card internals */
    .register-card-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 18px;
        flex-wrap: wrap;
    }

    .register-card-content { display: grid; gap: 16px; }

    .register-progress-box {
        padding: 14px 16px;
        border-radius: 16px;
        background: #FFF3E0;
    }

    .register-progress-box.locked { background: #FFEBEE; }

    .register-progress-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 10px;
        font-size: 12px;
        color: #8D6E63;
    }

    .register-progress-head strong { font-size: 13px; color: #E65100; }

    .register-progress-bar {
        height: 10px;
        border-radius: 999px;
        background: rgba(255,152,0,0.15);
        overflow: hidden;
    }

    .register-progress-bar span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(135deg, #FF9800, #FF7043);
    }

    .register-checklist-preview {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .register-check-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 12px;
        border-radius: 14px;
        font-size: 12px;
        background: #FAFAFA;
    }

    .register-check-item i { font-size: 15px; }
    .register-check-item span { flex: 1; color: var(--text-dark); font-weight: 500; }
    .register-check-item small { font-size: 10px; font-weight: 700; color: #1565C0; background: #E3F2FD; padding: 3px 8px; border-radius: 999px; }

    .register-check-item.is-ready  { background: #F1F8E9; color: #2E7D32; }
    .register-check-item.is-ready i { color: #43A047; }
    .register-check-item.is-pending { background: #FFF8E1; }
    .register-check-item.is-pending i { color: #FB8C00; }

    .register-card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        font-size: 13px;
        font-weight: 600;
        color: #E65100;
    }

    .register-card-footer i { font-size: 18px; }

    /* Toast Warning */
    .toast-warning {
        position: fixed;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%) translateY(100px);
        background: white;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.18);
        padding: 18px 22px;
        display: flex;
        align-items: flex-start;
        gap: 14px;
        z-index: 9999;
        min-width: 320px;
        max-width: 480px;
        border-left: 5px solid #C62828;
        opacity: 0;
        transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        pointer-events: none;
    }

    .toast-warning.show {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
        pointer-events: auto;
    }

    .toast-warning > i { font-size: 22px; color: #C62828; margin-top: 2px; }
    .toast-warning > div { flex: 1; }
    .toast-warning strong { display: block; margin-bottom: 4px; color: var(--text-dark); }
    .toast-warning p { margin: 0; font-size: 13px; color: #666; }
    .toast-warning button { background: none; border: none; cursor: pointer; color: #999; font-size: 16px; padding: 0; }

    /* Responsive */
    @media (max-width: 768px) {
        .stats-content { grid-template-columns: repeat(2, 1fr); }
        .menu-grid { grid-template-columns: 1fr; }
        .register-card { grid-column: span 1; }
        .register-checklist-preview { grid-template-columns: 1fr; }
        .register-progress-head { flex-direction: column; align-items: flex-start; }
        .stats-header { flex-direction: column; align-items: flex-start; gap: 10px; }
    }

    @media (min-width: 769px) and (max-width: 1024px) {
        .menu-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .register-card { grid-column: span 2; }
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
