<div class="flex flex-col gap-3">
    <div class="flex items-center gap-3">
        <button
            type="button"
            wire:click="toggleSudahSidang"
            @class([
                'shrink-0 whitespace-nowrap rounded-full px-3 py-2 text-sm font-medium transition',
                'bg-success-600 text-white hover:bg-success-500' => $sudahSidangOnly,
                'bg-white text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-white/5 dark:text-gray-200 dark:ring-white/10' => ! $sudahSidangOnly,
            ])
        >
            {{ $sudahSidangOnly ? 'Buang Filter' : 'Filter Sidang' }}
        </button>

        <input
            type="text"
            wire:model.live.debounce.300ms="studentSearch"
            placeholder="Cari NIM, nama, pembimbing, atau penguji..."
            class="block w-full min-w-0 flex-1 rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white"
        />
    </div>

    {{ $this->table }}
</div>
