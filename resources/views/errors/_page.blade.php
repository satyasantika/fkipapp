{{--
    Kerangka bersama semua halaman error (403/404/419/429/500/503/4xx/5xx) -
    dipanggil lewat @include dari tiap file kode, mengoper $code/$title/
    $description. Tema disamakan persis dengan halaman login
    (resources/views/filament/pages/auth/login.blade.php): warna, font,
    kartu kaca, motif rosette - supaya terasa satu identitas visual, bukan
    error page generik.

    SENGAJA tidak memuat @livewireStyles/@filamentScripts - ini halaman
    statis murni (tombol pakai <form> POST biasa + confirm() bawaan
    browser), supaya tetap bisa tampil normal walau error 500 terjadi
    gara-gara sesuatu di tumpukan Livewire/Filament sendiri.
--}}
@php
    $appName = config('app.name', 'Laravel');
    $isImpersonating = session()->has('impersonator_id');
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $code }} &mdash; {{ $title }} &mdash; {{ $appName }}</title>

    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:400,500,600,700|manrope:400,500,600,700,800|ibm-plex-mono:400,500" rel="stylesheet">

    <style>
        :root {
            --teal-950: #042f2e;
            --teal-600: #0d9488;
            --emerald-400: #34d399;
            --mint-50: #ecfdf5;

            --teal-800: #0b4844;
            --teal-700: #0e5e57;
            --ink-text: #14261f;
            --slate-500: #5b6b6a;
            --slate-400: #7c8c8a;
            --danger-600: #dc2626;
            --danger-700: #b91c1c;
            --danger-50: #fef2f2;
            --ring-teal: rgba(13, 148, 136, 0.22);

            --font-display: 'Fraunces', 'Georgia', serif;
            --font-body: 'Manrope', -apple-system, sans-serif;
            --font-mono: 'IBM Plex Mono', 'Courier New', monospace;
        }

        * { box-sizing: border-box; }

        html, body {
            height: 100%;
            margin: 0;
        }

        body {
            font-family: var(--font-body);
            background: var(--teal-950);
            color: var(--mint-50);
            min-height: 100vh;
        }

        .hero {
            position: relative;
            min-height: 100vh;
            width: 100%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(ellipse 900px 700px at 12% -8%, rgba(52, 211, 153, 0.22), transparent 60%),
                radial-gradient(ellipse 900px 800px at 105% 105%, rgba(13, 148, 136, 0.30), transparent 60%),
                linear-gradient(165deg, var(--teal-950) 0%, var(--teal-800) 52%, var(--teal-950) 100%);
        }

        .hero-seal-wrap {
            position: absolute;
            top: -22%;
            right: -16%;
            width: clamp(700px, 62vw, 950px);
            aspect-ratio: 1 / 1;
            z-index: 0;
            opacity: 0.18;
            pointer-events: none;
            animation: rosette-spin 150s linear infinite;
            will-change: transform;
        }

        .hero-seal-wrap svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        @keyframes rosette-spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .error-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 560px;
            margin: 1.5rem;
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255, 255, 255, 0.55);
            border-radius: 20px;
            box-shadow: 0 40px 80px -30px rgba(2, 20, 18, 0.55);
            padding: clamp(2rem, 4vw, 3.25rem);
            opacity: 0;
            transform: translateY(14px);
            animation: card-in 0.6s cubic-bezier(.2,.7,.3,1) 0.1s forwards;
        }

        @keyframes card-in {
            to { opacity: 1; transform: translateY(0); }
        }

        .error-code {
            font-family: var(--font-display);
            font-weight: 700;
            font-size: clamp(3rem, 8vw, 4.25rem);
            line-height: 1;
            letter-spacing: -0.02em;
            margin: 0 0 0.5rem;
            background: linear-gradient(135deg, var(--teal-600), var(--emerald-400));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .card-eyebrow {
            font-family: var(--font-mono);
            font-size: 0.7rem;
            font-weight: 500;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--teal-600);
            margin: 0 0 0.6rem;
        }

        .card-title {
            font-family: var(--font-body);
            font-size: 1.4rem;
            font-weight: 700;
            margin: 0 0 0.4rem;
            color: var(--ink-text);
        }

        .card-subtitle {
            font-size: 0.92rem;
            line-height: 1.6;
            color: var(--slate-500);
            margin: 0 0 2rem;
        }

        .error-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.7rem;
        }

        .error-actions form {
            margin: 0;
            display: contents;
        }

        .btn {
            appearance: none;
            border: none;
            border-radius: 10px;
            padding: 0.7rem 1.1rem;
            font-family: inherit;
            font-size: 0.88rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
        }

        .btn:focus-visible {
            outline: 3px solid var(--emerald-400);
            outline-offset: 2px;
        }

        .btn-primary {
            color: #fff;
            background: var(--teal-600);
        }

        .btn-primary:hover {
            background: var(--teal-700);
            transform: translateY(-1px);
            box-shadow: 0 14px 26px -12px rgba(13, 148, 136, 0.55);
        }

        .btn-secondary {
            color: var(--ink-text);
            background: var(--mint-50);
        }

        .btn-secondary:hover {
            background: #ddf5ec;
            transform: translateY(-1px);
        }

        .btn-outline {
            color: var(--teal-700);
            background: transparent;
            box-shadow: inset 0 0 0 1.5px var(--teal-600);
        }

        .btn-outline:hover {
            background: rgba(13, 148, 136, 0.08);
        }

        .btn-danger {
            color: #fff;
            background: var(--danger-600);
        }

        .btn-danger:hover {
            background: var(--danger-700);
            transform: translateY(-1px);
            box-shadow: 0 14px 26px -12px rgba(220, 38, 38, 0.45);
        }

        .hero-footer {
            position: relative;
            z-index: 1;
            font-family: var(--font-mono);
            font-size: 0.72rem;
            letter-spacing: 0.03em;
            color: rgba(236, 253, 245, 0.55);
            text-align: center;
            margin: 1.75rem 0 0;
        }

        @media (max-width: 560px) {
            .error-card {
                padding: 1.5rem;
                border-radius: 16px;
            }

            .error-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .btn {
                width: 100%;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .hero-seal-wrap {
                animation: none !important;
            }

            .error-card {
                animation: none !important;
                opacity: 1 !important;
                transform: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="hero">
        <div class="hero-seal-wrap" aria-hidden="true">
            <svg viewBox="0 0 800 800" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="rosetteGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#0d9488" />
                        <stop offset="100%" stop-color="#34d399" />
                    </linearGradient>
                </defs>
                <g fill="none" stroke="url(#rosetteGrad)" stroke-linecap="round">
                    <circle cx="400" cy="400" r="375" stroke-width="1.5" opacity="0.9" />
                    <circle cx="400" cy="400" r="340" stroke-width="1" opacity="0.5" />
                    <path d="M 140.2,550.0 A 300,300 0 0,1 297.4,118.1" stroke-width="3" opacity="0.8" />
                    <path d="M 502.6,118.1 A 300,300 0 0,1 659.8,550.0" stroke-width="3" opacity="0.8" />
                    <circle cx="400" cy="400" r="220" stroke-width="1.25" stroke-dasharray="2 10" opacity="0.6" />
                    <circle cx="400" cy="400" r="80" stroke-width="1.5" opacity="0.7" />
                </g>
            </svg>
        </div>

        <section class="error-card" aria-labelledby="error-card-title">
            <p class="error-code" aria-hidden="true">{{ $code }}</p>
            <p class="card-eyebrow">Terjadi Kendala</p>
            <h1 id="error-card-title" class="card-title">{{ $title }}</h1>
            <p class="card-subtitle">{{ $description }}</p>

            <div class="error-actions">
                <button type="button" class="btn btn-secondary" onclick="window.history.length > 1 ? window.history.back() : (window.location.href = '/')">
                    &larr; Kembali
                </button>

                @auth
                    <a href="{{ \Filament\Facades\Filament::getUrl() }}" class="btn btn-primary">Ke Dashboard</a>

                    @if ($isImpersonating)
                        <form action="{{ route('impersonate.leave') }}" method="POST" onsubmit="return confirm('Yakin ingin kembali ke akun admin?');">
                            @csrf
                            <button type="submit" class="btn btn-outline">Kembali ke Akun Admin</button>
                        </form>
                    @endif

                    <form action="{{ route('logout') }}" method="POST" onsubmit="return confirm('Yakin ingin logout?');">
                        @csrf
                        <button type="submit" class="btn btn-danger">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary">Ke Halaman Masuk</a>
                @endauth
            </div>
        </section>

        <p class="hero-footer">&copy; {{ now()->year }} FKIP &middot; Universitas Siliwangi</p>
    </div>
</body>
</html>
