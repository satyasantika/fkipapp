<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ExamRegistrationResource;
use Filament\Widgets\ChartWidget;

/**
 * Donat sudah/belum dilaporkan (dari total ujian all-time, bukan cuma bulan
 * ini) - pelengkap visual untuk bento JurusanExamKpiWidget supaya proporsi
 * sudah-vs-belum kelihatan sekali pandang, bukan cuma dua angka bersebelahan.
 */
class JurusanReportStatusChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Proporsi Status Laporan';

    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = 0;

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('jurusan') ?? false;
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $sudah = ExamRegistrationResource::getEloquentQuery()->where('dilaporkan', true)->count();
        $belum = ExamRegistrationResource::getEloquentQuery()->where('dilaporkan', false)->count();

        return [
            'datasets' => [
                [
                    'data' => [$sudah, $belum],
                    // Emerald-500 & Amber-500 - disamakan dengan warna
                    // success/warning yang sudah dipakai di seluruh panel
                    // ini (success di-override jadi Emerald di
                    // AdminPanelProvider, warning pakai default Amber
                    // Filament) supaya konsisten walau Chart.js perlu nilai
                    // warna literal (tidak bisa baca var(--x) CSS langsung).
                    'backgroundColor' => ['#10b981', '#f59e0b'],
                ],
            ],
            'labels' => ['Sudah Dilaporkan', 'Belum Dilaporkan'],
        ];
    }
}
