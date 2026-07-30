@extends('auth.template.index')

@section('content')
    <div class="login-container">
        <div class="login-card">
            <!-- Loading Overlay -->
            <div class="form-overlay" id="loadingOverlay">
                <div class="spinner"></div>
            </div>

            <h2 class="login-title">
                <i class="fas fa-user-plus me-2"></i>
                Registrasi
            </h2>

            <form method="POST" action="{{ route('register.submit') }}" id="registerForm">
                @csrf
                <div class="form-floating">
                    <input type="text" class="form-control" id="name" name="name" 
                        value="{{ old('name') }}" placeholder="Nama Lengkap" required autofocus>
                    <label for="name">
                        <i class="fas fa-user me-2"></i>Nama Lengkap
                    </label>
                </div>

                <div class="form-floating">
                    <input type="email" class="form-control" id="email" name="email" 
                        value="{{ old('email') }}" placeholder="Email" required>
                    <label for="email">
                        <i class="fas fa-envelope me-2"></i>Email Address
                    </label>
                </div>

                <div class="form-floating">
                    <input type="password" class="form-control" id="password" name="password" 
                        placeholder="Password" required>
                    <label for="password">
                        <i class="fas fa-lock me-2"></i>Password
                    </label>
                    <i class="fas fa-eye input-icon" id="togglePassword" style="cursor: pointer;"></i>
                </div>

                <div class="form-floating">
                    <input type="password" class="form-control" id="password_confirmation" 
                        name="password_confirmation" placeholder="Konfirmasi Password" required>
                    <label for="password_confirmation">
                        <i class="fas fa-lock me-2"></i>Konfirmasi Password
                    </label>
                    <i class="fas fa-eye input-icon" id="togglePasswordConfirm" style="cursor: pointer;"></i>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <span>
                            <i class="fas fa-user-plus me-2"></i>
                            Daftar Sekarang
                        </span>
                    </button>
                </div>
            </form>

            <!-- Success/Error Alert -->
            @if (session('success'))
                <div class="alert alert-success mt-3">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                </div>
            @endif
            
            @if (session('error'))
                <div class="alert alert-danger mt-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ session('error') }}
                </div>
            @endif
        </div>
    </div>
@endsection

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

            toggle("togglePassword", "password");
            toggle("togglePasswordConfirm", "password_confirmation");

            // Show loading overlay on form submit
            const form = document.getElementById("registerForm");
            if (form) {
                form.addEventListener("submit", () => {
                    overlay.style.display = "flex";
                });
            }
        });
    </script>
@endpush
