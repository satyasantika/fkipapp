<?php

namespace App\Filament\Resources\ExamTypeResource\Pages;

use App\Filament\Resources\ExamTypeResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;

class EditExamType extends EditRecord
{
    protected static string $resource = ExamTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->iconButton()
                ->modalHeading('Hapus jenis ujian ini?')
                ->modalDescription('Jenis ujian ini akan dihapus permanen dari daftar.')
                ->before(function () {
                    if ($reason = $this->record->deletionBlockReason()) {
                        Notification::make()->title($reason)->danger()->send();

                        throw new Halt();
                    }
                }),
        ];
    }
}
