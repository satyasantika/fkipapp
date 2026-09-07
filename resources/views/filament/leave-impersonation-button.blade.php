@if (session()->has('impersonator_id'))
    <x-filament::modal id="confirm-leave-impersonation" width="sm" icon="heroicon-o-arrow-uturn-left" icon-color="warning">
        <x-slot name="trigger">
            <x-filament::icon-button
                color="warning"
                icon="heroicon-o-arrow-uturn-left"
                label="Kembali ke akun admin"
            />
        </x-slot>

        <x-slot name="heading">
            Kembali ke akun admin?
        </x-slot>

        <p class="text-sm text-gray-500 dark:text-gray-400">
            Anda akan berhenti masuk sebagai pengguna ini dan kembali ke akun admin semula.
        </p>

        <x-slot name="footerActions">
            <x-filament::button color="gray" x-on:click="close">
                Batal
            </x-filament::button>

            <form action="{{ route('impersonate.leave') }}" method="POST">
                @csrf

                <x-filament::button type="submit" color="warning">
                    Ya, kembali
                </x-filament::button>
            </form>
        </x-slot>
    </x-filament::modal>
@endif
