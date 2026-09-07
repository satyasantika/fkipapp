@php
    use Illuminate\Support\Carbon;

    $monthNames = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
    $dayNames = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

    $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
    $monthEnd = $monthStart->copy()->endOfMonth();
    $gridStart = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
    $gridEnd = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);

    $today = Carbon::today()->toDateString();
    $currentYear = now()->year;
    $years = range($currentYear - 5, $currentYear + 1);

    $monthTotal = array_sum(array_column($days, 'total'));
@endphp

<div class="fi-section mb-4 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <button
                type="button"
                wire:click="goToPreviousMonth"
                class="inline-flex items-center justify-center rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5"
            >
                <x-filament::icon icon="heroicon-o-chevron-left" class="h-4 w-4" />
            </button>

            <select
                wire:model.live="calendarMonth"
                class="rounded-lg border-0 bg-white py-1.5 text-sm text-gray-700 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:text-gray-200 dark:ring-white/10"
            >
                @foreach ($monthNames as $num => $name)
                    <option value="{{ $num }}">{{ $name }}</option>
                @endforeach
            </select>

            <select
                wire:model.live="calendarYear"
                class="rounded-lg border-0 bg-white py-1.5 text-sm text-gray-700 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:text-gray-200 dark:ring-white/10"
            >
                @foreach ($years as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>

            <button
                type="button"
                wire:click="goToNextMonth"
                class="inline-flex items-center justify-center rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5"
            >
                <x-filament::icon icon="heroicon-o-chevron-right" class="h-4 w-4" />
            </button>

            <button
                type="button"
                wire:click="goToCurrentMonth"
                class="ms-1 rounded-lg px-2 py-1 text-sm font-medium text-primary-600 hover:bg-primary-50 dark:text-primary-400 dark:hover:bg-white/5"
            >
                Hari ini
            </button>
        </div>

        <div class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-300">
            <span>Total ujian bulan ini: <strong class="text-gray-900 dark:text-white">{{ $monthTotal }}</strong></span>

            <span class="flex items-center gap-2">
                <x-filament::badge color="info" size="xs">0</x-filament::badge> Total
                <x-filament::badge color="warning" size="xs">0</x-filament::badge> Belum
                <x-filament::badge color="success" size="xs">0</x-filament::badge> Sudah
            </span>

            @if ($selectedDate)
                <button
                    type="button"
                    wire:click="clearSelectedDate"
                    class="inline-flex items-center gap-1 rounded-full bg-primary-50 px-2 py-1 font-medium text-primary-700 hover:bg-primary-100 dark:bg-white/5 dark:text-primary-400"
                >
                    Tanggal: {{ Carbon::parse($selectedDate)->translatedFormat('d M Y') }}
                    <x-filament::icon icon="heroicon-o-x-mark" class="h-3.5 w-3.5" />
                </button>
            @endif
        </div>
    </div>

    <div class="mb-1 grid grid-cols-7 gap-1 text-center text-xs font-medium text-gray-500 dark:text-gray-400">
        @foreach ($dayNames as $d)
            <div>{{ $d }}</div>
        @endforeach
    </div>

    @php $cursor = $gridStart->copy(); $weekIndex = 0; @endphp
    @while ($cursor->lte($gridEnd))
        <div
            @class([
                'grid grid-cols-7 gap-1',
                'mt-1 border-t border-gray-100 pt-1 dark:border-white/10' => $weekIndex > 0,
            ])
        >
            @for ($i = 0; $i < 7; $i++)
                @php
                    $dateStr = $cursor->toDateString();
                    $inMonth = $cursor->month === (int) $month;
                    $info = $days[$dateStr] ?? null;
                    $total = $info['total'] ?? 0;
                    $sudah = $info['sudah'] ?? 0;
                    $belum = $info['belum'] ?? 0;
                    $isToday = $dateStr === $today;
                    $isSelected = $selectedDate === $dateStr;
                    $tooltip = $cursor->translatedFormat('d M Y').($total ? ' - Total: '.$total.' (Sudah: '.$sudah.', Belum: '.$belum.')' : '');
                @endphp
                <button
                    type="button"
                    wire:click="selectCalendarDate('{{ $dateStr }}')"
                    @if (! $inMonth) disabled @endif
                    title="{{ $tooltip }}"
                    @class([
                        'relative flex min-h-[4rem] flex-col items-center gap-1 rounded-lg p-1 pt-1.5 text-sm transition',
                        'text-gray-300 dark:text-gray-700' => ! $inMonth,
                        'hover:bg-gray-100 dark:hover:bg-white/5' => $inMonth && ! $isSelected,
                        'fi-calendar-day-selected' => $isSelected,
                        'fi-calendar-day-today' => $isToday && ! $isSelected,
                    ])
                >
                    <span class="text-base font-semibold leading-none {{ $isSelected ? 'text-white' : 'text-gray-700 dark:text-gray-200' }}">
                        {{ $cursor->day }}
                    </span>

                    @if ($inMonth && $total > 0)
                        <span class="flex flex-wrap items-center justify-center gap-0.5">
                            <x-filament::badge color="info" size="xs" tooltip="Total ujian">
                                {{ $total }}
                            </x-filament::badge>
                            @if ($belum > 0)
                                <x-filament::badge color="warning" size="xs" tooltip="Belum dilaporkan">
                                    {{ $belum }}
                                </x-filament::badge>
                            @endif
                            @if ($sudah > 0)
                                <x-filament::badge color="success" size="xs" tooltip="Sudah dilaporkan">
                                    {{ $sudah }}
                                </x-filament::badge>
                            @endif
                        </span>
                    @endif
                </button>
                @php $cursor->addDay(); @endphp
            @endfor
        </div>
        @php $weekIndex++; @endphp
    @endwhile
</div>
