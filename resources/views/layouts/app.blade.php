<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'BPS Karanganyar') }} - Admin</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Laravel Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            box-sizing: border-box;
        }

        html {
            width: 100%;
            min-height: 100%;
            overflow-x: hidden;
        }

        body {
            width: 100%;
            min-height: 100vh;
            margin: 0;
            padding: 0;

            font-family: 'Plus Jakarta Sans',
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;

            background: #f4f7fb;
            color: #10233f;

            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        button,
        input,
        textarea,
        select {
            font-family: inherit;
        }

        a {
            -webkit-tap-highlight-color: transparent;
        }

        ::selection {
            background: rgba(7, 87, 184, .15);
            color: #0757b8;
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 7px;
            height: 7px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 999px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* SweetAlert agar mengikuti tema dashboard */
        .swal2-popup {
            border-radius: 18px !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }

        .swal2-title {
            font-size: 18px !important;
            font-weight: 800 !important;
        }

        .swal2-html-container {
            font-size: 12px !important;
        }

        .swal2-confirm,
        .swal2-cancel {
            border-radius: 9px !important;
            font-size: 11px !important;
            font-weight: 700 !important;
        }

        /* Fokus keyboard */
        :focus-visible {
            outline: 3px solid rgba(11, 116, 229, .2);
            outline-offset: 2px;
        }

        /* Mengurangi animasi jika user mematikannya */
        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .01ms !important;
                scroll-behavior: auto !important;
            }
        }
    </style>
</head>

<body class="antialiased">

    {{-- SweetAlert: Berhasil --}}
    @if(session('status'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: @json(session('status')),
                    confirmButtonColor: '#0757B8',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            });
        </script>
    @endif

    {{-- SweetAlert: Error --}}
    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: @json(session('error')),
                    confirmButtonColor: '#DC2626'
                });
            });
        </script>
    @endif

    {{-- Main Dashboard --}}
    @yield('content')

</body>
</html>