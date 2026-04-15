@extends('students.layouts.super-app')

@section('content')
@php
function prepareJsOptions() {
$dict = \App\Services\SkpPointCalculator::getDictionary();
$activityTypes = [];
$levelOptions = [];
$roleOptions = [];
$typeCategoryMap = [];
$pointsTable = [];

foreach ($dict as $catKey => $catData) {
foreach ($catData['types'] as $typeKey => $typeData) {
$activityTypes[$catKey][$typeKey] = $typeData['label'];
$levelOptions[$typeKey] = $typeData['levels'];
$roleOptions[$typeKey] = $typeData['roles'];
$typeCategoryMap[$typeKey] = $catKey;
$pointsTable[$typeKey] = $typeData['points'];
}
}
return compact('activityTypes', 'levelOptions', 'roleOptions', 'typeCategoryMap', 'pointsTable');
}
extract(prepareJsOptions());
@endphp

@if (session('success'))
<div class="toast-notification success show" id="toastNotification">
    <div class="toast-icon"><i class="bi bi-check-circle-fill"></i></div>
    <div class="toast-content">
        <h6>Berhasil!</h6>
        <p>{{ session('success') }}</p>
    </div>
    <button type="button" class="toast-close" onclick="closeToast()"><i class="bi bi-x"></i></button>
</div>
@endif

@if (session('error'))
<div class="toast-notification error show" id="toastNotification">
    <div class="toast-icon"><i class="bi bi-exclamation-circle-fill"></i></div>
    <div class="toast-content">
        <h6>Gagal!</h6>
        <p>{{ session('error') }}</p>
    </div>
    <button type="button" class="toast-close" onclick="closeToast()"><i class="bi bi-x"></i></button>
</div>
@endif

