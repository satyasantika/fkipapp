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
            Actions\DeleteAction::make()
                ->iconButton()
                ->modalHeading('Hapus rate honor ini?')
                ->modalDescription('Data rate honor untuk kombinasi jabatan akademik dan pendidikan ini akan dihapus permanen.'),
        ];
    }
}
