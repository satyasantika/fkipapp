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
 * Sama seperti tabel /admin/exam-payment-reports (ExamPaymentReportResource),
 * dipakai di slide-over "List Bayar ASN"/"List Bayar Non-ASN" pada baris
 * ReportDateResource - discoped ke satu periode (report_date_id) + satu
 * status (pns) sekaligus, jadi kolom Status dibuang (konstan di dalam
 * slide-over ini, sama seperti kolom Lapor? yang dibuang di kartu "akan
 * dilaporkan"). Kolom lain, aksi edit (termasuk skema form-nya), dan aksi
 * delete sama persis dengan ExamPaymentReportResource - dipakai bareng lewat
 * ExamPaymentReportResource::baseColumns()/updateRecord() supaya tidak
 * digandakan.
 *
 * EditAction butuh ->form() eksplisit di sini (beda dengan di
 * ExamPaymentReportResource sendiri) karena skema form Edit biasanya
 * disambung OTOMATIS oleh Filament\Resources\Pages\ListRecords::
 * configureEditAction() - itu cuma jalan kalau tabelnya ada di halaman
 * resource asli, bukan di komponen Livewire polos seperti ini.
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
            ->columns(ExamPaymentReportResource::baseColumns(includeStatus: false))
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

    public function render()
    {
        return view('filament.resources.report-date-resource.tables.payment-section-embedded');
    }
}
