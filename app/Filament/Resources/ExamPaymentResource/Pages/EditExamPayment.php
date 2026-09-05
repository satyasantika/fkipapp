<?php

namespace App\Filament\Resources\ExamPaymentResource\Pages;

use App\Filament\Resources\ExamPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExamPayment extends EditRecord
{
    protected static string $resource = ExamPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
