@if (config('services.google.aktif'))
    <div style="margin-top: 1rem; text-align: center;">
        @if (session('galat_google'))
            <p role="alert" style="margin-bottom: .75rem; color: #b91c1c; font-size: .875rem;">{{ session('galat_google') }}</p>
        @endif
        <a href="{{ route('auth.google.redirect', ['asal' => \Livewire\Livewire::originalUrl()]) }}"
           style="display: block; padding: .625rem 1rem; border: 1px solid #d1d5db; border-radius: .5rem; color: inherit; text-decoration: none; font-weight: 500;">Masuk dengan Google</a>
    </div>
@endif
