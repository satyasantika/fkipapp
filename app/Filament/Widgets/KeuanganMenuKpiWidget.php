<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ExamPaymentResource;
use App\Filament\Resources\ExamRegistrationResource;
use App\Filament\Resources\LectureResource;
use App\Filament\Resources\ReportDateResource;
use App\Filament\Resources\StudentExamStatusResource;
use App\Models\ExamPayment;
use App\Models\ExamRegistration;
use App\Models\Lecture;
use App\Models\ReportDate;
use App\Models\Student;
use Filament\Widgets\Widget;

/**
 * Satu kartu KPI/bento per menu yang bisa diakses role keuangan, klik
 * langsung ke resource-nya - menggantikan tombol polos "Dosen"/"Laporan
 * Ujian"/"Reg Ujian" di DashboardQuickLinks (lihat canView() di widget itu,
 * disembunyikan untuk keuangan sekarang). Daftar 5 menu ini dikonfirmasi
 * dari shouldRegisterNavigation()/canViewAny() tiap resource - keuangan
 * TIDAK melihat User/Mahasiswa/Jurusan/Jenis Ujian (admin-only) atau
 * Laporan Honor (navigasinya disembunyikan untuk semua role).
 */
class KeuanganMenuKpiWidget extends Widget
{
    protected static string $view = 'filament.widgets.keuangan-menu-kpi-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -1;

    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('keuangan') ?? false;
    }

    protected function getViewData(): array
    {
        return [
            'menus' => [
                [
                    'label' => 'Dosen',
                    'count' => Lecture::count(),
                    'url' => LectureResource::getUrl(),
                    'icon' => 'heroicon-o-academic-cap',
                ],
                [
                    'label' => 'Rate Honor',
                    'count' => ExamPayment::count(),
                    'url' => ExamPaymentResource::getUrl(),
                    'icon' => 'heroicon-o-banknotes',
                ],
                [
                    'label' => 'Registrasi Ujian',
                    'count' => ExamRegistration::count(),
                    'url' => ExamRegistrationResource::getUrl(),
                    'icon' => 'heroicon-o-clipboard-document-list',
                ],
                [
                    'label' => 'Penarikan Laporan',
                    'count' => ReportDate::count(),
                    'url' => ReportDateResource::getUrl(),
                    'icon' => 'heroicon-o-calendar-days',
                ],
                [
                    'label' => 'Status Ujian Mahasiswa',
                    'count' => Student::count(),
                    'url' => StudentExamStatusResource::getUrl(),
                    'icon' => 'heroicon-o-document-magnifying-glass',
                ],
            ],
        ];
    }
}
