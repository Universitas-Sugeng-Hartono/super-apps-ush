<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $pageTitle ?? 'USH Super Apps' }}</title>

    <link rel="icon" href="{{ asset('icon.png') }}">

    {{-- Fonts & CSS --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #1e63c5;
            --blue-soft: #eaf2ff;
            --green: #2fb344;
            --yellow: #f6b10a;
            --radius: 18px;
            --shadow: 0 14px 40px rgba(0,0,0,.10);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #73c2fb, #00aeee);
            min-height: 100vh;
            margin: 0;
            color: #0f172a;
        }

         /* background pattern (ikon samar, sedikit lebih cerah dari background) */
         body::before {
             content: "";
             position: fixed;
             inset: 0;
             pointer-events: none;
             background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='320' height='320' viewBox='0 0 320 320'%3E%3Cg fill='none' stroke='%23ffffff' stroke-opacity='0.34' stroke-width='3'%3E%3Cpath d='M46 276h120M70 270V124l36-16 36 16v146'/%3E%3Cpath d='M70 156h72M70 186h72M70 216h72'/%3E%3Cpath d='M238 92c18-28 54-34 82-16 28 18 34 54 16 82-18 28-54 34-82 16-28-18-34-54-16-82z'/%3E%3Cpath d='M262 108l32 22M258 148l32-22'/%3E%3Cpath d='M255 240c0-12 10-22 22-22s22 10 22 22c0 8-4 14-9 18v10h-26v-10c-5-4-9-10-9-18z'/%3E%3Cpath d='M264 276h26'/%3E%3Cpath d='M176 250l18-18 18 18-18 18z'/%3E%3C/g%3E%3C/svg%3E");
             background-size: 300px 300px;
             background-repeat: repeat;
             opacity: 0.38;
             z-index: 0;
         }

         /* layer kedua: ikon lebih besar, lebih soft (biar mirip contoh yang ada outline besar) */
         body::after {
             content: "";
             position: fixed;
             inset: 0;
             pointer-events: none;
             background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='520' height='520' viewBox='0 0 520 520'%3E%3Cg fill='none' stroke='%23ffffff' stroke-opacity='0.26' stroke-width='3'%3E%3Cpath d='M70 430h180M100 422V210l55-25 55 25v212'/%3E%3Cpath d='M100 255h110M100 300h110M100 345h110'/%3E%3Cpath d='M360 140c28-44 84-54 128-26 44 28 54 84 26 128-28 44-84 54-128 26-44-28-54-84-26-128z'/%3E%3Cpath d='M398 168l50 34M392 230l50-34'/%3E%3Cpath d='M392 356c0-18 14-32 32-32s32 14 32 32c0 12-6 22-14 28v14h-36v-14c-8-6-14-16-14-28z'/%3E%3Cpath d='M404 412h40'/%3E%3C/g%3E%3C/svg%3E");
             background-size: 520px 520px;
             background-repeat: repeat;
             background-position: 40px 20px;
             opacity: 0.20;
             z-index: 0;
         }

        /* global wrapper */
        .landing-wrap {
            position: relative;
            z-index: 1;
            padding: 3rem 1rem 4rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
        }

        /* white card */
        .landing-card {
            background: #fff;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            position: relative;
            padding: 2.2rem 2.4rem;
        }

        /* topbar */
        .topbar {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
        }

        .brand-center {
            display: inline-flex;
            align-items: center;
            gap: .6rem;
            padding: .35rem 1rem;
            background: rgba(234,242,255,.8);
            border-radius: 999px;
            font-weight: 600;
        }

        .brand-logo {
            width: 42px;
            height: 42px;
            object-fit: contain;
        }

        /* title */
        .title-main {
            font-weight: 800;
            font-size: 1.9rem;
            color: var(--primary);
            line-height: 1.15;
        }

        .subtitle {
            color: rgba(15,23,42,.65);
            font-size: .95rem;
        }

        /* buttons */
        .btn-login {
            background: var(--yellow);
            color: #fff;
            font-weight: 600;
            border-radius: 12px;
            padding: .55rem 1rem;
            border: none;
        }

        .btn-login:hover {
            background: #e9a507;
            color: #fff;
        }

        .btn-outline-primary-ush {
            border: 2px solid var(--primary);
            color: var(--primary);
            font-weight: 600;
            border-radius: 12px;
            padding: .7rem;
        }

        .btn-outline-primary-ush:hover {
            background: var(--primary);
            color: #fff;
        }

        /* stat card */
        .stat-card {
            border-radius: 14px;
            box-shadow: 0 10px 20px rgba(0,0,0,.08);
        }

        .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,.4);
        }

        .stat-label {
            font-size: .85rem;
            opacity: .85;
        }

        .stat-value {
            font-size: 1.9rem;
            font-weight: 800;
        }

        .stat-blue { background: var(--blue-soft); }
        .stat-green { background: var(--green); color:#fff; }
        .stat-orange { background: var(--yellow); color:#fff; }

        /* panels */
        .panel {
            border-radius: 14px;
            box-shadow: 0 10px 20px rgba(0,0,0,.08);
            border: none;
        }

        /* calendar */
        .calendar-table {
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid rgba(15,23,42,.08);
        }

        .calendar-table th {
            background: var(--blue-soft);
            text-align: center;
            font-weight: 600;
        }

        .calendar-table td {
            height: 48px;
            text-align: center;
            vertical-align: middle;
        }

        .day-pill {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .day-muted {
            opacity: .3;
        }

        /* decoration */
        .decor-megaphone {
            position: absolute;
            width: 200px;
            filter: drop-shadow(0 8px 16px rgba(0,0,0,.25));
            pointer-events: none;
        }

        .decor-megaphone.tr {
            top: -70px;
            right: -70px;
        }

        .decor-megaphone.bl {
            bottom: -70px;
            left: -70px;
            transform: rotate(-12deg);
            transform: scaleX(-1);
        }

        .note {
            font-size: .8rem;
            opacity: .65;
            font-style: italic;
        }

        @media (max-width: 768px) {
            .title-main { font-size: 1.45rem; }
            .landing-card { padding: 1.5rem; }
            .topbar { grid-template-columns: 1fr; gap: .75rem; }

            /* Mobile-safe layout:
               - hilangkan transform/scale dari inline style
               - beri ruang ekstra di bawah agar konten tidak tertutup navbar/browser UI
            */
            .landing-wrap {
                transform: none !important;
                scale: 1 !important;
                max-height: none !important;
                padding-top: 1.75rem;
                padding-bottom: 5.5rem;
            }
        }
    </style>

    @stack('css')
</head>
<body>

    @yield('content')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
