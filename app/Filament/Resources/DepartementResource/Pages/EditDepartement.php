<?php

namespace App\Filament\Resources\DepartementResource\Pages;

use App\Filament\Resources\DepartementResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;

class EditDepartement extends EditRecord
{
    protected static string $resource = DepartementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->iconButton()
                ->modalHeading('Hapus jurusan ini?')
                ->modalDescription('Data jurusan ini akan dihapus permanen.')
                ->before(function () {
                    if ($reason = $this->record->deletionBlockReason()) {
                        Notification::make()->title($reason)->danger()->send();

                        throw new Halt();
                    }
                }),
        ];
    }
}
