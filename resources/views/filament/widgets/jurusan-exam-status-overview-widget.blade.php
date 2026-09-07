{{--
    Donat digambar pakai CSS conic-gradient murni (bukan Chart.js) - dua/tiga
    segmen sederhana begini tidak perlu library JS sama sekali. "Lubang"
    donat & background kartu dipasangkan SAMA PERSIS (rgb(var(--info-50))
    untuk kartu 1, rgb(var(--warning-50)) untuk kartu 2) supaya menyatu
    mulus - var(--x) Filament berisi angka RGB polos tanpa pembungkus, jadi
    WAJIB dibungkus rgb(...) di sini (beda dari kelas Tailwind gray-* yang
    sudah pasti aman dipakai polos, gray-nya Filament sendiri butuh
    pembungkusan yang sama seperti warna lain).
--}}
<x-filament-widgets::widget>
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        {{-- Kartu 1: Total ujian + donat sudah dilaporkan --}}
        <div
            class="flex items-center justify-between gap-4 rounded-2xl p-5"
            style="background-color: rgb(var(--info-50))"
        >
            <div>
                <div class="text-4xl font-bold text-gray-900 dark:text-gray-900">{{ $total }}</div>
                <div class="mt-1 text-xs font-semibold tracking-wide text-gray-500">TOTAL UJIAN</div>
                <div class="mt-2 text-sm font-semibold" style="color: rgb(var(--success-600))">
                    {{ $sudah }} Sudah Dilaporkan
                </div>
            </div>

            <div
                class="relative flex h-24 w-24 shrink-0 items-center justify-center rounded-full"
                style="background: conic-gradient(rgb(var(--success-500)) 0% {{ $sudahPct }}%, rgb(var(--gray-200)) {{ $sudahPct }}% 100%)"
            >
                <div
                    class="flex h-16 w-16 flex-col items-center justify-center rounded-full"
                    style="background-color: rgb(var(--info-50))"
                >
                    <span class="text-base font-bold text-gray-900">{{ $sudahPct }}%</span>
                    <span class="text-[9px] font-semibold tracking-wide text-gray-400">DILAPORKAN</span>
                </div>
            </div>
        </div>

        {{-- Kartu 2: Belum dilaporkan + donat proporsi jenis ujian --}}
        <div
            class="flex items-center justify-between gap-4 rounded-2xl p-5"
            style="background-color: rgb(var(--warning-50))"
        >
            <div>
                <div class="text-4xl font-bold text-gray-900">{{ $belum }}</div>
                <div class="mt-1 text-xs font-semibold tracking-wide text-gray-500">
                    BELUM DILAPORKAN
                    <span class="font-bold" style="color: rgb(var(--warning-600))">{{ $belumPct }}%</span>
                </div>
            </div>

            <div
                class="relative flex h-24 w-24 shrink-0 items-center justify-center rounded-full"
                style="background: conic-gradient(
                    rgb(var(--warning-500)) 0% {{ $stopSempro }}%,
                    rgb(var(--info-500)) {{ $stopSempro }}% {{ $stopSemhas }}%,
                    rgb(var(--success-500)) {{ $stopSemhas }}% 100%
                )"
            >
                <div
                    class="flex h-16 w-16 flex-col items-center justify-center rounded-full"
                    style="background-color: rgb(var(--warning-50))"
                >
                    <span class="text-base font-bold text-gray-900">{{ $belum }}</span>
                    <span class="text-[9px] font-semibold tracking-wide text-gray-400">UJIAN</span>
                </div>
            </div>

            <div class="flex flex-col gap-1.5 text-xs">
                <div class="flex items-center gap-1.5">
                    <span class="h-2 w-2 shrink-0 rounded-full" style="background-color: rgb(var(--warning-500))"></span>
                    <span class="text-gray-600">Sempro</span>
                    <span class="font-semibold text-gray-900">{{ $belumSempro }}</span>
                    <span class="text-gray-400">({{ $pctSempro }}%)</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="h-2 w-2 shrink-0 rounded-full" style="background-color: rgb(var(--info-500))"></span>
                    <span class="text-gray-600">Semhas</span>
                    <span class="font-semibold text-gray-900">{{ $belumSemhas }}</span>
                    <span class="text-gray-400">({{ $pctSemhas }}%)</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="h-2 w-2 shrink-0 rounded-full" style="background-color: rgb(var(--success-500))"></span>
                    <span class="text-gray-600">Sidang</span>
                    <span class="font-semibold text-gray-900">{{ $belumSidang }}</span>
                    <span class="text-gray-400">({{ $pctSidang }}%)</span>
                </div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
