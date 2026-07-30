<!-- Mobile Bottom Navigation -->
<nav class="mobile-nav">
    <ul class="mobile-nav-list">
        <li class="mobile-nav-item">
            <a href="{{ route('admin.dashboard') }}"
                class="mobile-nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-house mobile-nav-icon"></i>
                <span>SuperApps</span>
            </a>
        </li>
        <li class="mobile-nav-item">
            <a href="{{ route('dashboard.admin.index') }}"
                class="mobile-nav-link {{ request()->is('admin/personal') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-pie mobile-nav-icon"></i>
                <span>Dashboard</span>
            </a>
        </li>
        {{-- <li class="mobile-nav-item">
            <a href="{{ route('admin.students.index') }}"
                class="mobile-nav-link {{ request()->is('admin/students') ? 'active' : '' }} {{ request()->is('admin/students/create') ? 'active' : '' }}">
                <i class="fa-solid fa-users mobile-nav-icon"></i>
                <span>Students</span>
            </a>
        </li> --}}
        <li class="mobile-nav-item">
            <a href="{{ route('admin.counseling.index') }}"
                class="mobile-nav-link {{ request()->is('admin/counseling') ? 'active' : '' }}">
                <i class="fa-solid fa-book-open mobile-nav-icon"></i>
                <span>Counseling</span>
            </a>
        </li>
        @if (auth()->user()->role === 'masteradmin' || auth()->user()->role === 'superadmin')
            <li class="mobile-nav-item">
                <a href="{{ route('user.admin.main') }}"
                    class="mobile-nav-link {{ request()->is('admin/user/main') ? 'active' : '' }}">
                    <i class="fa-solid fa-chalkboard-user mobile-nav-icon"></i>
                    <span>Lecturers</span>
                </a>
            </li>
        @endif
    </ul>
</nav>