<div class="exclusive-container">

    {{-- PREMIUM HEADER --}}
    <div class="page-header-simple">
        <div class="header-info">
            <h1 class="page-title-simple">Prestasi & Aktivitas Mahasiswa</h1>
            <p class="page-subtitle-simple">Kelola seluruh catatan capaian ekstrakurikuler dan prestasi Anda untuk kebutuhan SKPI.</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('student.skpi.index') }}" class="btn-simple-outline">
                <i class="bi bi-arrow-left"></i> Kembali ke SKPI
            </a>
            @if ($student->is_edited)
            <button type="button" class="btn-simple-primary" onclick="document.getElementById('skpiFormCard').scrollIntoView({behavior: 'smooth'})">
                <i class="bi bi-plus-lg"></i> Tambah Baru
            </button>
            @endif
        </div>
    </div>

    {{-- ANALYTICS GRID --}}
    <div class="analytics-grid">
        <div class="analytic-card total">
            <div class="card-icon"><i class="bi bi-folder2-open"></i></div>
            <div class="card-info">
                <span>Total Koleksi</span>
                <strong>{{ $student->achievements_count ?? 0 }}</strong>
            </div>
        </div>
        <div class="analytic-card approved">
            <div class="card-icon"><i class="bi bi-patch-check"></i></div>
            <div class="card-info">
                <span>Terverifikasi</span>
                <strong>{{ $student->approved_achievements_count ?? 0 }}</strong>
            </div>
        </div>
        <div class="analytic-card pending">
            <div class="card-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="card-info">
                <span>Menunggu Review</span>
                <strong>{{ $student->pending_achievements_count ?? 0 }}</strong>
            </div>
        </div>
        <div class="analytic-card skp-total">
            <div class="card-label">TOTAL SKP SAAT INI</div>
            <div class="card-value">{{ $student->achievements->where('status', 'approved')->sum('skp_points') }}</div>
            <div class="card-footer">Hanya dari data terverifikasi</div>
        </div>
    </div>

    {{-- MAIN CONTENT AREA --}}
    <div class="main-content-layout">

        @if ($student->is_edited)
        {{-- EXCLUSIVE INPUT SECTION --}}
        <div class="exclusive-card input-section" id="skpiFormSection">
            <div class="exclusive-card-header">
                <div class="header-icon"><i class="bi bi-pencil-square"></i></div>
                <div class="header-text">
                    <h3>Input Prestasi Baru</h3>
                    <p>Lengkapi formulir di bawah ini dengan data yang valid.</p>
                </div>
            </div>

            <form action="{{ route('student.personal.achievement.store') }}" method="POST" enctype="multipart/form-data" id="skpiForm" class="exclusive-form">
                @csrf
                <input type="hidden" id="category" name="category" value="{{ old('category') }}">

                <div class="form-wizard-steps">
                    <div class="wizard-step active" id="step1">
                        <div class="step-num">01</div>
                        <div class="step-label">Kegiatan</div>
                    </div>
                    <div class="wizard-separator"></div>
                    <div class="wizard-step" id="step2">
                        <div class="step-num">02</div>
                        <div class="step-label">Tingkat</div>
                    </div>
                    <div class="wizard-separator"></div>
                    <div class="wizard-step" id="step3">
                        <div class="step-num">03</div>
                        <div class="step-label">Verifikasi</div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-12">
                        <div class="form-floating-custom">
                            <label class="custom-label">Jenis Kegiatan Akademik/Non-Akademik</label>
                            <select id="activity_type" name="activity_type" class="custom-select" required>
                                <option value="">— Pilih Jenis Kegiatan —</option>
                                @foreach(\App\Models\StudentAchievement::manualCategoryOptions() as $cat => $catLabel)
                                @if(isset($activityTypes[$cat]))
                                <optgroup label="{{ $catLabel }}">
                                    @foreach($activityTypes[$cat] as $type => $typeLabel)
                                    <option value="{{ $type }}" {{ old('activity_type') === $type ? 'selected' : '' }}>
                                        {{ $typeLabel }}
                                    </option>
                                    @endforeach
                                </optgroup>
                                @endif
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6" id="rowTingkat">
                        <div class="form-floating-custom">
                            <label class="custom-label">Cakupan / Tingkat</label>
                            <select id="level" name="level" class="custom-select" required>
                                <option value="">— Pilih Tingkat —</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6" id="rowJabatan">
                        <div class="form-floating-custom">
                            <label class="custom-label" id="jabatanLabel">Jabatan / Peran / Prestasi</label>
                            <select id="participation_role" name="participation_role" class="custom-select">
                                <option value="">— Pilih —</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="upload-container-exclusive" id="uploadArea">
                            <input type="file" id="certificate" name="certificate" accept=".pdf,.jpg,.jpeg,.png" onchange="previewFile(this)">
                            <div class="upload-overlay" id="uploadPlaceholder">
                                <div class="pulse-icon"><i class="bi bi-cloud-arrow-up"></i></div>
                                <h4>Unggah Bukti Pendukung</h4>
                                <p>Tarik file ke sini atau klik untuk mencari file</p>
                                <div class="upload-types">PDF, JPG, PNG (Maks. 5 MB)</div>
                            </div>
                            <div class="upload-result-exclusive" id="uploadPreview" style="display:none">
                                <i class="bi bi-file-earmark-code"></i>
                                <div class="file-details">
                                    <span id="uploadFileName">filename.pdf</span>
                                    <small>Siap untuk diunggah</small>
                                </div>
                                <button type="button" class="btn-clear-file" onclick="clearFile()"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- POINT DISPLAY --}}
                <div id="skpPreviewRow" class="points-badge-exclusive" style="display:none">
                    <div class="badge-icon-exclusive"><i class="bi bi-lightning-charge-fill"></i></div>
                    <div class="badge-content-exclusive">
                        <span class="label">ESTIMASI POIN SKP</span>
                        <span class="value" id="skpPreviewValue">0</span>
                    </div>
                </div>

                <div class="form-footer-exclusive">
                    <button type="submit" class="btn-exclusive-submit">
                        <i class="bi bi-check2-circle"></i> Simpan Data Prestasi
                    </button>
                </div>
            </form>
        </div>
        @else
        <div class="locked-profile-card">
            <div class="locked-icon"><i class="bi bi-shield-lock-fill"></i></div>
            <h3>Data Profil Terkunci</h3>
            <p>Menu input dinonaktifkan sementara karena data Anda sedang dalam proses verifikasi atau telah dikunci oleh bagian Akademik. Hubungi Dosen Pembimbing untuk instruksi lebih lanjut.</p>
        </div>
        @endif

        {{-- HISTORY TABLE SECTION --}}
        <div class="exclusive-card table-section">
            <div class="exclusive-card-header">
                <div class="header-icon"><i class="bi bi-journal-text"></i></div>
                <div class="header-text">
                    <h3>Riwayat Aktivitas</h3>
                    <p>Daftar seluruh aktivitas yang telah Anda ajukan.</p>
                </div>
            </div>

            <div class="exclusive-table-container">
                <table class="exclusive-table">
                    <thead>
                        <tr>
                            <th>NO</th>
                            <th>JENIS KEGIATAN & EVENT</th>
                            <th>TINGKAT</th>
                            <th>PERAN/JABATAN</th>
                            <th>POIN</th>
                            <th>STATUS</th>
                            <th>BUKTI</th>
                            @if ($student->is_edited)<th>AKSI</th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($student->achievements as $index => $achievement)
                        <tr>
                            <td class="col-num">{{ $index + 1 }}</td>
                            <td class="col-main">
                                <div class="activity-info">
                                    @if($achievement->event && $achievement->event !== '-')
                                        <strong>{{ $achievement->event }}</strong>
                                        <span>{{ $achievement->activity_type_label ?? 'Lainnya' }}</span>
                                    @else
                                        <strong>{{ $achievement->activity_type_label ?? '-' }}</strong>
                                        <span>Kategori Manual</span>
                                    @endif
                                </div>
                            </td>
                            <td><span class="pill-level">{{ $achievement->level }}</span></td>
                            <td class="col-role">{{ $achievement->participation_role ?? '-' }}</td>
                            <td>
                                @if ($achievement->skp_points > 0)
                                <div class="skp-pill-exclusive">{{ $achievement->skp_points }}</div>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @php
                                $statusClass = match($achievement->status ?? 'pending') {
                                'approved' => 'st-approved',
                                'rejected' => 'st-rejected',
                                default => 'st-pending',
                                };
                                $statusLabel = match($achievement->status ?? 'pending') {
                                'approved' => 'Verified',
                                'rejected' => 'Rejected',
                                default => 'Pending',
                                };
                                @endphp
                                <div class="status-indicator {{ $statusClass }}">
                                    <span class="dot"></span>
                                    <span class="label">{{ $statusLabel }}</span>
                                </div>
                                @if ($achievement->approval_notes)
                                <div class="approval-note-exclusive" title="{{ $achievement->approval_notes }}">
                                    <i class="bi bi-info-circle"></i> Catatan Review
                                </div>
                                @endif
                            </td>
                            <td>
                                @if ($achievement->certificate)
                                <a href="{{ asset('storage/' . $achievement->certificate) }}" target="_blank" class="btn-table-action view">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            @if ($student->is_edited)
                            <td>
                                <form action="{{ route('student.personal.achievement.delete', $achievement->id) }}" method="POST" onsubmit="return confirm('Hapus data prestasi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-table-action delete"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-exclusive">
                                    <i class="bi bi-emoji-smile"></i>
                                    <p>Belum ada data prestasi yang tercatat.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
    :root {
        --exclusive-orange: #FF8F00;
        --exclusive-orange-light: #FFB300;
        --exclusive-bg: #F8F9FD;
        --exclusive-card-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        --text-premium: #2A2F3B;
        --card-radius: 24px;
    }

    .exclusive-container {
        padding: 10px;
        font-family: 'Inter', sans-serif;
    }

    /* SIMPLE HEADER STYLES */
    .page-header-simple {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-bottom: 30px;
        padding: 20px 10px 15px;
        border-bottom: 2px solid #F3F4F6;
    }

    .page-title-simple {
        font-size: 32px;
        font-weight: 800;
        color: var(--text-premium);
        margin: 0 0 8px;
    }

    .page-subtitle-simple {
        font-size: 15px;
        color: #6B7280;
        margin: 0;
    }

    .header-actions {
        display: flex;
        gap: 15px;
    }

    .btn-simple-outline {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        background: white;
        border: 1.5px solid #E5E7EB;
        border-radius: 12px;
        color: #4B5563;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
    }

    .btn-simple-outline:hover {
        background: #F9FAFB;
        border-color: #D1D5DB;
        color: #111827;
    }

    .btn-simple-primary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: var(--exclusive-orange);
        border: none;
        border-radius: 12px;
        color: white;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(255, 143, 0, 0.2);
    }

    .btn-simple-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 15px rgba(255, 143, 0, 0.3);
    }

    .text-gradient {
        background: linear-gradient(135deg, var(--exclusive-orange), #FFD600);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: 800;
    }

    /* PREMIUM HERO */
    .premium-hero {
        background: white;
        border-radius: var(--card-radius);
        padding: 50px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        box-shadow: var(--exclusive-card-shadow);
        position: relative;
        overflow: hidden;
    }

    .premium-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(255, 143, 0, 0.05) 0%, transparent 70%);
        z-index: 0;
    }

    .hero-main-content {
        max-width: 60%;
        position: relative;
        z-index: 1;
    }

    .hero-badge-premium {
        background: #FFF3E0;
        color: var(--exclusive-orange);
        padding: 8px 16px;
        border-radius: 100px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 1px;
        display: inline-block;
        margin-bottom: 20px;
    }

    .hero-main-content h1 {
        font-size: 42px;
        font-weight: 800;
        color: var(--text-premium);
        line-height: 1.2;
        margin-bottom: 20px;
    }

    .hero-main-content p {
        font-size: 16px;
        color: #6B7280;
        line-height: 1.8;
        margin-bottom: 30px;
    }

    .hero-actions-container {
        display: flex;
        gap: 15px;
    }

    .btn-hero-primary {
        background: linear-gradient(135deg, var(--exclusive-orange), #FFA000);
        color: white;
        border: none;
        padding: 14px 28px;
        border-radius: 16px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 10px 20px rgba(255, 143, 0, 0.2);
    }

    .btn-hero-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(255, 143, 0, 0.3);
    }

    .btn-hero-secondary {
        background: #F3F4F6;
        color: #4B5563;
        padding: 14px 28px;
        border-radius: 16px;
        font-weight: 700;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s;
    }

    .btn-hero-secondary:hover {
        background: #E5E7EB;
    }

    .hero-illustration {
        width: 30%;
        display: flex;
        justify-content: center;
    }

    .trophy-glow {
        font-size: 120px;
        color: var(--exclusive-orange);
        filter: drop-shadow(0 0 20px rgba(255, 143, 0, 0.4));
        animation: float 4s ease-in-out infinite;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0) rotate(0deg);
        }

        50% {
            transform: translateY(-15px) rotate(5deg);
        }
    }

    /* ANALYTICS GRID */
    .analytics-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .analytic-card {
        background: white;
        border-radius: 20px;
        padding: 24px;
        display: flex;
        align-items: center;
        gap: 20px;
        box-shadow: var(--exclusive-card-shadow);
        transition: transform 0.3s;
    }

    .analytic-card:hover {
        transform: translateY(-5px);
    }

    .card-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        background: #F3F4F6;
        color: #374151;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

    .analytic-card.total .card-icon {
        background: #E0F2FE;
        color: #0284C7;
    }

    .analytic-card.approved .card-icon {
        background: #DCFCE7;
        color: #16A34A;
    }

    .analytic-card.pending .card-icon {
        background: #FEF3C7;
        color: #D97706;
    }

    .card-info span {
        display: block;
        font-size: 12px;
        text-transform: uppercase;
        font-weight: 700;
        color: #9CA3AF;
        margin-bottom: 4px;
    }

    .card-info strong {
        font-size: 24px;
        color: var(--text-premium);
        font-weight: 800;
    }

    .analytic-card.skp-total {
        background: linear-gradient(135deg, var(--exclusive-orange), #FFB300);
        flex-direction: column;
        align-items: flex-start;
        justify-content: center;
        color: white;
        gap: 5px;
    }

    .skp-total .card-label {
        font-size: 11px;
        font-weight: 800;
        opacity: 0.9;
    }

    .skp-total .card-value {
        font-size: 36px;
        font-weight: 900;
    }

    .skp-total .card-footer {
        font-size: 10px;
        opacity: 0.8;
    }

    /* CONTENT CARDS */
    .exclusive-card {
        background: white;
        border-radius: var(--card-radius);
        margin-bottom: 30px;
        box-shadow: var(--exclusive-card-shadow);
        overflow: hidden;
    }

    .exclusive-card-header {
        padding: 30px;
        display: flex;
        align-items: center;
        gap: 20px;
        border-bottom: 1px solid #F3F4F6;
    }

    .header-icon {
        width: 50px;
        height: 50px;
        background: #F8FAFC;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        color: var(--exclusive-orange);
    }

    .header-text h3 {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        color: var(--text-premium);
    }

    .header-text p {
        margin: 0;
        font-size: 13px;
        color: #9CA3AF;
    }

    /* FORM STYLES */
    .exclusive-form {
        padding: 40px;
    }

    .form-wizard-steps {
        display: flex;
        align-items: center;
        margin-bottom: 40px;
        padding: 20px;
        background: #F9FAFB;
        border-radius: 16px;
    }

    .wizard-step {
        display: flex;
        align-items: center;
        gap: 12px;
        opacity: 0.4;
        transition: all 0.3s;
    }

    .wizard-step.active {
        opacity: 1;
    }

    .wizard-step.done {
        opacity: 1;
        color: #16A34A;
    }

    .step-num {
        width: 32px;
        height: 32px;
        background: #E5E7EB;
        color: #374151;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 800;
    }

    .wizard-step.active .step-num {
        background: var(--exclusive-orange);
        color: white;
        box-shadow: 0 4px 10px rgba(255, 143, 0, 0.3);
    }

    .wizard-step.done .step-num {
        background: #16A34A;
        color: white;
        font-size: 0;
    }

    .wizard-step.done .step-num::after {
        content: '✓';
        font-size: 14px;
    }

    .step-label {
        font-weight: 700;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .wizard-separator {
        flex: 1;
        height: 2px;
        background: #E5E7EB;
        margin: 0 20px;
    }

    .form-floating-custom {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .custom-label {
        font-size: 13px;
        font-weight: 700;
        color: #4B5563;
        margin-left: 5px;
    }

    .custom-select {
        background-color: #F8FAFC;
        border: 2px solid #E2E8F0;
        border-radius: 16px;
        padding: 16px;
        font-size: 15px;
        color: var(--text-premium);
        transition: all 0.2s;
        cursor: pointer;
        width: 100%;
        max-width: 100%;
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
    }

    .custom-select:focus {
        border-color: var(--exclusive-orange);
        background-color: white;
        outline: none;
        box-shadow: 0 0 0 4px rgba(255, 143, 0, 0.1);
    }

    .custom-select optgroup {
        font-weight: 800;
        color: #1E293B;
        padding-top: 10px;
    }

    .custom-select option {
        padding: 10px;
        font-weight: 500;
    }

    /* UPLOAD AREA EXCLUSIVE */
    .upload-container-exclusive {
        position: relative;
        height: 180px;
        border: 2px dashed #CBD5E1;
        border-radius: 20px;
        background: #F8FAFC;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 10px;
    }

    .upload-container-exclusive:hover {
        border-color: var(--exclusive-orange);
        background: #FFFBF5;
    }

    .upload-container-exclusive input {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        z-index: 10;
    }

    .upload-overlay {
        text-align: center;
        pointer-events: none;
    }

    .pulse-icon {
        font-size: 40px;
        color: #94A3B8;
        margin-bottom: 10px;
    }

    .upload-overlay h4 {
        font-size: 16px;
        font-weight: 700;
        color: #475569;
        margin: 0 0 5px;
    }

    .upload-overlay p {
        font-size: 13px;
        color: #94A3B8;
        margin: 0;
    }

    .upload-types {
        margin-top: 10px;
        font-size: 11px;
        font-weight: 700;
        color: #CBD5E1;
        text-transform: uppercase;
    }

    .upload-result-exclusive {
        display: flex;
        align-items: center;
        gap: 15px;
        width: 100%;
        padding: 0 20px;
    }

    .upload-result-exclusive i {
        font-size: 32px;
        color: #16A34A;
    }

    .file-details {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .file-details span {
        font-weight: 700;
        color: #1E293B;
        font-size: 14px;
    }

    .file-details small {
        color: #16A34A;
        font-weight: 600;
        font-size: 11px;
    }

    .btn-clear-file {
        background: #FEE2E2;
        color: #DC2626;
        border: none;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        cursor: pointer;
        z-index: 11;
        position: relative;
    }

    /* POINTS BADGE */
    .points-badge-exclusive {
        margin-top: 30px;
        background: #111827;
        border-radius: 20px;
        padding: 20px 30px;
        display: inline-flex;
        align-items: center;
        gap: 20px;
        color: white;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        animation: slideIn 0.5s ease;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .badge-icon-exclusive {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #F59E0B, #D97706);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        box-shadow: 0 0 20px rgba(245, 158, 11, 0.4);
    }

    .badge-content-exclusive .label {
        display: block;
        font-size: 11px;
        font-weight: 800;
        color: #9CA3AF;
        letter-spacing: 1px;
    }

    .badge-content-exclusive .value {
        display: block;
        font-size: 32px;
        font-weight: 900;
        line-height: 1;
    }

    .form-footer-exclusive {
        margin-top: 40px;
        padding-top: 30px;
        border-top: 1px solid #F3F4F6;
        display: flex;
        justify-content: flex-end;
    }

    .btn-exclusive-submit {
        background: linear-gradient(135deg, var(--exclusive-orange), #FFA000);
        color: white;
        border: none;
        padding: 16px 40px;
        border-radius: 18px;
        font-weight: 700;
        font-size: 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 10px 25px rgba(255, 143, 0, 0.2);
    }

    .btn-exclusive-submit:hover {
        transform: scale(1.02);
    }

    /* TABLE STYLES EXCLUSIVE */
    .exclusive-table-wrapper {
        padding: 0 0 20px;
    }

    .exclusive-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 12px;
    }

    .exclusive-table thead th {
        padding: 0 30px 10px;
        font-size: 11px;
        font-weight: 800;
        color: #94A3B8;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        border: none;
    }

    .exclusive-table tbody tr {
        background: white;
        transition: all 0.3s;
    }

    .exclusive-table tbody tr td {
        padding: 20px 30px;
        background: white;
        border-top: 1px solid #F3F4F6;
        border-bottom: 1px solid #F3F4F6;
        vertical-align: middle;
    }

    .exclusive-table tbody tr td:first-child {
        border-left: 1px solid #F3F4F6;
        border-radius: 16px 0 0 16px;
    }

    .exclusive-table tbody tr td:last-child {
        border-right: 1px solid #F3F4F6;
        border-radius: 0 16px 16px 0;
    }

    .exclusive-table tbody tr:hover td {
        background: #F8FAFC;
    }

    .col-num {
        font-weight: 800;
        color: #CBD5E1;
        font-size: 14px;
    }

    .activity-info strong {
        display: block;
        color: var(--text-premium);
        font-size: 15px;
        margin-bottom: 2px;
    }

    .activity-info span {
        display: block;
        color: #94A3B8;
        font-size: 12px;
    }

    .pill-level {
        background: #EEF2FF;
        color: #4F46E5;
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 11px;
    }

    .skp-pill-exclusive {
        background: #FFF7ED;
        color: #EA580C;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 16px;
        border: 2px solid #FFEDD5;
    }

    .status-indicator {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .status-indicator .dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }

    .status-indicator .label {
        font-weight: 700;
        font-size: 13px;
    }

    .st-approved {
        color: #16A34A;
    }

    .st-approved .dot {
        background: #16A34A;
        box-shadow: 0 0 8px rgba(22, 163, 74, 0.4);
    }

    .st-pending {
        color: #D97706;
    }

    .st-pending .dot {
        background: #D97706;
        box-shadow: 0 0 8px rgba(217, 119, 6, 0.4);
    }

    .st-rejected {
        color: #DC2626;
    }

    .st-rejected .dot {
        background: #DC2626;
        box-shadow: 0 0 8px rgba(220, 38, 38, 0.4);
    }

    .btn-table-action {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        font-size: 16px;
    }

    .btn-table-action.view {
        background: #F0F9FF;
        color: #0369A1;
    }

    .btn-table-action.view:hover {
        background: #0369A1;
        color: white;
    }

    .btn-table-action.delete {
        background: #FEF2F2;
        color: #B91C1C;
    }

    .btn-table-action.delete:hover {
        background: #B91C1C;
        color: white;
    }

    /* LOCKED CARD */
    .locked-profile-card {
        background: #F1F5F9;
        border-radius: var(--card-radius);
        padding: 50px;
        text-align: center;
        border: 2px dashed #CBD5E1;
        margin-bottom: 30px;
    }

    .locked-icon {
        font-size: 50px;
        color: #94A3B8;
        margin-bottom: 20px;
    }

    .locked-profile-card h3 {
        font-weight: 800;
        color: #475569;
    }

    .locked-profile-card p {
        color: #64748B;
        max-width: 500px;
        margin: 10px auto 0;
        line-height: 1.6;
    }

    /* TOAST */
    .toast-notification {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: white;
        padding: 20px;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        gap: 15px;
        z-index: 1000;
        border-left: 6px solid #16A34A;
    }

    .toast-notification.error {
        border-left-color: #DC2626;
    }

    /* --- RESPONSIVE ADJUSTMENTS --- */
    @media (max-width: 1024px) {
        .page-header-simple {
            flex-direction: column;
            align-items: flex-start;
            gap: 20px;
        }

        .header-actions {
            width: 100%;
        }

        .header-actions>* {
            flex: 1;
            justify-content: center;
        }

        .analytics-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .page-title-simple {
            font-size: 26px;
        }

        .analytics-grid {
            gap: 12px;
        }

        .analytic-card {
            padding: 16px;
            gap: 12px;
        }

        .card-icon {
            width: 44px;
            height: 44px;
            font-size: 20px;
        }

        .card-info strong {
            font-size: 18px;
        }

        .exclusive-form {
            padding: 24px 15px;
        }

        .form-wizard-steps {
            padding: 12px;
            gap: 5px;
        }

        .wizard-step .step-label {
            display: none;
        }

        .wizard-separator {
            margin: 0 10px;
        }

        .exclusive-card-header {
            padding: 20px;
        }

        .header-icon {
            width: 40px;
            height: 40px;
            font-size: 18px;
        }

        .header-text h3 {
            font-size: 17px;
        }

        .exclusive-table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin: 0 -15px;
            padding: 0 15px;
        }

        .exclusive-table {
            min-width: 800px;
        }

        .custom-select {
            padding: 12px;
            font-size: 14px;
        }
    }

    @media (max-width: 480px) {
        .header-actions {
            flex-direction: column;
        }

        .analytics-grid {
            grid-template-columns: 1fr;
        }

        .analytic-card.skp-total {
            align-items: center;
            text-align: center;
        }

        .page-header-simple {
            padding: 15px 5px;
        }

        .points-badge-exclusive {
            width: 100%;
            justify-content: center;
            padding: 15px;
        }

        .badge-content-exclusive .value {
            font-size: 24px;
        }

        .form-footer-exclusive {
            justify-content: center;
            padding-top: 20px;
        }

        .btn-exclusive-submit {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    function closeToast() {
        const el = document.getElementById('toastNotification');
        if (el) {
            el.classList.remove('show');
            setTimeout(() => el.remove(), 400);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const toast = document.getElementById('toastNotification');
        if (toast) setTimeout(() => closeToast(), 5000);

        const LEVEL_OPTIONS = @json($levelOptions);
        const ROLE_OPTIONS = @json($roleOptions);
        const JS_POINTS_TABLE = @json($pointsTable);
        const TYPE_CATEGORY = @json($typeCategoryMap);

        const $actType = document.getElementById('activity_type');
        const $level = document.getElementById('level');
        const $role = document.getElementById('participation_role');
        const $jabLabel = document.getElementById('jabatanLabel');
        const $catInput = document.getElementById('category');
        const $skpBox = document.getElementById('skpPreviewRow');
        const $skpValue = document.getElementById('skpPreviewValue');

        // wizard steps
        const s1 = document.getElementById('step1');
        const s2 = document.getElementById('step2');
        const s3 = document.getElementById('step3');

        function calcSkp(t, l, r) {
            try {
                if (JS_POINTS_TABLE[t][l][r]) return JS_POINTS_TABLE[t][l][r];
            } catch (e) {}
            try {
                const fallback = JS_POINTS_TABLE[t]['-'];
                return fallback[r] || fallback['-'] || 0;
            } catch (e) {}
            return 0;
        }

        function fillSelect(el, options, placeholder) {
            el.innerHTML = `<option value="">${placeholder}</option>`;
            for (const [val, label] of Object.entries(options)) {
                const opt = document.createElement('option');
                opt.value = val;
                opt.textContent = label;
                el.appendChild(opt);
            }
        }

        function updateStep3Done() {
            const roleSelected = $role.value !== '';
            const fileUploaded = document.getElementById('certificate').files.length > 0;
            if (roleSelected && fileUploaded) {
                s3.classList.add('done');
            } else {
                s3.classList.remove('done');
            }
        }

        function updateSkp() {
            const pts = calcSkp($actType.value, $level.value, $role.value);
            if (pts > 0) {
                $skpValue.textContent = pts;
                $skpBox.style.display = 'inline-flex';
            } else {
                $skpBox.style.display = 'none';
            }
            updateStep3Done();
        }

        if ($actType) {
            $actType.addEventListener('change', function() {
                const type = this.value;
                $level.innerHTML = '<option value="">— Pilih Tingkat —</option>';
                $role.innerHTML = '<option value="">— Pilih —</option>';
                s1.classList.add('done');
                s2.classList.add('active');
                s3.classList.remove('done');
                s3.classList.remove('active');

                if (!type) {
                    s1.classList.remove('done');
                    s2.classList.remove('active');
                    $skpBox.style.display = 'none';
                    return;
                }

                $catInput.value = TYPE_CATEGORY[type] || '';
                if (LEVEL_OPTIONS[type]) {
                    fillSelect($level, LEVEL_OPTIONS[type], '— Pilih Tingkat —');
                }
                updateSkp();
            });

            $level.addEventListener('change', function() {
                const type = $actType.value;
                const level = this.value;
                $role.innerHTML = '<option value="">— Pilih —</option>';
                s2.classList.add('done');
                s3.classList.add('active');
                s3.classList.remove('done');

                if (!level) {
                    s2.classList.remove('done');
                    s3.classList.remove('active');
                    return;
                }

                if (ROLE_OPTIONS[type]) {
                    fillSelect($role, ROLE_OPTIONS[type], '— Pilih —');
                }
                updateSkp();
            });

            $role.addEventListener('change', updateSkp);
        }
    });

    function previewFile(input) {
        const file = input.files[0];
        if (!file) return;
        document.getElementById('uploadPlaceholder').style.display = 'none';
        document.getElementById('uploadPreview').style.display = 'flex';
        document.getElementById('uploadFileName').textContent = file.name;

        // Update step 3 if it exists in scope
        const s3 = document.getElementById('step3');
        const role = document.getElementById('participation_role');
        if (s3 && role && role.value !== '') {
            s3.classList.add('done');
        }
    }

    function clearFile() {
        document.getElementById('certificate').value = '';
        document.getElementById('uploadPlaceholder').style.display = 'block';
        document.getElementById('uploadPreview').style.display = 'none';

        const s3 = document.getElementById('step3');
        if (s3) s3.classList.remove('done');
    }
</script>
@endpush