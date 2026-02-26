@extends('admin.template.index')

@push('css')
    <style>
        .profile-card {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            color: #fff;
            text-align: center;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .profile-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            /* overlay gelap biar teks jelas */
            z-index: 1;
            transition: background 0.3s ease;
        }

        .profile-card:hover::before {
            background: rgba(0, 0, 0, 0.1);
            /* hover = overlay tipis, foto asli lebih kelihatan */
        }

        .profile-card img {
            width: 100%;
            height: auto;
            /* otomatis ikut rasio asli foto */
            display: block;
            transition: transform 0.4s ease;
        }

        .profile-card:hover img {
            transform: scale(1.05);
            /* zoom in */
        }

        .profile-info {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 2;
            padding: 15px;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.7), transparent);
        }

        .profile-info h6 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .profile-info span {
            font-size: 14px;
            font-weight: 400;
            color: #ddd;
        }

        .profile-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">

        <div class="row">
            @foreach ($users as $user)
                <div class="col-xl-3 col-md-6 mb-4">
                    <a href="{{ route('admin.students.changeAdvisorById', $user->id) }}" class="text-decoration-none">
                        <div class="profile-card">
                            @php
                                // Hapus gelar (semua teks setelah koma pertama)
                                $cleanName = preg_replace('/,.*$/', '', $user->name);

                                // Cek apakah user punya foto di storage
                                $photoUrl = !empty($user->photo)
                                    ? asset('storage/' . $user->photo)
                                    : 'https://ui-avatars.com/api/?name=' .
                                        urlencode(trim($cleanName)) .
                                        '&size=256&background=random';
                            @endphp

                            <img src="{{ $photoUrl }}" alt="{{ $user->name }}">
                            <div class="profile-info">
                                <h6>{{ $user->name }}</h6>
                                <span>Dosen Pembimbing</span>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

    </div>
@endsection

@push('scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof bootstrap === 'undefined') return;

            function attachModalAnimation(modalId, onShow = null) {
                const modal = document.getElementById(modalId);
                if (!modal) return;
                const content = modal.querySelector('.modal-content');
                content.classList.add('animate__animated');

                modal.addEventListener('show.bs.modal', function(event) {
                    if (onShow) onShow(event, modal);
                    content.classList.remove('animate__fadeOutDown');
                    content.classList.add('animate__fadeInUp');
                    content.addEventListener('animationend', function handler() {
                        content.classList.remove('animate__fadeInUp');
                        content.removeEventListener('animationend', handler);
                    }, {
                        once: true
                    });
                });

                modal.addEventListener('hide.bs.modal', function(event) {
                    if (modal.dataset.bsAnimating === 'true') return;
                    event.preventDefault();
                    modal.dataset.bsAnimating = 'true';
                    content.classList.remove('animate__fadeInUp');
                    content.classList.add('animate__fadeOutDown');
                    content.addEventListener('animationend', function handler() {
                        const instance = bootstrap.Modal.getInstance(modal);
                        if (instance) {
                            instance.hide();
                        } else {
                            modal.classList.remove('show');
                            modal.style.display = 'none';
                            document.body.classList.remove('modal-open');
                            document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                            content.classList.remove('animate__fadeOutDown');
                            delete modal.dataset.bsAnimating;
                        }
                        content.removeEventListener('animationend', handler);
                    }, {
                        once: true
                    });
                });

                modal.addEventListener('hidden.bs.modal', function() {
                    content.classList.remove('animate__fadeInUp', 'animate__fadeOutDown');
                    delete modal.dataset.bsAnimating;
                });
            }

            // Delete Modal
            attachModalAnimation('deleteModal', function(event, modal) {
                if (modal.id === 'deleteModal' && event.relatedTarget) {
                    const btn = event.relatedTarget;
                    const studentId = btn.getAttribute('data-id');
                    const studentName = btn.getAttribute('data-name');
                    document.getElementById('studentName').textContent = studentName;
                    document.getElementById('deleteForm').action = "{{ url('admin/students') }}/" +
                        studentId;
                }
            });

            // Notes Modal
            attachModalAnimation('notesModal', function(event, modal) {
                if (modal.id === 'notesModal' && event.relatedTarget) {
                    const btn = event.relatedTarget;
                    const studentName = btn.getAttribute('data-name');
                    const notes = btn.getAttribute('data-notes');
                    document.getElementById('notesStudentName').textContent = studentName;
                    document.getElementById('notesContent').innerHTML = notes ?
                        notes : '<span class="text-muted">Tidak ada catatan</span>';
                }
            });

            // Reset Password Modal
            attachModalAnimation('resetPasswordModal', function(event, modal) {
                if (modal.id === 'resetPasswordModal' && event.relatedTarget) {
                    const btn = event.relatedTarget;
                    const studentId = btn.getAttribute('data-id');
                    const studentName = btn.getAttribute('data-name');
                    document.getElementById('resetStudentName').textContent = studentName;
                    document.getElementById('resetPasswordForm').action =
                        "{{ url('admin/students') }}/" + studentId + "/resetpassword";
                }
            });
        });
    </script>
@endpush
