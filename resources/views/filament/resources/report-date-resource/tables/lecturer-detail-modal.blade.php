@php
    $typeColors = [
        'sempro' => 'gray',
        'semhas' => 'info',
        'sidang' => 'success',
    ];
@endphp

<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-200 dark:border-white/10">
                <th class="px-3 py-2 text-start font-medium text-gray-500 dark:text-gray-400">Tanggal Ujian</th>
                <th class="px-3 py-2 text-start font-medium text-gray-500 dark:text-gray-400">Jenis Ujian</th>
                <th class="px-3 py-2 text-start font-medium text-gray-500 dark:text-gray-400">Mahasiswa</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-white/10">
            @forelse ($registrations as $registration)
                <tr>
                    <td class="px-3 py-2 text-gray-950 dark:text-white">
                        {{ $registration->tanggal_ujian ? \Illuminate\Support\Carbon::parse($registration->tanggal_ujian)->format('d M Y') : '-' }}
                    </td>
                    <td class="px-3 py-2">
                        <x-filament::badge :color="$typeColors[$registration->ujian] ?? 'gray'">
                            {{ $registration->ujian }}
                        </x-filament::badge>
                    </td>
                    <td class="px-3 py-2">
                        <p class="text-gray-950 dark:text-white">{{ $registration->student?->nama }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $registration->student?->nim }}</p>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-3 py-4 text-center text-gray-500 dark:text-gray-400">
                        Tidak ada data ujian.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
