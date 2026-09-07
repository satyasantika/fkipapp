<div class="shrink-0">
    <button
        type="button"
        wire:click="toggleBelumSesuaiOnly"
        title="{{ $active ? 'Tampilkan semua dosen' : 'Filter dosen yang belum sesuai' }}"
        @class([
            'inline-flex h-9 w-9 items-center justify-center rounded-lg transition',
            'fi-filter-toggle-on' => $active,
            'bg-white text-gray-500 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-white/5 dark:text-gray-400 dark:ring-white/10' => ! $active,
        ])
    >
        <x-filament::icon icon="heroicon-o-funnel" class="h-4 w-4" />
    </button>
</div>
