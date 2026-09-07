<x-filament-panels::page>
    @if ($this->record->is_locked)
        <div class="fi-locked-banner">
            <x-filament::icon icon="heroicon-o-lock-closed" />
            <p>
                Penarikan laporan ini sedang terkunci, jadi tidak bisa ditambah/dikurangi.
                Kalau memang perlu ditambah/dikurangi, buka dulu kuncinya dari halaman Penarikan Laporan.
            </p>
        </div>
    @endif

    {{ $this->table }}
</x-filament-panels::page>
