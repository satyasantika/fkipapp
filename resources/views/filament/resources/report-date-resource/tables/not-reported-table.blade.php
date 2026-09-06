<div class="flex flex-col gap-3">
    <div class="flex items-center gap-3">
        {{-- Tombol biasa (bukan <x-filament::badge>, itu terlalu kecil) -
             warna lewat kelas .fi-filter-toggle-on (theme-overrides.blade.php,
             pakai rgb(var(--success-600))) supaya tidak mengulang kesalahan
             bg-success-600 lama yang tidak pernah benar-benar terkompilasi.
             h-9 dipasang di sini dan di input pencarian supaya tingginya
             persis sama. --}}
        <button
            type="button"
            wire:click="toggleSudahSidang"
            @class([
                'inline-flex h-9 shrink-0 items-center whitespace-nowrap rounded-full px-3 text-sm font-medium transition',
                'fi-filter-toggle-on' => $sudahSidangOnly,
                'bg-white text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-white/5 dark:text-gray-200 dark:ring-white/10' => ! $sudahSidangOnly,
            ])
        >
            {{ $sudahSidangOnly ? 'Buang Filter' : 'Filter Sidang' }}
        </button>

        <input
            type="text"
            wire:model.live.debounce.300ms="studentSearch"
            placeholder="Cari NIM atau nama..."
            class="block h-9 w-full min-w-0 flex-1 rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white"
        />
    </div>

    {{ $this->table }}
</div>
