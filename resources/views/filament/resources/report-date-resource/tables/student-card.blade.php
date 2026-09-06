@php
    $student = $getRecord();
    $pending = $student->examregistrations;
    $pendingCount = $pending->count();
    $pendingChrono = $pending->sortBy('tanggal_ujian')->values();
    $state = \App\Filament\Resources\ReportDateResource\Tables\NotReportedTable::cardState($student);

    // Warna badge lewat <x-filament::badge> (bukan kelas Tailwind tebakan
    // semacam bg-info-100/text-warning-700) karena panel ini tidak punya
    // build Tailwind sendiri - CSS yang dipakai cuma bawaan Filament yang
    // sudah di-tree-shake sesuai komponen Filament sendiri, jadi kelas warna
    // custom di luar itu tidak pernah benar-benar terkompilasi (baru
    // ketahuan saat tombol "Buang Filter" ternyata tidak terlihat sama
    // sekali). <x-filament::badge color="..."> dijamin benar karena
    // memakai CSS custom property var(--{warna}-*) yang Filament generate
    // sendiri di :root.
    $typeColors = [
        'sempro' => 'gray',
        'semhas' => 'info',
        'sidang' => 'success',
    ];
@endphp

{{-- Tidak menggambar kotak/border sendiri di sini - kartu bawaan Filament
     di grid (fi-ta-record, sudah rounded-xl+shadow+h-full) SUDAH jadi satu-
     satunya kotak yang terlihat, supaya tidak ada kartu di dalam kartu.
     Pewarnaan status (fi-report-card-success/danger) ditambahkan lewat
     Table::recordClasses() di NotReportedTable, bukan di sini. --}}
<div class="flex h-full flex-col gap-3 p-4">
    <div>
        <p class="text-sm font-semibold text-gray-950 dark:text-white">{{ $student->nama }}</p>
        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $student->nim }}</p>
    </div>

    @if ($state['isDanger'])
        <p class="text-xs font-medium text-danger-600 dark:text-danger-400">
            Sidang sudah dilaporkan, tapi ada ujian lain yang belum
        </p>
    @endif

    <div class="flex flex-col items-start gap-1">
        @foreach ($pendingChrono as $examRegistration)
            @php
                $type = $examRegistration->ujian ?? '';
                $tanggal = $examRegistration->tanggal_ujian
                    ? \Illuminate\Support\Carbon::parse($examRegistration->tanggal_ujian)->format('d M Y')
                    : '-';
            @endphp
            <x-filament::badge
                tag="button"
                color="{{ $typeColors[$type] ?? 'gray' }}"
                wire:click="assignSingle({{ $examRegistration->id }})"
                style="border-radius:9999px;cursor:pointer"
            >
                + {{ $type }}: {{ $tanggal }}
            </x-filament::badge>
        @endforeach

        @if ($pendingCount > 1)
            <x-filament::badge
                tag="button"
                color="primary"
                wire:click="assignAllForStudent({{ $pending->first()->id }})"
                wire:confirm="Tambahkan {{ $pendingCount }} data ujian mahasiswa ini ke laporan?"
                style="border-radius:9999px;cursor:pointer"
            >
                +{{ $pendingCount }} semua ujian
            </x-filament::badge>
        @endif
    </div>
</div>
