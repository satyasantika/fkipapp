<?php

namespace App\Filament\Resources\ExamPaymentReportResource\Pages;

use App\Filament\Resources\ExamPaymentReportResource;
use App\Models\ExamPayment;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;

class EditExamPaymentReport extends EditRecord
{
    protected static string $resource = ExamPaymentReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->iconButton()
                ->modalHeading('Hapus laporan honor ini?')
                ->modalDescription('Baris laporan honor ini akan dihapus permanen.'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl();
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $examPayment = ExamPayment::where('jabatan_akademik', $data['jabatan_akademik'] ?? null)
            ->where('pendidikan', $data['pendidikan'] ?? null)
            ->first();

        if (! $examPayment) {
            Notification::make()
                ->title('Data honor untuk jabatan akademik "'.($data['jabatan_akademik'] ?? '').'" dan pendidikan "'.($data['pendidikan'] ?? '').'" belum diatur di data honor ujian.')
                ->warning()
                ->send();

            throw new Halt();
        }

        $data['status'] = $this->data['pns'] ?? false ? 1 : 0;
        $data['honor_pembimbing'] = $examPayment->honor;

        $record->update($data);

        return $record;
    }
}
