@if (session()->has('impersonator_id'))
    <form action="{{ route('impersonate.leave') }}" method="POST">
        @csrf

        <x-filament::icon-button
            type="submit"
            color="warning"
            icon="heroicon-o-arrow-uturn-left"
            label="Kembali ke akun admin"
        />
    </form>
@endif
