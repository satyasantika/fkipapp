<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ExamRegistrationResource;
use Filament\Widgets\Widget;

/**
 * Dua kartu status ujian ala referensi desain user: kartu 1 = total ujian
 * all-time + donat sudah-dilaporkan (2 segmen: hijau=sudah, abu=sisanya),
 * kartu 2 = belum dilaporkan + donat 3 segmen (proporsi sempro/semhas/
 * sidang DI DALAM yang belum dilaporkan) + legenda titik warna+jumlah+
 * persen. Donat digambar pakai CSS conic-gradient murni (bukan Chart.js/
 * library JS) - lingkaran sederhana 2-3 segmen begini tidak perlu library,
 * dan menghindari perlu memuat aset chart.js yang biasanya baru otomatis
 * dimuat Filament kalau ada ChartWidget di halaman.
 */
class JurusanExamStatusOverviewWidget extends Widget
{
    protected static string $view = 'filament.widgets.jurusan-exam-status-overview-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -1;

    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('jurusan') ?? false;
    }

    protected function getViewData(): array
    {
        $query = ExamRegistrationResource::getEloquentQuery();

        $total = (clone $query)->count();
        $sudah = (clone $query)->where('dilaporkan', true)->count();
        $belum = $total - $sudah;
        $sudahPct = $total > 0 ? round($sudah / $total * 100, 1) : 0.0;
        $belumPct = $total > 0 ? round(100 - $sudahPct, 1) : 0.0;

        $belumByType = (clone $query)
            ->join('exam_types', 'exam_types.id', '=', 'exam_registrations.exam_type_id')
            ->where('exam_registrations.dilaporkan', false)
            ->selectRaw('exam_types.singkat_ujian, count(*) as jumlah')
            ->groupBy('exam_types.singkat_ujian')
            ->pluck('jumlah', 'singkat_ujian');

        $belumSempro = (int) ($belumByType['sempro'] ?? 0);
        $belumSemhas = (int) ($belumByType['semhas'] ?? 0);
        $belumSidang = (int) ($belumByType['sidang'] ?? 0);

        $pctSempro = $belum > 0 ? round($belumSempro / $belum * 100, 1) : 0.0;
        $pctSemhas = $belum > 0 ? round($belumSemhas / $belum * 100, 1) : 0.0;
        $pctSidang = $belum > 0 ? round($belumSidang / $belum * 100, 1) : 0.0;

        return [
            'total' => $total,
            'sudah' => $sudah,
            'belum' => $belum,
            'sudahPct' => $sudahPct,
            'belumPct' => $belumPct,
            'belumSempro' => $belumSempro,
            'belumSemhas' => $belumSemhas,
            'belumSidang' => $belumSidang,
            'pctSempro' => $pctSempro,
            'pctSemhas' => $pctSemhas,
            'pctSidang' => $pctSidang,
            // Titik potong kumulatif untuk conic-gradient (segmen ke-2 mulai
            // di ujung segmen ke-1, dst).
            'stopSempro' => $pctSempro,
            'stopSemhas' => $pctSempro + $pctSemhas,
        ];
    }
}
