@extends('students.layouts.super-app')

@push('css')
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <style>
        /* Page Header */
        .page-header {
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }

        .page-subtitle {
            color: var(--text-gray);
            font-size: 14px;
            margin: 0;
        }

        /* Profile Cards */
        .profile-card {
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
            overflow: hidden;
            transition: var(--transition-normal);
        }

        .profile-card:hover {
            box-shadow: var(--shadow-hover);
            transform: translateY(-2px);
        }

        .profile-card-header {
            background: linear-gradient(135deg, var(--primary-orange), #FFB347);
            padding: 15px 20px;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            font-size: 16px;
        }

        .profile-card-header i {
            font-size: 20px;
        }

        .profile-card-body {
            padding: 25px;
        }

        /* Photo Display */
        .photo-display {
            text-align: center;
            margin-bottom: 20px;
        }

        .photo-wrapper {
            width: 200px;
            height: 200px;
            margin: 0 auto 20px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            position: relative;
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
        }

        .photo-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 80px;
            color: var(--primary-orange);
        }

        /* Signature Display */
        .signature-wrapper {
            width: 100%;
            height: 150px;
            margin: 0 auto 20px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            background: white;
            border: 2px dashed #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .signature-wrapper img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        /* Form Styling */
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 14px;
            transition: var(--transition-normal);
            background: #fafafa;
        }

        .form-control:focus {
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 0.2rem rgba(255, 152, 0, 0.15);
            background: white;
        }

        .form-control:disabled,
        .form-control[readonly] {
            background: #f5f5f5;
            border-color: #e0e0e0;
            color: #999;
        }

        .form-control.is-invalid {
            border-color: #dc3545;
            background: #fff5f5;
        }

        .form-control.is-invalid:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }

        .form-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        /* Buttons */
        .btn-upload {
            background: linear-gradient(135deg, var(--primary-orange), #FFB347);
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            color: white;
            transition: var(--transition-normal);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            justify-content: center;
        }

        .btn-upload:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 152, 0, 0.3);
            color: white;
        }

        .btn-save {
            background: linear-gradient(135deg, #4CAF50, #81C784);
            border: none;
            border-radius: 12px;
            padding: 14px 32px;
            font-weight: 600;
            color: white;
            transition: var(--transition-normal);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(76, 175, 80, 0.3);
            color: white;
        }

        /* File Input Custom */
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
            margin-bottom: 15px;
        }

        .file-input-wrapper input[type=file] {
            position: absolute;
            left: -9999px;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px 20px;
            background: white;
            border: 2px dashed var(--primary-orange);
            border-radius: 12px;
            cursor: pointer;
            transition: var(--transition-normal);
            color: var(--primary-orange);
            font-weight: 500;
        }

        .file-input-label:hover {
            background: rgba(255, 152, 0, 0.05);
            border-color: #FF7043;
        }

        .file-input-label i {
            font-size: 20px;
        }

        /* Map Container */
        #map {
            width: 100%;
            height: 350px;
            border-radius: 15px;
            border: 2px solid #e0e0e0;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
        }

        /* Info Alert */
        .info-alert {
            background: linear-gradient(135deg, #E3F2FD, #BBDEFB);
            border: none;
            border-radius: 15px;
            padding: 20px;
            color: #1976D2;
            display: flex;
            align-items: start;
            gap: 15px;
        }

        .info-alert i {
            font-size: 24px;
            margin-top: 2px;
        }

        .info-alert-content h6 {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .info-alert-content p {
            margin: 0;
            font-size: 14px;
        }

        /* Student Info Badge */
        .student-info {
            background: linear-gradient(135deg, rgba(255, 152, 0, 0.1), rgba(255, 179, 71, 0.1));
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .student-info-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .student-info-item:last-child {
            margin-bottom: 0;
        }

        .student-info-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary-orange), #FFB347);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }

        .student-info-text {
            flex: 1;
        }

        .student-info-label {
            font-size: 12px;
            color: var(--text-gray);
            margin-bottom: 2px;
        }

        .student-info-value {
            font-weight: 600;
            color: var(--text-dark);
            font-size: 14px;
        }

        /* Toast Notification */
        .toast-notification {
            position: fixed;
            top: 100px;
            right: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            padding: 20px;
            display: flex;
            align-items: start;
            gap: 15px;
            min-width: 350px;
            max-width: 450px;
            z-index: 10000;
            opacity: 0;
            transform: translateX(450px);
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .toast-notification.show {
            opacity: 1;
            transform: translateX(0);
        }

        .toast-notification.success {
            border-left: 5px solid #4CAF50;
        }

        .toast-notification.error {
            border-left: 5px solid #FF5252;
        }

        .toast-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .toast-notification.success .toast-icon {
            background: linear-gradient(135deg, #4CAF50, #81C784);
            color: white;
        }

        .toast-notification.error .toast-icon {
            background: linear-gradient(135deg, #FF5252, #FF8A80);
            color: white;
        }

        .toast-content {
            flex: 1;
        }

        .toast-content h6 {
            font-weight: 600;
            color: var(--text-dark);
            margin: 0 0 5px 0;
            font-size: 16px;
        }

        .toast-content p {
            margin: 0;
            color: var(--text-gray);
            font-size: 14px;
            line-height: 1.4;
        }

        .toast-close {
            background: transparent;
            border: none;
            color: var(--text-gray);
            font-size: 20px;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: var(--transition-fast);
        }

        .toast-close:hover {
            background: rgba(0, 0, 0, 0.05);
            color: var(--text-dark);
        }

        /* Toast Animation */
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(450px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideOutRight {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(450px);
            }
        }

        /* Table Styling */
        .table {
            margin-bottom: 0;
        }

        .table thead {
            background: linear-gradient(135deg, rgba(255, 152, 0, 0.1), rgba(255, 179, 71, 0.1));
        }

        .table thead th {
            font-weight: 600;
            color: var(--text-dark);
            border-bottom: 2px solid var(--primary-orange);
            padding: 15px 12px;
            font-size: 14px;
        }

        .table tbody td {
            padding: 15px 12px;
            vertical-align: middle;
            font-size: 14px;
        }

        .table-hover tbody tr {
            transition: var(--transition-fast);
        }

        .table-hover tbody tr:hover {
            background: rgba(255, 152, 0, 0.05);
        }

        .badge {
            padding: 6px 12px;
            font-weight: 500;
            border-radius: 8px;
            font-size: 12px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-title {
                font-size: 22px;
            }

            .photo-wrapper {
                width: 150px;
                height: 150px;
            }

            .profile-card-body {
                padding: 20px;
            }

            .toast-notification {
                right: 10px;
                left: 10px;
                min-width: auto;
                max-width: none;
            }

            .table-responsive {
                font-size: 12px;
            }

            .table thead th,
            .table tbody td {
                padding: 10px 8px;
            }
        }
        .btn-cv-download {
            color: rgb(3, 3, 3);
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-cv-download:hover{
            color: #4CAF50;
        }

        /* Signature Pad Styles */
        .signature-pad-container {
            position: relative;
            width: 100%;
            background: #fff;
            border: 2px dashed #FFB347;
            border-radius: 14px;
            overflow: hidden;
            cursor: crosshair;
            transition: border-color 0.2s;
        }
        .signature-pad-container:hover {
            border-color: var(--primary-orange);
        }
        .signature-pad-container.is-drawing {
            border-style: solid;
            border-color: var(--primary-orange);
        }
        #signatureCanvas {
            display: block;
            width: 100%;
            height: 160px;
            touch-action: none;
        }
        .sig-placeholder {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            color: var(--primary-orange);
            gap: 4px;
            transition: opacity 0.2s;
        }
        .sig-placeholder.hidden {
            opacity: 0;
        }
        .btn-sig-clear {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            background: #FDE8E7;
            color: #C23934;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-sig-clear:hover {
            background: #f5c6c5;
        }

        /* Premium Table Styles (from achievements page) */
        .table-premium thead {
            background: linear-gradient(135deg, rgba(255, 152, 0, 0.07), rgba(255, 179, 71, 0.07));
        }

        .table-premium thead th {
            font-weight: 600;
            color: var(--text-dark);
            border-bottom: 2px solid var(--primary-orange);
            padding: 14px 12px !important;
            font-size: 13px;
        }

        .table-premium tbody td {
            padding: 14px 12px !important;
            vertical-align: middle;
            font-size: 14px;
        }

        .cell-kegiatan strong {
            display: block;
            font-size: 14px;
            color: var(--text-dark);
        }

        .cell-kegiatan small {
            display: block;
            font-size: 12px;
            color: #888;
            margin-top: 2px;
        }

        .badge-level {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 8px;
            background: #EEF2FF;
            color: #4338CA;
            font-size: 11px;
            font-weight: 600;
        }

        .skp-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 42px;
            padding: 4px 8px;
            border-radius: 999px;
            background: #FFF1DA;
            color: #C46A00;
            font-size: 12px;
            font-weight: 700;
        }

        .status-pill {
            display: inline-flex;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
        }

        .status-approved {
            background: #E8F7EE;
            color: #1E7A44;
        }

        .status-pending {
            background: #FFF1DA;
            color: #C46A00;
        }

        .status-rejected {
            background: #FDE8E7;
            color: #C23934;
        }

        .doc-btn-sm {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: #EEF2FF;
            color: #4338CA;
            border-radius: 8px;
            font-size: 14px;
            text-decoration: none;
            transition: background 0.2s;
        }

        .btn-del-sm {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: #FDE8E7;
            color: #C23934;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s;
        }

        /* SKP Result Box (from achievements page) */
        .skp-result-box {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 20px;
            background: linear-gradient(135deg, #FFF8E1, #FFFDE7);
            border: 2px solid #FFD54F;
            border-radius: 14px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }

        .skp-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #FFC107, #FFB300);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: white;
            flex-shrink: 0;
        }

        .skp-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
            flex: 1;
        }

        .skp-info span {
            font-size: 12px;
            color: #888;
        }

        .skp-info strong {
            font-size: 32px;
            color: #E65100;
            line-height: 1;
            font-weight: 800;
        }

        .skp-note {
            margin-left: auto;
            font-size: 11px;
            color: #AAA;
            text-align: right;
            max-width: 140px;
        }
    </style>
@endpush

@section('content')
    <!-- Toast Notifications -->
    @if (session('success'))
        <div class="toast-notification success show" id="toastNotification">
            <div class="toast-icon">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div class="toast-content">
                <h6>Berhasil!</h6>
                <p>{{ session('success') }}</p>
            </div>
            <button type="button" class="toast-close" onclick="closeToast()">
                <i class="bi bi-x"></i>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="toast-notification error show" id="toastNotification">
            <div class="toast-icon">
                <i class="bi bi-exclamation-circle-fill"></i>
            </div>
            <div class="toast-content">
                <h6>Gagal!</h6>
                <p>{{ session('error') }}</p>
            </div>
            <button type="button" class="toast-close" onclick="closeToast()">
                <i class="bi bi-x"></i>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="toast-notification error show" id="toastNotification">
            <div class="toast-icon">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div class="toast-content">
                <h6>Terjadi Kesalahan!</h6>
                <ul style="margin: 5px 0 0 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="toast-close" onclick="closeToast()">
                <i class="bi bi-x"></i>
            </button>
        </div>
    @endif

    <!-- Page Header -->
    <div class="page-header">
        <h2 class="page-title">
            <i class="bi bi-person-badge"></i>
            Profil Mahasiswa
        </h2>
        <p class="page-subtitle">Kelola informasi personal dan data akademik Anda</p>
    </div>

    <div class="row g-4">
        <!-- Left Column - Profile Photo & Signature -->
        <div class="col-lg-4">
            <!-- Profile Photo Card -->
            <div class="profile-card">
                <div class="profile-card-header">
                    <i class="bi bi-camera-fill"></i>
                    <span>Foto Profil</span>
                </div>
                <div class="profile-card-body">
                    <div class="photo-display">
                        <div class="photo-wrapper">
                            @if ($student->foto)
                                <img src="{{ asset('storage/' . $student->foto) }}" alt="Foto Profil">
                            @else
                                <div class="photo-placeholder">
                                    <i class="bi bi-person-circle"></i>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if ($student->is_edited)
                        <form action="{{ route('student.personal.updateData') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="form_type" value="foto">

                            <div class="file-input-wrapper">
                                <input type="file" name="foto" id="fotoInput" accept="image/*">
                                <label for="fotoInput" class="file-input-label">
                                    <i class="bi bi-cloud-upload"></i>
                                    <span>Pilih Foto Baru</span>
                                </label>
                            </div>

                            <button type="submit" class="btn-upload">
                                <i class="bi bi-save"></i>
                                Simpan Foto
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Signature Card -->
            <div class="profile-card">
                <div class="profile-card-header">
                    <i class="bi bi-pen-fill"></i>
                    <span>Tanda Tangan</span>
                </div>
                <div class="profile-card-body">

                    @if ($student->is_edited)
                        {{-- Mode Edit: Canvas Signature Pad --}}
                        <form action="{{ route('student.personal.updateData') }}" method="POST" id="ttdForm">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="form_type" value="ttd">
                            <input type="hidden" name="ttd_base64" id="ttdBase64Input">

                            <div style="margin-bottom: 10px;">
                                <label style="font-size: 13px; font-weight: 600; color: var(--text-dark);">
                                    <i class="bi bi-pencil-square me-1"></i>
                                    Tanda tangan di sini:
                                </label>
                            </div>

                            <div class="signature-pad-container" id="signaturePadContainer">
                                <canvas id="signatureCanvas" width="320" height="160"></canvas>
                                <div class="sig-placeholder" id="sigPlaceholder">
                                    <i class="bi bi-pen" style="font-size:22px; opacity:0.3;"></i>
                                    <span style="font-size:12px; opacity:0.4; margin-top:4px;">Tanda tangan di sini...</span>
                                </div>
                            </div>

                            <div style="display:flex; gap:8px; margin-top:10px;">
                                <button type="button" id="resetSignatureBtn" class="btn-sig-clear">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </button>
                                <button type="submit" id="saveTtdBtn" class="btn-upload" style="flex:1;" onclick="return prepareSignature()">
                                    <i class="bi bi-save"></i> Simpan TTD
                                </button>
                            </div>

                            @if ($student->ttd)
                                <div style="margin-top:12px; padding:10px; background:#f8f9fa; border-radius:10px; text-align:center;">
                                    <div style="font-size:11px; color:#888; margin-bottom:6px;">TTD saat ini:</div>
                                    <img src="{{ asset('storage/' . $student->ttd) }}" alt="Tanda Tangan Saat Ini"
                                        style="max-height:60px; max-width:100%; opacity:0.7; border-radius:6px;">
                                </div>
                            @endif
                        </form>
                    @else
                        {{-- Mode View: Tampilkan TTD yang tersimpan --}}
                        <div class="signature-wrapper">
                            @if ($student->ttd)
                                <img src="{{ asset('storage/' . $student->ttd) }}" alt="Tanda Tangan">
                            @else
                                <span style="color: #ccc; font-size: 14px;">Belum ada tanda tangan</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Student Info Badge -->
            <div class="student-info">
                <div class="student-info-item">
                    <div class="student-info-icon">
                        <i class="bi bi-person"></i>
                    </div>
                    <div class="student-info-text">
                        <div class="student-info-label">Nama Lengkap</div>
                        <div class="student-info-value">{{ $student->nama_lengkap }}</div>
                    </div>
                </div>
                <div class="student-info-item">
                    <div class="student-info-icon">
                        <i class="bi bi-card-text"></i>
                    </div>
                    <div class="student-info-text">
                        <div class="student-info-label">NIM</div>
                        <div class="student-info-value">{{ $student->nim }}</div>
                    </div>
                </div>
                <div class="student-info-item">
                    <div class="student-info-icon">
                        <i class="bi bi-mortarboard"></i>
                    </div>
                    <div class="student-info-text">
                        <div class="student-info-label">Program Studi</div>
                        <div class="student-info-value">{{ $student->program_studi ?? 'N/A' }}</div>
                    </div>
                </div>
                <div class="student-info-item">
                    <div class="student-info-icon">
                         <i class="bi bi-file-earmark-person-fill"></i>
                    </div>

                    <div class="student-info-value">
                        @if($student->skpiRegistration?->status === 'approved')

                            <a href="{{ route('student.personal.cv.download') }}" class="btn-cv-download w-100">

                                Download CV
                            </a>

                        @else
                            <div class="student-info-item mt-3">
                                <div style="font-size:12px; color:#aaa; text-align:center; width:100%;">
                                    <i class="bi bi-lock me-1"></i>
                                    Download CV tersedia setelah SKPI disetujui
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Personal Data Form -->
        <div class="col-lg-8">
            <div class="profile-card">
                <div class="profile-card-header">
                    <i class="bi bi-file-person-fill"></i>
                    <span>Informasi Personal</span>
                </div>
                <div class="profile-card-body">
                    <form action="{{ route('student.personal.updateData') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="form_type" value="text">

                        <h6 class="mt-4 mb-3 fw-bold border-bottom pb-2"><i class="bi bi-person-badge me-2"></i>Data Identitas Mahasiswa</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label"><i class="bi bi-person-fill me-1"></i>Nama Lengkap (Sesuai Ijazah Terakhir)</label>
                                    <input type="text" name="nama_lengkap" class="form-control" value="{{ old('nama_lengkap', $student->nama_lengkap) }}" @readonly(!$student->is_edited) placeholder="Masukkan Nama Lengkap">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label"><i class="bi bi-card-text me-1"></i>NIM</label>
                                    <input type="text" class="form-control" value="{{ $student->nim }}" readonly style="background-color: #f8f9fa;">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label"><i class="bi bi-mortarboard me-1"></i>Program Studi</label>
                                    <input type="text" name="program_studi" class="form-control" value="{{ old('program_studi', $student->program_studi) }}" @readonly(!$student->is_edited) placeholder="Contoh: Teknik Informatika">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label"><i class="bi bi-calendar-event me-1"></i>Angkatan</label>
                                    <input type="number" name="angkatan" class="form-control" value="{{ old('angkatan', $student->angkatan) }}" @readonly(!$student->is_edited) placeholder="Contoh: 2022">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label"><i class="bi bi-building me-1"></i>Fakultas</label>
                                    <input type="text" name="fakultas" class="form-control" value="{{ old('fakultas', $student->fakultas) }}" @readonly(!$student->is_edited) placeholder="Contoh: FST">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label"><i class="bi bi-credit-card-2-front me-1"></i>NIK</label>
                                    <input type="text" name="nik" class="form-control" value="{{ old('nik', $student->nik) }}" @readonly(!$student->is_edited) placeholder="Masukkan NIK">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label"><i class="bi bi-credit-card-2-front me-1"></i>NISN</label>
                                    <input type="text" name="nisn" class="form-control" value="{{ old('nisn', $student->nisn) }}" @readonly(!$student->is_edited) placeholder="Masukkan NISN">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label"><i class="bi bi-gender-ambiguous me-1"></i>Jenis Kelamin</label>
                                    <select name="jenis_kelamin" class="form-control" @disabled(!$student->is_edited)>
                                        <option value="L" @selected($student->jenis_kelamin == 'L')>Laki-laki</option>
                                        <option value="P" @selected($student->jenis_kelamin == 'P')>Perempuan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label"><i class="bi bi-geo-alt me-1"></i>Tempat Lahir</label>
                                    <input type="text" name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir', $student->tempat_lahir) }}" @readonly(!$student->is_edited) placeholder="Masukkan tempat lahir">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label"><i class="bi bi-calendar me-1"></i>Tanggal Lahir</label>
                                    <input type="date" name="tanggal_lahir" class="form-control" value="{{ old('tanggal_lahir', $student->tanggal_lahir ? \Carbon\Carbon::parse($student->tanggal_lahir)->format('Y-m-d') : '') }}" @readonly(!$student->is_edited)>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label"><i class="bi bi-award me-1"></i>Gelar Lulusan (Otomatis dari Prodi)</label>
                                    <input type="text" class="form-control" style="background-color: #f8f9fa;" value="{{ $gelarFromProfile ?? 'Belum diatur oleh Prodi' }}" readonly>
                                </div>
                            </div>
                        </div>

                        <h6 class="mt-4 mb-3 fw-bold border-bottom pb-2"><i class="bi bi-people me-2"></i>Data Orang Tua</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label"><i class="bi bi-person me-1"></i>Nama Orangtua / Ayah</label>
                                    <input type="text" name="nama_orangtua" class="form-control" value="{{ old('nama_orangtua', $student->nama_orangtua) }}" @readonly(!$student->is_edited) placeholder="Masukkan nama orangtua">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label"><i class="bi bi-person-hearts me-1"></i>Nama Ibu Kandung</label>
                                    <input type="text" name="nama_ibu_kandung" class="form-control" value="{{ old('nama_ibu_kandung', $student->nama_ibu_kandung) }}" @readonly(!$student->is_edited) placeholder="Masukkan nama ibu kandung">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label"><i class="bi bi-telephone-fill me-1"></i>No. HP Orang Tua</label>
                                    <input type="text" name="no_telepon_orangtua" class="form-control" value="{{ old('no_telepon_orangtua', $student->no_telepon_orangtua) }}" @readonly(!$student->is_edited) placeholder="08xxxxxxxxxx">
                                </div>
                            </div>
                        </div>



                        <h6 class="mt-4 mb-3 fw-bold border-bottom pb-2"><i class="bi bi-house-door me-2"></i>Kontak & Tempat Tinggal</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label"><i class="bi bi-phone me-1"></i>No. HP (WhatsApp)</label>
                                    <input type="text" name="no_telepon" class="form-control" value="{{ old('no_telepon', $student->no_telepon) }}" @readonly(!$student->is_edited) placeholder="08xxxxxxxxxx">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label"><i class="bi bi-envelope me-1"></i>Email</label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', $student->email) }}" @readonly(!$student->is_edited) placeholder="email@example.com">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label"><i class="bi bi-geo-alt-fill me-1"></i>Alamat Lengkap</label>
                            <textarea name="alamat" rows="3" class="form-control" @readonly(!$student->is_edited) placeholder="Masukkan alamat lengkap">{{ old('alamat', $student->alamat) }}</textarea>
                        </div>

                        @if ($student->is_edited)
                            <div class="form-group" style="display: none;">
                                <label class="form-label">
                                    <i class="bi bi-map me-1"></i>
                                    Lokasi di Peta
                                </label>
                                <div id="map" style="display: none;"></div>
                                <input type="hidden" name="alamat_lat" id="alamat_lat"
                                    value="{{ old('alamat_lat', $student->alamat_lat) }}">
                                <input type="hidden" name="alamat_lng" id="alamat_lng"
                                    value="{{ old('alamat_lng', $student->alamat_lng) }}">
                                  <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Seret pin untuk menyesuaikan lokasi Anda
                                </small>
                            </div>
                        @else
                            @if ($student->alamat_lat && $student->alamat_lng)
                                <div class="form-group" style="display: none;">
                                    <a href="https://www.google.com/maps?q={{ $student->alamat_lat }},{{ $student->alamat_lng }}"
                                        target="_blank" class="btn btn-outline-primary">
                                        <i class="bi bi-map me-2"></i>
                                        Lihat Lokasi di Google Maps
                                    </a>
                                </div>
                            @endif
                        @endif

                        @if ($student->is_edited)
                            <h6 class="mt-4 mb-3 fw-bold border-bottom pb-2"><i class="bi bi-shield-lock me-2"></i>Keamanan Akun</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label"><i class="bi bi-key me-1"></i>Password Baru</label>
                                        <input type="password" name="password" id="password" class="form-control" minlength="8" placeholder="Minimal 8 karakter">
                                        <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label"><i class="bi bi-key-fill me-1"></i>Konfirmasi Password</label>
                                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" minlength="8" placeholder="Ulangi password baru">
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($student->is_edited)
                            <div class="text-end mt-4">
                                <button type="submit" class="btn-save">
                                    <i class="bi bi-check-circle"></i>
                                    Simpan Semua Data
                                </button>
                            </div>
                        @else
                            <div class="info-alert mt-4">
                                <i class="bi bi-lock-fill"></i>
                                <div class="info-alert-content">
                                    <h6>Data Terkunci</h6>
                                    <p>Data Anda sudah terkunci dan tidak dapat diubah. Jika ada perubahan yang diperlukan,
                                       silakan hubungi <strong>dosen pembimbing akademik</strong> Anda.</p>
                                </div>
                            </div>
                        @endif
                    

                        </form>
                </div>
            </div>

            <!-- Logout Card (Mobile Only) -->
            <div class="profile-card d-md-none mt-3">
                <div class="profile-card-body p-3">
                    <a href="{{ route('auth.logout') }}" class="btn btn-danger w-100 py-3" style="border-radius: 12px; font-weight: 700;">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout dari Akun
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Toast Notification Functions
        function closeToast() {
            const toast = document.getElementById('toastNotification');
            if (toast) {
                toast.classList.remove('show');
                setTimeout(() => {
                    toast.remove();
                }, 400);
            }
        }

        // Convert canvas to base64 and inject into hidden input before submit
        function prepareSignature() {
            const canvas = document.getElementById('signatureCanvas');
            if (!canvas) return true; // fallback: allow submit

            // Check if canvas is blank (all pixels transparent)
            const pixelData = canvas.getContext('2d').getImageData(0, 0, canvas.width, canvas.height).data;
            const isBlank = !pixelData.some((channel, i) => i % 4 !== 3 && channel !== 0);

            if (isBlank) {
                alert('Silakan buat tanda tangan Anda terlebih dahulu di kotak yang tersedia.');
                return false;
            }

            const dataURL = canvas.toDataURL('image/png');
            document.getElementById('ttdBase64Input').value = dataURL;
            return true;
        }

        // Auto hide toast after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const toast = document.getElementById('toastNotification');
            if (toast) {
                setTimeout(() => {
                    closeToast();
                }, 5000);
            }

            // ──────────────────────────────────────────────
            // Signature Pad (Canvas Draw)
            // ──────────────────────────────────────────────
            const canvas = document.getElementById('signatureCanvas');
            if (canvas) {
                const ctx = canvas.getContext('2d');
                const container = document.getElementById('signaturePadContainer');
                const placeholder = document.getElementById('sigPlaceholder');
                let drawing = false;
                let isEmpty = true;

                // Resize canvas to match actual rendered size
                function resizeCanvas() {
                    const rect = canvas.getBoundingClientRect();
                    const scaleX = rect.width / canvas.width;
                    const scaleY = rect.height / canvas.height;
                    // only resize if visually different
                    canvas.width = Math.round(rect.width);
                    canvas.height = Math.round(rect.height);
                    ctx.lineWidth = 2.5;
                    ctx.lineCap = 'round';
                    ctx.lineJoin = 'round';
                    ctx.strokeStyle = '#1a1a2e';
                }
                resizeCanvas();
                window.addEventListener('resize', resizeCanvas);

                function getPos(e) {
                    const rect = canvas.getBoundingClientRect();
                    if (e.touches) {
                        return {
                            x: e.touches[0].clientX - rect.left,
                            y: e.touches[0].clientY - rect.top
                        };
                    }
                    return { x: e.clientX - rect.left, y: e.clientY - rect.top };
                }

                function startDraw(e) {
                    e.preventDefault();
                    drawing = true;
                    const pos = getPos(e);
                    ctx.beginPath();
                    ctx.moveTo(pos.x, pos.y);
                    container.classList.add('is-drawing');
                }

                function draw(e) {
                    if (!drawing) return;
                    e.preventDefault();
                    const pos = getPos(e);
                    ctx.lineTo(pos.x, pos.y);
                    ctx.stroke();
                    if (isEmpty) {
                        isEmpty = false;
                        placeholder.classList.add('hidden');
                    }
                }

                function stopDraw(e) {
                    drawing = false;
                    container.classList.remove('is-drawing');
                }

                // Mouse events
                canvas.addEventListener('mousedown', startDraw);
                canvas.addEventListener('mousemove', draw);
                canvas.addEventListener('mouseup', stopDraw);
                canvas.addEventListener('mouseleave', stopDraw);

                // Touch events (mobile/tablet)
                canvas.addEventListener('touchstart', startDraw, { passive: false });
                canvas.addEventListener('touchmove', draw, { passive: false });
                canvas.addEventListener('touchend', stopDraw);

                // Reset button
                const resetBtn = document.getElementById('resetSignatureBtn');
                if (resetBtn) {
                    resetBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        ctx.clearRect(0, 0, canvas.width, canvas.height);
                        ctx.beginPath(); // Reset path to prevent redrawing old lines
                        isEmpty = true;
                        placeholder.classList.remove('hidden');
                        
                        // Clear the hidden input if it was set
                        const base64Input = document.getElementById('ttdBase64Input');
                        if (base64Input) base64Input.value = '';
                    });
                }
            }


            // Password validation
            const passwordInput = document.getElementById('password');
            const passwordConfirmationInput = document.getElementById('password_confirmation');

            if (passwordInput && passwordConfirmationInput) {
                function validatePassword() {
                    const password = passwordInput.value;
                    const passwordConfirmation = passwordConfirmationInput.value;

                    if (password && passwordConfirmation) {
                        if (password !== passwordConfirmation) {
                            passwordConfirmationInput.setCustomValidity('Password tidak cocok');
                            passwordConfirmationInput.classList.add('is-invalid');
                        } else {
                            passwordConfirmationInput.setCustomValidity('');
                            passwordConfirmationInput.classList.remove('is-invalid');
                        }
                    } else if (passwordConfirmation && !password) {
                        passwordConfirmationInput.setCustomValidity('Harap isi password baru terlebih dahulu');
                        passwordConfirmationInput.classList.add('is-invalid');
                    } else {
                        passwordConfirmationInput.setCustomValidity('');
                        passwordConfirmationInput.classList.remove('is-invalid');
                    }
                }

                passwordInput.addEventListener('input', validatePassword);
                passwordConfirmationInput.addEventListener('input', validatePassword);

                // Validate on form submit
                const form = passwordInput.closest('form');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        validatePassword();
                        if (!form.checkValidity()) {
                            e.preventDefault();
                            e.stopPropagation();
                        }
                    });
                }
            }
        });
    </script>

    @if ($student->is_edited)
        <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
        <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var lat = {{ old('alamat_lat', $student->alamat_lat ?? -6.2) }};
                var lng = {{ old('alamat_lng', $student->alamat_lng ?? 106.816666) }};
                var map = L.map('map').setView([lat, lng], 13);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);

                var marker = L.marker([lat, lng], {
                    draggable: true
                }).addTo(map);

                marker.on('dragend', function(e) {
                    var pos = marker.getLatLng();
                    document.getElementById('alamat_lat').value = pos.lat;
                    document.getElementById('alamat_lng').value = pos.lng;
                });

                L.Control.geocoder({
                    defaultMarkGeocode: false
                })
                .on('markgeocode', function(e) {
                    var center = e.geocode.center;
                    map.setView(center, 16);
                    marker.setLatLng(center);
                    document.getElementById('alamat_lat').value = center.lat;
                    document.getElementById('alamat_lng').value = center.lng;
                }).addTo(map);
            });
        </script>
    @endif
@endpush
