    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toastEls = document.querySelectorAll('.toast');
            toastEls.forEach((toastEl, index) => {
                const delay = 5000 * (index + 1); // Delay toast 5 detik berturut-turut
                const toast = new bootstrap.Toast(toastEl, {
                    delay: delay
                });
                toast.show(); // Menampilkan toast
            });
        });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility (aman untuk halaman non-login)
        (function() {
            const toggle = document.getElementById('togglePassword');
            const password = document.getElementById('password');
            if (!toggle || !password) return;

            toggle.addEventListener('click', function() {
            const icon = this;
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        })();

        // Form submission with loading - Modified for Laravel
        function handleFormSubmit(formId) {
            const form = document.getElementById(formId);
            if (!form) return;

            form.addEventListener('submit', function() {
                const button = this.querySelector('button[type="submit"]');
                const overlay = document.getElementById('loadingOverlay');

                // Show loading state
                if (button) button.classList.add('loading');
                if (overlay) overlay.classList.add('show');

                // Let the form submit normally to Laravel
                // The loading will be hidden when page redirects/reloads
            });
        }

        // Initialize form handlers
        handleFormSubmit('dosenForm');
        handleFormSubmit('mahasiswaForm');

        // Show Laravel session errors
        @if (session('error'))
            showError('{{ session('error') }}');
        @endif

        // Show Laravel validation errors
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                showError('{{ $error }}');
            @endforeach
        @endif

        // Error handling
        function showError(message) {
            const errorAlert = document.getElementById('errorAlert');
            const errorMessage = document.getElementById('errorMessage');

            if (!errorAlert || !errorMessage) return;

            errorMessage.textContent = message;
            errorAlert.style.display = 'block';

            // Auto hide after 5 seconds
            setTimeout(() => {
                errorAlert.style.display = 'none';
            }, 5000);
        }

        // Enhanced form interactions
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-2px)';
            });

            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });

        // Tab switching animation
        document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(e) {
                const targetPane = document.querySelector(this.getAttribute('data-bs-target'));
                if (!targetPane) return;
                targetPane.style.animation = 'fadeInUp 0.4s ease-out';

                setTimeout(() => {
                    targetPane.style.animation = '';
                }, 400);
            });
        });
    </script>
    </body>

    </html>
