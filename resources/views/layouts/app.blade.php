<!doctype html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'BPS Karanganyar') }} - Admin Dashboard</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Laravel Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
        }

        html {
            width: 100%;
            min-height: 100%;
            overflow-x: hidden;
            scroll-behavior: smooth;
        }

        body {
            width: 100%;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #f8fafc;
            color: #0f172a;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        button, input, textarea, select {
            font-family: inherit;
        }

        a {
            -webkit-tap-highlight-color: transparent;
            text-decoration: none;
        }

        ::selection {
            background: rgba(37, 99, 235, 0.18);
            color: #1e40af;
        }

        /* Modern Slim Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 999px;
            border: 2px solid #f1f5f9;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Modern SweetAlert Custom Styling */
        .swal2-popup {
            border-radius: 20px !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            padding: 24px !important;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25) !important;
            border: 1px solid rgba(226, 232, 240, 0.8) !important;
        }

        .swal2-title {
            font-size: 18px !important;
            font-weight: 800 !important;
            color: #0f172a !important;
            letter-spacing: -0.02em !important;
        }

        .swal2-html-container {
            font-size: 13px !important;
            color: #475569 !important;
            line-height: 1.6 !important;
        }

        .swal2-confirm {
            border-radius: 10px !important;
            font-size: 12px !important;
            font-weight: 700 !important;
            padding: 10px 20px !important;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3) !important;
            transition: all 0.2s ease !important;
        }

        .swal2-cancel {
            border-radius: 10px !important;
            font-size: 12px !important;
            font-weight: 700 !important;
            padding: 10px 20px !important;
            background: #f1f5f9 !important;
            color: #475569 !important;
            transition: all 0.2s ease !important;
        }

        .swal2-cancel:hover {
            background: #e2e8f0 !important;
            color: #0f172a !important;
        }

        /* Keyboard Focus */
        :focus-visible {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .01ms !important;
                scroll-behavior: auto !important;
            }
        }
    </style>
</head>

<body class="antialiased">

    {{-- SweetAlert: Flash Status Message --}}
    @if(session('status'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: @json(session('status')),
                    confirmButtonColor: '#2563eb',
                    timer: 3500,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    customClass: {
                        popup: 'swal2-border-success'
                    }
                });
            });
        </script>
    @endif

    {{-- SweetAlert: Flash Error Message --}}
    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan',
                    text: @json(session('error')),
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'Tutup'
                });
            });
        </script>
    @endif

    {{-- Main View Container --}}
    @yield('content')

</body>
</html>