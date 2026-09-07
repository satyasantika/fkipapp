<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

/**
 * Meniru home.blade.php + dashboards/{admin,departement,financial}.blade.php
 * lama - pesan selamat datang + tautan cepat per role, di dashboard panel
 * Filament. Sebagian tautan mengarah ke Resource baru (yang sudah dimigrasi),
 * sebagian lagi masih ke route lama (laporan-laporan yang belum dipindah).
 */
class DashboardQuickLinks extends Widget
{
    protected static string $view = 'filament.widgets.dashboard-quick-links';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -2;

    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        // Blok jurusan di view ini sudah kosong (Mahasiswa/Dosen jadi KPI,
        // Reg Ujian/Rekap Ujian dihapus - lihat JurusanExamKpiWidget) -
        // kartu selamat datang + catatan "tampilan lama" jadi tidak relevan
        // lagi buat jurusan, jadi widget ini disembunyikan untuk role itu.
        return ! (auth()->user()?->hasRole('jurusan') ?? false);
    }
}
