<!-- Desktop Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="logo">
        <h1>{{ session('student_prodi') }}</h1>
        <h7>Bimbingan PA</h7>
    </div>
    <nav>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="{{ route('student.personal.index') }}"
                    class="nav-link {{ request()->is('student/personal') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line nav-icon"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('student.counseling.show') }}"
                    class="nav-link {{ request()->is('student/counseling/') ? 'active' : '' }}">
                    <i class="fa-solid fa-comments nav-icon"></i>
                    Counseling
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('student.dashboard') }}"
                    class="nav-link {{ request()->is('student/dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-house nav-icon"></i>
                    SuperApps
                </a>
            </li>
        </ul>
    </nav>
</aside>
