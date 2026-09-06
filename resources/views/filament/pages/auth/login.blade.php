@php
    $appName = config('app.name', 'Laravel');
    $stages = [
        ['label' => 'Ujian Proposal', 'code' => '01'],
        ['label' => 'Seminar Hasil', 'code' => '02'],
        ['label' => 'Sidang Skripsi', 'code' => '03'],
    ];
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk &mdash; {{ $appName }}</title>

    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:400,500,600,700|manrope:400,500,600,700,800|ibm-plex-mono:400,500" rel="stylesheet">

    <style>
        :root {
            /* Locked brand hues */
            --teal-950: #042f2e;
            --teal-600: #0d9488;
            --emerald-400: #34d399;
            --mint-50: #ecfdf5;

            /* Supporting utility tokens built on top of the brand hues */
            --teal-800: #0b4844;
            --teal-700: #0e5e57;
            --ink-text: #14261f;
            --slate-500: #5b6b6a;
            --slate-400: #7c8c8a;
            --danger-600: #dc2626;
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

        /* ---------- Hero backdrop ---------- */
        .hero {
            position: relative;
            min-height: 100vh;
            width: 100%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            background:
                radial-gradient(ellipse 900px 700px at 12% -8%, rgba(52, 211, 153, 0.22), transparent 60%),
                radial-gradient(ellipse 900px 800px at 105% 105%, rgba(13, 148, 136, 0.30), transparent 60%),
                linear-gradient(165deg, var(--teal-950) 0%, var(--teal-800) 52%, var(--teal-950) 100%);
        }

        /* ---------- Ambient seal / rosette motif ---------- */
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

        /* ---------- Layout shell ---------- */
        .hero-inner {
            position: relative;
            z-index: 1;
            flex: 1;
            width: 100%;
            max-width: 1440px;
            margin: 0 auto;
            padding: clamp(2.5rem, 6vw, 5rem) clamp(1.5rem, 6vw, 4.5rem);
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(440px, 560px);
            gap: clamp(2.5rem, 6vw, 5.5rem);
            align-items: center;
        }

        .hero-content {
            max-width: 720px;
        }

        .hero-eyebrow {
            font-family: var(--font-mono);
            font-size: 0.78rem;
            font-weight: 500;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--emerald-400);
            margin: 0 0 1.25rem;
        }

        .hero-headline {
            font-family: var(--font-display);
            font-weight: 600;
            font-size: clamp(1.85rem, 3.6vw, 2.85rem);
            line-height: 1.22;
            letter-spacing: -0.01em;
            margin: 0 0 1rem;
            color: #f4fdfa;
        }

        .hero-lede {
            font-size: 0.98rem;
            line-height: 1.65;
            color: #b9d4cd;
            max-width: 48ch;
            margin: 0;
        }

        /* ---------- Process strip ---------- */
        .process-strip {
            list-style: none;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.35rem 0;
            margin: 2.25rem 0 0;
            padding: 0;
        }

        .process-step {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            opacity: 0;
            transform: translateY(6px);
            animation: step-in 0.55s ease forwards;
        }

        .process-step:nth-child(1) { animation-delay: 0.15s; }
        .process-step:nth-child(3) { animation-delay: 0.30s; }
        .process-step:nth-child(5) { animation-delay: 0.45s; }

        @keyframes step-in {
            to { opacity: 1; transform: translateY(0); }
        }

        .process-code {
            font-family: var(--font-mono);
            font-size: 0.78rem;
            font-weight: 500;
            color: var(--mint-50);
            width: 2.1rem;
            height: 2.1rem;
            border-radius: 50%;
            border: 1px solid rgba(52, 211, 153, 0.45);
            background: rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            justify-content: center;
            flex: none;
        }

        .process-label {
            font-size: 0.86rem;
            font-weight: 500;
            color: #d7ece6;
            white-space: nowrap;
        }

        .process-connector {
            width: clamp(1.5rem, 3vw, 2.75rem);
            height: 1px;
            margin: 0 0.6rem;
            background: linear-gradient(90deg, rgba(52, 211, 153, 0.55), rgba(52, 211, 153, 0.05));
            flex: none;
        }

        /* ---------- Glass login card ---------- */
        .login-card {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255, 255, 255, 0.55);
            border-radius: 20px;
            box-shadow: 0 40px 80px -30px rgba(2, 20, 18, 0.55);
            padding: clamp(2rem, 3.5vw, 3.25rem);
            opacity: 0;
            transform: translateY(14px);
            animation: card-in 0.6s cubic-bezier(.2,.7,.3,1) 0.1s forwards;
        }

        @keyframes card-in {
            to { opacity: 1; transform: translateY(0); }
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
            font-size: 0.88rem;
            line-height: 1.55;
            color: var(--slate-500);
            margin: 0 0 1.75rem;
        }

        .field {
            position: relative;
            margin-bottom: 1.25rem;
        }

        .field label {
            display: block;
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--ink-text);
            margin-bottom: 0.4rem;
        }

        .field input {
            width: 100%;
            padding: 0.7rem 0.9rem;
            font-size: 0.95rem;
            font-family: inherit;
            color: var(--ink-text);
            background: var(--mint-50);
            border: 1.5px solid transparent;
            border-radius: 10px;
            transition: border-color 0.18s ease, background 0.18s ease, box-shadow 0.18s ease;
        }

        .field input::placeholder {
            color: var(--slate-400);
        }

        .field input:focus {
            outline: none;
            background: #ffffff;
            border-color: var(--teal-600);
            box-shadow: 0 0 0 4px var(--ring-teal);
        }

        .field.has-error input {
            background: var(--danger-50);
            border-color: var(--danger-600);
        }

        /* More specific than .field input:focus (3 classes vs 2), so this correctly
           wins regardless of source order when a field is both erroring and focused. */
        .field.has-error input:focus {
            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.15);
        }

        .field-error {
            display: block;
            margin-top: 0.4rem;
            font-size: 0.8rem;
            color: var(--danger-600);
        }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            margin-bottom: 1.65rem;
        }

        .remember-row input {
            width: 16px;
            height: 16px;
            accent-color: var(--teal-600);
        }

        .remember-row label {
            font-size: 0.85rem;
            color: var(--slate-500);
        }

        .submit-btn {
            width: 100%;
            padding: 0.8rem 1rem;
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 700;
            color: #fff;
            background: var(--teal-600);
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
        }

        .submit-btn:hover {
            background: var(--teal-700);
            transform: translateY(-1px);
            box-shadow: 0 14px 26px -12px rgba(13, 148, 136, 0.55);
        }

        .submit-btn:focus-visible {
            outline: 3px solid var(--emerald-400);
            outline-offset: 2px;
        }

        .submit-btn[disabled] {
            opacity: 0.7;
            cursor: progress;
        }

        .card-footnote {
            margin-top: 1.6rem;
            font-size: 0.78rem;
            color: var(--slate-400);
            text-align: center;
        }

        /* ---------- Page footer ---------- */
        .hero-footer {
            position: relative;
            z-index: 1;
            font-family: var(--font-mono);
            font-size: 0.72rem;
            letter-spacing: 0.03em;
            color: rgba(236, 253, 245, 0.55);
            text-align: center;
            margin: 0;
            padding: 0 1.25rem 1.75rem;
        }

        /* ---------- Responsive ---------- */
        @media (max-width: 1180px) {
            .hero-inner {
                grid-template-columns: 1fr;
                justify-items: center;
                text-align: center;
            }

            .hero-content {
                max-width: 100%;
            }

            .hero-lede {
                margin-left: auto;
                margin-right: auto;
            }

            .process-strip {
                justify-content: center;
            }

            .login-card {
                width: 100%;
                max-width: 480px;
            }
        }

        @media (max-width: 560px) {
            .hero-inner {
                padding: 2rem 1.1rem;
                gap: 2rem;
            }

            .hero-headline {
                font-size: 1.65rem;
            }

            .process-strip {
                gap: 0.5rem 0;
            }

            .process-connector {
                width: 1.1rem;
                margin: 0 0.35rem;
            }

            .process-label {
                font-size: 0.78rem;
            }

            .login-card {
                padding: 1.5rem;
                border-radius: 16px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .hero-seal-wrap {
                animation: none !important;
            }

            .login-card,
            .process-step {
                animation: none !important;
                opacity: 1 !important;
                transform: none !important;
            }
        }
    </style>

    @livewireStyles
    @filamentStyles
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
                    <!-- outer thin solid ring -->
                    <circle cx="400" cy="400" r="375" stroke-width="1.5" opacity="0.9" />

                    <!-- ticked compass/stamp ring, 48 ticks at 7.5deg increments -->
                    <g stroke-width="2" opacity="0.85">
                        <line x1="762.0" y1="400.0" x2="788.0" y2="400.0" />
                        <line x1="758.9" y1="447.3" x2="784.7" y2="450.6" />
                        <line x1="749.7" y1="493.7" x2="774.8" y2="500.4" />
                        <line x1="734.4" y1="538.5" x2="758.5" y2="548.5" />
                        <line x1="713.5" y1="581.0" x2="736.0" y2="594.0" />
                        <line x1="687.2" y1="620.4" x2="707.8" y2="636.2" />
                        <line x1="656.0" y1="656.0" x2="674.4" y2="674.4" />
                        <line x1="620.4" y1="687.2" x2="636.2" y2="707.8" />
                        <line x1="581.0" y1="713.5" x2="594.0" y2="736.0" />
                        <line x1="538.5" y1="734.4" x2="548.5" y2="758.5" />
                        <line x1="493.7" y1="749.7" x2="500.4" y2="774.8" />
                        <line x1="447.3" y1="758.9" x2="450.6" y2="784.7" />
                        <line x1="400.0" y1="762.0" x2="400.0" y2="788.0" />
                        <line x1="352.7" y1="758.9" x2="349.4" y2="784.7" />
                        <line x1="306.3" y1="749.7" x2="299.6" y2="774.8" />
                        <line x1="261.5" y1="734.4" x2="251.5" y2="758.5" />
                        <line x1="219.0" y1="713.5" x2="206.0" y2="736.0" />
                        <line x1="179.6" y1="687.2" x2="163.8" y2="707.8" />
                        <line x1="144.0" y1="656.0" x2="125.6" y2="674.4" />
                        <line x1="112.8" y1="620.4" x2="92.2" y2="636.2" />
                        <line x1="86.5" y1="581.0" x2="64.0" y2="594.0" />
                        <line x1="65.6" y1="538.5" x2="41.5" y2="548.5" />
                        <line x1="50.3" y1="493.7" x2="25.2" y2="500.4" />
                        <line x1="41.1" y1="447.3" x2="15.3" y2="450.6" />
                        <line x1="38.0" y1="400.0" x2="12.0" y2="400.0" />
                        <line x1="41.1" y1="352.7" x2="15.3" y2="349.4" />
                        <line x1="50.3" y1="306.3" x2="25.2" y2="299.6" />
                        <line x1="65.6" y1="261.5" x2="41.5" y2="251.5" />
                        <line x1="86.5" y1="219.0" x2="64.0" y2="206.0" />
                        <line x1="112.8" y1="179.6" x2="92.2" y2="163.8" />
                        <line x1="144.0" y1="144.0" x2="125.6" y2="125.6" />
                        <line x1="179.6" y1="112.8" x2="163.8" y2="92.2" />
                        <line x1="219.0" y1="86.5" x2="206.0" y2="64.0" />
                        <line x1="261.5" y1="65.6" x2="251.5" y2="41.5" />
                        <line x1="306.3" y1="50.3" x2="299.6" y2="25.2" />
                        <line x1="352.7" y1="41.1" x2="349.4" y2="15.3" />
                        <line x1="400.0" y1="38.0" x2="400.0" y2="12.0" />
                        <line x1="447.3" y1="41.1" x2="450.6" y2="15.3" />
                        <line x1="493.7" y1="50.3" x2="500.4" y2="25.2" />
                        <line x1="538.5" y1="65.6" x2="548.5" y2="41.5" />
                        <line x1="581.0" y1="86.5" x2="594.0" y2="64.0" />
                        <line x1="620.4" y1="112.8" x2="636.2" y2="92.2" />
                        <line x1="656.0" y1="144.0" x2="674.4" y2="125.6" />
                        <line x1="687.2" y1="179.6" x2="707.8" y2="163.8" />
                        <line x1="713.5" y1="219.0" x2="736.0" y2="206.0" />
                        <line x1="734.4" y1="261.5" x2="758.5" y2="251.5" />
                        <line x1="749.7" y1="306.3" x2="774.8" y2="299.6" />
                        <line x1="758.9" y1="352.7" x2="784.7" y2="349.4" />
                    </g>

                    <!-- second thin solid ring -->
                    <circle cx="400" cy="400" r="340" stroke-width="1" opacity="0.5" />

                    <!-- opposing laurel arcs -->
                    <path d="M 140.2,550.0 A 300,300 0 0,1 297.4,118.1" stroke-width="3" opacity="0.8" />
                    <path d="M 502.6,118.1 A 300,300 0 0,1 659.8,550.0" stroke-width="3" opacity="0.8" />

                    <!-- dashed inner ring -->
                    <circle cx="400" cy="400" r="220" stroke-width="1.25" stroke-dasharray="2 10" opacity="0.6" />

                    <!-- center core -->
                    <circle cx="400" cy="400" r="80" stroke-width="1.5" opacity="0.7" />
                </g>
            </svg>
        </div>

        <div class="hero-inner">
            <div class="hero-content">
                <p class="hero-eyebrow">Akses Internal &middot; FKIP UNSIL</p>
                <h1 class="hero-headline">Satu pintu untuk seluruh proses ujian akhir mahasiswa.</h1>
                <p class="hero-lede">
                    Kelola jadwal, penilaian, dan honorarium penguji untuk ujian proposal,
                    seminar hasil, hingga sidang skripsi &mdash; dalam satu sistem terpadu.
                </p>

                <ol class="process-strip" aria-hidden="true">
                    @foreach ($stages as $stage)
                        <li class="process-step">
                            <span class="process-code">{{ $stage['code'] }}</span>
                            <span class="process-label">{{ $stage['label'] }}</span>
                        </li>
                        @unless ($loop->last)
                            <li class="process-connector"></li>
                        @endunless
                    @endforeach
                </ol>
            </div>

            <section class="login-card" aria-labelledby="login-card-title">
                <p class="card-eyebrow">Portal Masuk</p>
                <h2 id="login-card-title" class="card-title">Masuk ke akun Anda</h2>
                <p class="card-subtitle">Gunakan username dan kata sandi yang terdaftar untuk mengakses {{ $appName }}.</p>

                <form wire:submit="authenticate" novalidate>
                    <div class="field @error('data.username') has-error @enderror">
                        <label for="username">Username</label>
                        <input
                            id="username"
                            type="text"
                            wire:model="data.username"
                            placeholder="Masukkan username"
                            required
                            autocomplete="username"
                            autofocus
                            @error('data.username') aria-invalid="true" aria-describedby="username-error" @enderror
                        >
                        @error('data.username')
                            <span class="field-error" id="username-error" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field @error('data.password') has-error @enderror">
                        <label for="password">Password</label>
                        <input
                            id="password"
                            type="password"
                            wire:model="data.password"
                            placeholder="Masukkan password"
                            required
                            autocomplete="current-password"
                            @error('data.password') aria-invalid="true" aria-describedby="password-error" @enderror
                        >
                        @error('data.password')
                            <span class="field-error" id="password-error" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="remember-row">
                        <input type="checkbox" wire:model="data.remember" id="remember">
                        <label for="remember">Ingat saya di perangkat ini</label>
                    </div>

                    <button type="submit" class="submit-btn" wire:loading.attr="disabled" wire:target="authenticate">
                        <span wire:loading.remove wire:target="authenticate">Masuk</span>
                        <span wire:loading wire:target="authenticate">Memproses&hellip;</span>
                    </button>
                </form>

                <p class="card-footnote">Hubungi admin fakultas bila mengalami kendala akses.</p>
            </section>
        </div>

        <p class="hero-footer">&copy; {{ now()->year }} FKIP &middot; Universitas Siliwangi</p>

        {{-- Komponen notifikasi Filament (toast) - tanpa ini, notifikasi seperti
             peringatan rate-limit ("terlalu banyak percobaan login") dikirim
             tapi tidak pernah tampil sama sekali, terasa seperti "tidak terjadi
             apa-apa" saat tombol Masuk diklik. Ditaruh DI DALAM .hero (bukan
             sebagai sibling) karena Livewire cuma boleh satu root element per
             komponen - taruh di luar akan bikin error "multiple root elements". --}}
        @livewire(\Filament\Livewire\Notifications::class)

        {{-- @filamentScripts juga merender <style> penutup (variabel CSS), bukan
             cuma <script> - kalau ditaruh sebagai sibling .hero di luar sini,
             <body> akan punya 2 elemen anak (.hero + <style>) dan Livewire
             melempar "multiple root elements" lagi. Makanya ikut ditaruh DI
             DALAM .hero, supaya <body> tetap cuma punya satu elemen anak. --}}
        @livewireScripts
        @filamentScripts(withCore: true)
    </div>
</body>
</html>
