<!DOCTYPE html>
<html lang="en">
@include('auth.template.head')
@stack('css')
<style>
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1055;
    }
</style>

</head>

<body>
    @include('message.index')

    @yield('content')
</body>
@include('auth.template.footer')
@stack('scripts')
{{-- 
<script src="https://www.google.com/recaptcha/api.js?render={{ env('RECAPTCHA_SITE_KEY') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function attachRecaptcha(formId, actionName) {
            const form = document.getElementById(formId);
            if (!form) return;

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                if (typeof grecaptcha === "undefined") {
                    alert("Captcha gagal dimuat. Silakan refresh halaman.");
                    return;
                }

                grecaptcha.ready(function() {
                    grecaptcha.execute('{{ env('RECAPTCHA_SITE_KEY') }}', {
                        action: actionName
                    }).then(function(token) {
                        let input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'g-recaptcha-response';
                        input.value = token;
                        form.appendChild(input);
                        form.submit();
                    });
                });
            });
        }

        attachRecaptcha('dosenForm', 'login_dosen');
        attachRecaptcha('mahasiswaForm', 'login_mahasiswa');
    }); 
</script>
--}}

</html>
