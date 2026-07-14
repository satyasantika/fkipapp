@php
    $appName = config('app.name', 'Laravel');
    $initials = collect(preg_split('/\s+/', trim($appName)))
        ->filter()
        ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
        ->take(2)
        ->implode('');
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
    <link href="https://fonts.bunny.net/css?family=nunito:400,600,700,800|spectral:500,600,600i" rel="stylesheet">

    <style>
        :root {
            --ink-950: #101b33;
            --ink-800: #1e2e52;
            --paper-0: #ffffff;
            --paper-100: #f3f1ec;
            --brass-500: #b8924b;
            --brass-100: #e9dcc0;
            --ink-text: #1b2436;
            --slate-500: #5b6579;
            --danger-600: #b3441f;
            --danger-50: #fbf0ea;
        }

        * { box-sizing: border-box; }

        html, body {
            height: 100%;
            margin: 0;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background: var(--paper-100);
            color: var(--ink-text);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem 1.25rem;
        }

        .auth-card {
            display: grid;
            grid-template-columns: minmax(0, 5fr) minmax(0, 6fr);
            width: 100%;
            max-width: 960px;
            min-height: 560px;
            background: var(--paper-0);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 30px 60px -20px rgba(16, 27, 51, 0.35);
            opacity: 0;
            transform: translateY(14px);
            animation: card-in 0.6s cubic-bezier(.2,.7,.3,1) forwards;
        }

        @keyframes card-in {
            to { opacity: 1; transform: translateY(0); }
        }

        /* ---------- Brand panel ---------- */
        .brand-panel {
            position: relative;
            background:
                radial-gradient(circle at 15% 12%, rgba(184,146,75,0.18), transparent 45%),
                linear-gradient(160deg, var(--ink-950) 0%, var(--ink-800) 100%);
            color: #eef0f6;
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .seal {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            border: 1.5px solid rgba(184,146,75,0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Spectral', serif;
            font-weight: 600;
            font-size: 1.05rem;
            letter-spacing: 0.02em;
            color: var(--brass-100);
        }

        .brand-word {
            font-family: 'Spectral', serif;
            font-weight: 600;
            font-size: 1.7rem;
            line-height: 1.2;
            margin: 1.25rem 0 0.6rem;
            letter-spacing: 0.01em;
        }

        .brand-tagline {
            font-size: 0.92rem;
            line-height: 1.6;
            color: #b7bedc;
            max-width: 30ch;
        }

        .stage-list {
            list-style: none;
            margin: 2.25rem 0 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
        }

        .stage-list li {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 0.9rem;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(184,146,75,0.22);
            border-radius: 10px;
            opacity: 0;
            transform: translateX(-8px);
            animation: stage-in 0.5s ease forwards;
        }

        .stage-list li:nth-child(1) { animation-delay: 0.35s; }
        .stage-list li:nth-child(2) { animation-delay: 0.48s; margin-left: 0.9rem; }
        .stage-list li:nth-child(3) { animation-delay: 0.61s; margin-left: 1.8rem; }

        @keyframes stage-in {
            to { opacity: 1; transform: translateX(0); }
        }

        .stage-code {
            font-family: 'Spectral', serif;
            font-size: 0.78rem;
            color: var(--brass-100);
            border: 1px solid rgba(184,146,75,0.5);
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex: none;
        }

        .stage-label {
            font-size: 0.85rem;
            color: #dfe2ef;
        }

        .brand-footer {
            font-size: 0.75rem;
            color: #7c85a8;
            margin-top: 2.5rem;
            letter-spacing: 0.02em;
        }

        /* ---------- Form panel ---------- */
        .form-panel {
            padding: 3.25rem 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-eyebrow {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--brass-500);
            margin: 0 0 0.5rem;
        }

        .form-title {
            font-family: 'Spectral', serif;
            font-size: 1.65rem;
            font-weight: 600;
            margin: 0 0 0.4rem;
            color: var(--ink-text);
        }

        .form-subtitle {
            font-size: 0.88rem;
            color: var(--slate-500);
            margin: 0 0 2rem;
        }

        .field {
            position: relative;
            margin-bottom: 1.35rem;
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
            padding: 0.72rem 0.9rem;
            font-size: 0.95rem;
            font-family: inherit;
            color: var(--ink-text);
            background: var(--paper-100);
            border: 1.5px solid transparent;
            border-radius: 10px;
            transition: border-color 0.18s ease, background 0.18s ease, box-shadow 0.18s ease;
        }

        .field input::placeholder {
            color: #99a1b5;
        }

        .field input:focus {
            outline: none;
            background: var(--paper-0);
            border-color: var(--brass-500);
            box-shadow: 0 0 0 4px rgba(184,146,75,0.15);
        }

        .field.has-error input {
            background: var(--danger-50);
            border-color: var(--danger-600);
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
            margin-bottom: 1.75rem;
        }

        .remember-row input {
            width: 16px;
            height: 16px;
            accent-color: var(--brass-500);
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
            background: var(--ink-950);
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
        }

        .submit-btn:hover {
            background: var(--ink-800);
            transform: translateY(-1px);
            box-shadow: 0 12px 24px -10px rgba(16, 27, 51, 0.55);
        }

        .submit-btn:focus-visible {
            outline: 3px solid var(--brass-500);
            outline-offset: 2px;
        }

        .form-footer {
            margin-top: 1.75rem;
            font-size: 0.78rem;
            color: #9aa1b3;
            text-align: center;
        }

        @media (max-width: 860px) {
            .auth-card {
                grid-template-columns: 1fr;
                min-height: 0;
            }

            .brand-panel {
                padding: 2rem 1.75rem;
                flex-direction: row;
                align-items: center;
                justify-content: flex-start;
                gap: 1rem;
            }

            .brand-word { margin: 0; font-size: 1.3rem; }
            .brand-tagline, .stage-list, .brand-footer { display: none; }
            .form-panel { padding: 2.25rem 1.75rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            .auth-card, .stage-list li {
                animation: none !important;
                opacity: 1 !important;
                transform: none !important;
            }
        }
    </style>
</head>
<body>
    <main class="auth-card">
        <section class="brand-panel">
            <div>
                <div class="seal" aria-hidden="true">{{ $initials }}</div>
                <h1 class="brand-word">{{ $appName }}</h1>
                <p class="brand-tagline">Rekap ujian proposal, seminar hasil, dan sidang skripsi untuk FKIP Universitas Siliwangi.</p>

                <ol class="stage-list" aria-hidden="true">
                    @foreach ($stages as $stage)
                        <li>
                            <span class="stage-code">{{ $stage['code'] }}</span>
                            <span class="stage-label">{{ $stage['label'] }}</span>
                        </li>
                    @endforeach
                </ol>
            </div>
            <p class="brand-footer">&copy; {{ now()->year }} FKIP &middot; Universitas Siliwangi</p>
        </section>

        <section class="form-panel">
            <p class="form-eyebrow">Akses Internal</p>
            <h2 class="form-title">Masuk ke akun Anda</h2>
            <p class="form-subtitle">Gunakan username dan kata sandi yang terdaftar untuk mengakses {{ $appName }}.</p>

            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                <div class="field @error('username') has-error @enderror">
                    <label for="username">Username</label>
                    <input
                        id="username"
                        type="text"
                        name="username"
                        value="{{ old('username') }}"
                        placeholder="Masukkan username"
                        required
                        autocomplete="username"
                        autofocus
                        @error('username') aria-invalid="true" aria-describedby="username-error" @enderror
                    >
                    @error('username')
                        <span class="field-error" id="username-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="field @error('password') has-error @enderror">
                    <label for="password">Password</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        placeholder="Masukkan password"
                        required
                        autocomplete="current-password"
                        @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                    >
                    @error('password')
                        <span class="field-error" id="password-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="remember-row">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember">Ingat saya di perangkat ini</label>
                </div>

                <button type="submit" class="submit-btn">Masuk</button>
            </form>

            <p class="form-footer">Hubungi admin fakultas bila mengalami kendala akses.</p>
        </section>
    </main>
</body>
</html>
