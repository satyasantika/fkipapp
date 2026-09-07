@php
    $options = [
        'semua' => 'Semua',
        'sudah' => 'Sudah',
        'belum' => 'Belum',
    ];
@endphp

<div class="flex shrink-0 items-center gap-1">
    @foreach ($options as $value => $label)
        <button
            type="button"
            wire:click="setDilaporkanFilter('{{ $value }}')"
            @class([
                'inline-flex items-center whitespace-nowrap rounded-full px-3 py-1.5 text-sm font-medium transition',
                'fi-dilaporkan-filter-active' => $current === $value,
                'bg-white text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-white/5 dark:text-gray-200 dark:ring-white/10' => $current !== $value,
            ])
        >
            {{ $label }}
        </button>
    @endforeach
</div>
