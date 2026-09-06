<div class="flex flex-col gap-3">
    <div class="flex items-center gap-3">
        {{-- <x-filament::badge> dipakai (bukan kelas Tailwind tebakan
             bg-success-600) supaya warnanya benar-benar terkompilasi -
             lihat catatan panjang di student-card.blade.php. --}}
        <x-filament::badge
            tag="button"
            :color="$sudahSidangOnly ? 'success' : 'gray'"
            wire:click="toggleSudahSidang"
            style="border-radius:9999px;cursor:pointer"
            class="shrink-0 whitespace-nowrap"
        >
            {{ $sudahSidangOnly ? 'Buang Filter' : 'Filter Sidang' }}
        </x-filament::badge>

        <input
            type="text"
            wire:model.live.debounce.300ms="studentSearch"
            placeholder="Cari NIM atau nama..."
            class="block w-full min-w-0 flex-1 rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white"
        />
    </div>

    {{ $this->table }}
</div>
