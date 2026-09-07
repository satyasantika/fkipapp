{{-- Warna per jenis ujian disamakan dengan konvensi kalender ujian
     (ListExamRegistrations): sempro=gray, semhas=warning, sidang=success,
     total=info - rgb(var(--x)) dipakai karena panel ini tidak punya build
     Tailwind sendiri (kelas semacam bg-success-50 tidak pernah benar-benar
     terkompilasi, lihat catatan panjang di student-card.blade.php). --}}
<x-filament-widgets::widget>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        {{-- Bento 1: total ujian pernah dilaksanakan (all-time). --}}
        <x-filament::section>
            <x-slot name="heading">Total Ujian Dilaksanakan</x-slot>

            <div class="grid grid-cols-2 gap-3">
                <div
                    class="col-span-2 flex flex-col justify-center rounded-xl p-4"
                    style="background-color: color-mix(in srgb, rgb(var(--info-500)) 10%, transparent)"
                >
                    <span class="text-sm font-medium" style="color: rgb(var(--info-700))">Total</span>
                    <span class="text-3xl font-bold" style="color: rgb(var(--info-700))">{{ $total['total'] }}</span>
                </div>

                <div class="flex flex-col justify-center rounded-xl bg-gray-50 p-3 dark:bg-white/5">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Sempro</span>
                    <span class="text-xl font-bold text-gray-700 dark:text-gray-200">{{ $total['sempro'] }}</span>
                </div>

                <div
                    class="flex flex-col justify-center rounded-xl p-3"
                    style="background-color: color-mix(in srgb, rgb(var(--warning-500)) 10%, transparent)"
                >
                    <span class="text-xs font-medium" style="color: rgb(var(--warning-700))">Semhas</span>
                    <span class="text-xl font-bold" style="color: rgb(var(--warning-700))">{{ $total['semhas'] }}</span>
                </div>

                <div
                    class="col-span-2 flex flex-col justify-center rounded-xl p-3"
                    style="background-color: color-mix(in srgb, rgb(var(--success-500)) 10%, transparent)"
                >
                    <span class="text-xs font-medium" style="color: rgb(var(--success-700))">Sidang</span>
                    <span class="text-xl font-bold" style="color: rgb(var(--success-700))">{{ $total['sidang'] }}</span>
                </div>
            </div>
        </x-filament::section>

        {{-- Bento 2: belum dilaporkan - sama strukturnya, ditandai warna
             hangat (amber) di kartu Total supaya beda kesan dari bento 1
             (ini yang perlu perhatian/tindak lanjut). --}}
        <x-filament::section>
            <x-slot name="heading">Belum Dilaporkan</x-slot>

            <div class="grid grid-cols-2 gap-3">
                <div
                    class="col-span-2 flex flex-col justify-center rounded-xl p-4"
                    style="background-color: color-mix(in srgb, rgb(var(--warning-500)) 12%, transparent)"
                >
                    <span class="text-sm font-medium" style="color: rgb(var(--warning-700))">Total</span>
                    <span class="text-3xl font-bold" style="color: rgb(var(--warning-700))">{{ $belumDilaporkan['total'] }}</span>
                </div>

                <div class="flex flex-col justify-center rounded-xl bg-gray-50 p-3 dark:bg-white/5">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Sempro</span>
                    <span class="text-xl font-bold text-gray-700 dark:text-gray-200">{{ $belumDilaporkan['sempro'] }}</span>
                </div>

                <div
                    class="flex flex-col justify-center rounded-xl p-3"
                    style="background-color: color-mix(in srgb, rgb(var(--warning-500)) 10%, transparent)"
                >
                    <span class="text-xs font-medium" style="color: rgb(var(--warning-700))">Semhas</span>
                    <span class="text-xl font-bold" style="color: rgb(var(--warning-700))">{{ $belumDilaporkan['semhas'] }}</span>
                </div>

                <div
                    class="col-span-2 flex flex-col justify-center rounded-xl p-3"
                    style="background-color: color-mix(in srgb, rgb(var(--success-500)) 10%, transparent)"
                >
                    <span class="text-xs font-medium" style="color: rgb(var(--success-700))">Sidang</span>
                    <span class="text-xl font-bold" style="color: rgb(var(--success-700))">{{ $belumDilaporkan['sidang'] }}</span>
                </div>
            </div>
        </x-filament::section>
    </div>

    {{-- Mahasiswa & Dosen sebagai KPI yang bisa diklik (menggantikan tombol
         "Mahasiswa"/"Dosen" di widget quick-links) - tetap menuju resource
         masing-masing, cuma bentuknya kartu angka, bukan tombol polos. --}}
    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <a
            href="{{ $studentUrl }}"
            class="flex items-center gap-4 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 transition hover:ring-primary-300 dark:bg-gray-900 dark:ring-white/10 dark:hover:ring-primary-500"
        >
            <span
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg"
                style="background-color: color-mix(in srgb, rgb(var(--primary-500)) 12%, transparent)"
            >
                <x-filament::icon icon="heroicon-o-user-group" class="h-6 w-6" style="color: rgb(var(--primary-600))" />
            </span>
            <span class="flex flex-col">
                <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $studentCount }}</span>
                <span class="text-sm text-gray-500 dark:text-gray-400">Mahasiswa</span>
            </span>
        </a>

        <a
            href="{{ $lectureUrl }}"
            class="flex items-center gap-4 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 transition hover:ring-primary-300 dark:bg-gray-900 dark:ring-white/10 dark:hover:ring-primary-500"
        >
            <span
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg"
                style="background-color: color-mix(in srgb, rgb(var(--primary-500)) 12%, transparent)"
            >
                <x-filament::icon icon="heroicon-o-academic-cap" class="h-6 w-6" style="color: rgb(var(--primary-600))" />
            </span>
            <span class="flex flex-col">
                <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $lectureCount }}</span>
                <span class="text-sm text-gray-500 dark:text-gray-400">Dosen</span>
            </span>
        </a>
    </div>

    <div class="mt-4">
        <x-filament::button tag="a" :href="$examRegistrationUrl" icon="heroicon-o-clipboard-document-list" color="primary">
            Kelola Laporan Ujian
        </x-filament::button>
    </div>
</x-filament-widgets::widget>
