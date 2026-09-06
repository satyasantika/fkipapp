<?php

namespace App\Filament\Resources\ReportDateResource\Tables;

use App\Http\Controllers\ReportDateController;
use App\Models\ExamRegistration;
use App\Models\ReportDate;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Livewire\Component;

/**
 * Sama persis logikanya dengan NotReportedList (bekas halaman penuh di
 * /admin/report-dates/{record}/not-reported) - dipindah jadi komponen
 * Livewire biasa (bukan Filament Page) supaya bisa ditanam di mana saja,
 * termasuk di dalam slide-over ReportedList lewat ->modalContent().
 *
 * Kombinasi 4 interface+trait ini meniru persis Filament\Widgets\TableWidget
 * bawaan (lihat vendor/filament/widgets/src/TableWidget.php) - HasTable saja
 * TIDAK cukup karena tabel Filament dibangun di atas forms/actions/infolists
 * (filter, action dengan form, dst semua butuh method dari situ).
 */
class NotReportedTable extends Component implements Actions\Contracts\HasActions, Forms\Contracts\HasForms, Infolists\Contracts\HasInfolists, HasTable
{
    use Actions\Concerns\InteractsWithActions;
    use Forms\Concerns\InteractsWithForms;
    use Infolists\Concerns\InteractsWithInfolists;
    use InteractsWithTable;

    public ReportDate $record;

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->actions($this->getTableActions())
            ->defaultSort('tanggal_ujian', 'desc');
    }

    protected function getTableQuery(): Builder
    {
        $query = ExamRegistration::with(['exam_type', 'student', 'pembimbing1', 'pembimbing2', 'penguji1', 'penguji2', 'penguji3']);

        if (auth()->user()?->hasRole('keuangan')) {
            $query->whereNull('report_date_id');
        }

        return $query;
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('dilaporkan')
                ->label('Lapor?')
                ->formatStateUsing(fn (bool $state): string => $state ? 'sudah' : 'belum'),
            Tables\Columns\TextColumn::make('exam_type.singkat_ujian')
                ->label('Ujian'),
            Tables\Columns\TextColumn::make('tanggal_ujian')
                ->date(),
            Tables\Columns\TextColumn::make('student.nama')
                ->label('Mahasiswa')
                ->description(fn (ExamRegistration $record): ?string => $record->student?->nim),
            Tables\Columns\TextColumn::make('pembimbing')
                ->label('Pembimbing')
                ->getStateUsing(fn (ExamRegistration $record): array => collect([
                    $record->pembimbing1?->nama,
                    $record->pembimbing2?->nama,
                ])->filter()->values()->all())
                ->listWithLineBreaks()
                ->bulleted(),
            Tables\Columns\TextColumn::make('penguji')
                ->label('Penguji')
                ->getStateUsing(fn (ExamRegistration $record): array => collect([
                    $record->penguji1?->nama,
                    $record->penguji2?->nama,
                    $record->penguji3?->nama,
                ])->filter()->values()->all())
                ->listWithLineBreaks()
                ->bulleted(),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('assign')
                ->label('Tambahkan')
                ->icon('heroicon-o-plus')
                ->iconButton()
                ->color('success')
                ->action(function (ExamRegistration $record): void {
                    $request = Request::create('', 'PUT', [
                        'report_date_id' => $this->record->id,
                        'dilaporkan' => 1,
                    ]);

                    try {
                        app(ReportDateController::class)->setReportDate($request, $record);
                        Notification::make()->title('Ditambahkan ke laporan')->success()->send();
                    } catch (\RuntimeException $e) {
                        Notification::make()->title($e->getMessage())->warning()->send();
                    }

                    $this->resetTable();
                }),
        ];
    }

    public function render()
    {
        return view('filament.resources.report-date-resource.tables.embedded-table');
    }
}
