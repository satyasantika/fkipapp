<x-filament-widgets::widget>
    <x-filament::section>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Selamat datang di Aplikasi Laporan Ujian FKIP Universitas Siliwangi! Silakan pilih menu berikut.
        </p>

        <div class="mt-4 flex flex-wrap gap-2">
            @role('admin')
                <x-filament::button tag="a" href="{{ \App\Filament\Resources\UserResource::getUrl() }}" color="primary">
                    User
                </x-filament::button>
                <x-filament::button tag="a" href="{{ \App\Filament\Resources\StudentResource::getUrl() }}" color="primary">
                    Mahasiswa
                </x-filament::button>
                <x-filament::button tag="a" href="{{ \App\Filament\Resources\LectureResource::getUrl() }}" color="primary">
                    Dosen
                </x-filament::button>
            @endrole
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
