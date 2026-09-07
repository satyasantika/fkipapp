<div class="shrink-0">
    <button
        type="button"
        wire:click="toggleBelumDilaporkan"
        @class([
            'inline-flex items-center whitespace-nowrap rounded-full px-3 py-1.5 text-sm font-medium transition',
            'fi-filter-toggle-on' => $active,
            'bg-white text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-white/5 dark:text-gray-200 dark:ring-white/10' => ! $active,
        ])
    >
        {{ $active ? 'Buang Filter' : 'Belum Dilaporkan' }}
    </button>
</div>
