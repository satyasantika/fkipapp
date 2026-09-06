<?php

namespace App\Filament\Resources\ReportDateResource\Tables;

use App\Filament\Resources\ExamPaymentReportResource;
use App\Models\ExamPaymentReport;
use App\Models\ReportDate;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

/**
 * Dipakai di slide-over "List Bayar ASN"/"List Bayar Non-ASN" pada baris
 * ReportDateResource - discoped ke satu periode (report_date_id) + satu
 * status (pns) sekaligus. Ini MENGGANTIKAN halaman lama
 * /exam/reports/{pns}/{report_date_id} (ViewExamPaymentReportsDataTable),
 * jadi kolomnya sengaja dibuat SAMA PERSIS dengan DataTable lama itu
 * (getColumns() di bawah) - bukan versi ringkas ExamPaymentReportResource::
 * baseColumns() yang dipakai /admin/exam-payment-reports, supaya tidak ada
 * kolom yang hilang dibanding sebelumnya. Satu-satunya beda yang disengaja:
 * tombol aksi (edit/delete) ada di kolom TERAKHIR (bawaan Filament),
 * sebelumnya di kolom pertama.
 *
 * Aksi edit (skema form-nya) & logika update tetap dipakai bareng lewat
 * ExamPaymentReportResource::form()/updateRecord() supaya tidak digandakan.
 *
 * EditAction butuh ->form() eksplisit di sini (beda dengan di
 * ExamPaymentReportResource sendiri) karena skema form Edit biasanya
 * disambung OTOMATIS oleh Filament\Resources\Pages\ListRecords::
 * configureEditAction() - itu cuma jalan kalau tabelnya ada di halaman
 * resource asli, bukan di komponen Livewire polos seperti ini.
 *
 * Tombol "Export Excel" (header action) mengarah ke rute
 * reports.section.export (ExamPaymentReportController::exportSection(),
 * pakai Maatwebsite\Excel - sudah terpasang di composer.json tapi belum
 * pernah dipakai sebelum ini) - bukan komponen exports bawaan Filament
 * (Filament\Actions\Exports\Exporter) supaya tidak perlu infrastruktur
 * queue/migrasi tambahan untuk kebutuhan sesederhana ini.
 *
 * Kombinasi 4 interface+trait ini meniru persis Filament\Widgets\TableWidget
 * bawaan (lihat vendor/filament/widgets/src/TableWidget.php) - HasTable saja
 * TIDAK cukup karena tabel Filament dibangun di atas forms/actions/infolists.
 */
class PaymentSectionTable extends Component implements Actions\Contracts\HasActions, Forms\Contracts\HasForms, Infolists\Contracts\HasInfolists, HasTable
{
    use Actions\Concerns\InteractsWithActions;
    use Forms\Concerns\InteractsWithForms;
    use Infolists\Concerns\InteractsWithInfolists;
    use InteractsWithTable;

    public ReportDate $record;

    public int $pns;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ExamPaymentReport::query()
                    ->where('report_date_id', $this->record->id)
                    ->where('status', $this->pns)
            )
            ->headerActions([
                Tables\Actions\Action::make('exportExcel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn (): string => route('reports.section.export', [
                        'pns' => $this->pns,
                        'report_date_id' => $this->record->id,
                    ]))
                    ->openUrlInNewTab(),
            ])
            ->columns(static::getColumns())
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->form(fn (Form $form): Form => ExamPaymentReportResource::form($form->columns(2)))
                    ->using(fn (ExamPaymentReport $record, array $data) => ExamPaymentReportResource::updateRecord($record, $data)),
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->modalHeading('Hapus laporan honor ini?')
                    ->modalDescription('Baris laporan honor ini akan dihapus permanen.'),
            ])
            ->bulkActions([
                //
            ]);
    }

    /**
     * Sama persis dengan ViewExamPaymentReportsDataTable::getColumns() (halaman
     * lama /exam/reports/{pns}/{report_date_id}) supaya tidak ada kolom yang
     * hilang - termasuk rate honor mentah (honor_pembimbing dkk) dan jumlah
     * yang dibimbing/diuji (banyak_membimbing1 dkk), yang sempat tidak ikut
     * kebawa waktu awal dipindah ke Filament.
     *
     * @return array<Tables\Columns\Column>
     */
    public static function getColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('reportdate.tanggal')
                ->label('Periode')
                ->date(),
            Tables\Columns\TextColumn::make('departemen_id')
                ->label('Departemen'),
            Tables\Columns\TextColumn::make('dosen')
                ->label('Dosen')
                ->searchable(),
            Tables\Columns\TextColumn::make('status_nama')
                ->label('Status'),
            Tables\Columns\TextColumn::make('golongan_nama')
                ->label('Gol'),
            Tables\Columns\TextColumn::make('npwp'),
            Tables\Columns\TextColumn::make('rekening'),
            Tables\Columns\TextColumn::make('jabatan_akademik'),
            Tables\Columns\TextColumn::make('pendidikan'),
            Tables\Columns\TextColumn::make('honor_pembimbing')
                ->label('Rate Honor Pembimbing')
                ->money('idr', divideBy: 1),
            Tables\Columns\TextColumn::make('honor_penguji_skripsi')
                ->label('Rate Honor Penguji Skripsi')
                ->money('idr', divideBy: 1),
            Tables\Columns\TextColumn::make('honor_penguji_proposal')
                ->label('Rate Honor Penguji Proposal')
                ->money('idr', divideBy: 1),
            Tables\Columns\TextColumn::make('honor_penguji_seminar')
                ->label('Rate Honor Penguji Seminar')
                ->money('idr', divideBy: 1),
            Tables\Columns\TextColumn::make('banyak_membimbing1')
                ->label('Bimbing 1'),
            Tables\Columns\TextColumn::make('banyak_membimbing2')
                ->label('Bimbing 2'),
            Tables\Columns\TextColumn::make('banyak_menguji_skripsi')
                ->label('Uji Skripsi'),
            Tables\Columns\TextColumn::make('banyak_menguji_proposal')
                ->label('Uji Proposal'),
            Tables\Columns\TextColumn::make('banyak_menguji_seminar')
                ->label('Uji Seminar'),
            Tables\Columns\TextColumn::make('jumlah_honor_pembimbing')
                ->money('idr', divideBy: 1),
            Tables\Columns\TextColumn::make('jumlah_honor_penguji_skripsi')
                ->money('idr', divideBy: 1),
            Tables\Columns\TextColumn::make('jumlah_honor_penguji_proposal')
                ->money('idr', divideBy: 1),
            Tables\Columns\TextColumn::make('jumlah_honor_penguji_seminar')
                ->money('idr', divideBy: 1),
            Tables\Columns\TextColumn::make('total_honor')
                ->money('idr', divideBy: 1),
            Tables\Columns\TextColumn::make('potong_pajak')
                ->label('Pajak')
                ->money('idr', divideBy: 1),
            Tables\Columns\TextColumn::make('honor_dibayar')
                ->label('Jumlah')
                ->money('idr', divideBy: 1),
        ];
    }

    public function render()
    {
        return view('filament.resources.report-date-resource.tables.payment-section-embedded');
    }
}
