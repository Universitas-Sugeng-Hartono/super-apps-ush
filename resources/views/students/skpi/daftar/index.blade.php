@extends('students.layouts.super-app')

@section('content')

{{-- Hero --}}
<div class="hero-card">
    <div>
        <span class="hero-eyebrow">Pendaftaran SKPI</span>
        <h3>Pengajuan SKPI</h3>
        <p>pintu masuk untuk mengisi data pemegang SKPI, memeriksa kelengkapan data, dan mengirim pengajuan SKPI.</p>
    </div>
    <div class="hero-side">
        <span class="hero-badge">{{ $student->program_studi ?? 'Program Studi' }}</span>
        <span class="status-badge {{ $registrationStatus['badge_class'] }}">{{ $registrationStatus['label'] }}</span>
    </div>
</div>

{{-- Summary Cards --}}
<div class="summary-grid">
    <div class="summary-card">
        <div class="summary-icon" style="background: linear-gradient(135deg, #FF9800, #FFB347);">
            <i class="bi bi-clipboard-check"></i>
        </div>
        <div>
            <h5>Data Sistem Siap</h5>
            <strong>{{ $registrationMeta['completed_count'] }}/{{ $registrationMeta['required_count'] }}</strong>
            <p>{{ $registrationMeta['ready'] ? 'Data dasar SKPI sudah siap dilanjutkan ke draft.' : 'Masih ada data dasar yang perlu dicek.' }}</p>
        </div>
    </div>

    <div class="summary-card">
        <div class="summary-icon" style="background: linear-gradient(135deg, #4CAF50, #81C784);">
            <i class="bi bi-person-vcard"></i>
        </div>
        <div>
            <h5>Form Pemegang SKPI</h5>
            <strong>{{ $holderMeta['filled_count'] }}/{{ $holderMeta['total_count'] }}</strong>
            <p>{{ $holderMeta['complete'] ? 'Semua field identitas pemegang SKPI sudah terisi di draft.' : 'Nomor ijazah atau gelar masih bisa Anda lengkapi di form draft.' }}</p>
        </div>
    </div>
</div>

{{-- Status Pengajuan (hanya jika sudah ada registrasi) --}}
@if($skpiRegistration)
<div class="content-card current-card">
    <div class="section-header">
        <div>
            <h4>Status Pengajuan Saat Ini</h4>
            <p>{{ $registrationStatus['description'] }}</p>
        </div>
        {{-- Tombol download juga di sini sebagai shortcut --}}
        @if($skpiRegistration->status === 'approved')
        <a href="{{ route('student.skpi.download-pdf') }}" class="btn-download-skpi">
            <i class="bi bi-file-earmark-pdf-fill"></i> Download SKPI
        </a>
        @endif
    </div>

    <div class="current-grid">
        <div class="current-item">
            <span>Dikirim Pada</span>
            <strong>{{ $skpiRegistration->submitted_at?->format('d M Y H:i') ?? '-' }}</strong>
        </div>
        <div class="current-item">
            <span>Direview Oleh</span>
            <strong>{{ $skpiRegistration->approver->name ?? 'Belum direview' }}</strong>
        </div>
        <div class="current-item">
            <span>Review Terakhir</span>
            <strong>{{ $skpiRegistration->approved_at?->format('d M Y H:i') ?? '-' }}</strong>
        </div>
    </div>

    @if($skpiRegistration->approval_notes)
    <div class="review-note">
        <i class="bi bi-chat-left-text"></i>
        <div>
            <h5>Catatan Superuser</h5>
            <p>{{ $skpiRegistration->approval_notes }}</p>
        </div>
    </div>
    @endif

    {{-- Banner approved --}}
    @if($skpiRegistration->status === 'approved')
    <div class="approved-banner">
        <i class="bi bi-patch-check-fill"></i>
        <div>
            <strong>SKPI Anda sudah disetujui!</strong>
            <p>Klik tombol <strong>Download SKPI</strong> untuk mengunduh dokumen SKPI Anda dalam format PDF.</p>
        </div>
    </div>
    @endif
</div>
@endif

