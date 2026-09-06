@php
    $record = $getRecord();
    $label = $record->ujian ?? '';
    $tanggal = $record->tanggal_ujian
        ? \Illuminate\Support\Carbon::parse($record->tanggal_ujian)->format('d M Y')
        : '-';
    $pendingCount = $record->student->examregistrations->count();
@endphp

<div class="flex flex-wrap gap-1">
    <button
        type="button"
        wire:click="assignSingle({{ $record->id }})"
        class="fi-badge inline-flex items-center rounded-md px-2 text-xs font-medium bg-success-100 text-success-700 hover:bg-success-200 cursor-pointer"
    >
        + {{ $label }}: {{ $tanggal }}
    </button>

    @if ($pendingCount > 1)
        <button
            type="button"
            wire:click="assignAllForStudent({{ $record->id }})"
            wire:confirm="Tambahkan {{ $pendingCount }} data ujian mahasiswa ini ke laporan?"
            class="fi-badge inline-flex items-center rounded-md px-2 text-xs font-medium bg-primary-100 text-primary-700 hover:bg-primary-200 cursor-pointer"
        >
            +{{ $pendingCount }} semua ujian
        </button>
    @endif
</div>
