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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('user.admin.create') }}" class="btn btn-primary btn-lg shadow">
            <i class="fas fa-plus-circle me-2"></i> Add New Lecturer
        </a>
    </div>

    <div class="row">
        @foreach ($users as $user)
            <div class="col-xl-3 col-md-6 mb-4">
                <a href="{{ route('admin.students.CheckStudentByLecturer', $user->id) }}" class="text-decoration-none">
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
@endsection

@push('scripts')
    <script>
        // 3D tilt effect
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.profile-card').forEach(function(card) {
                card.addEventListener('mousemove', function(e) {
                    const rect = card.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;

                    const centerX = rect.width / 2;
                    const centerY = rect.height / 2;

                    const rotateX = (y - centerY) / 15;
                    const rotateY = (centerX - x) / 15;

                    card.style.transform =
                        `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-10px)`;
                });

                card.addEventListener('mouseleave', function() {
                    card.style.transform =
                        'perspective(1000px) rotateX(0deg) rotateY(0deg) translateY(0px)';
                });
            });
        });
    </script>
@endpush
