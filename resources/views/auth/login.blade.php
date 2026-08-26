@extends('layouts.app')

@section('content')

<style>
    /* =========================================================
       BPS KARANGANYAR - LOGIN PAGE
       ========================================================= */

    :root {
        --bps-blue: #0757b8;
        --bps-blue-dark: #043b80;
        --bps-blue-light: #0b74e5;
        --bps-orange: #f58220;
        --bps-ink: #142b48;
        --bps-muted: #7b8b9f;
        --bps-border: #e4eaf1;
        --bps-bg: #f4f7fb;
    }

    .login-page {
        min-height: 100vh;
        width: 100%;
        display: flex;
        background: var(--bps-bg);
        font-family: 'Plus Jakarta Sans', sans-serif;
        overflow: hidden;
    }

    /* =========================================================
       LEFT SIDE
       ========================================================= */

    .login-visual {
        position: relative;
        width: 54%;
        min-height: 100vh;
        overflow: hidden;
        color: white;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 38px 48px;
        background:
            radial-gradient(
                circle at 80% 20%,
                rgba(255,255,255,.13),
                transparent 28%
            ),
            linear-gradient(
                135deg,
                #043b80 0%,
                #0757b8 48%,
                #0b74e5 100%
            );
    }

    .login-visual::before {
        content: "";
        position: absolute;
        width: 620px;
        height: 620px;
        border: 1px solid rgba(255,255,255,.11);
        border-radius: 50%;
        right: -300px;
        top: -220px;
    }

    .login-visual::after {
        content: "";
        position: absolute;
        width: 470px;
        height: 470px;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 50%;
        left: -300px;
        bottom: -260px;
    }

    /* Decorative grid */

    .visual-grid {
        position: absolute;
        inset: 0;
        opacity: .13;
        background-image:
            linear-gradient(rgba(255,255,255,.2) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,.2) 1px, transparent 1px);
        background-size: 45px 45px;
        mask-image: linear-gradient(
            to bottom right,
            black,
            transparent 75%
        );
        pointer-events: none;
    }

    /* Logo */

    .visual-header {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        gap: 13px;
    }

    .visual-logo {
        width: 47px;
        height: 47px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.2);
        backdrop-filter: blur(12px);
        font-size: 13px;
        font-weight: 900;
        box-shadow: 0 10px 25px rgba(0,0,0,.12);
    }

    .visual-brand strong {
        display: block;
        font-size: 13px;
        font-weight: 900;
        letter-spacing: -.01em;
    }

    .visual-brand span {
        display: block;
        margin-top: 3px;
        font-size: 8px;
        font-weight: 700;
        letter-spacing: .11em;
        text-transform: uppercase;
        color: rgba(255,255,255,.68);
    }

    /* Main visual content */

    .visual-content {
        position: relative;
        z-index: 2;
        max-width: 570px;
        margin-top: -20px;
    }

    .visual-label {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 10px;
        margin-bottom: 18px;
        border: 1px solid rgba(255,255,255,.18);
        border-radius: 8px;
        background: rgba(255,255,255,.08);
        backdrop-filter: blur(10px);
        font-size: 8px;
        font-weight: 800;
        letter-spacing: .1em;
        text-transform: uppercase;
    }

    .visual-label-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #48e4a2;
        box-shadow: 0 0 12px rgba(72,228,162,.8);
    }

    .visual-content h1 {
        margin: 0;
        font-size: clamp(32px, 4vw, 52px);
        line-height: 1.05;
        letter-spacing: -.055em;
        font-weight: 900;
    }

    .visual-content h1 span {
        color: #9ed0ff;
    }

    .visual-content p {
        max-width: 500px;
        margin: 18px 0 0;
        color: rgba(255,255,255,.74);
        font-size: 11px;
        line-height: 1.85;
    }

    /* Decorative dashboard preview */

    .visual-preview {
        position: relative;
        width: 430px;
        max-width: 100%;
        margin-top: 30px;
        padding: 15px;
        border-radius: 17px;
        border: 1px solid rgba(255,255,255,.17);
        background: rgba(255,255,255,.08);
        backdrop-filter: blur(15px);
        box-shadow: 0 25px 60px rgba(0,0,0,.13);
        transform: perspective(900px) rotateY(-4deg) rotateX(2deg);
        animation: previewFloat 5s ease-in-out infinite;
    }

    @keyframes previewFloat {
        0%,100% {
            transform: perspective(900px) rotateY(-4deg) rotateX(2deg) translateY(0);
        }

        50% {
            transform: perspective(900px) rotateY(-4deg) rotateX(2deg) translateY(-6px);
        }
    }

    .preview-top {
        height: 27px;
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 0 5px;
    }

    .preview-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: rgba(255,255,255,.5);
    }

    .preview-body {
        display: grid;
        grid-template-columns: 70px 1fr;
        gap: 10px;
    }

    .preview-side {
        min-height: 120px;
        border-radius: 9px;
        background: rgba(255,255,255,.08);
        padding: 9px 7px;
    }

    .preview-side-line {
        height: 6px;
        border-radius: 10px;
        background: rgba(255,255,255,.18);
        margin-bottom: 8px;
    }

    .preview-side-line.active {
        background: rgba(255,255,255,.65);
    }

    .preview-main {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 7px;
        align-content: start;
    }

    .preview-card {
        height: 43px;
        border-radius: 8px;
        background: rgba(255,255,255,.1);
        padding: 8px;
    }

    .preview-card.wide {
        grid-column: span 2;
        height: 70px;
    }

    .preview-line {
        height: 5px;
        width: 55%;
        border-radius: 10px;
        background: rgba(255,255,255,.25);
        margin-bottom: 7px;
    }

    .preview-line.big {
        width: 70%;
        height: 8px;
        background: rgba(255,255,255,.65);
    }

    /* Visual footer */

    .visual-footer {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        color: rgba(255,255,255,.55);
        font-size: 8px;
    }

    .visual-footer strong {
        color: rgba(255,255,255,.85);
        font-weight: 800;
    }

    .visual-time {
        text-align: right;
    }

    .visual-time strong {
        display: block;
        font-size: 15px;
        font-variant-numeric: tabular-nums;
        letter-spacing: .02em;
    }

    .visual-time span {
        display: block;
        margin-top: 2px;
        font-size: 7px;
        color: rgba(255,255,255,.5);
    }

    /* =========================================================
       RIGHT SIDE
       ========================================================= */

    .login-panel {
        width: 46%;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px;
        background: #fff;
    }

    .login-box {
        width: 100%;
        max-width: 405px;
        animation: loginAppear .65s cubic-bezier(.22,1,.36,1) both;
    }

    @keyframes loginAppear {
        from {
            opacity: 0;
            transform: translateY(16px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .login-heading {
        margin-bottom: 28px;
    }

    .login-heading .mobile-logo {
        display: none;
    }

    .login-heading h2 {
        margin: 0;
        color: var(--bps-ink);
        font-size: 25px;
        line-height: 1.2;
        font-weight: 900;
        letter-spacing: -.04em;
    }

    .login-heading p {
        margin: 8px 0 0;
        color: var(--bps-muted);
        font-size: 10px;
        line-height: 1.7;
    }

    /* Error */

    .login-error {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px 13px;
        margin-bottom: 20px;
        border: 1px solid #fecaca;
        border-radius: 11px;
        background: #fff5f5;
        color: #b42323;
        font-size: 9px;
        line-height: 1.6;
        animation: errorShake .4s ease;
    }

    .login-error svg {
        flex: none;
        width: 16px;
        height: 16px;
        margin-top: 1px;
    }

    @keyframes errorShake {
        0%,100% { transform: translateX(0); }
        25% { transform: translateX(-4px); }
        75% { transform: translateX(4px); }
    }

    /* Form */

    .login-form {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .login-field label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 7px;
        color: #53657c;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .login-field label a {
        color: var(--bps-blue);
        font-size: 8px;
        text-transform: none;
        letter-spacing: 0;
    }

    .login-input-wrap {
        position: relative;
    }

    .login-input-icon {
        position: absolute;
        left: 13px;
        top: 50%;
        width: 17px;
        height: 17px;
        color: #9aa8b8;
        transform: translateY(-50%);
        pointer-events: none;
        transition: color .2s ease;
    }

    .login-input {
        width: 100%;
        height: 46px;
        padding: 0 13px 0 41px;
        border: 1px solid var(--bps-border);
        border-radius: 11px;
        outline: none;
        background: #fbfcfe;
        color: var(--bps-ink);
        font-size: 10px;
        transition:
            border-color .2s ease,
            box-shadow .2s ease,
            background .2s ease;
    }

    .login-input::placeholder {
        color: #aeb9c7;
    }

    .login-input:hover {
        border-color: #cbd7e5;
        background: #fff;
    }

    .login-input:focus {
        background: #fff;
        border-color: var(--bps-blue-light);
        box-shadow: 0 0 0 4px rgba(11,116,229,.08);
    }

    .login-input:focus + .login-input-icon {
        color: var(--bps-blue-light);
    }

    .login-input.password {
        padding-right: 45px;
    }

    .password-toggle {
        position: absolute;
        right: 9px;
        top: 50%;
        width: 31px;
        height: 31px;
        display: grid;
        place-items: center;
        border: 0;
        border-radius: 8px;
        background: transparent;
        color: #94a3b8;
        cursor: pointer;
        transform: translateY(-50%);
        transition: .2s ease;
    }

    .password-toggle:hover {
        color: var(--bps-blue);
        background: #edf5ff;
    }

    .password-toggle svg {
        width: 16px;
        height: 16px;
    }

    /* Remember */

    .login-options {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: -2px;
    }

    .remember-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #718096;
        font-size: 9px;
        cursor: pointer;
        user-select: none;
    }

    .remember-checkbox {
        appearance: none;
        width: 15px;
        height: 15px;
        margin: 0;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        background: #fff;
        cursor: pointer;
        transition: .2s ease;
        position: relative;
    }

    .remember-checkbox:checked {
        border-color: var(--bps-blue);
        background: var(--bps-blue);
    }

    .remember-checkbox:checked::after {
        content: "";
        position: absolute;
        width: 4px;
        height: 7px;
        left: 4px;
        top: 2px;
        border: solid white;
        border-width: 0 1.5px 1.5px 0;
        transform: rotate(45deg);
    }

    /* Submit */

    .login-submit {
        position: relative;
        width: 100%;
        height: 47px;
        margin-top: 3px;
        border: 0;
        border-radius: 11px;
        overflow: hidden;
        background: linear-gradient(
            110deg,
            #06499e,
            #0757b8,
            #0b74e5
        );
        color: white;
        font-size: 10px;
        font-weight: 900;
        cursor: pointer;
        box-shadow: 0 10px 23px rgba(7,87,184,.2);
        transition:
            transform .2s ease,
            box-shadow .2s ease,
            filter .2s ease;
    }

    .login-submit::before {
        content: "";
        position: absolute;
        top: 0;
        left: -80px;
        width: 55px;
        height: 100%;
        background: rgba(255,255,255,.2);
        transform: skewX(-22deg);
        transition: left .55s ease;
    }

    .login-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 28px rgba(7,87,184,.26);
        filter: brightness(1.04);
    }

    .login-submit:hover::before {
        left: calc(100% + 80px);
    }

    .login-submit:active {
        transform: translateY(0);
    }

    .submit-content {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .submit-content svg {
        width: 15px;
        height: 15px;
    }

    /* Security information */

    .security-note {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        padding: 12px;
        margin-top: 21px;
        border: 1px solid #e6edf5;
        border-radius: 10px;
        background: #f8fafc;
    }

    .security-icon {
        flex: none;
        width: 27px;
        height: 27px;
        display: grid;
        place-items: center;
        border-radius: 8px;
        background: #eaf4ff;
        color: var(--bps-blue);
    }

    .security-icon svg {
        width: 13px;
        height: 13px;
    }

    .security-note strong {
        display: block;
        color: #40536b;
        font-size: 8px;
        font-weight: 900;
    }

    .security-note span {
        display: block;
        margin-top: 3px;
        color: #93a0b0;
        font-size: 7px;
        line-height: 1.5;
    }

    /* Footer */

    .login-footer {
        margin-top: 27px;
        padding-top: 17px;
        border-top: 1px solid #edf1f5;
        text-align: center;
        color: #a1adba;
        font-size: 7px;
        line-height: 1.7;
    }

    .login-footer strong {
        color: #738298;
        font-weight: 800;
    }

    /* =========================================================
       RESPONSIVE
       ========================================================= */

    @media (max-width: 950px) {
        .login-visual {
            width: 48%;
            padding: 32px;
        }

        .login-panel {
            width: 52%;
            padding: 32px;
        }

        .visual-content h1 {
            font-size: 36px;
        }

        .visual-preview {
            width: 340px;
        }
    }

    @media (max-width: 760px) {
        .login-page {
            min-height: 100vh;
            display: block;
            overflow-y: auto;
            background:
                radial-gradient(
                    circle at 100% 0,
                    rgba(11,116,229,.08),
                    transparent 35%
                ),
                #f4f7fb;
        }

        .login-visual {
            width: 100%;
            min-height: auto;
            padding: 23px 20px 25px;
        }

        .visual-content {
            margin-top: 45px;
        }

        .visual-content h1 {
            font-size: 31px;
        }

        .visual-content p {
            font-size: 9px;
            margin-top: 12px;
        }

        .visual-preview {
            display: none;
        }

        .visual-footer {
            margin-top: 42px;
        }

        .login-panel {
            width: 100%;
            min-height: auto;
            padding: 30px 20px 35px;
            background: transparent;
        }

        .login-box {
            max-width: 440px;
            padding: 0;
        }

        .login-heading {
            margin-bottom: 23px;
        }

        .login-heading h2 {
            font-size: 22px;
        }

        .login-heading p {
            font-size: 9px;
        }
    }

    @media (max-width: 420px) {
        .visual-content h1 {
            font-size: 27px;
        }

        .visual-footer {
            align-items: flex-end;
        }

        .visual-time strong {
            font-size: 13px;
        }

        .login-panel {
            padding-left: 16px;
            padding-right: 16px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        *,
        *::before,
        *::after {
            animation: none !important;
            transition: none !important;
        }
    }
</style>


<div class="login-page">

    {{-- =====================================================
         LEFT / BRANDING
         ===================================================== --}}

    <section class="login-visual">

        <div class="visual-grid"></div>

        {{-- Header --}}
        <div class="visual-header">

            <div class="visual-logo">
                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQf1IUEkGN16g9RhT8qFdfmIZ4dhUxQJBcvHlhlK8KrG2KrCmp0-o5Wx12J&s=10" alt="BPS Logo" class="visual-logo-img" style="width:72px;height:auto">
            </div>

            <div class="visual-brand">
                <strong>BPS Kabupaten Karanganyar</strong>
                <span>Sistem Administrasi & Absensi</span>
            </div>

        </div>


        {{-- Main --}}
        <div class="visual-content">

            <div class="visual-label">
                <span class="visual-label-dot"></span>
                SISTEM INTERNAL AKTIF
            </div>

            <h1>
                Kelola Absensi.
                <br>
                <span>Lebih Cepat & Teratur.</span>
            </h1>

            <p>
                Selamat datang di panel administrasi BPS Kabupaten Karanganyar.
                Kelola jadwal pengingat, data karyawan, dan kebutuhan administrasi
                absensi dari satu tempat.
            </p>


            {{-- Mini Dashboard Preview --}}
            <div class="visual-preview">

                <div class="preview-top">
                    <span class="preview-dot"></span>
                    <span class="preview-dot"></span>
                    <span class="preview-dot"></span>
                </div>

                <div class="preview-body">

                    <div class="preview-side">
                        <div class="preview-side-line active"></div>
                        <div class="preview-side-line"></div>
                        <div class="preview-side-line"></div>
                        <div class="preview-side-line"></div>
                        <div class="preview-side-line"></div>
                    </div>

                    <div class="preview-main">

                        <div class="preview-card">
                            <div class="preview-line"></div>
                            <div class="preview-line big"></div>
                        </div>

                        <div class="preview-card">
                            <div class="preview-line"></div>
                            <div class="preview-line big"></div>
                        </div>

                        <div class="preview-card">
                            <div class="preview-line"></div>
                            <div class="preview-line big"></div>
                        </div>

                        <div class="preview-card wide">
                            <div class="preview-line"></div>
                            <div class="preview-line big"></div>
                        </div>

                        <div class="preview-card">
                            <div class="preview-line"></div>
                            <div class="preview-line big"></div>
                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Footer --}}
        <div class="visual-footer">

            <div>
                <strong>Portal Internal</strong>
                <br>
                BPS Kabupaten Karanganyar
            </div>

            <div class="visual-time">
                <strong id="loginClock">00:00:00</strong>
                <span id="loginDate">Memuat waktu...</span>
            </div>

        </div>

    </section>


    {{-- =====================================================
         RIGHT / LOGIN
         ===================================================== --}}

    <section class="login-panel">

        <div class="login-box">

            {{-- Heading --}}
            <div class="login-heading">

                <h2>
                    Selamat datang kembali
                </h2>

                <p>
                    Masuk menggunakan akun administrator untuk melanjutkan.
                </p>

            </div>


            {{-- Error Laravel --}}
            @if($errors->any())

                <div class="login-error">

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>

                    <span>
                        {{ $errors->first() }}
                    </span>

                </div>

            @endif


            {{-- =================================================
                 LOGIN FORM
                 ================================================= --}}

            <form
                method="POST"
                action="{{ url('/login') }}"
                class="login-form"
            >

                @csrf


                {{-- Email --}}
                <div class="login-field">

                    <label for="email">
                        <span>Alamat Email</span>
                    </label>

                    <div class="login-input-wrap">

                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="nama@bps.go.id"
                            class="login-input @error('email') border-red-300 @enderror"
                        />

                        <svg
                            class="login-input-icon"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                            />
                        </svg>

                    </div>

                </div>


                {{-- Password --}}
                <div class="login-field">

                    <label for="password">

                        <span>Password</span>

                        <a href="#">
                            Lupa password?
                        </a>

                    </label>

                    <div class="login-input-wrap">

                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="current-password"
                            placeholder="Masukkan password"
                            class="login-input password"
                        />

                        <svg
                            class="login-input-icon"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M12 15a3 3 0 100-6 3 3 0 000 6z"
                            />

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M2.46 12C3.73 7.94 7.52 5 12 5s8.27 2.94 9.54 7c-1.27 4.06-5.06 7-9.54 7s-8.27-2.94-9.54-7z"
                            />
                        </svg>


                        {{-- Toggle Password --}}
                        <button
                            type="button"
                            id="togglePassword"
                            class="password-toggle"
                            aria-label="Tampilkan password"
                        >

                            {{-- Eye --}}
                            <svg
                                id="eyeIcon"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M2.46 12C3.73 7.94 7.52 5 12 5s8.27 2.94 9.54 7c-1.27 4.06-5.06 7-9.54 7S3.73 16.06 2.46 12z"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                />
                            </svg>


                            {{-- Eye Off --}}
                            <svg
                                id="eyeOffIcon"
                                class="hidden"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M3 3l18 18"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M10.58 10.58a2 2 0 002.83 2.83"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M9.88 5.1A10.6 10.6 0 0112 5c4.48 0 8.27 2.94 9.54 7a10.7 10.7 0 01-3.05 4.62M6.23 6.23A10.74 10.74 0 002.46 12c1.27 4.06 5.06 7 9.54 7 1.61 0 3.13-.36 4.48-1"
                                />
                            </svg>

                        </button>

                    </div>

                </div>


                {{-- Remember --}}
                <div class="login-options">

                    <label class="remember-label">

                        <input
                            type="checkbox"
                            name="remember"
                            class="remember-checkbox"
                        >

                        <span>
                            Ingat saya di perangkat ini
                        </span>

                    </label>

                </div>


                {{-- Submit --}}
                <button
                    type="submit"
                    class="login-submit"
                >

                    <span class="submit-content">

                        <span>
                            Masuk ke Panel Admin
                        </span>

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M13 5l7 7-7 7M4 12h16"
                            />
                        </svg>

                    </span>

                </button>

            </form>


            {{-- Security --}}
            <div class="security-note">

                <div class="security-icon">

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M12 3l7 4v5c0 4.5-3 7.7-7 9-4-1.3-7-4.5-7-9V7l7-4z"
                        />

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M9.5 12l1.7 1.7 3.4-3.5"
                        />
                    </svg>

                </div>

                <div>

                    <strong>
                        Akses Terlindungi
                    </strong>

                    <span>
                        Gunakan akun resmi Anda. Jangan membagikan
                        informasi login kepada pihak lain.
                    </span>

                </div>

            </div>


            {{-- Footer --}}
            <div class="login-footer">

                © {{ date('Y') }}
                <strong>BPS Kabupaten Karanganyar</strong>
                · Sistem Administrasi Internal

            </div>

        </div>

    </section>

</div>


{{-- =========================================================
     JAVASCRIPT
     ========================================================= --}}

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* =========================================
       TOGGLE PASSWORD
       ========================================= */

    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    const eyeOffIcon = document.getElementById('eyeOffIcon');

    if (
        togglePassword &&
        passwordInput &&
        eyeIcon &&
        eyeOffIcon
    ) {

        togglePassword.addEventListener('click', function () {

            const isPassword =
                passwordInput.getAttribute('type') === 'password';

            passwordInput.setAttribute(
                'type',
                isPassword ? 'text' : 'password'
            );

            eyeIcon.classList.toggle(
                'hidden',
                isPassword
            );

            eyeOffIcon.classList.toggle(
                'hidden',
                !isPassword
            );

            togglePassword.setAttribute(
                'aria-label',
                isPassword
                    ? 'Sembunyikan password'
                    : 'Tampilkan password'
            );

        });

    }


    /* =========================================
       REALTIME CLOCK
       ========================================= */

    const clock = document.getElementById('loginClock');
    const dateElement = document.getElementById('loginDate');

    function updateLoginClock() {

        const now = new Date();

        const time = now.toLocaleTimeString(
            'id-ID',
            {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            }
        );

        const date = now.toLocaleDateString(
            'id-ID',
            {
                weekday: 'long',
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            }
        );

        if (clock) {
            clock.textContent = time;
        }

        if (dateElement) {
            dateElement.textContent = date;
        }
    }

    updateLoginClock();

    setInterval(
        updateLoginClock,
        1000
    );


    /* =========================================
       SUBMIT FEEDBACK
       ========================================= */

    const loginForm = document.querySelector('.login-form');
    const submitButton = document.querySelector('.login-submit');

    if (loginForm && submitButton) {

        loginForm.addEventListener('submit', function () {

            submitButton.style.pointerEvents = 'none';
            submitButton.style.opacity = '.8';

            const content =
                submitButton.querySelector('.submit-content');

            if (content) {

                content.innerHTML = `
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="15"
                        height="15"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        style="animation:loginSpin .8s linear infinite"
                    >
                        <path d="M12 2v4"/>
                        <path d="M12 18v4"/>
                        <path d="M4.93 4.93l2.83 2.83"/>
                        <path d="M16.24 16.24l2.83 2.83"/>
                        <path d="M2 12h4"/>
                        <path d="M18 12h4"/>
                        <path d="M4.93 19.07l2.83-2.83"/>
                        <path d="M16.24 7.76l2.83-2.83"/>
                    </svg>

                    <span>Memproses...</span>
                `;

            }

        });

    }

});
</script>

<style>
@keyframes loginSpin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}
</style>

@endsection