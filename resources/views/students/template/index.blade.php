@include('students.template.header')



<div class="admin-container">
    @include('students.message.index')
    @include('students.template.sidenav')
    <!-- Main Content -->
    <main class="main-content">
        <header class="header">
            @php
                $student = auth()->guard('student')->user();
                $nama = $student ? $student->nama_lengkap : session('student_nama');
                $foto = $student && $student->foto ? $student->foto : (session('path_pic') !== '0' && session('path_pic') !== 0 ? session('path_pic') : null);
                $inisial = collect(explode(' ', trim($nama ?? '')))->map(fn($word) => strtoupper(substr($word, 0, 1)))->join('');
            @endphp
            <div>
                <h2>
                    Selamat {{ $greeting }} {{ ucfirst(explode(' ', trim($nama ?? ''))[0]) }}
                </h2>

            </div>
            <div class="user-profile">
                <div class="profile-img" style=" object-fit: cover; object-position: center;">
                    @if ($foto)
                        <div class="photo-wrapper">
                            <img src="{{ asset('storage/' . $foto) }}" alt="Photo Student">
                        </div>
                    @else
                        {{ $inisial }}
                    @endif

                </div>

                <div class="dropdown">
                    <a class="btn  dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        {{ $inisial }}
                    </a>

                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('student.dashboard') }}">SuperApps Dashboard</a></li>
                    </ul>
                </div>
            </div>

        </header>

        @yield('content')
    </main>


    @include('students.template.mobile')

</div>

@include('students.template.footer')
@stack('scripts')
