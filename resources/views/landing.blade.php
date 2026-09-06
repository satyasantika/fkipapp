@php
    $appName = config('app.name', 'Laravel');
    $stages = [
        ['label' => 'Ujian Proposal', 'code' => '01'],
        ['label' => 'Seminar Hasil', 'code' => '02'],
        ['label' => 'Sidang Skripsi', 'code' => '03'],
    ];
    $features = [
        [
            'title' => 'Registrasi & Penjadwalan',
            'body' => 'Catat pendaftaran ujian, susun pembimbing dan penguji, dan jadwalkan ruang sidang dalam satu alur.',
        ],
        [
            'title' => 'Honor & Pembayaran',
            'body' => 'Hitung honor pembimbing dan penguji otomatis per periode, lengkap dengan potongan pajak sesuai golongan.',
        ],
        [
            'title' => 'Laporan per Periode',
            'body' => 'Tarik laporan penarikan pembayaran per tanggal, telusuri status setiap ujian yang sudah maupun belum dilaporkan.',
        ],
    ];
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $appName }}</title>

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

            --font-display: 'Fraunces', 'Georgia', serif;
            --font-body: 'Manrope', -apple-system, sans-serif;
            --font-mono: 'IBM Plex Mono', 'Courier New', monospace;
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
        }

        body {
            font-family: var(--font-body);
            background: var(--mint-50);
            color: var(--ink-text);
        }

        /* ---------- Hero backdrop (shared with login) ---------- */
        .hero {
            position: relative;
            width: 100%;
            overflow: hidden;
            background:
                radial-gradient(ellipse 900px 700px at 12% -8%, rgba(52, 211, 153, 0.22), transparent 60%),
                radial-gradient(ellipse 900px 800px at 105% 105%, rgba(13, 148, 136, 0.30), transparent 60%),
                linear-gradient(165deg, var(--teal-950) 0%, var(--teal-800) 52%, var(--teal-950) 100%);
            color: var(--mint-50);
        }

        .hero-seal-wrap {
            position: absolute;
            top: -22%;
            right: -16%;
            width: clamp(700px, 62vw, 950px);
            aspect-ratio: 1 / 1;
            z-index: 0;
            opacity: 0.16;
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

        .hero-inner {
            position: relative;
            z-index: 1;
            max-width: 860px;
            margin: 0 auto;
            padding: clamp(4rem, 10vw, 7rem) clamp(1.25rem, 5vw, 3rem) clamp(3rem, 8vw, 5rem);
            text-align: center;
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
            font-size: clamp(2.1rem, 5vw, 3.4rem);
            line-height: 1.16;
            letter-spacing: -0.01em;
            margin: 0 0 1.1rem;
            color: #f4fdfa;
        }

        .hero-lede {
            font-size: 1.05rem;
            line-height: 1.65;
            color: #b9d4cd;
            max-width: 56ch;
            margin: 0 auto 2.25rem;
        }

        .cta-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.85rem 1.75rem;
            font-family: inherit;
            font-size: 0.98rem;
            font-weight: 700;
            color: var(--teal-950);
            background: var(--emerald-400);
            border: none;
            border-radius: 10px;
            text-decoration: none;
            transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
        }

        .cta-btn:hover {
            background: #5eead4;
            transform: translateY(-1px);
            box-shadow: 0 14px 26px -12px rgba(52, 211, 153, 0.55);
        }

        .cta-btn:focus-visible {
            outline: 3px solid var(--mint-50);
            outline-offset: 2px;
        }

        /* ---------- Process strip (shared with login) ---------- */
        .process-strip {
            list-style: none;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 0.35rem 0;
            margin: 2.75rem 0 0;
            padding: 0;
        }

        .process-step {
            display: flex;
            align-items: center;
            gap: 0.65rem;
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

        /* ---------- Feature section ---------- */
        .features {
            max-width: 1080px;
            margin: 0 auto;
            padding: clamp(3rem, 8vw, 5rem) clamp(1.25rem, 5vw, 3rem);
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1.5rem;
        }

        .feature-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 1.75rem;
            box-shadow: 0 20px 40px -28px rgba(4, 47, 46, 0.25);
        }

        .feature-eyebrow {
            font-family: var(--font-mono);
            font-size: 0.7rem;
            font-weight: 500;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--teal-600);
            margin: 0 0 0.5rem;
        }

        .feature-title {
            font-family: var(--font-display);
            font-size: 1.15rem;
            font-weight: 600;
            margin: 0 0 0.5rem;
            color: var(--ink-text);
        }

        .feature-body {
            font-size: 0.9rem;
            line-height: 1.55;
            color: var(--slate-500);
            margin: 0;
        }

        /* ---------- Footer ---------- */
        .page-footer {
            font-family: var(--font-mono);
            font-size: 0.72rem;
            letter-spacing: 0.03em;
            color: var(--slate-500);
            text-align: center;
            padding: 0 1.25rem 2.5rem;
        }

        @media (max-width: 860px) {
            .features {
                grid-template-columns: 1fr;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .hero-seal-wrap {
                animation: none !important;
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
                    <circle cx="400" cy="400" r="340" stroke-width="1" opacity="0.5" />
                    <path d="M 140.2,550.0 A 300,300 0 0,1 297.4,118.1" stroke-width="3" opacity="0.8" />
                    <path d="M 502.6,118.1 A 300,300 0 0,1 659.8,550.0" stroke-width="3" opacity="0.8" />
                    <circle cx="400" cy="400" r="220" stroke-width="1.25" stroke-dasharray="2 10" opacity="0.6" />
                    <circle cx="400" cy="400" r="80" stroke-width="1.5" opacity="0.7" />
                </g>
            </svg>
        </div>

        <div class="hero-inner">
            <p class="hero-eyebrow">FKIP &middot; Universitas Siliwangi</p>
            <h1 class="hero-headline">Satu sistem untuk seluruh siklus ujian akhir mahasiswa.</h1>
            <p class="hero-lede">
                {{ $appName }} membantu jurusan, keuangan, dan dekanat mengelola pendaftaran,
                penjadwalan, dan honorarium ujian proposal, seminar hasil, hingga sidang skripsi
                &mdash; dalam satu tempat.
            </p>

            <a href="{{ \Filament\Facades\Filament::getLoginUrl() }}" class="cta-btn">Masuk ke Sistem &rarr;</a>

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
    </div>

    <div class="features">
        @foreach ($features as $feature)
            <div class="feature-card">
                <p class="feature-eyebrow">{{ sprintf('%02d', $loop->iteration) }}</p>
                <h3 class="feature-title">{{ $feature['title'] }}</h3>
                <p class="feature-body">{{ $feature['body'] }}</p>
            </div>
        @endforeach
    </div>

    <p class="page-footer">&copy; {{ now()->year }} FKIP &middot; Universitas Siliwangi</p>
</body>
</html>