{{-- Aksi --}}
<div class="action-section">
    <div class="section-header">
        <div>
            <h4>Langkah Berikutnya</h4>
            <p>Pilih aksi yang ingin Anda lakukan pada draft pendaftaran SKPI.</p>
        </div>
    </div>

    <div class="action-grid">
        <a href="{{ route('student.skpi.daftar.create') }}" class="action-card primary">
            <div class="action-icon">
                <i class="bi bi-ui-checks-grid"></i>
            </div>
            <h5>{{ $skpiRegistration ? ($canEditRegistration ? 'Perbarui Form Pendaftaran' : 'Lihat Form Tersimpan') : 'Isi Form Data Pemegang SKPI' }}</h5>
            <p>Form isian identitas pemegang SKPI seperti nama, NIM, nomor ijazah, dan gelar.</p>
            <span>{{ $skpiRegistration ? ($canEditRegistration ? 'Edit & simpan ulang' : 'Lihat detail form') : 'Masuk ke form' }}</span>
        </a>

        <a href="{{ $skpiRegistration ? route('student.skpi.daftar.show') : route('student.skpi.daftar.create') }}" class="action-card">
            <div class="action-icon">
                <i class="bi bi-file-earmark-text"></i>
            </div>
            <h5>{{ $skpiRegistration ? 'Lihat Pengajuan Tersimpan' : 'Mulai Isi Draft' }}</h5>
            <p>{{ $skpiRegistration ? 'Tinjau hasil data pemegang SKPI yang sudah tersimpan dan cek status review terbarunya.' : 'Anda belum punya pengajuan tersimpan. Buka form untuk mulai mengisi draft pendaftaran.' }}</p>
            <span>{{ $skpiRegistration ? 'Buka halaman show' : 'Buka form dulu' }}</span>
        </a>


    </div>
</div>

{{-- Checklist --}}
<div class="content-card">
    <div class="section-header">
        <div>
            <h4>Cek Kelengkapan Data Dasar</h4>
        </div>
    </div>

    <div class="checklist-list">
        @foreach($registrationChecklist as $item)
        <div class="checklist-item">
            <div class="checklist-icon {{ $item['ready'] ? 'ready' : 'pending' }}">
                <i class="bi {{ $item['ready'] ? 'bi-check-lg' : 'bi-exclamation-lg' }}"></i>
            </div>
            <div class="checklist-body">
                <div class="checklist-title-row">
                    <h5>{{ $item['title'] }}</h5>
                    <span class="status-badge {{ $item['ready'] ? 'active' : ($item['required'] ? 'warning' : 'info') }}">
                        {{ $item['ready'] ? 'Siap' : ($item['required'] ? 'Perlu Dicek' : 'Opsional') }}
                    </span>
                </div>
                <p>{{ $item['description'] }}</p>
            </div>
        </div>
        @endforeach
    </div>
</div>

@endsection

