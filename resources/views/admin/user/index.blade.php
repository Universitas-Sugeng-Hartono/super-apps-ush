@extends('admin.layouts.super-app')

@section('content')
    <!-- Page Header -->
    <div class="page-header mb-4">
        <h2 class="page-title">Edit Profil Saya</h2>
        <span class="semester-badge">{{ $user->program_studi ?? 'Bisnis Digital' }}</span>
    </div>

    <!-- Form Card -->
    <div class="form-card">
        <form action="{{ route('user.admin.update', $user->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Terjadi kesalahan:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <!-- Profile Avatar Section -->
            @if ($user->photo)
            <div class="profile-avatar-section">
                <div class="avatar-wrapper">
                    <img src="{{ asset('storage/' . $user->photo) }}" alt="{{ $user->name }}" class="profile-avatar">
                    <div class="avatar-overlay">
                        <i class="bi bi-camera-fill"></i>
                    </div>
                </div>
                <h4 class="profile-name">{{ $user->name }}</h4>
                <p class="profile-role">{{ $user->role_label }} • {{ $user->program_studi }}</p>
            </div>
            @endif

            <!-- Informasi Dasar -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon-wrapper">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <h5 class="section-title">Informasi Dasar</h5>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="inputNama" class="form-label">
                            <i class="bi bi-person-fill"></i>
                            <span>Nama Lengkap</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="bi bi-person input-icon"></i>
                            <input type="text" class="form-control" id="inputNama" name="name"
                                value="{{ old('name', $user->name) }}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="inputEmail" class="form-label">
                            <i class="bi bi-envelope-fill"></i>
                            <span>Email</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="bi bi-envelope input-icon"></i>
                            <input type="email" class="form-control" id="inputEmail" name="email"
                                value="{{ old('email', $user->email) }}" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="inputPassword" class="form-label">
                        <i class="bi bi-lock-fill"></i>
                        <span>Password (Opsional)</span>
                    </label>
                    <div class="input-wrapper">
                        <i class="bi bi-lock input-icon"></i>
                        <input type="password" class="form-control" id="inputPassword" name="password"
                            placeholder="Kosongkan jika tidak ingin mengganti password">
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="bi bi-eye" id="passwordToggleIcon"></i>
                        </button>
                    </div>
                    <small class="form-text">
                        <i class="bi bi-info-circle me-1"></i>
                        Minimal 8 karakter
                    </small>
                </div>
            </div>

            <!-- Informasi Akademik -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon-wrapper">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>
                    <h5 class="section-title">Informasi Akademik</h5>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="inputProgramStudi" class="form-label">
                            <i class="bi bi-mortarboard-fill"></i>
                            <span>Program Studi</span>
                        </label>
                        <div class="select-wrapper">
                            <i class="bi bi-mortarboard select-icon"></i>
                            <select class="form-select" id="inputProgramStudi" name="program_studi" required>
                                <option value="" disabled>-- Pilih Program Studi --</option>
                                @foreach($studyPrograms as $prodi)
                                    <option value="{{ $prodi->name }}" {{ old('program_studi', $user->program_studi) == $prodi->name ? 'selected' : '' }}>
                                        {{ $prodi->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="inputRole" class="form-label">
                            <i class="bi bi-shield-check-fill"></i>
                            <span>Role</span>
                        </label>
                        <div class="select-wrapper">
                            <i class="bi bi-shield-check select-icon"></i>
                            <select class="form-select" id="inputRole" name="role" required>
                                <option value="" disabled>-- Pilih Role --</option>
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Dosen</option>
                                <option value="superadmin" {{ old('role', $user->role) == 'superadmin' ? 'selected' : '' }}>Kaprodi</option>
                                <option value="masteradmin" {{ old('role', $user->role) == 'masteradmin' ? 'selected' : '' }}>Superuser</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload File -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon-wrapper">
                        <i class="bi bi-image-fill"></i>
                    </div>
                    <h5 class="section-title">Foto & Tanda Tangan</h5>
                </div>

                <div class="file-upload-grid">
                    <div class="file-upload-card">
                        <label for="inputphoto" class="file-upload-label">
                            <div class="file-upload-content">
                                <i class="bi bi-camera-fill file-upload-icon"></i>
                                <span class="file-upload-text">Upload Photo Profil</span>
                                <span class="file-upload-hint">PNG, JPG max 2MB</span>
                            </div>
                            <input type="file" class="file-input" id="inputphoto" name="photo" accept="image/*" onchange="previewImage(this, 'photoPreview')">
                        </label>
                        @if ($user->photo)
                            <div class="preview-container">
                                <img src="{{ asset('storage/' . $user->photo) }}" alt="{{ $user->name }}" id="photoPreview" class="preview-image">
                            </div>
                        @endif
                    </div>

                    <div class="file-upload-card">
                        <label for="inputttd" class="file-upload-label">
                            <div class="file-upload-content">
                                <i class="bi bi-pen-fill file-upload-icon"></i>
                                <span class="file-upload-text">Upload Tanda Tangan</span>
                                <span class="file-upload-hint">PNG dengan transparan</span>
                            </div>
                            <input type="file" class="file-input" id="inputttd" name="ttd" accept="image/*" onchange="previewImage(this, 'ttdPreview')">
                        </label>
                        @if ($user->ttd)
                            <div class="preview-container">
                                <img src="{{ asset('storage/' . $user->ttd) }}" alt="ttd {{ $user->name }}" id="ttdPreview" class="preview-image">
                            </div>
                        @endif
                    </div>
                </div>

                <div class="alert alert-warning mt-4" role="alert">
                    <div class="alert-icon-wrapper">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div class="alert-content">
                        <strong>Perhatian:</strong> Unggah tanda tangan digital dengan latar belakang transparan.
                        Jika tanda tangan masih memiliki latar belakang, silakan bersihkan terlebih dahulu menggunakan
                        <a href="https://www.photoroom.com/tools/background-remover" target="_blank" class="alert-link">alat penghapus latar belakang</a>.
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="form-actions">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-2"></i>Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection

@push('css')
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .page-title {
        font-size: 28px;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0;
        background: linear-gradient(135deg, var(--primary-orange), #FF7043);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
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

    .form-card {
        background: linear-gradient(145deg, #ffffff, #f8f9fa);
        border-radius: 24px;
        padding: 40px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08), 0 2px 10px rgba(0, 0, 0, 0.04);
        margin-bottom: 30px;
        border: 1px solid rgba(255, 152, 0, 0.1);
        position: relative;
        overflow: hidden;
    }

    .form-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-orange), #FFB347, var(--primary-orange));
        background-size: 200% 100%;
        animation: shimmer 3s infinite;
    }

    @keyframes shimmer {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    /* Profile Avatar Section */
    .profile-avatar-section {
        text-align: center;
        margin-bottom: 40px;
        padding: 30px;
        background: linear-gradient(135deg, rgba(255, 152, 0, 0.1), rgba(255, 179, 71, 0.05));
        border-radius: 20px;
        border: 2px dashed rgba(255, 152, 0, 0.3);
    }

    .avatar-wrapper {
        position: relative;
        display: inline-block;
        margin-bottom: 20px;
    }

    .profile-avatar {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 5px solid white;
        box-shadow: 0 8px 30px rgba(255, 152, 0, 0.3);
        transition: transform 0.3s ease;
    }

    .avatar-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 152, 0, 0.8);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        cursor: pointer;
    }

    .avatar-overlay i {
        font-size: 40px;
        color: white;
    }

    .avatar-wrapper:hover .avatar-overlay {
        opacity: 1;
    }

    .avatar-wrapper:hover .profile-avatar {
        transform: scale(1.05);
    }

    .profile-name {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-dark);
        margin: 15px 0 5px;
    }

    .profile-role {
        font-size: 14px;
        color: var(--text-gray);
        margin: 0;
    }

    .form-section {
        margin-bottom: 40px;
        padding: 30px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        border: 1px solid rgba(255, 152, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .form-section:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(255, 152, 0, 0.15);
    }

    .form-section:last-of-type {
        margin-bottom: 0;
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid rgba(255, 152, 0, 0.2);
    }

    .section-icon-wrapper {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, var(--primary-orange), #FFB347);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 15px rgba(255, 152, 0, 0.3);
    }

    .section-icon-wrapper i {
        font-size: 24px;
        color: white;
    }

    .section-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0;
        background: linear-gradient(135deg, var(--primary-orange), #FF7043);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-label {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }

    .form-label i {
        color: var(--primary-orange);
        font-size: 16px;
    }

    .input-wrapper, .select-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-icon, .select-icon {
        position: absolute;
        left: 16px;
        color: var(--primary-orange);
        font-size: 18px;
        z-index: 1;
    }

    .form-control, .form-select {
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        padding: 14px 16px 14px 50px;
        font-size: 14px;
        transition: all 0.3s ease;
        background: #fafafa;
        width: 100%;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-orange);
        box-shadow: 0 0 0 4px rgba(255, 152, 0, 0.1);
        background: white;
        outline: none;
    }

    .password-toggle {
        position: absolute;
        right: 16px;
        background: none;
        border: none;
        color: var(--text-gray);
        cursor: pointer;
        font-size: 18px;
        padding: 0;
        z-index: 1;
        transition: color 0.3s ease;
    }

    .password-toggle:hover {
        color: var(--primary-orange);
    }

    .form-text {
        font-size: 12px;
        margin-top: 8px;
        color: var(--text-gray);
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .form-text i {
        color: var(--primary-orange);
    }

    /* File Upload */
    .file-upload-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 20px;
    }

    .file-upload-card {
        background: linear-gradient(135deg, rgba(255, 152, 0, 0.05), rgba(255, 179, 71, 0.02));
        border: 2px dashed rgba(255, 152, 0, 0.3);
        border-radius: 16px;
        padding: 30px;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .file-upload-card:hover {
        border-color: var(--primary-orange);
        background: linear-gradient(135deg, rgba(255, 152, 0, 0.1), rgba(255, 179, 71, 0.05));
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(255, 152, 0, 0.15);
    }

    .file-upload-label {
        display: block;
        cursor: pointer;
    }

    .file-upload-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }

    .file-upload-icon {
        font-size: 48px;
        color: var(--primary-orange);
        margin-bottom: 10px;
    }

    .file-upload-text {
        font-weight: 600;
        color: var(--text-dark);
        font-size: 16px;
    }

    .file-upload-hint {
        font-size: 12px;
        color: var(--text-gray);
    }

    .file-input {
        display: none;
    }

    .preview-container {
        margin-top: 20px;
        text-align: center;
    }

    .preview-image {
        max-width: 100%;
        max-height: 200px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        border: 3px solid white;
        transition: transform 0.3s ease;
    }

    .preview-image:hover {
        transform: scale(1.05);
    }

    .alert {
        border-radius: 16px;
        border: none;
        padding: 20px;
        display: flex;
        align-items: flex-start;
        gap: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .alert-icon-wrapper {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .alert-success .alert-icon-wrapper {
        background: rgba(76, 175, 80, 0.2);
    }

    .alert-success .alert-icon-wrapper i {
        color: #4CAF50;
        font-size: 20px;
    }

    .alert-danger .alert-icon-wrapper {
        background: rgba(244, 67, 54, 0.2);
    }

    .alert-danger .alert-icon-wrapper i {
        color: #F44336;
        font-size: 20px;
    }

    .alert-warning .alert-icon-wrapper {
        background: rgba(255, 152, 0, 0.2);
    }

    .alert-warning .alert-icon-wrapper i {
        color: #FF9800;
        font-size: 20px;
    }

    .alert-content {
        flex: 1;
    }

    .alert-success {
        background: #E8F5E9;
        color: #2E7D32;
        border-left: 4px solid #4CAF50;
    }

    .alert-danger {
        background: #FFEBEE;
        color: #C62828;
        border-left: 4px solid #F44336;
    }

    .alert-warning {
        background: #FFF3E0;
        color: #E65100;
        border-left: 4px solid #FF9800;
    }

    .alert-warning .alert-link {
        color: #E65100;
        font-weight: 600;
        text-decoration: underline;
    }

    .alert ul {
        margin: 0;
        padding-left: 20px;
    }

    .form-actions {
        display: flex;
        gap: 15px;
        justify-content: flex-end;
        margin-top: 30px;
        padding-top: 25px;
        border-top: 2px solid #f0f0f0;
    }

    .btn {
        padding: 12px 24px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        transition: var(--transition-normal);
        border: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-orange), #FFB347);
        color: white;
        box-shadow: 0 6px 20px rgba(255, 152, 0, 0.4);
        position: relative;
        overflow: hidden;
    }

    .btn-primary::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transition: left 0.5s ease;
    }

    .btn-primary:hover::before {
        left: 100%;
    }

    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(255, 152, 0, 0.5);
    }

    .btn-secondary {
        background: linear-gradient(135deg, #f5f5f5, #e8e8e8);
        color: var(--text-dark);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .btn-secondary:hover {
        background: linear-gradient(135deg, #e8e8e8, #d8d8d8);
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .page-title {
            font-size: 22px;
        }

        .form-card {
            padding: 20px;
        }

        .form-section {
            padding: 20px;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .file-upload-grid {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
function togglePassword() {
    const passwordInput = document.getElementById('inputPassword');
    const toggleIcon = document.getElementById('passwordToggleIcon');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('bi-eye');
        toggleIcon.classList.add('bi-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('bi-eye-slash');
        toggleIcon.classList.add('bi-eye');
    }
}

function previewImage(input, previewId) {
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            let preview = document.getElementById(previewId);
            if (!preview) {
                const container = input.closest('.file-upload-card');
                const previewContainer = document.createElement('div');
                previewContainer.className = 'preview-container';
                preview = document.createElement('img');
                preview.id = previewId;
                preview.className = 'preview-image';
                previewContainer.appendChild(preview);
                container.appendChild(previewContainer);
            }
            preview.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}
</script>
@endpush
