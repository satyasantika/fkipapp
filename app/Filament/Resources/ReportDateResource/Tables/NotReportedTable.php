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
 * Roster gabungan semua ujian yang belum dilaporkan ke periode manapun -
 * dulu terpisah jadi dua slide-over (satu untuk semua jenis ujian, satu
 * lagi khusus sidang dengan opsi tambah sekaligus sempro/semhas-nya).
 * Digabung jadi satu tabel: badge "+ jenis: tanggal" menambahkan hanya
 * baris itu, badge "+N semua ujian" (muncul kalau mahasiswa itu masih
 * punya lebih dari satu ujian pending) menambahkan semuanya sekaligus -
 * keduanya ditaruh di kolom Ujian supaya tidak perlu kolom tanggal terpisah.
 *
 * Kombinasi 4 interface+trait ini meniru persis Filament\Widgets\TableWidget
 * bawaan (lihat vendor/filament/widgets/src/TableWidget.php) - HasTable saja
 * TIDAK cukup karena tabel Filament dibangun di atas forms/actions/infolists.
 */
class NotReportedTable extends Component implements Actions\Contracts\HasActions, Forms\Contracts\HasForms, Infolists\Contracts\HasInfolists, HasTable
{
    use Actions\Concerns\InteractsWithActions;
    use Forms\Concerns\InteractsWithForms;
    use Infolists\Concerns\InteractsWithInfolists;
    use InteractsWithTable;

    public ReportDate $record;

    private const EXAM_TYPE_SIDANG = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->filters($this->getTableFilters())
            ->defaultSort('tanggal_ujian', 'desc');
    }

    protected function getTableQuery(): Builder
    {
        $query = ExamRegistration::with([
            'exam_type', 'student',
            'student.examregistrations' => fn ($q) => $q->whereNull('report_date_id'),
        ]);

        if (auth()->user()?->hasRole('keuangan')) {
            $query->whereNull('report_date_id');
        }

        return $query;
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\ViewColumn::make('ujian')
                ->label('Ujian')
                ->view('filament.resources.report-date-resource.tables.ujian-badge'),
            Tables\Columns\TextColumn::make('student.nim')
                ->label('NIM')
                ->searchable(),
            Tables\Columns\TextColumn::make('student.nama')
                ->label('Mahasiswa')
                ->searchable(),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\Filter::make('sidangSaja')
                ->label('Hanya sidang')
                ->toggle()
                ->query(fn (Builder $query): Builder => $query->where('exam_type_id', self::EXAM_TYPE_SIDANG)),
        ];
    }

    public function assignSingle(int $examRegistrationId): void
    {
        $record = ExamRegistration::findOrFail($examRegistrationId);

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
    }

    public function assignAllForStudent(int $examRegistrationId): void
    {
        $record = ExamRegistration::findOrFail($examRegistrationId);

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
    }

    public function render()
    {
        return view('filament.resources.report-date-resource.tables.embedded-table');
    }
}
