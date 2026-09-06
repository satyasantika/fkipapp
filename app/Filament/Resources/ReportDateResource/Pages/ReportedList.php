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
            Actions\Action::make('addFromNotReported')
                ->label('+ data pelaporan')
                ->url(fn (): string => ReportDateResource::getUrl('not-reported-list', ['record' => $this->record])),
            Actions\Action::make('addFromSidangConfirmed')
                ->label('+ data pasti sidang')
                ->url(fn (): string => ReportDateResource::getUrl('sidang-confirmed-list', ['record' => $this->record])),
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
            Tables\Columns\TextColumn::make('dilaporkan')
                ->label('Lapor?')
                ->formatStateUsing(fn (bool $state): string => $state ? 'sudah' : 'belum'),
            Tables\Columns\TextColumn::make('exam_type.singkat_ujian')
                ->label('Ujian'),
            Tables\Columns\TextColumn::make('tanggal_ujian')
                ->date(),
            Tables\Columns\TextColumn::make('student.nim')
                ->label('NIM'),
            Tables\Columns\TextColumn::make('student.nama')
                ->label('Mahasiswa'),
            Tables\Columns\TextColumn::make('pembimbing1.nama')
                ->label('Pemb.1'),
            Tables\Columns\TextColumn::make('pembimbing2.nama')
                ->label('Pemb.2'),
            Tables\Columns\TextColumn::make('penguji1.nama')
                ->label('Peng.1'),
            Tables\Columns\TextColumn::make('penguji2.nama')
                ->label('Peng.2'),
            Tables\Columns\TextColumn::make('penguji3.nama')
                ->label('Peng.3'),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('retract')
                ->label('-')
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
