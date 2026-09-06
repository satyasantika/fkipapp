<?php

namespace App\Filament\Resources\ReportDateResource\Pages;

use App\Filament\Resources\ReportDateResource;
use App\Http\Controllers\ReportDateController;
use App\Models\ExamRegistration;
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

class NotReportedList extends Page implements HasTable
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
        return 'Belum Dilaporkan - '.Carbon::parse($this->record->tanggal)->format('Y-m-d');
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
            Tables\Actions\Action::make('assign')
                ->label('+')
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
}
