<?php

namespace App\Filament\Resources\ReportDateResource\Pages;

use App\Filament\Resources\ReportDateResource;
use App\Models\ExamRegistration;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReportDate extends EditRecord
{
    protected static string $resource = ReportDateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn (): bool => ! ExamRegistration::where('report_date_id', $this->record->id)->exists()),
        ];
    }
}
