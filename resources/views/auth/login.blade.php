@extends('auth.template.index')

@section('content')
    <div class="login-container" style="scale: 0.8;">
        <div class="login-card">
            <!-- Loading Overlay -->
            <div class="form-overlay" id="loadingOverlay">
                <div class="spinner"></div>
            </div>

            <h2 class="login-title">
                <i class="fas fa-graduation-cap me-2"></i>
                Login Portal
            </h2>

            <!-- Nav tabs -->
            <ul class="nav nav-tabs" id="loginTabs" role="tablist">
                <li class="nav-item flex-fill" role="presentation">
                    <button class="nav-link active w-100" id="dosen-tab" data-bs-toggle="tab" data-bs-target="#dosen"
                        type="button" role="tab" aria-controls="dosen" aria-selected="true">
                        <i class="fas fa-chalkboard-teacher me-2"></i>
                        Dosen/Staff
                    </button>
                </li>
                <li class="nav-item flex-fill" role="presentation">
                    <button class="nav-link w-100" id="mahasiswa-tab" data-bs-toggle="tab" data-bs-target="#mahasiswa"
                        type="button" role="tab" aria-controls="mahasiswa" aria-selected="false">
                        <i class="fas fa-user-graduate me-2"></i>
                        Mahasiswa
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="loginTabsContent">
                <!-- Dosen tab -->
                <div class="tab-pane fade show active" id="dosen" role="tabpanel" aria-labelledby="dosen-tab">
                    <form method="POST" action="{{ route('auth.login.dosen') }}" id="dosenForm">
                        @csrf
                        <div class="form-floating">
                            <input type="email" class="form-control" id="emailDosen" name="email"
                                placeholder="Email address" required>
                            <label for="emailDosen">
                                <i class="fas fa-envelope me-2"></i>Email Address
                            </label>
                        </div>

                        <div class="form-floating">
                            <input type="password" class="form-control" id="passwordDosen" name="password"
                                placeholder="Password" required>
                            <label for="passwordDosen">
                                <i class="fas fa-lock me-2"></i>Password
                            </label>
                            <i class="fas fa-eye input-icon" id="togglePasswordDosen" style="cursor: pointer;"></i>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <span>
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    Masuk sebagai Dosen/Staff
                                </span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Mahasiswa tab -->
                <div class="tab-pane fade" id="mahasiswa" role="tabpanel" aria-labelledby="mahasiswa-tab">
                    <form method="POST" action="{{ route('auth.login.mahasiswa') }}" id="mahasiswaForm">
                        @csrf
                        <div class="form-floating">
                            <input type="text" class="form-control" id="nim" name="nim" placeholder="NIM"
                                required>
                            <label for="nim">
                                <i class="fas fa-id-card me-2"></i>Nomor Induk Mahasiswa
                            </label>
                        </div>
                        <div class="form-floating">
                            <input type="password" class="form-control" id="passwordMahasiswa" name="password"
                                placeholder="Password" required>
                            <label for="passwordMahasiswa">
                                <i class="fas fa-lock me-2"></i>Password
                            </label>
                            <i class="fas fa-eye input-icon" id="togglePasswordMahasiswa" style="cursor: pointer;"></i>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">
                                <span>
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    Masuk sebagai Mahasiswa
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Error Alert -->
            @if (session('error'))
                <div class="alert alert-danger mt-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ session('error') }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('css')
    <style>
        .form-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 10;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #ddd;
            border-top: 4px solid #FF9800;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const overlay = document.getElementById("loadingOverlay");

            // Toggle password function
            const toggle = (toggleId, inputId) => {
                const toggleIcon = document.getElementById(toggleId);
                const input = document.getElementById(inputId);

                if (toggleIcon && input) {
                    toggleIcon.addEventListener("click", () => {
                        const type = input.type === "password" ? "text" : "password";
                        input.type = type;
                        toggleIcon.classList.toggle("fa-eye-slash");
                    });
                }
            };

            toggle("togglePasswordDosen", "passwordDosen");
            toggle("togglePasswordMahasiswa", "passwordMahasiswa");

            // Show loading overlay on form submit
            ["dosenForm", "mahasiswaForm"].forEach(formId => {
                const form = document.getElementById(formId);
                if (form) {
                    form.addEventListener("submit", () => {
                        overlay.style.display = "flex";
                    });
                }
            });
        });
    </script>
@endpush
