<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $pageTitle ?? 'USH SuperApps' }} - Universitas Sugeng Hartono</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('public/icon.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('public/icon.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('public/icon.png') }}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @stack('css')

    <!-- Custom CSS -->
    <style>
        /* ========================================
           CSS VARIABLES & RESET
           ======================================== */
        :root {
            --primary-orange: #FF9800;
            --primary-blue: #29375d;
            --primary-yellow: #FFC107;
            --bg-cream: #FFF5E6;
            --bg-light: #FFFBF0;
            --text-dark: #2C3E50;
            --text-gray: #7F8C8D;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 8px 30px rgba(0, 0, 0, 0.12);
            --transition-fast: 0.2s ease;
            --transition-normal: 0.3s ease;
            --transition-slow: 0.5s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-cream);
            color: var(--text-dark);
            overflow-x: hidden;
            padding-bottom: 80px;
            margin: 0 !important;
            padding-top: 0 !important;
        }

        /* Header Section */
        .header-section {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: var(--primary-blue);
            height: 80px;
            padding: 0;
            margin: 0;
            border-radius: 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
            z-index: 9998;
            display: flex;
            align-items: center;
        }

        .header-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 0 20px;
        }

        /* Logo Section */
        .logo-section {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 0;
            text-align: left;
            padding-left: 15px;
        }

        .header-logo {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            transition: var(--transition-normal);
        }

        .app-title {
            color: white;
            font-size: 18px;
            margin: 0;
            font-weight: 600;
        }

        /* User Profile */
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 0;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-left: 20px;
            margin-left: 20px;
            border-left: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            cursor: pointer;
            z-index: 10000;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-details {
            text-align: right;
            pointer-events: none;
        }

        .user-details h5 {
            color: white;
            font-size: 14px;
            font-weight: 600;
            margin: 0;
        }

        .user-details p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 12px;
            margin: 0;
        }

        /* Burger Menu Button */
        .burger-menu-btn {
            position: relative !important;
            width: 40px !important;
            height: 40px !important;
            background: rgba(255, 255, 255, 0.1) !important;
            border: none;
            border-radius: 8px !important;
            color: white !important;
            font-size: 24px !important;
            display: flex !important;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: none !important;
            transition: var(--transition-normal);
            margin-right: 15px;
        }

        .burger-menu-btn:hover {
            background: rgba(255, 255, 255, 0.2) !important;
        }

        /* User Dropdown */
        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: var(--transition-normal);
            z-index: 9999;
            margin-top: 10px;
        }

        .user-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
            z-index: 99999 !important;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            cursor: pointer;
            transition: var(--transition-fast);
            color: var(--text-dark);
            font-size: 14px;
            text-decoration: none;
        }

        .dropdown-item:hover {
            background: var(--bg-cream);
        }

        .dropdown-item.logout {
            color: #FF5252;
        }

        .dropdown-item i {
            font-size: 16px;
            width: 20px;
            text-align: center;
        }

        .dropdown-divider {
            height: 1px;
            background: #E0E0E0;
            margin: 8px 0;
        }

        /* Sidebar Navigation */
        .sidebar-nav {
            position: fixed;
            top: 0px;
            left: -280px;
            width: 280px;
            height: 100vh;
            background: white;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
            z-index: 997;
            transition: left var(--transition-normal);
            display: flex;
            flex-direction: column;
        }

        .sidebar-nav.active {
            left: 0;
        }

        .sidebar-header {
            padding: 20px;
            background: linear-gradient(135deg, var(--primary-orange), #FFB347);
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }

        .sidebar-logo {
            width: 60px;
            height: 60px;
            margin-bottom: 10px;
            border-radius: 50%;
            background: white;
            padding: 5px;
        }

        .sidebar-header h4 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }

        .sidebar-content {
            flex: 1;
            padding: 20px 0 40px 0;
            overflow-y: auto;
        }

        .sidebar-content .nav-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 25px;
            cursor: pointer;
            transition: var(--transition-normal);
            position: relative;
            text-decoration: none;
            color: inherit;
        }

        .sidebar-content .nav-item:hover {
            background: var(--bg-cream);
        }

        .sidebar-content .nav-item.active {
            background: linear-gradient(90deg, rgba(255, 152, 0, 0.1), transparent);
            border-left: 4px solid var(--primary-orange);
        }

        .sidebar-content .nav-item .nav-icon {
            font-size: 24px;
            color: var(--text-gray);
            transition: var(--transition-normal);
        }

        .sidebar-content .nav-item span {
            font-size: 15px;
            color: var(--text-dark);
            font-weight: 500;
        }

        .sidebar-content .nav-item.active .nav-icon {
            color: var(--primary-orange);
        }

        .sidebar-content .nav-item.active span {
            color: var(--primary-orange);
            font-weight: 600;
        }

        /* Bottom Navigation */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 12px 10px 8px;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
            border-radius: 25px 25px 0 0;
            z-index: 1000;
        }

        .bottom-nav .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            transition: var(--transition-normal);
            padding: 8px 15px;
            border-radius: 15px;
            position: relative;
            text-decoration: none;
        }

        .bottom-nav .nav-item:hover {
            background: var(--bg-cream);
        }

        .nav-icon {
            font-size: 24px;
            color: var(--text-gray);
            transition: var(--transition-normal);
            position: relative;
        }

        .bottom-nav .nav-item span {
            font-size: 11px;
            color: var(--text-gray);
            font-weight: 500;
            transition: var(--transition-normal);
        }

        .bottom-nav .nav-item.active .nav-icon {
            color: var(--primary-orange);
            transform: scale(1.15);
        }

        .bottom-nav .nav-item.active span {
            color: var(--primary-orange);
            font-weight: 600;
        }

        .nav-badge {
            position: absolute;
            top: -4px;
            right: -6px;
            background: #FF5252;
            color: rgb(255, 255, 255) !important;
            font-size: 9px;
            font-weight: 700;
            padding: 0 4px;
            border-radius: 999px;
            min-width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(255, 82, 82, 0.4);
            line-height: 1;

        }

        /* Main Container */
        .container {
            padding: 20px 15px;
            margin-top: 80px;
            z-index: 1;
            position: relative;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-section {
                position: relative !important;
                background: linear-gradient(135deg, var(--primary-orange) 0%, #FFB347 100%) !important;
                height: auto !important;
                padding: 20px 20px 10px !important;
                border-radius: 0 0 30px 30px !important;
            }

            .header-content {
                display: flex;
                flex-direction: column;
            }

            .user-info {
                order: 1;
                width: 100%;
                flex-direction: row-reverse;
                display: block !important;
            }

            .user-details {
                text-align: left !important;
            }

            .user-profile {
                position: relative;
            }

            .user-avatar {
                position: absolute !important;
                right: 0 !important;
                top: 0 !important;
            }

            .logo-section {
                order: 2;
            }

            .user-dropdown {
                display: block;
                right: -5px;
                top: 60px;
                width: 180px;
                z-index: 10001 !important;
            }

            .desktop-only {
                display: none !important;
            }

            .burger-menu-btn {
                display: none !important;
            }

            .sidebar-nav {
                top: 0;
                height: 100vh;
            }

            .bottom-nav {
                display: flex;
            }

            .container {
                margin-top: 0;
                /* ruang ekstra agar konten tidak tertutup bottom-nav */
                padding-bottom: 5.5rem;
            }
        }

        @media (min-width: 769px) {
            .desktop-only {
                display: flex;
            }

            .bottom-nav {
                display: none;
            }

            body {
                padding-bottom: 0;
            }
        }

        body.sidebar-active .container {
            margin-left: 280px;
            transition: margin-left var(--transition-normal);
        }

        body.sidebar-active .header-section {
            margin-left: 280px;
            transition: margin-left var(--transition-normal);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header-section">
        <div class="header-content">
            <!-- Burger Menu Button (Desktop Only) -->
            <button class="burger-menu-btn" id="burgerBtn">
                <i class="bi bi-list"></i>
            </button>

            <!-- Logo Section (Desktop Only) link to dashboard -->
            <a href="{{ route('admin.dashboard') }}" class="logo-section desktop-only" style="transform: translateX(50px);text-decoration: none;">
                <img src="{{ asset('ush.png') }}" alt="USH Logo" class="header-logo">
                <h4 class="app-title">Universitas Sugeng Hartono</h4>
            </a>

            <!-- User Info -->
            <div class="user-info">
                <div class="user-profile" id="userProfile">
                    <div class="user-details">
                        <h5>Halo, {{ explode(' ', session('user_name'))[0] }}!</h5>
                        <p>Lecturer {{ session('user_prodi') ?? 'Dosen' }}</p>
                    </div>
                    <div class="user-avatar">
                        @if (session('user_photo') !== '0')
                            <img src="{{ asset('storage/' . session('user_photo')) }}" alt="Profile">
                        @else
                            <i class="bi bi-person-circle" style="font-size: 35px; color: var(--primary-orange);"></i>
                        @endif
                    </div>

                    <!-- Dropdown Menu -->
                    <div class="user-dropdown" id="userDropdown">
                        <a href="{{ route('admin.dashboard') }}" class="dropdown-item">
                            <i class="bi bi-house-door"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('user.admin.index') }}" class="dropdown-item">
                            <i class="bi bi-person"></i>
                            <span>Profile</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('auth.logout') }}" class="dropdown-item logout">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Navigation (Desktop) -->
    <nav class="sidebar-nav" id="sidebarNav">
        <div class="sidebar-header">
            <img src="{{ asset('ush.png') }}" alt="USH Logo" class="sidebar-logo">
            <h4>SuperApps</h4>
        </div>
        <div class="sidebar-content">
            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <div class="nav-icon">
                    <i class="bi bi-house-door-fill"></i>
                </div>
                <span>Home</span>
            </a>
            <a href="{{ route('calendar.index') }}" class="nav-item {{ request()->routeIs('calendar.index') ? 'active' : '' }}">
                <div class="nav-icon">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <span>Calendar</span>
            </a>
            <a href="{{ route('notifications.index') }}" class="nav-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                <div class="nav-icon">
                    <i class="bi bi-bell-fill"></i>
                    @if(!empty($globalUnreadCount) && $globalUnreadCount > 0)
                        <span class="nav-badge">{{ $globalUnreadCount > 99 ? '99+' : $globalUnreadCount }}</span>
                    @endif
                </div>
                <span>Notification</span>
            </a>
            <a href="{{ route('admin.announcements.index') }}" class="nav-item {{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}">
                <div class="nav-icon">
                    <i class="bi bi-bell-fill"></i>
                </div>
                <span>Pengumuman</span>
            </a>
            <a href="{{ route('user.admin.index') }}" class="nav-item {{ request()->routeIs('user.admin.index') ? 'active' : '' }}">
                <div class="nav-icon">
                    <i class="bi bi-person-circle"></i>
                </div>
                <span>Profile</span>
            </a>
        </div>
    </nav>

    <!-- Bottom Navigation (Mobile) -->
    <nav class="bottom-nav">
        <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <div class="nav-icon">
                <i class="bi bi-house-door-fill"></i>
            </div>
            <span>Home</span>
        </a>
        <a href="{{ route('calendar.index') }}" class="nav-item {{ request()->routeIs('calendar.index') ? 'active' : '' }}">
            <div class="nav-icon">
                <i class="bi bi-calendar-check"></i>
            </div>
            <span>Calendar</span>
        </a>
        <a href="{{ route('notifications.index') }}" class="nav-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
            <div class="nav-icon">
                <i class="bi bi-bell-fill"></i>
                @if(!empty($globalUnreadCount) && $globalUnreadCount > 0)
                        <span class="nav-badge">{{ $globalUnreadCount > 99 ? '99+' : $globalUnreadCount }}</span>
                    @endif
            </div>
            <span>Notif</span>
        </a>
        <a href="{{ route('admin.announcements.index') }}" class="nav-item {{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}">
            <div class="nav-icon">
                <i class="bi bi-bell-fill"></i>
            </div>
            <span>Pengumuman</span>
        </a>

        @if(auth()->user()->role === 'superadmin' || auth()->user()->role === 'masteradmin')
        <a href="{{ route('user.admin.main') }}" class="nav-item {{ request()->routeIs('user.admin.main') ? 'active' : '' }}">
            <div class="nav-icon">
                <i class="bi bi-person-badge-fill"></i>
            </div>
            <span>Dosen</span>
        </a>
        @endif
        <a href="{{ route('user.admin.index') }}" class="nav-item {{ request()->routeIs('user.admin.index') ? 'active' : '' }}">
            <div class="nav-icon">
                <i class="bi bi-person-circle"></i>
            </div>
            <span>Profile</span>
        </a>
    </nav>

    <!-- Main Content -->
    <div class="container">
        @yield('content')
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')

    <!-- Custom JavaScript -->
    <script>
        // Sidebar Toggle
        const burgerBtn = document.getElementById('burgerBtn');
        const sidebarNav = document.getElementById('sidebarNav');

        function toggleSidebar() {
            if (sidebarNav.classList.contains('active')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        }

        function openSidebar() {
            sidebarNav.classList.add('active');
            document.body.classList.add('sidebar-active');
        }

        function closeSidebar() {
            sidebarNav.classList.remove('active');
            document.body.classList.remove('sidebar-active');
        }

        if (burgerBtn) {
            burgerBtn.addEventListener('click', toggleSidebar);
        }

        // Auto open sidebar on desktop
        window.addEventListener('load', function() {
            if (burgerBtn && window.innerWidth >= 769) {
                // openSidebar();
            }
        });

        // Close sidebar on ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeSidebar();
            }
        });

        // User Dropdown
        const userProfile = document.getElementById('userProfile');
        const userDropdown = document.getElementById('userDropdown');

        function toggleUserDropdown() {
            userDropdown.classList.toggle('show');
        }

        function closeUserDropdown() {
            userDropdown.classList.remove('show');
        }

        if (userProfile && userDropdown) {
            userProfile.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleUserDropdown();
            });

            document.addEventListener('click', function(e) {
                if (!userProfile.contains(e.target)) {
                    closeUserDropdown();
                }
            });
        }
    </script>
</body>
</html>
