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

            @if ($canSync)
                <button
                    type="button"
                    wire:click="openSyncModal"
                    class="inline-flex items-center gap-1.5 rounded-lg px-2 py-1 text-sm font-medium text-primary-600 hover:bg-primary-50 dark:text-primary-400 dark:hover:bg-white/5"
                >
                    <x-filament::icon icon="heroicon-o-arrow-path" class="h-4 w-4" />
                    Sinkronisasi
                </button>
            @endif
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
                    Hapus filter tanggal
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

{{-- Modal sinkronisasi Sintesys, 2 langkah (pilih jurusan -> preview -> tarik
    & simpan). Sama pola dgn modal logout/leave-impersonate: id dicocokkan
    lewat event open-modal/close-modal, trigger tombol "Sinkronisasi" di atas. --}}
@if ($canSync)
    <x-filament::modal id="sync-exams" width="3xl" icon="heroicon-o-arrow-path" icon-color="primary">
        <x-slot name="heading">
            Sinkronisasi Ujian - {{ $monthNames[$month] }} {{ $year }}
        </x-slot>

        @if ($syncStep === 'pick')
            <div class="space-y-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Data ujian bulan {{ $monthNames[$month] }} {{ $year }} akan ditarik dari Sintesys dan dicocokkan
                    dengan data lokal. Ujian yang sudah dilaporkan tidak akan diubah.
                </p>

                @if ($isJurusan)
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Jurusan</label>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ $departements->firstWhere('id', $syncDepartementId)?->nama ?? '-' }}
                        </p>
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Semua jurusan akan diperiksa sekaligus (bisa beberapa saat, tergantung jumlah jurusan).
                    </p>
                @endif
            </div>

            <x-slot name="footerActions">
                <x-filament::button color="gray" x-on:click="close">
                    Batal
                </x-filament::button>

                <x-filament::button wire:click="loadSyncPreview" color="primary">
                    Tampilkan Preview
                </x-filament::button>
            </x-slot>
        @else
            <div class="space-y-4">
                @php $summary = $syncPreview['summary'] ?? []; @endphp
                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <x-filament::badge color="gray">Total: {{ $summary['total'] ?? 0 }}</x-filament::badge>
                    <x-filament::badge color="success">Dibuat: {{ $summary['dibuat'] ?? 0 }}</x-filament::badge>
                    <x-filament::badge color="info">Diperbarui: {{ $summary['diperbarui'] ?? 0 }}</x-filament::badge>
                    <x-filament::badge color="warning">Dilewati: {{ $summary['dilewati_jenis_tidak_dikenal'] ?? 0 }}</x-filament::badge>
                    @if (($summary['mahasiswa_baru'] ?? 0) > 0)
                        <x-filament::badge color="danger">Mahasiswa baru: {{ $summary['mahasiswa_baru'] }}</x-filament::badge>
                    @endif
                    @if (($summary['dosen_baru'] ?? 0) > 0)
                        <x-filament::badge color="danger">Dosen baru: {{ $summary['dosen_baru'] }}</x-filament::badge>
                    @endif
                </div>

                @if (! empty($summary['gagal_jurusan']))
                    <p class="text-sm" style="color: rgb(var(--danger-600))">
                        Gagal ditarik dari: {{ implode(', ', $summary['gagal_jurusan']) }}. Jurusan lain tetap berhasil diproses.
                    </p>
                @endif

                <div class="max-h-96 overflow-y-auto overflow-x-auto rounded-lg ring-1 ring-gray-200 dark:ring-white/10">
                    <table class="w-full text-start text-sm">
                        <thead class="bg-gray-50 text-xs font-medium text-gray-500 dark:bg-white/5 dark:text-gray-400">
                            <tr>
                                <th class="px-3 py-2 text-start">Mahasiswa</th>
                                <th class="px-3 py-2 text-start">Jenis Ujian</th>
                                <th class="px-3 py-2 text-start">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @forelse ($syncPreview['items'] ?? [] as $item)
                                <tr>
                                    <td class="px-3 py-2">
                                        {{ $item['nama'] ?? '-' }}
                                        <div class="text-xs text-gray-400">{{ $item['nim'] ?? '-' }}</div>
                                        @if ($item['student_baru'] ?? false)
                                            <x-filament::badge color="danger" size="xs">Mahasiswa baru</x-filament::badge>
                                        @endif
                                        @if (! empty($item['dosen_baru']))
                                            <x-filament::badge color="danger" size="xs">
                                                {{ count($item['dosen_baru']) }} dosen baru
                                            </x-filament::badge>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2">
                                        {{ $item['jenis_ujian'] }}
                                        <div class="text-xs text-gray-400">{{ $item['tanggal_ujian'] ?? '-' }}</div>
                                    </td>
                                    <td class="px-3 py-2">
                                        @if ($item['status'] === 'dibuat')
                                            <x-filament::badge color="success">Baru</x-filament::badge>
                                        @elseif ($item['status'] === 'diperbarui')
                                            <x-filament::badge color="info">Perbarui</x-filament::badge>
                                        @else
                                            <x-filament::badge color="warning" tooltip="{{ $item['alasan'] }}">Dilewati</x-filament::badge>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-3 py-4 text-center text-gray-400">
                                        Tidak ada data ujian dari Sintesys untuk bulan ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <x-slot name="footerActions">
                <x-filament::button color="gray" wire:click="backToSyncPick">
                    Kembali
                </x-filament::button>

                <x-filament::button wire:click="confirmSync" color="success" :disabled="empty($syncPreview['items'])">
                    Tarik &amp; Simpan
                </x-filament::button>
            </x-slot>
        @endif
    </x-filament::modal>
@endif
