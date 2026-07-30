<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }}</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('public/icon.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('public/icon.png') }}">

    <!-- Apple Touch Icon (iOS) -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('public/icon.png') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Primary Colors - USH Theme */
            --primary-orange: #FF9800;
            --primary-blue: #29375d;
            --primary-yellow: #FFC107;
            
            /* Gradients */
            --primary-gradient: linear-gradient(135deg, #FF9800 0%, #FFB347 100%);
            --secondary-gradient: linear-gradient(135deg, #29375d 0%, #3d4f7a 100%);
            --success-gradient: linear-gradient(135deg, #5B9BD5 0%, #7DB8E8 100%);
            
            /* Background Colors */
            --bg-cream: #FFF5E6;
            --bg-light: #FFFBF0;
            
            /* Text Colors */
            --text-dark: #2C3E50;
            --text-gray: #7F8C8D;
            
            /* Shadow Effects */
            --shadow-light: 0 4px 15px rgba(0, 0, 0, 0.1);
            --shadow-heavy: 0 10px 30px rgba(0, 0, 0, 0.15);
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 8px 30px rgba(0, 0, 0, 0.12);
            
            --border-radius: 16px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            background: var(--bg-cream);
            min-height: 100vh;
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="0.5" fill="%23FF9800" opacity="0.03"/><circle cx="20" cy="20" r="0.3" fill="%23FF9800" opacity="0.02"/><circle cx="80" cy="30" r="0.4" fill="%23FFB347" opacity="0.025"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            pointer-events: none;
            z-index: 1;
        }

        .login-container {
            position: relative;
            z-index: 2;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-heavy);
            padding: 2.5rem;
            width: 100%;
            max-width: 440px;
            position: relative;
            overflow: hidden;
            transform: translateY(0);
            transition: var(--transition);
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 2px;
            background: var(--primary-gradient);
            transition: var(--transition);
        }

        .login-card:hover::before {
            left: 0;
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        .login-title {
            font-size: 2rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 2rem;
            text-align: center;
        }

        .nav-tabs {
            border: none;
            background: rgba(248, 249, 250, 0.6);
            border-radius: 12px;
            padding: 6px;
            margin-bottom: 2rem;
        }

        .nav-tabs .nav-link {
            border: none;
            background: transparent;
            color: #6c757d;
            font-weight: 500;
            padding: 12px 24px;
            border-radius: 8px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .nav-tabs .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--primary-gradient);
            transition: var(--transition);
            z-index: -1;
        }

        .nav-tabs .nav-link.active::before {
            left: 0;
        }

        .nav-tabs .nav-link.active {
            background: transparent;
            color: white;
            box-shadow: var(--shadow-light);
        }

        .nav-tabs .nav-link:hover:not(.active) {
            background: rgba(255, 152, 0, 0.1);
            color: var(--primary-orange);
        }

        .form-floating {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .form-control {
            border: 2px solid rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            padding: 1rem 1.25rem;
            font-size: 1rem;
            transition: var(--transition);
            background: rgba(255, 255, 255, 0.8);
            height: auto;
        }

        .form-control:focus {
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 0.25rem rgba(255, 152, 0, 0.15);
            background: white;
            transform: translateY(-2px);
        }

        .form-label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            border-radius: 12px;
            padding: 1rem 2rem;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: var(--transition);
        }

        .btn-primary:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-heavy);
        }

        .btn-success {
            background: var(--success-gradient);
            border: none;
            border-radius: 12px;
            padding: 1rem 2rem;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .btn-success::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: var(--transition);
        }

        .btn-success:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-heavy);
        }

        .alert {
            border: none;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-top: 1.5rem;
            font-weight: 500;
            animation: slideIn 0.3s ease-out;
        }

        .alert-danger {
            background: linear-gradient(135deg, #FF5252 0%, #FF8A80 100%);
            color: white;
        }

        .alert-success {
            background: linear-gradient(135deg, #4CAF50 0%, #81C784 100%);
            color: white;
        }

        /* Loading Animation */
        .loading {
            position: relative;
            pointer-events: none;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .loading span {
            opacity: 0;
        }

        /* Loading Overlay */
        .form-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: none;
            align-items: center;
            justify-content: center;
            border-radius: var(--border-radius);
            z-index: 10;
        }

        .form-overlay.show {
            display: flex;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(255, 152, 0, 0.3);
            border-top: 3px solid var(--primary-orange);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        /* Animations */
        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-card {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Input Focus Effects */
        .form-control:focus+.form-label,
        .form-control:not(:placeholder-shown)+.form-label {
            color: var(--primary-orange);
        }

        /* Responsive */
        @media (max-width: 576px) {
            .login-card {
                padding: 1.5rem;
                margin: 10px;
            }

            .login-title {
                font-size: 1.5rem;
            }
        }

        /* Icon Styling */
        .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            transition: var(--transition);
        }

        .form-control:focus~.input-icon {
            color: var(--primary-orange);
        }
    </style>
