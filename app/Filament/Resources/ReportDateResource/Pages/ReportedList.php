<?php

namespace App\Filament\Resources\ReportDateResource\Pages;

use App\Filament\Resources\ReportDateResource;
use App\Http\Controllers\ReportDateController;
use App\Models\ExamRegistration;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportedList extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = ReportDateResource::class;

    protected static string $view = 'filament.resources.report-date-resource.pages.table-page';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getTitle(): string
    {
        return 'Sudah Dilaporkan - '.Carbon::parse($this->record->tanggal)->format('Y-m-d');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('backToReportDates')
                ->label('Kembali ke Penarikan Laporan')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn () => ReportDateResource::getUrl()),
            Actions\Action::make('addFromNotReported')
                ->label('+ data pelaporan')
                ->icon('heroicon-o-plus-circle')
                ->disabled(fn (): bool => $this->record->is_locked)
                ->slideOver()
                ->modalWidth('7xl')
                ->modalHeading('Akan Dilaporkan - '.Carbon::parse($this->record->tanggal)->format('Y-m-d'))
                ->modalContent(fn () => view('filament.resources.report-date-resource.tables.not-reported-slideover', [
                    'record' => $this->record,
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup'),
            Actions\ActionGroup::make([
                Actions\Action::make('lecturerWorkload')
                    ->label('Ujian by Dosen Penguji')
                    ->icon('heroicon-o-user-group')
                    ->disabled(fn (): bool => $this->record->is_locked)
                    ->slideOver()
                    ->modalWidth('7xl')
                    ->modalHeading('Ujian by Dosen Penguji - '.Carbon::parse($this->record->tanggal)->format('Y-m-d'))
                    ->modalContent(fn () => view('filament.resources.report-date-resource.tables.lecturer-workload-slideover', [
                        'record' => $this->record,
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
            ]),
        ];
    }

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
            $query->where('report_date_id', $this->record->id);
        }

        return $query;
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('exam_type.singkat_ujian')
                ->label('Ujian')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'sempro' => 'gray',
                    'semhas' => 'info',
                    'sidang' => 'success',
                    default => 'gray',
                }),
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
            Tables\Actions\Action::make('retract')
                ->label('Cabut dari laporan')
                ->icon('heroicon-o-arrow-uturn-left')
                ->iconButton()
                ->color('danger')
                ->requiresConfirmation()
                ->modalDescription('Batalkan laporan ujian ini? Status dibayar tiap pembimbing/penguji akan direset (mengikuti perilaku form lama).')
                ->action(function (ExamRegistration $record): void {
                    $request = Request::create('', 'PUT', [
                        'report_date_id' => null,
                        'dilaporkan' => 0,
                        'pembimbing1_dibayar' => $record->pembimbing1_dibayar,
                        'pembimbing2_dibayar' => $record->pembimbing2_dibayar,
                        'penguji1_dibayar' => $record->penguji1_dibayar,
                        'penguji2_dibayar' => $record->penguji2_dibayar,
                        'penguji3_dibayar' => $record->penguji3_dibayar,
                    ]);

                    try {
                        app(ReportDateController::class)->setReportDate($request, $record);
                        Notification::make()->title('Laporan dicabut')->success()->send();
                    } catch (\RuntimeException $e) {
                        Notification::make()->title($e->getMessage())->warning()->send();
                    }

                    $this->resetTable();
                }),
        ];
    }
}
