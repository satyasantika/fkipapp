<?php

namespace App\Filament\Resources\LectureResource\Pages;

use App\Filament\Resources\LectureResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;

class EditLecture extends EditRecord
{
    protected static string $resource = LectureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->iconButton()
                ->modalHeading('Hapus dosen ini?')
                ->modalDescription('Data dosen ini akan dihapus permanen.')
                ->before(function () {
                    if ($reason = $this->record->deletionBlockReason()) {
                        Notification::make()->title($reason)->danger()->send();

                        throw new Halt();
                    }
                }),
        ];
    }
}
