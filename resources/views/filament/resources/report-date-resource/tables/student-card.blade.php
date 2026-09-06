@php
    $student = $getRecord();
    $pending = $student->examregistrations;
    $pendingCount = $pending->count();
    $hasPendingSidang = $pending->contains(fn ($e) => (int) $e->exam_type_id === 3);
    $hasPendingNonSidang = $pending->contains(fn ($e) => (int) $e->exam_type_id !== 3);
    $hasReportedSidang = (bool) ($student->has_reported_sidang ?? false);
    $isDanger = $hasReportedSidang && $hasPendingNonSidang;
    $isSuccess = (! $isDanger) && $hasPendingSidang;
    $latest = $pending->first();

    $typeColors = [
        'sempro' => ['bg-gray-100', 'text-gray-700', 'hover:bg-gray-200', 'dark:bg-gray-500/20', 'dark:text-gray-300'],
        'semhas' => ['bg-info-100', 'text-info-700', 'hover:bg-info-200', 'dark:bg-info-500/20', 'dark:text-info-300'],
        'sidang' => ['bg-success-100', 'text-success-700', 'hover:bg-success-200', 'dark:bg-success-500/20', 'dark:text-success-300'],
    ];

    $pembimbing = collect([$latest?->pembimbing1?->nama, $latest?->pembimbing2?->nama])->filter()->implode(', ');
    $penguji = collect([$latest?->penguji1?->nama, $latest?->penguji2?->nama, $latest?->penguji3?->nama])->filter()->implode(', ');
@endphp

<div
    @class([
        'flex w-full flex-col gap-3 rounded-xl border p-4',
        'border-danger-300 bg-danger-50 dark:border-danger-500/30 dark:bg-danger-500/10' => $isDanger,
        'border-success-300 bg-success-50 dark:border-success-500/30 dark:bg-success-500/10' => $isSuccess,
        'border-gray-200 dark:border-white/10' => (! $isDanger) && (! $isSuccess),
    ])
>
    <div>
        <p class="text-sm font-semibold text-gray-950 dark:text-white">{{ $student->nama }}</p>
        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $student->nim }}</p>
    </div>

    @if ($isDanger)
        <p class="text-xs font-medium text-danger-700 dark:text-danger-400">
            Sidang sudah dilaporkan, tapi ada ujian lain yang belum
        </p>
    @endif

    <div class="flex flex-wrap gap-1">
        @foreach ($pending as $examRegistration)
            @php
                $type = $examRegistration->ujian ?? '';
                [$bg, $text, $hover, $darkBg, $darkText] = $typeColors[$type] ?? $typeColors['sempro'];
                $tanggal = $examRegistration->tanggal_ujian
                    ? \Illuminate\Support\Carbon::parse($examRegistration->tanggal_ujian)->format('d M Y')
                    : '-';
            @endphp
            <button
                type="button"
                wire:click="assignSingle({{ $examRegistration->id }})"
                class="inline-flex cursor-pointer items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $bg }} {{ $text }} {{ $hover }} {{ $darkBg }} {{ $darkText }}"
            >
                + {{ $type }}: {{ $tanggal }}
            </button>
        @endforeach

        @if ($pendingCount > 1)
            <button
                type="button"
                wire:click="assignAllForStudent({{ $pending->first()->id }})"
                wire:confirm="Tambahkan {{ $pendingCount }} data ujian mahasiswa ini ke laporan?"
                class="inline-flex cursor-pointer items-center rounded-full bg-primary-100 px-2.5 py-1 text-xs font-medium text-primary-700 hover:bg-primary-200 dark:bg-primary-500/20 dark:text-primary-300"
            >
                +{{ $pendingCount }} semua ujian
            </button>
        @endif
    </div>

    <div class="space-y-0.5 text-xs text-gray-600 dark:text-gray-300">
        <p><span class="font-medium">Pembimbing:</span> {{ $pembimbing !== '' ? $pembimbing : '-' }}</p>
        <p><span class="font-medium">Penguji:</span> {{ $penguji !== '' ? $penguji : '-' }}</p>
    </div>
</div>
