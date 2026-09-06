<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;

class EditStudent extends EditRecord
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->iconButton()
                ->modalHeading('Hapus data mahasiswa ini?')
                ->modalDescription('Data mahasiswa ini akan dihapus permanen.')
                ->before(function () {
                    if ($reason = $this->record->deletionBlockReason()) {
                        Notification::make()->title($reason)->danger()->send();

                        throw new Halt();
                    }
                }),
        ];
    }
}
