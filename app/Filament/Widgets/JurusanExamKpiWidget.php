<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ExamRegistrationResource;
use App\Filament\Resources\LectureResource;
use App\Filament\Resources\StudentResource;
use Filament\Widgets\Widget;

/**
 * Dua "bento" KPI khusus role jurusan di dashboard /admin: total ujian yang
 * pernah dilaksanakan (all-time) dan ujian yang belum dilaporkan, masing-
 * masing dipecah per jenis ujian (sempro/semhas/sidang) - plus tombol ke
 * menu Registrasi Ujian. Query dasarnya reuse ExamRegistrationResource::
 * getEloquentQuery() supaya scope departemen jurusan konsisten dengan
 * halaman lain (bukan menduplikasi filter departement_id manual).
 */
class JurusanExamKpiWidget extends Widget
{
    protected static string $view = 'filament.widgets.jurusan-exam-kpi-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -1;

    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('jurusan') ?? false;
    }

    /**
     * @return array{total: int, sempro: int, semhas: int, sidang: int}
     */
    private function counts(?bool $dilaporkan = null): array
    {
        $query = ExamRegistrationResource::getEloquentQuery()
            ->join('exam_types', 'exam_types.id', '=', 'exam_registrations.exam_type_id');

        if (! is_null($dilaporkan)) {
            $query->where('exam_registrations.dilaporkan', $dilaporkan);
        }

        $rows = $query->selectRaw('exam_types.singkat_ujian, count(*) as jumlah')
            ->groupBy('exam_types.singkat_ujian')
            ->pluck('jumlah', 'singkat_ujian');

        $sempro = (int) ($rows['sempro'] ?? 0);
        $semhas = (int) ($rows['semhas'] ?? 0);
        $sidang = (int) ($rows['sidang'] ?? 0);

        return [
            'total' => $sempro + $semhas + $sidang,
            'sempro' => $sempro,
            'semhas' => $semhas,
            'sidang' => $sidang,
        ];
    }

    protected function getViewData(): array
    {
        return [
            'total' => $this->counts(),
            'belumDilaporkan' => $this->counts(false),
            'examRegistrationUrl' => ExamRegistrationResource::getUrl(),
            'studentCount' => StudentResource::getEloquentQuery()->count(),
            'studentUrl' => StudentResource::getUrl(),
            'lectureCount' => LectureResource::getEloquentQuery()->count(),
            'lectureUrl' => LectureResource::getUrl(),
        ];
    }
}
