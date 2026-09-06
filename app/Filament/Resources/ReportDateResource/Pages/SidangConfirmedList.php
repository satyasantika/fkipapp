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
use Illuminate\Support\HtmlString;

/**
 * Roster mahasiswa dengan data ujian sidang yang belum pernah dimasukkan ke
 * periode laporan manapun - porting dari ViewExamSidangConfirmedDataTable,
 * meniru persis logika markIfChanged/pendingSiblings/siblingsByType-nya.
 */
class SidangConfirmedList extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = ReportDateResource::class;

    protected static string $view = 'filament.resources.report-date-resource.pages.table-page';

    private const EXAM_TYPE_SIDANG = 3;

    private const EXAM_TYPE_SEMPRO_SEMHAS = [1, 2];

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getTitle(): string
    {
        return 'Pasti Sidang Belum Dilaporkan - '.Carbon::parse($this->record->tanggal)->format('Y-m-d');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->actions($this->getTableActions())
            ->defaultSort('tanggal_ujian', 'asc');
    }

    protected function getTableQuery(): Builder
    {
        return ExamRegistration::with([
            'exam_type', 'student', 'pembimbing1', 'pembimbing2', 'penguji1', 'penguji2', 'penguji3',
            'student.examregistrations' => fn ($q) => $q->whereIn('exam_type_id', [1, 2, self::EXAM_TYPE_SIDANG]),
        ])
            ->where('exam_type_id', self::EXAM_TYPE_SIDANG)
            ->whereNull('report_date_id');
    }

    private function siblingsAll(ExamRegistration $row)
    {
        return $row->student->examregistrations;
    }

    private function pendingSiblings(ExamRegistration $row)
    {
        return $this->siblingsAll($row)
            ->whereIn('exam_type_id', self::EXAM_TYPE_SEMPRO_SEMHAS)
            ->whereNull('report_date_id');
    }

    private function markIfChanged(ExamRegistration $row, string $column, ?string $displayName): HtmlString
    {
        $name = e($displayName ?? '');
        $changed = $this->siblingsAll($row)->pluck($column)->filter()->unique()->count() > 1;

        if (! $changed) {
            return new HtmlString($name);
        }

        return new HtmlString($name.' <span class="fi-badge inline-flex items-center rounded-md px-2 text-xs font-medium bg-warning-100 text-warning-700" title="Berbeda antara sempro/semhas/sidang">&ne;</span>');
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('dilaporkan')
                ->label('Lapor?')
                ->formatStateUsing(fn (bool $state): string => $state ? 'sudah' : 'belum'),
            Tables\Columns\TextColumn::make('ujian')
                ->label('Ujian')
                ->html()
                ->formatStateUsing(function (ExamRegistration $record): HtmlString {
                    $badges = '<span class="fi-badge inline-flex items-center rounded-md px-2 text-xs font-medium bg-primary-100 text-primary-700">'.e($record->ujian ?? '').'</span>';

                    foreach ([1, 2] as $examTypeId) {
                        $sibling = $this->siblingsAll($record)->firstWhere('exam_type_id', $examTypeId);
                        if (! $sibling || ! is_null($sibling->report_date_id)) {
                            continue;
                        }
                        $badges .= $examTypeId === 1
                            ? ' <span class="fi-badge inline-flex items-center rounded-md px-2 text-xs font-medium bg-gray-100 text-gray-700" title="sempro belum dilaporkan">sempro</span>'
                            : ' <span class="fi-badge inline-flex items-center rounded-md px-2 text-xs font-medium bg-info-100 text-info-700" title="semhas belum dilaporkan">semhas</span>';
                    }

                    return new HtmlString($badges);
                }),
            Tables\Columns\TextColumn::make('tanggal_ujian')
                ->label('Tgl. Sidang')
                ->date(),
            Tables\Columns\TextColumn::make('student.nim')
                ->label('NIM'),
            Tables\Columns\TextColumn::make('student.nama')
                ->label('Mahasiswa'),
            Tables\Columns\TextColumn::make('pembimbing1_id')
                ->label('Pemb.1')
                ->html()
                ->formatStateUsing(fn (ExamRegistration $record) => $this->markIfChanged($record, 'pembimbing1_id', $record->pembimbing1_nama)),
            Tables\Columns\TextColumn::make('pembimbing2_id')
                ->label('Pemb.2')
                ->html()
                ->formatStateUsing(fn (ExamRegistration $record) => $this->markIfChanged($record, 'pembimbing2_id', $record->pembimbing2_nama)),
            Tables\Columns\TextColumn::make('penguji1_id')
                ->label('Peng.1')
                ->html()
                ->formatStateUsing(fn (ExamRegistration $record) => $this->markIfChanged($record, 'penguji1_id', $record->penguji1_nama)),
            Tables\Columns\TextColumn::make('penguji2_id')
                ->label('Peng.2')
                ->html()
                ->formatStateUsing(fn (ExamRegistration $record) => $this->markIfChanged($record, 'penguji2_id', $record->penguji2_nama)),
            Tables\Columns\TextColumn::make('penguji3_id')
                ->label('Peng.3')
                ->html()
                ->formatStateUsing(fn (ExamRegistration $record) => $this->markIfChanged($record, 'penguji3_id', $record->penguji3_nama)),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('cascade')
                ->label(fn (ExamRegistration $record): string => '+ '.(1 + $this->pendingSiblings($record)->count()))
                ->tooltip(fn (ExamRegistration $record): string => 'Tambahkan '.(1 + $this->pendingSiblings($record)->count()).' data ujian ke laporan ini')
                ->color('success')
                ->requiresConfirmation()
                ->action(function (ExamRegistration $record): void {
                    $request = Request::create('', 'PUT', [
                        'report_date_id' => $this->record->id,
                    ]);

                    try {
                        app(ReportDateController::class)->confirmSidangCascade($request, $record);
                        Notification::make()->title('Berhasil menambahkan data ujian ke laporan')->success()->send();
                    } catch (\RuntimeException $e) {
                        Notification::make()->title($e->getMessage())->warning()->send();
                    }

                    $this->resetTable();
                }),
        ];
    }
}
