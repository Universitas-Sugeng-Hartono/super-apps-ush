<!-- Desktop Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="logo">
        <h1>{{ session('user_prodi') }}</h1>
        <h7>Bimbingan PA</h7>
    </div>
    <nav>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="{{ route('dashboard.admin.index') }}"
                    class="nav-link {{ request()->is('admin/personal') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-pie nav-icon"></i>
                    Dashboard
                </a>
            </li>
            {{-- <li class="nav-item">
                <a href="{{ route('admin.students.index') }}"
                    class="nav-link {{ request()->is('admin/students') ? 'active' : '' }} {{ request()->is('admin/students/create') ? 'active' : '' }}">
                    <i class="fa-solid fa-users nav-icon"></i>
                    Students
                </a>
            </li> --}}
            <li class="nav-item">
                <a href="{{ route('admin.counseling.index') }}"
                    class="nav-link {{ request()->is('admin/counseling') ? 'active' : '' }}">
                    <i class="fa-solid fa-book-open nav-icon"></i>
                    Counseling
                </a>
            </li>
            @if (auth()->user()->role === 'masteradmin' || auth()->user()->role === 'superadmin')
                <li class="nav-item">
                    <a href="{{ route('user.admin.main') }}"
                        class="nav-link {{ request()->is('admin/user/main') ? 'active' : '' }}">
                        <i class="fa-solid fa-chalkboard-user nav-icon"></i>
                        Lecturers
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.students.changeAdvisor') }}"
                        class="nav-link {{ request()->is('admin/students/change-advisor') ? 'active' : '' }}">
                        <i class="fa-solid fa-user-edit nav-icon"></i>
                        Change of Academic Advisor
                    </a>
                </li>
            @endif

            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}"
                    class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-house nav-icon"></i>
                    SuperApps
                </a>
            </li>
        </ul>
    </nav>
</aside>
