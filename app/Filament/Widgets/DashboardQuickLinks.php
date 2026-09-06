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
}
