<?php

namespace App\Filament\Resources\ExamPaymentReportResource\Pages;

use App\Filament\Resources\ExamPaymentReportResource;
use App\Models\ExamPaymentReport;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListExamPaymentReports extends ListRecords
{
    protected static string $resource = ExamPaymentReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('emptyZeroHonor')
                ->label('Kosongkan Tanpa Honor')
                ->color('danger')
                ->requiresConfirmation()
                ->modalDescription('Hapus semua baris laporan yang jumlah dibayarnya Rp 0?')
                ->action(function (): void {
                    $ids = ExamPaymentReport::all()
                        ->filter(fn (ExamPaymentReport $r) => $r->honor_dibayar == 0)
                        ->pluck('id');
                    ExamPaymentReport::destroy($ids);
                    Notification::make()->title($ids->count().' baris tanpa honor dihapus')->success()->send();
                }),
        ];
    }
}