@push('css')
<style>
    .hero-card,
    .content-card,
    .summary-card,
    .action-card {
        background: white;
        border-radius: 20px;
        box-shadow: var(--shadow);
    }

    .hero-card {
        padding: 24px;
        margin-bottom: 24px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 18px;
        background: linear-gradient(135deg, #FFF8EE, #FFFFFF);
    }

    .hero-eyebrow {
        display: inline-flex;
        padding: 5px 12px;
        border-radius: 999px;
        background: #FFF3E0;
        color: #E65100;
        font-size: 11px;
        font-weight: 700;
        margin-bottom: 12px;
    }

    .hero-card h3 {
        margin: 0 0 8px;
        color: var(--text-dark);
        font-weight: 700;
    }

    .hero-card p {
        margin: 0;
        color: #666;
        line-height: 1.7;
        font-size: 14px;
    }

    .hero-badge {
        white-space: nowrap;
        padding: 7px 14px;
        border-radius: 999px;
        background: linear-gradient(135deg, var(--primary-orange), #FFB347);
        color: white;
        font-size: 12px;
        font-weight: 700;
    }

    .hero-side {
        display: flex;
        flex-direction: column;
        gap: 10px;
        align-items: flex-end;
        flex-shrink: 0;
    }

    /* Download Button */
    .btn-download-skpi {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 12px;
        background: linear-gradient(135deg, #1565C0, #42A5F5);
        color: white;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        transition: opacity 0.2s;
        white-space: nowrap;
    }

    .btn-download-skpi:hover {
        opacity: 0.88;
        color: white;
    }

    /* Summary */
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .summary-card {
        padding: 20px;
        display: flex;
        gap: 16px;
        align-items: flex-start;
    }

    .summary-icon {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
        flex-shrink: 0;
    }

    .summary-card h5 {
        margin: 0 0 6px;
        color: var(--text-dark);
        font-weight: 700;
    }

    .summary-card strong {
        display: block;
        margin-bottom: 8px;
        color: #E65100;
        font-size: 26px;
        line-height: 1;
    }

    .summary-card p {
        margin: 0;
        color: #666;
        line-height: 1.7;
        font-size: 14px;
    }

    /* Current Status Card */
    .action-section,
    .content-card {
        margin-bottom: 24px;
    }

    .content-card {
        padding: 22px;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 18px;
        gap: 16px;
    }

    .section-header h4 {
        margin: 0 0 4px;
        color: var(--text-dark);
        font-weight: 700;
    }

    .section-header p {
        margin: 0;
        color: #666;
        font-size: 14px;
    }

    .current-card {
        margin-bottom: 24px;
    }

    .current-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .current-item {
        padding: 14px 16px;
        border-radius: 16px;
        background: #FAFAFA;
        border: 1px solid #F1F1F1;
    }

    .current-item span {
        display: block;
        margin-bottom: 6px;
        font-size: 12px;
        color: #777;
    }

    .current-item strong {
        color: var(--text-dark);
        font-size: 14px;
    }

    .review-note {
        margin-top: 16px;
        padding: 16px;
        border-radius: 16px;
        background: #FFF8E1;
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }

    .review-note i {
        color: #E65100;
        font-size: 18px;
        margin-top: 2px;
    }

    .review-note h5 {
        margin: 0 0 4px;
        color: #795548;
    }

    .review-note p {
        color: #795548;
        margin: 0;
        font-size: 14px;
    }

    /* Approved Banner */
    .approved-banner {
        margin-top: 16px;
        padding: 16px 20px;
        border-radius: 16px;
        background: linear-gradient(135deg, #E8F5E9, #F1F8E9);
        border: 1px solid #A5D6A7;
        display: flex;
        align-items: flex-start;
        gap: 14px;
        color: #2E7D32;
    }

    .approved-banner i {
        font-size: 26px;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .approved-banner strong {
        display: block;
        font-size: 15px;
        margin-bottom: 4px;
    }

    .approved-banner p {
        margin: 0;
        font-size: 13px;
        color: #388E3C;
    }

    /* Action Cards */
    .action-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .action-card {
        padding: 22px;
        text-decoration: none;
        transition: var(--transition-normal);
        display: block;
        border-radius: 20px;
    }

    .action-card:hover {
        transform: translateY(-6px);
        box-shadow: var(--shadow-hover);
    }

    .action-card.primary {
        background: linear-gradient(135deg, #FFF3E0, #FFFFFF);
        border: 1px solid rgba(255, 152, 0, 0.18);
    }

    /* Download Card */
    .action-card.download-card {
        background: linear-gradient(135deg, #E3F2FD, #FFFFFF);
        border: 1px solid rgba(21, 101, 192, 0.15);
        grid-column: span 2;
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 20px 24px;
    }

    .action-icon {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 26px;
        flex-shrink: 0;
        margin-bottom: 16px;
        background: linear-gradient(135deg, var(--primary-orange), #FF7043);
        box-shadow: 0 8px 20px rgba(255, 152, 0, 0.2);
    }

    .download-icon {
        background: linear-gradient(135deg, #1565C0, #42A5F5) !important;
        box-shadow: 0 8px 20px rgba(21, 101, 192, 0.2) !important;
        margin-bottom: 0;
    }

    .action-card h5 {
        margin: 0 0 8px;
        color: var(--text-dark);
        font-weight: 700;
    }

    .action-card p {
        margin: 0;
        color: #666;
        font-size: 14px;
        line-height: 1.6;
    }

    .action-card span {
        display: inline-flex;
        margin-top: 14px;
        color: #E65100;
        font-size: 13px;
        font-weight: 700;
    }

    .download-card span {
        color: #1565C0;
        margin-top: 8px;
    }

    .download-card .action-card-body {
        flex: 1;
    }

    /* Status Badge */
    .status-badge {
        display: inline-flex;
        padding: 5px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-badge.active {
        background: #E8F5E9;
        color: #2E7D32;
    }

    .status-badge.warning {
        background: #FFF3E0;
        color: #E65100;
    }

    .status-badge.info {
        background: #E3F2FD;
        color: #1565C0;
    }

    .status-badge.danger {
        background: #FFEBEE;
        color: #C62828;
    }

    .status-badge.muted {
        background: #F5F5F5;
        color: #757575;
    }

    /* Checklist */
    .checklist-list {
        display: grid;
        gap: 14px;
    }

    .checklist-item {
        display: flex;
        gap: 14px;
        padding: 16px;
        border-radius: 18px;
        border: 1px solid #F1F1F1;
        background: #FCFCFC;
    }

    .checklist-icon {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .checklist-icon.ready {
        background: #E8F5E9;
        color: #2E7D32;
    }

    .checklist-icon.pending {
        background: #FFF3E0;
        color: #E65100;
    }

    .checklist-body {
        flex: 1;
    }

    .checklist-title-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 4px;
    }

    .checklist-body h5 {
        margin: 0 0 4px;
        color: var(--text-dark);
        font-weight: 700;
    }

    .checklist-body p {
        margin: 0;
        color: #666;
        font-size: 14px;
        line-height: 1.6;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .hero-card {
            flex-direction: column;
        }

        .hero-side {
            align-items: flex-start;
        }

        .hero-badge {
            white-space: normal;
        }

        .summary-grid,
        .action-grid,
        .current-grid {
            grid-template-columns: 1fr;
        }

        .action-card.download-card {
            grid-column: span 1;
            flex-direction: column;
        }

        .checklist-item,
        .checklist-title-row {
            flex-direction: column;
        }
    }
</style>
@endpush