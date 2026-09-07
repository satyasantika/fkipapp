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

            <span class="flex items-center gap-1">
                <span class="h-2 w-2 rounded-full" style="background-color: rgb(var(--success-500))"></span> Sudah
                <span class="ms-2 h-2 w-2 rounded-full bg-gray-400"></span> Belum
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

    <div class="grid grid-cols-7 gap-1">
        @php $cursor = $gridStart->copy(); @endphp
        @while ($cursor->lte($gridEnd))
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
                    'relative flex min-h-[3.25rem] flex-col items-center justify-center rounded-lg p-1.5 text-sm transition',
                    'text-gray-300 dark:text-gray-700' => ! $inMonth,
                    'hover:bg-gray-100 dark:hover:bg-white/5' => $inMonth && ! $isSelected,
                    'fi-calendar-day-selected' => $isSelected,
                    'fi-calendar-day-today' => $isToday && ! $isSelected,
                ])
            >
                <span class="font-medium">{{ $cursor->day }}</span>

                @if ($inMonth && $total > 0)
                    <span class="mt-0.5 text-[0.65rem] font-semibold {{ $isSelected ? 'text-white' : 'text-gray-600 dark:text-gray-300' }}">
                        {{ $total }}
                    </span>
                    <span class="mt-0.5 flex h-1 w-6 overflow-hidden rounded-full bg-gray-200 dark:bg-white/10">
                        @if ($sudah > 0)
                            <span style="width: {{ ($sudah / $total) * 100 }}%; background-color: rgb(var(--success-500))"></span>
                        @endif
                        @if ($belum > 0)
                            <span style="width: {{ ($belum / $total) * 100 }}%; background-color: rgb(var(--gray-400))"></span>
                        @endif
                    </span>
                @endif
            </button>
            @php $cursor->addDay(); @endphp
        @endwhile
    </div>
</div>
