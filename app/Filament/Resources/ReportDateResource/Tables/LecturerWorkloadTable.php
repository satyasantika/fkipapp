<?php

namespace App\Filament\Resources\ReportDateResource\Tables;

use App\Http\Controllers\ReportDateController;
use App\Models\Lecture;
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
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * Roster dosen (satu dosen = satu baris tabel, bukan kartu - beda dengan
 * "Akan Dilaporkan") yang punya minimal satu ujian belum dilaporkan ke
 * periode manapun, sebagai pembimbing1/2 ATAU penguji1/2/3. Dipakai di
 * slide-over "Ujian by Dosen Penguji" pada baris ReportDateResource (lewat
 * ActionGroup "..." / tiga titik vertikal).
 *
 * Tombol "Daftarkan Semua" mendaftarkan SEMUA ujian dosen itu (peran
 * apapun) yang belum pernah dilaporkan ke periode manapun, ke periode
 * $this->record ini - lewat ReportDateController::confirmLecturerCascade()
 * (pola sama seperti confirmSidangCascade(), cuma kuncinya kolom peran
 * dosen bukan student_id).
 *
 * Tombol "Detail" membuka MODAL biasa (bukan slide-over) berisi semua
 * ujian dosen itu, diurutkan tanggal ujian terbaru.
 *
 * Kombinasi 4 interface+trait ini meniru persis Filament\Widgets\TableWidget
 * bawaan (lihat vendor/filament/widgets/src/TableWidget.php) - HasTable saja
 * TIDAK cukup karena tabel Filament dibangun di atas forms/actions/infolists.
 */
