<?php

namespace App\Filament\Resources\ReportDateResource\Pages;

use App\Filament\Resources\ReportDateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReportDates extends ListRecords
{
    protected static string $resource = ReportDateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('+ Penarikan Laporan'),
        ];
    }
}
