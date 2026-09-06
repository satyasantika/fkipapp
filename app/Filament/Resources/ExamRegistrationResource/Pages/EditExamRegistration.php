<?php

namespace App\Filament\Resources\ExamRegistrationResource\Pages;

use App\Filament\Resources\ExamRegistrationResource;
use App\Http\Controllers\ExamPaymentReportController;
use App\Models\Student;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditExamRegistration extends EditRecord
{
    protected static string $resource = ExamRegistrationResource::class;

    protected function afterSave(): void
    {
        $registration = $this->record;
        $student = Student::find($registration->student_id);

        if (! $student) {
            return;
        }

        $student->update([
            'penguji1_id' => $registration->penguji1_id,
            'penguji2_id' => $registration->penguji2_id,
            'penguji3_id' => $registration->penguji3_id,
            'pembimbing1_id' => $registration->pembimbing1_id,
            'pembimbing2_id' => $registration->pembimbing2_id,
            'ketuapenguji_id' => $registration->ketuapenguji_id,
            ...(($column = ExamRegistrationResource::examTypeDateColumn($registration->exam_type_id))
                ? [$column => $registration->tanggal_ujian]
                : []),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('laporkanUjian')
                ->label('Laporkan Ujian')
                ->color('success')
                ->requiresConfirmation()
                ->modalDescription('Data ujian ini akan dilaporkan ke atasan.')
                ->visible(fn (): bool => (auth()->user()?->hasRole('keuangan') ?? false) && ! $this->record->dilaporkan)
                ->action(function () {
                    try {
                        app(ExamPaymentReportController::class)->_reportStore($this->record->id);
                        Notification::make()
                            ->title('Data laporan para penguji untuk mahasiswa '.strtoupper($this->record->student->nama).' telah ditambahkan')
                            ->success()
                            ->send();
                    } catch (\RuntimeException $e) {
                        Notification::make()
                            ->title($e->getMessage())
                            ->warning()
                            ->send();
                    }
                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                }),

            Actions\Action::make('cabutLaporan')
                ->label('Cabut Laporan')
                ->color('danger')
                ->requiresConfirmation()
                ->modalDescription('Batalkan laporan? Status dibayar tiap pembimbing/penguji akan direset (mengikuti perilaku form lama).')
                ->visible(fn (): bool => (auth()->user()?->hasRole('keuangan') ?? false) && $this->record->dilaporkan)
                ->action(function () {
                    $this->record->update([
                        'dilaporkan' => false,
                        'pembimbing1_dibayar' => false,
                        'pembimbing2_dibayar' => false,
                        'penguji1_dibayar' => false,
                        'penguji2_dibayar' => false,
                        'penguji3_dibayar' => false,
                    ]);
                    Notification::make()->title('Laporan dibatalkan')->success()->send();
                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                }),

            Actions\DeleteAction::make()
                ->iconButton()
                ->modalHeading('Hapus registrasi ujian ini?')
                ->modalDescription('Registrasi ujian mahasiswa ini akan dihapus permanen. Tanggal ujian pada data mahasiswa terkait akan direset.')
                ->visible(fn (): bool => ! $this->record->dilaporkan)
                ->before(function () {
                    $student = Student::find($this->record->student_id);
                    if ($student && ($column = ExamRegistrationResource::examTypeDateColumn($this->record->exam_type_id))) {
                        $student->update([$column => null]);
                    }
                }),
        ];
    }
}
