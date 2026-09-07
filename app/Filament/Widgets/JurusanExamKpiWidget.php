<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ExamRegistrationResource;
use App\Filament\Resources\LectureResource;
use App\Filament\Resources\StudentResource;
use Filament\Widgets\Widget;

/**
 * KPI Mahasiswa & Dosen (bisa diklik ke resource masing-masing) + tombol
 * "Kelola Laporan Ujian", khusus role jurusan di dashboard /admin. Query
 * reuse StudentResource/LectureResource::getEloquentQuery() supaya scope
 * departemen jurusan konsisten dengan halaman lain (bukan menduplikasi
 * filter departement_id manual).
 */
class JurusanExamKpiWidget extends Widget
{
    protected static string $view = 'filament.widgets.jurusan-exam-kpi-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 0;

    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('jurusan') ?? false;
    }

    protected function getViewData(): array
    {
        return [
            'examRegistrationUrl' => ExamRegistrationResource::getUrl(),
            'studentCount' => StudentResource::getEloquentQuery()->count(),
            'studentUrl' => StudentResource::getUrl(),
            'lectureCount' => LectureResource::getEloquentQuery()->count(),
            'lectureUrl' => LectureResource::getUrl(),
        ];
    }
}