class LecturerWorkloadTable extends Component implements Actions\Contracts\HasActions, Forms\Contracts\HasForms, Infolists\Contracts\HasInfolists, HasTable
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
            ->defaultSort('nama');
    }

    protected function getTableQuery(): Builder
    {
        return Lecture::query()
            ->where(function (Builder $q) {
                $q->whereHas('pembimbing1Registrations', fn ($r) => $r->whereNull('report_date_id'))
                    ->orWhereHas('pembimbing2Registrations', fn ($r) => $r->whereNull('report_date_id'))
                    ->orWhereHas('penguji1Registrations', fn ($r) => $r->whereNull('report_date_id'))
                    ->orWhereHas('penguji2Registrations', fn ($r) => $r->whereNull('report_date_id'))
                    ->orWhereHas('penguji3Registrations', fn ($r) => $r->whereNull('report_date_id'));
            })
            ->with([
                'departement',
                'pembimbing1Registrations' => fn ($q) => $q->whereNull('report_date_id')->with(['exam_type', 'student']),
                'pembimbing2Registrations' => fn ($q) => $q->whereNull('report_date_id')->with(['exam_type', 'student']),
                'penguji1Registrations' => fn ($q) => $q->whereNull('report_date_id')->with(['exam_type', 'student']),
                'penguji2Registrations' => fn ($q) => $q->whereNull('report_date_id')->with(['exam_type', 'student']),
                'penguji3Registrations' => fn ($q) => $q->whereNull('report_date_id')->with(['exam_type', 'student']),
            ]);
    }

    /**
     * Semua ujian (peran apapun) dosen ini yang belum dilaporkan ke periode
     * manapun - dipakai untuk deskripsi konfirmasi "Daftarkan Semua" dan
     * isi modal "Detail".
     */
    public static function involvedRegistrations(Lecture $lecture): Collection
    {
        return collect([
            $lecture->pembimbing1Registrations,
            $lecture->pembimbing2Registrations,
            $lecture->penguji1Registrations,
            $lecture->penguji2Registrations,
            $lecture->penguji3Registrations,
        ])->flatten()->unique('id');
    }

    /**
     * Subset khusus peran penguji (bukan pembimbing) - dipakai kolom
     * "Jumlah Menguji", terpisah dari involvedRegistrations() supaya
     * "Total" di situ tidak ikut menghitung baris pembimbing.
     */
    public static function pengujiRegistrations(Lecture $lecture): Collection
    {
        return collect([
            $lecture->penguji1Registrations,
            $lecture->penguji2Registrations,
            $lecture->penguji3Registrations,
        ])->flatten()->unique('id');
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('nama')
                ->label('Nama Dosen')
                ->searchable(),
            Tables\Columns\TextColumn::make('departement.nama')
                ->label('Jurusan'),

            Tables\Columns\ColumnGroup::make('Jumlah Menguji', [
                Tables\Columns\TextColumn::make('menguji_total')
                    ->label('Total')
                    ->badge()
                    ->color('primary')
                    ->getStateUsing(fn (Lecture $record) => static::pengujiRegistrations($record)->count()),
                Tables\Columns\TextColumn::make('menguji_sempro')
                    ->label('Sempro')
                    ->badge()
                    ->color('gray')
                    ->getStateUsing(fn (Lecture $record) => static::pengujiRegistrations($record)->where('ujian', 'sempro')->count()),
                Tables\Columns\TextColumn::make('menguji_semhas')
                    ->label('Semhas')
                    ->badge()
                    ->color('info')
                    ->getStateUsing(fn (Lecture $record) => static::pengujiRegistrations($record)->where('ujian', 'semhas')->count()),
                Tables\Columns\TextColumn::make('menguji_sidang')
                    ->label('Sidang')
                    ->badge()
                    ->color('success')
                    ->getStateUsing(fn (Lecture $record) => static::pengujiRegistrations($record)->where('ujian', 'sidang')->count()),
            ]),

            Tables\Columns\ColumnGroup::make('Jumlah Membimbing', [
                Tables\Columns\TextColumn::make('membimbing_total')
                    ->label('Total')
                    ->badge()
                    ->color('primary')
                    ->getStateUsing(fn (Lecture $record) => $record->pembimbing1Registrations->count() + $record->pembimbing2Registrations->count()),
                Tables\Columns\TextColumn::make('membimbing_1')
                    ->label('Pembimbing 1')
                    ->badge()
                    ->color('warning')
                    ->getStateUsing(fn (Lecture $record) => $record->pembimbing1Registrations->count()),
                Tables\Columns\TextColumn::make('membimbing_2')
                    ->label('Pembimbing 2')
                    ->badge()
                    ->color('info')
                    ->getStateUsing(fn (Lecture $record) => $record->pembimbing2Registrations->count()),
            ]),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('registerAll')
                ->label('Daftarkan Semua')
                ->tooltip('Daftarkan Semua')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->iconButton()
                ->requiresConfirmation()
                ->modalDescription(fn (Lecture $record) => 'Tambahkan '.static::involvedRegistrations($record)->count().' data ujian dosen ini ke laporan periode ini?')
                ->action(function (Lecture $record): void {
                    $request = Request::create('', 'PUT', [
                        'report_date_id' => $this->record->id,
                    ]);

                    try {
                        app(ReportDateController::class)->confirmLecturerCascade($request, $record);
                        Notification::make()->title('Berhasil menambahkan data ujian ke laporan')->success()->send();
                    } catch (\RuntimeException $e) {
                        Notification::make()->title($e->getMessage())->warning()->send();
                    }

                    $this->resetTable();
                }),
            Tables\Actions\Action::make('detail')
                ->label('Detail')
                ->tooltip('Detail')
                ->icon('heroicon-o-eye')
                ->iconButton()
                ->modalHeading(fn (Lecture $record) => 'Ujian - '.$record->nama)
                ->modalContent(fn (Lecture $record) => view('filament.resources.report-date-resource.tables.lecturer-detail-modal', [
                    'registrations' => static::involvedRegistrations($record)->sortByDesc('tanggal_ujian'),
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup'),
        ];
    }

    public function render()
    {
        return view('filament.resources.report-date-resource.tables.lecturer-workload-embedded');
    }
}
