<!DOCTYPE html>
<html lang="id">
@php
    $isWaEnabled = \App\Models\AppSetting::isWaVisibleForRole('student');
    $waNumber    = \App\Models\AppSetting::get('wa_number', '6289613942890');
    $waTemplate  = \App\Models\AppSetting::get('wa_message_template', 'Halo Admin USH, saya mahasiswa yang ingin bertanya mengenai...');
    $waTitle     = \App\Models\AppSetting::get('wa_tooltip_title', 'Butuh bantuan? 👋');
    $waMessage   = \App\Models\AppSetting::get('wa_tooltip_message', "Halo! Ada yang bisa kami bantu?\nSilakan chat langsung dengan Admin lewat WhatsApp. ✨");
@endphp

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $pageTitle ?? 'USH SuperApps' }} - Universitas Sugeng Hartono</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('icon.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('icon.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icon.png') }}">

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
            top: -5px;
            right: -8px;
            background: #FF5252;
            color: white !important;
            font-size: 10px;
            font-weight: 600;
            padding: 1px 4px;
            border-radius: 999px;
            min-width: 16px;
            height: 16px;
            text-align: center;
            justify-content: center;
            display: flex;
            line-height: 1;
            box-shadow: 0 2px 8px rgba(255, 82, 82, 0.4);
        }

        /* Main Container */
        .container {
            padding: 20px 15px;
            margin-top: 80px;
            position: relative;
        }

        /* Bootstrap modal layering fix:
           - our layout uses high z-index for header/dropdown
           - ensure modals/backdrops are always above them
           - avoid stacking-context issues by not forcing container z-index
        */
        .modal-backdrop {
            z-index: 10040 !important;
        }

        .modal {
            z-index: 10050 !important;
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

        /* Modal Styles for Profile Completion (Global) */
        .celebration-card {
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 18px 55px rgba(0,0,0,0.18);
        }
        .celebration-body {
            position: relative;
            padding: 30px 20px;
            text-align: center;
            background: radial-gradient(1200px 400px at 50% 0%, rgba(255,152,0,0.12), transparent 60%),
                        linear-gradient(135deg, rgba(255,255,255,1), rgba(255,251,240,1));
        }
        .celebration-icon {
            width: 64px;
            height: 64px;
            border-radius: 18px;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            box-shadow: 0 14px 35px rgba(255,152,0,0.18);
        }
        .celebration-title {
            margin: 0;
            font-weight: 900;
            color: #333;
        }
        .celebration-text {
            color: #555;
            font-weight: 500;
            font-size: 14px;
            line-height: 1.6;
        }
        .celebration-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .btn-close-soft {
            border-radius: 12px;
            padding: 10px 18px;
            font-weight: 700;
            border: 1px solid rgba(0,0,0,0.1);
            color: #666;
        }
        .btn-read-soft {
            border: none;
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-orange), #FFB347);
            color: white;
            box-shadow: 0 8px 20px rgba(255,152,0,0.25);
        }
        .btn-read-soft:hover { 
            filter: brightness(1.05); 
            color: white;
        }

        /* ========================================
           FLOATING WHATSAPP BUTTON
           ======================================== */
        #wa-float-btn {
            position: fixed;
            bottom: 100px;
            right: 20px;
            z-index: 10060;
            cursor: grab;
            user-select: none;
            /* touch-action dibiarkan default agar tap mobile tidak terblokir */
            -webkit-tap-highlight-color: transparent;
        }
        #wa-float-btn:active {
            cursor: grabbing;
        }
        .wa-btn-circle {
            width: 58px;
            height: 58px;
            border-radius: 50%;
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 24px rgba(37,211,102,0.45), 0 2px 8px rgba(0,0,0,0.15);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
        }
        .wa-btn-circle:hover {
            transform: scale(1.1);
            box-shadow: 0 10px 32px rgba(37,211,102,0.55), 0 4px 12px rgba(0,0,0,0.2);
        }
        .wa-btn-circle svg {
            width: 32px;
            height: 32px;
            fill: white;
        }
        /* Pulse ring animation */
        .wa-pulse-ring {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 58px;
            height: 58px;
            border-radius: 50%;
            border: 3px solid rgba(37,211,102,0.6);
            animation: wa-pulse 2s ease-out infinite;
            pointer-events: none;
        }
        @keyframes wa-pulse {
            0%   { transform: translate(-50%, -50%) scale(1); opacity: 0.8; }
            100% { transform: translate(-50%, -50%) scale(1.9); opacity: 0; }
        }
        /* Tooltip popup */
        .wa-tooltip {
            position: absolute;
            bottom: 70px;
            right: 0;
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
            padding: 16px 18px;
            width: 230px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px) scale(0.95);
            transition: all 0.25s cubic-bezier(0.34,1.56,0.64,1);
            pointer-events: none;
        }
        .wa-tooltip.open {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
            pointer-events: all;
        }
        /* Tooltip arrow */
        .wa-tooltip::after {
            content: '';
            position: absolute;
            bottom: -8px;
            right: 22px;
            width: 16px;
            height: 16px;
            background: white;
            transform: rotate(45deg);
            box-shadow: 3px 3px 5px rgba(0,0,0,0.06);
        }
        .wa-tooltip-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        .wa-tooltip-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, #25D366, #128C7E);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .wa-tooltip-avatar svg {
            width: 22px;
            height: 22px;
            fill: white;
        }
        .wa-tooltip-info small {
            display: block;
            font-size: 10px;
            color: #25D366;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .wa-tooltip-info p {
            margin: 0;
            font-size: 13px;
            font-weight: 600;
            color: #2C3E50;
            line-height: 1.2;
        }
        .wa-tooltip-msg {
            background: #f0fdf4;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 12.5px;
            color: #374151;
            line-height: 1.5;
            margin-bottom: 12px;
            border-left: 3px solid #25D366;
        }
        .wa-chat-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 10px 14px;
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: filter 0.2s ease, transform 0.15s ease;
            box-shadow: 0 4px 14px rgba(37,211,102,0.35);
        }
        .wa-chat-btn:hover {
            filter: brightness(1.08);
            color: white;
            transform: translateY(-1px);
        }
        .wa-chat-btn svg {
            width: 16px;
            height: 16px;
            fill: white;
        }
        /* Close tooltip button */
        .wa-tooltip-close {
            position: absolute;
            top: 8px;
            right: 10px;
            background: none;
            border: none;
            font-size: 16px;
            color: #9CA3AF;
            cursor: pointer;
            line-height: 1;
            padding: 2px 4px;
            border-radius: 4px;
        }
        .wa-tooltip-close:hover {
            color: #374151;
            background: #f3f4f6;
        }
        /* Badge notif dot */
        .wa-notif-dot {
            position: absolute;
            top: 2px;
            right: 2px;
            width: 12px;
            height: 12px;
            background: #FF5252;
            border-radius: 50%;
            border: 2px solid white;
            animation: wa-dot-bounce 1.5s ease infinite;
        }
        @keyframes wa-dot-bounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.3); }
        }
        @media (max-width: 768px) {
            #wa-float-btn {
                bottom: 90px;
                right: 16px;
                /* Pastikan selalu terlihat di mobile */
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
            }
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

            <!-- Logo Section (Desktop Only) -->
            <a href="{{ route('student.dashboard') }}" class="logo-section desktop-only" style="transform: translateX(50px);text-decoration: none;">
                <img src="{{ asset('ush.png') }}" alt="USH Logo" class="header-logo">
                <h4 class="app-title">Universitas Sugeng Hartono</h4>
            </a>

            <!-- User Info -->
            <div class="user-info">
                <div class="user-profile" id="userProfile">
                    <div class="user-details">
                        <h5>Halo, {{ explode(' ', session('student_nama'))[0] }}!</h5>
                        <p>Student {{ session('student_prodi') }}</p>
                    </div>
                    <div class="user-avatar">
                        @if (session('path_pic') !== '0')
                        <img src="{{ asset('storage/' . session('path_pic')) }}" alt="Profile">
                        @else
                        <i class="bi bi-person-circle" style="font-size: 35px; color: var(--primary-orange);"></i>
                        @endif
                    </div>

                    <!-- Dropdown Menu -->
                    <div class="user-dropdown" id="userDropdown">
                        <a href="{{ route('student.dashboard') }}" class="dropdown-item">
                            <i class="bi bi-house-door"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('student.personal.editDataIndex') }}" class="dropdown-item">
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
            <a href="{{ route('student.dashboard') }}" class="nav-item {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
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
            <a href="{{ route('student.personal.editDataIndex') }}" class="nav-item {{ request()->routeIs('student.personal.*') ? 'active' : '' }}">
                <div class="nav-icon">
                    <i class="bi bi-person-circle"></i>
                </div>
                <span>Profile</span>
            </a>
        </div>
    </nav>

    <!-- Bottom Navigation (Mobile) -->
    <nav class="bottom-nav">
        <a href="{{ route('student.dashboard') }}" class="nav-item {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
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
        <a href="{{ route('student.personal.editDataIndex') }}" class="nav-item {{ request()->routeIs('student.personal.*') ? 'active' : '' }}">
            <div class="nav-icon">
                <i class="bi bi-person-circle"></i>
            </div>
            <span>Profile</span>
        </a>
    </nav>

    <!-- ============================================
         FLOATING WHATSAPP BUTTON
         ============================================ -->
    @if($isWaEnabled)
    <div id="wa-float-btn" title="Chat dengan Admin">
        <!-- Pulse ring -->
        <div class="wa-pulse-ring"></div>

        <!-- Tooltip Popup -->
        <div class="wa-tooltip" id="waTooltip">
            <button class="wa-tooltip-close" id="waTooltipClose" title="Tutup">&times;</button>
            <div class="wa-tooltip-header">
                <div class="wa-tooltip-avatar">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                </div>
                <div class="wa-tooltip-info">
                    <small>Admin USH</small>
                    <p>{{ $waTitle }}</p>
                </div>
            </div>
            <div class="wa-tooltip-msg">
                {!! nl2br(e($waMessage)) !!}
            </div>
            <a
                href="https://wa.me/{{ $waNumber }}?text={{ urlencode($waTemplate) }}"
                target="_blank"
                rel="noopener"
                class="wa-chat-btn"
                id="waChatLink"
            >
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                Chat Sekarang
            </a>
        </div>

        <!-- Main Button -->
        <div class="wa-btn-circle" id="waBtnCircle">
            <div class="wa-notif-dot"></div>
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
        </div>
    </div>
    @endif

    <!-- Main Content -->
    <div class="container">
        <!-- Incomplete Profile Global Modal -->
        @if(auth()->guard('student')->check() && !auth()->guard('student')->user()->isProfileComplete())
            <div class="modal fade" id="globalIncompleteProfileModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content celebration-card" style="border: 1px solid rgba(255, 152, 0, 0.18);">
                        <div class="modal-body celebration-body">
                            <div class="celebration-icon" style="background: linear-gradient(135deg, rgba(255, 152, 0, 0.22), rgba(255, 179, 71, 0.12)); color: var(--primary-orange);">
                                <i class="bi bi-exclamation-circle-fill"></i>
                            </div>
                            <h4 class="celebration-title" style="margin-bottom: 10px;">Profil Belum Lengkap!</h4>
                            <p class="celebration-text">Hai <strong>{{ explode(' ', auth()->guard('student')->user()->nama_lengkap ?? '')[0] }}</strong>,<br>Mohon lengkapi data profil Anda (seperti NIK, NISN, Nama Orang Tua, dll) untuk mengakses menu ini dan keperluan SKPI/Sidang.</p>
                            
                            <div class="celebration-actions" style="margin-top: 20px;">
                                <button type="button" class="btn btn-light btn-close-soft" data-bs-dismiss="modal">
                                    Nanti Saja
                                </button>
                                <a href="{{ route('student.personal.editDataIndex') }}" class="btn btn-primary btn-read-soft" style="text-decoration: none;">
                                    Lengkapi Sekarang
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <script>
                // Make this flag available globally
                window.isProfileIncomplete = true;
            </script>
        @else
            <script>
                window.isProfileIncomplete = false;
            </script>
        @endif

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

        // Intercept link clicks if profile is incomplete
        document.addEventListener('DOMContentLoaded', () => {
            if (window.isProfileIncomplete) {
                // Select all links that might be restricted
                const restrictedLinks = document.querySelectorAll('.menu-card, .nav-item, .dropdown-item');
                
                restrictedLinks.forEach(link => {
                    link.addEventListener('click', function(e) {
                        const href = this.getAttribute('href');
                        // Allow logout, dashboard, and profile routes
                        if (!href || href === '#' || href.includes('logout') || href.includes('personal/edit') || href === '{{ route('student.dashboard') }}' || this.classList.contains('logout')) {
                            return; // Allow navigation
                        }
                        
                        // Prevent navigation and show modal
                        e.preventDefault();
                        const modalEl = document.getElementById('globalIncompleteProfileModal');
                        if (modalEl) {
                            const profileModal = bootstrap.Modal.getOrCreateInstance(modalEl, { backdrop: 'static', keyboard: false });
                            profileModal.show();
                        }
                    });
                });
            }
        });
    </script>

    <!-- ============================================
         WHATSAPP FLOAT BUTTON SCRIPT
         ============================================ -->
    @if($isWaEnabled)
    <script>
    (function () {
        const waBtn     = document.getElementById('wa-float-btn');
        const tooltip   = document.getElementById('waTooltip');
        const closeBtn  = document.getElementById('waTooltipClose');
        const btnCircle = document.getElementById('waBtnCircle');

        if (!waBtn) return;

        /* ── Helpers ── */
        var isMobile = function () { return window.innerWidth <= 768; };

        // Viewport key: simpan viewport width agar posisi desktop tidak dipakai di mobile
        var currentVpKey = (window.innerWidth <= 768 ? 'mobile' : 'desktop');

        /* ── Clamp posisi agar selalu dalam viewport ── */
        function clampPos(left, top) {
            var maxLeft = window.innerWidth  - waBtn.offsetWidth;
            var maxTop  = window.innerHeight - waBtn.offsetHeight;
            return {
                left: Math.max(4, Math.min(left, maxLeft - 4)),
                top:  Math.max(4, Math.min(top,  maxTop  - 4))
            };
        }

        /* ── Restore last position dari localStorage ── */
        function restorePosition() {
            try {
                var saved = JSON.parse(localStorage.getItem('wa_btn_pos') || 'null');
                // Hanya pakai posisi jika disimpan pada viewport yang sama (mobile/desktop)
                if (saved && saved.vpKey === currentVpKey) {
                    var clamped = clampPos(saved.left, saved.top);
                    waBtn.style.right  = 'auto';
                    waBtn.style.bottom = 'auto';
                    waBtn.style.left   = clamped.left + 'px';
                    waBtn.style.top    = clamped.top  + 'px';
                } else {
                    // Hapus posisi lama dari viewport berbeda
                    localStorage.removeItem('wa_btn_pos');
                }
            } catch (err) {
                localStorage.removeItem('wa_btn_pos');
            }
        }

        // Tunggu hingga elemen rendered agar offsetWidth tersedia
        window.addEventListener('load', restorePosition);

        /* ── Draggable Logic ── */
        var isDragging = false;
        var hasDragged = false;
        var startX, startY, startLeft, startTop;
        var touchMoved = false;

        function getInitialPos() {
            var rect = waBtn.getBoundingClientRect();
            return { left: rect.left, top: rect.top };
        }

        function onPointerDown(e) {
            // Hanya tombol kiri mouse
            if (e.type === 'mousedown' && e.button !== 0) return;
            isDragging = true;
            hasDragged = false;
            touchMoved = false;

            var pos = getInitialPos();
            startLeft = pos.left;
            startTop  = pos.top;

            var clientX = e.touches ? e.touches[0].clientX : e.clientX;
            var clientY = e.touches ? e.touches[0].clientY : e.clientY;
            startX = clientX - startLeft;
            startY = clientY - startTop;

            waBtn.style.right  = 'auto';
            waBtn.style.bottom = 'auto';
            waBtn.style.left   = startLeft + 'px';
            waBtn.style.top    = startTop  + 'px';

            document.addEventListener('mousemove', onPointerMove);
            document.addEventListener('mouseup',   onPointerUp);
            document.addEventListener('touchmove', onPointerMove, { passive: false });
            document.addEventListener('touchend',  onPointerUp);
        }

        function onPointerMove(e) {
            if (!isDragging) return;
            if (e.cancelable) e.preventDefault();

            var clientX = e.touches ? e.touches[0].clientX : e.clientX;
            var clientY = e.touches ? e.touches[0].clientY : e.clientY;

            var newLeft = clientX - startX;
            var newTop  = clientY - startY;

            var clamped = clampPos(newLeft, newTop);
            waBtn.style.left = clamped.left + 'px';
            waBtn.style.top  = clamped.top  + 'px';

            // Anggap drag jika bergerak > 8px
            if (Math.abs(clientX - (startX + startLeft)) > 8 ||
                Math.abs(clientY - (startY + startTop))  > 8) {
                hasDragged = true;
                touchMoved = true;
            }
        }

        function onPointerUp(e) {
            if (!isDragging) return;
            isDragging = false;

            document.removeEventListener('mousemove', onPointerMove);
            document.removeEventListener('mouseup',   onPointerUp);
            document.removeEventListener('touchmove', onPointerMove);
            document.removeEventListener('touchend',  onPointerUp);

            // Simpan posisi beserta info viewport
            var rect = waBtn.getBoundingClientRect();
            localStorage.setItem('wa_btn_pos', JSON.stringify({
                left:  rect.left,
                top:   rect.top,
                vpKey: currentVpKey
            }));

            // Jika ini touchend dan TIDAK ada gerakan → toggle tooltip (mobile tap)
            if (e && e.type === 'touchend' && !touchMoved) {
                tooltip.classList.toggle('open');
                hasDragged = false; // reset agar click event berikutnya juga bisa
            }
        }

        // Bind mousedown and touchstart only to the circle button (btnCircle)
        // to prevent touch events on the open tooltip (like clicking the WA chat link)
        // from triggering drag start and closing/hijacking the link on mobile touch devices.
        btnCircle.addEventListener('mousedown',  onPointerDown);
        btnCircle.addEventListener('touchstart', onPointerDown, { passive: true });

        /* ── Toggle Tooltip on Click / Tap ── */
        btnCircle.addEventListener('click', function (e) {
            if (hasDragged) {
                hasDragged = false;
                return;
            }
            tooltip.classList.toggle('open');
        });

        /* ── Close Tooltip ── */
        closeBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            tooltip.classList.remove('open');
        });
        closeBtn.addEventListener('touchend', function (e) {
            e.stopPropagation();
            e.preventDefault();
            tooltip.classList.remove('open');
        });

        /* ── Close when clicking/tapping outside ── */
        document.addEventListener('click', function (e) {
            if (!waBtn.contains(e.target)) {
                tooltip.classList.remove('open');
            }
        });
        document.addEventListener('touchend', function (e) {
            if (!waBtn.contains(e.target)) {
                tooltip.classList.remove('open');
            }
        });

        /* ── Re-clamp saat window di-resize ── */
        window.addEventListener('resize', function () {
            var newVpKey = (window.innerWidth <= 768 ? 'mobile' : 'desktop');
            if (newVpKey !== currentVpKey) {
                // Viewport type berubah → reset ke posisi CSS default
                currentVpKey = newVpKey;
                localStorage.removeItem('wa_btn_pos');
                waBtn.style.left   = '';
                waBtn.style.top    = '';
                waBtn.style.right  = '';
                waBtn.style.bottom = '';
            } else {
                // Viewport type sama → clamp posisi current
                var rect = waBtn.getBoundingClientRect();
                var clamped = clampPos(rect.left, rect.top);
                waBtn.style.right  = 'auto';
                waBtn.style.bottom = 'auto';
                waBtn.style.left   = clamped.left + 'px';
                waBtn.style.top    = clamped.top  + 'px';
            }
        });

        /* ── Auto-show tooltip hint setelah 4 detik (hanya kunjungan pertama) ── */
        if (!localStorage.getItem('wa_hint_shown')) {
            setTimeout(function () {
                tooltip.classList.add('open');
                localStorage.setItem('wa_hint_shown', '1');
                setTimeout(function () {
                    tooltip.classList.remove('open');
                }, 6000);
            }, 4000);
        }
    })();
    </script>
    @endif
</body>

</html>