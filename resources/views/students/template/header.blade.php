<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('public/icon.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('public/icon.png') }}">

    <!-- Apple Touch Icon (iOS) -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('public/icon.png') }}">

    <!-- Android/Chrome -->
    <link rel="manifest" href="/site.webmanifest">
    <title>{{ $pageTitle }}</title>
    <!-- FontAwesome 6.2.0 CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css"
        integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- (Optional) Use CSS or JS implementation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/js/all.min.js"
        integrity="sha512-naukR7I+Nk6gp7p5TMA4ycgfxaZBJ7MO5iC3Fp6ySQyKFHOGfpkSZkYVWV5R7u7cfAicxanwYQ5D1e17EfJcMA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    @stack('css')
    <style>
        .nav-icon {
            margin-right: 8px;
            width: 20px;
            text-align: center;
            font-size: 16px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 10px;
            color: #444;
            text-decoration: none;
            transition: background 0.2s, color 0.2s;
        }

        .nav-link.active,
        .nav-link:hover {
            background: #f0f4ff;
            color: #007bff;
            border-radius: 6px;
        }

        .photo-wrapper {

            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0f0f0;
        }

        .photo-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }


        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .admin-container {

            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: linear-gradient(145deg, #3338A0, #4a4fb8);
            color: white;
            padding: 2rem 0;
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 4px 0 15px rgba(51, 56, 160, 0.2);
            transition: transform 0.3s ease;
        }

        .sidebar.mobile-hidden {
            transform: translateX(-100%);
        }

        .logo {
            text-align: center;
            padding: 0 2rem 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo h1 {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(45deg, #fff, #e8e9ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-menu {
            padding: 2rem 0;
            list-style: none;
        }

        .nav-item {
            margin: 0.5rem 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 1rem 2rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }

        .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: #fff;
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .nav-link:hover::before,
        .nav-link.active::before {
            transform: scaleY(1);
        }

        .nav-icon {
            margin-right: 1rem;
            font-size: 1.2rem;
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
            transition: margin-left 0.3s ease;
        }

        .header {
            background: white;
            padding: 1.5rem 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h2 {
            color: #3338A0;
            font-size: 1.8rem;
            font-weight: 600;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .profile-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(45deg, #3338A0, #4a4fb8);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .menu-toggle {
            display: none;
            background: #3338A0;
            color: white;
            border: none;
            padding: 0.8rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.2rem;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .card-title {
            color: #333;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: linear-gradient(45deg, #3338A0, #4a4fb8);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .card-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: #3338A0;
            margin-bottom: 0.5rem;
        }

        .card-subtitle {
            color: #666;
            font-size: 0.9rem;
        }

        .chart-container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .chart-placeholder {
            height: 300px;
            background: linear-gradient(135deg, #f8f9ff 0%, #e8ebff 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #3338A0;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .mobile-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(145deg, #3338A0, #4a4fb8);
            padding: 1rem;
            box-shadow: 0 -4px 15px rgba(51, 56, 160, 0.2);
        }

        .mobile-nav-list {
            display: flex;
            justify-content: space-around;
            list-style: none;
        }

        .mobile-nav-item {
            text-align: center;
        }

        .mobile-nav-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s ease;
            font-size: 0.8rem;
        }

        .mobile-nav-link:hover,
        .mobile-nav-link.active {
            color: white;
        }

        .mobile-nav-icon {
            font-size: 1.5rem;
            margin-bottom: 0.3rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
                padding-bottom: 6rem;
            }

            .header {
                padding: 1rem;
            }

            .menu-toggle {
                display: block;
            }

            .mobile-nav {
                display: block;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .card {
                padding: 1.5rem;
            }

            .chart-container {
                padding: 1.5rem;
            }

            .user-profile span {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .header h2 {
                font-size: 1.4rem;
            }

            .card-value {
                font-size: 2rem;
            }

            .main-content {
                padding: 0.5rem;
                padding-bottom: 6rem;
            }
        }
    </style>
    <style>
        .toast-container {
            margin: 10px;
        }

        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1055;
        }

        .sh {
            box-shadow: rgba(0, 0, 0, 0.1) 0px 1px 3px 0px, rgba(0, 0, 0, 0.06) 0px 1px 2px 0px;
        }

        .sticky-footer {
            background-color: #2c3e50;
            color: #ecf0f1;
            padding: 1.5rem 0;
            text-align: center;
            font-size: 0.95rem;
            border-top: 2px solid #34495e;
        }

        .copyright span {
            font-weight: 500;
            color: #000000;
        }

        ol,
        ul {
            padding-left: 0rem !important;
        }
    </style>
</head>

<body>