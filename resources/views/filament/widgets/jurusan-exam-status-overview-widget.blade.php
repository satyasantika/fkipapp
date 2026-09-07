{{--
    Donat CSS conic-gradient murni (tanpa Chart.js) - warna pakai hex literal
    langsung (bukan rgb(var(--x)) Filament) supaya dijamin tampil apa adanya
    di browser manapun, tidak bergantung resolusi custom property tema.
--}}
<x-filament-widgets::widget>
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        {{-- Kartu 1: Total ujian + donat sudah dilaporkan --}}
        <div class="flex items-center justify-between gap-4 rounded-2xl p-5" style="background-color: #eff6ff">
            <div>
                <div class="text-4xl font-bold text-gray-900">{{ $total }}</div>
                <div class="mt-1 text-xs font-semibold tracking-wide text-gray-500">TOTAL UJIAN</div>
                <div class="mt-2 text-sm font-semibold" style="color: #16a34a">
                    {{ $sudah }} Sudah Dilaporkan
                </div>
            </div>

            <div
                class="relative flex h-24 w-24 shrink-0 items-center justify-center rounded-full"
                style="background: conic-gradient(#16a34a {{ $sudahPct }}%, #e2e8f0 0)"
            >
                <div class="flex h-16 w-16 flex-col items-center justify-center rounded-full" style="background-color: #eff6ff">
                    <span class="text-base font-bold text-gray-900">{{ $sudahPct }}%</span>
                    <span class="text-[9px] font-semibold tracking-wide text-gray-400">DILAPORKAN</span>
                </div>
            </div>
        </div>

        {{-- Kartu 2: Belum dilaporkan + donat proporsi jenis ujian --}}
        <div class="flex items-center justify-between gap-4 rounded-2xl p-5" style="background-color: #fffbeb">
            <div>
                <div class="text-4xl font-bold text-gray-900">{{ $belum }}</div>
                <div class="mt-1 text-xs font-semibold tracking-wide text-gray-500">
                    BELUM DILAPORKAN
                    <span class="font-bold" style="color: #d97706">{{ $belumPct }}%</span>
                </div>
            </div>

            <div
                class="relative flex h-24 w-24 shrink-0 items-center justify-center rounded-full"
                style="background: conic-gradient(#f59e0b 0%, #f59e0b {{ $stopSempro }}%, #2563eb {{ $stopSempro }}%, #2563eb {{ $stopSemhas }}%, #10b981 {{ $stopSemhas }}%, #10b981 100%)"
            >
                <div class="flex h-16 w-16 flex-col items-center justify-center rounded-full" style="background-color: #fffbeb">
                    <span class="text-base font-bold text-gray-900">{{ $belum }}</span>
                    <span class="text-[9px] font-semibold tracking-wide text-gray-400">UJIAN</span>
                </div>
            </div>

            <div class="flex flex-col gap-1.5 text-xs">
                <div class="flex items-center gap-1.5">
                    <span class="h-2 w-2 shrink-0 rounded-full" style="background-color: #f59e0b"></span>
                    <span class="text-gray-600">Sempro</span>
                    <span class="font-semibold text-gray-900">{{ $belumSempro }}</span>
                    <span class="text-gray-400">({{ $pctSempro }}%)</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="h-2 w-2 shrink-0 rounded-full" style="background-color: #2563eb"></span>
                    <span class="text-gray-600">Semhas</span>
                    <span class="font-semibold text-gray-900">{{ $belumSemhas }}</span>
                    <span class="text-gray-400">({{ $pctSemhas }}%)</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="h-2 w-2 shrink-0 rounded-full" style="background-color: #10b981"></span>
                    <span class="text-gray-600">Sidang</span>
                    <span class="font-semibold text-gray-900">{{ $belumSidang }}</span>
                    <span class="text-gray-400">({{ $pctSidang }}%)</span>
                </div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
