@include('admin.template.header')

@include('admin.message.index')

<div class="admin-container">

    @include('admin.template.sidenav')
    <!-- Main Content -->
    <main class="main-content">
        <header class="header">
            <div>
                <h2> Selamat {{ $greeting }} {{ ucfirst(explode(' ', trim(auth()->user()->name))[0]) }}</h2>
            </div>

            <div class="user-profile">
                <div class="profile-img">


                    @if (!empty(auth()->user()->photo))
                        <div class="photo-wrapper">
                            <img src="{{ asset('storage/' . auth()->user()->photo) }}" alt="Photo Student">
                        </div>
                    @else
                        {{ collect(explode(' ', auth()->user()->name))->map(fn($word) => strtoupper(substr($word, 0, 1)))->join('') }}
                    @endif


                </div>

                <div class="dropdown">
                    <a class="btn  dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        Menu
                    </a>

                    <ul class="dropdown-menu">
                        <li><a href="{{ route('user.admin.index') }}" class="dropdown-item">Profile</a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">SuperApps Dashboard</a></li>

                    </ul>
                </div>
            </div>
        </header>

        @yield('content')
    </main>

    @include('admin.template.mobile')

</div>

@include('admin.template.footer')
@stack('scripts')
