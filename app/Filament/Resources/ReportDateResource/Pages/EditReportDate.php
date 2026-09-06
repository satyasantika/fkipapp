<?php

namespace App\Filament\Resources\ReportDateResource\Pages;

use App\Filament\Resources\ReportDateResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;

class EditReportDate extends EditRecord
{
    protected static string $resource = ReportDateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->iconButton()
                ->modalHeading('Hapus tanggal penarikan laporan ini?')
                ->modalDescription('Tanggal penarikan laporan ini akan dihapus permanen.')
                ->before(function () {
                    if ($reason = $this->record->deletionBlockReason()) {
                        Notification::make()->title($reason)->danger()->send();

                        throw new Halt();
                    }
                }),
        ];
    }
}
