<?php

namespace App\Filament\Resources\ExamPaymentResource\Pages;

use App\Filament\Resources\ExamPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExamPayments extends ListRecords
{
    protected static string $resource = ExamPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Rate Honor')
                ->icon('heroicon-o-plus'),
        ];
    }
}
