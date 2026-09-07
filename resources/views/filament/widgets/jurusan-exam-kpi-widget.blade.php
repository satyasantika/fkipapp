<x-filament-widgets::widget>
    {{-- Mahasiswa & Dosen sebagai KPI yang bisa diklik (menggantikan tombol
         "Mahasiswa"/"Dosen" di widget quick-links) - tetap menuju resource
         masing-masing, cuma bentuknya kartu angka, bukan tombol polos. --}}
    <div class="flex flex-wrap gap-4">
        <a
            href="{{ $studentUrl }}"
            class="inline-flex w-fit items-center gap-4 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 transition hover:ring-primary-300 dark:bg-gray-900 dark:ring-white/10 dark:hover:ring-primary-500"
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
            class="inline-flex w-fit items-center gap-4 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 transition hover:ring-primary-300 dark:bg-gray-900 dark:ring-white/10 dark:hover:ring-primary-500"
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
