<?php

namespace App\Exports;

use App\Models\ExamPaymentReport;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

/**
 * Dipakai oleh tombol "Export Excel" di slide-over "List Bayar ASN"/"List
 * Bayar Non-ASN" (ReportDateResource\Tables\PaymentSectionTable) - kolomnya
 * disamakan persis dengan tabelnya (PaymentSectionTable::getColumns()),
 * yang sendirinya sengaja disamakan dengan halaman lama
 * /exam/reports/{pns}/{report_date_id} (ViewExamPaymentReportsDataTable).
 */
class PaymentSectionExport extends DefaultValueBinder implements FromCollection, WithCustomValueBinder, WithHeadings, WithMapping
{
    /**
     * Kolom (huruf, sesuai posisi di headings()/map() - F=Npwp, G=Rekening)
     * yang harus dipaksa jadi teks. Tanpa ini, PhpSpreadsheet men-deteksi
     * NPWP/nomor rekening sebagai angka (DefaultValueBinder::bindValue()
     * memanggil is_numeric()) - Excel lalu menampilkannya dalam notasi
     * ilmiah atau membuang nol di depan, karena keduanya angka panjang,
     * bukan nilai matematis.
     */
    private const TEXT_COLUMNS = ['F', 'G'];

    public function __construct(
        protected int $reportDateId,
        protected int $pns,
    ) {}

    public function bindValue(Cell $cell, $value): bool
    {
        if (in_array($cell->getColumn(), self::TEXT_COLUMNS, true)) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function collection(): Collection
    {
        return ExamPaymentReport::with('reportdate')
            ->where('report_date_id', $this->reportDateId)
            ->where('status', $this->pns)
            ->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal Laporan',
            'Departemen Id',
            'Dosen',
            'status',
            'gol',
            'Npwp',
            'Rekening',
            'Jabatan Akademik',
            'Pendidikan',
            'Honor Pembimbing',
            'Honor Penguji Skripsi',
            'Honor Penguji Proposal',
            'Honor Penguji Seminar',
            'Banyak Membimbing1',
            'Banyak Membimbing2',
            'Banyak Menguji Skripsi',
            'Banyak Menguji Proposal',
            'Banyak Menguji Seminar',
            'Jumlah Honor Pembimbing',
            'Jumlah Honor Penguji Skripsi',
            'Jumlah Honor Penguji Proposal',
            'Jumlah Honor Penguji Seminar',
            'Total Honor',
            'PAJAK',
            'JUMLAH',
        ];
    }

    public function map($report): array
    {
        return [
            optional($report->reportdate)->tanggal,
            $report->departemen_id,
            $report->dosen,
            $report->status_nama,
            $report->golongan_nama,
            $report->npwp,
            $report->rekening,
            $report->jabatan_akademik,
            $report->pendidikan,
            $report->honor_pembimbing,
            $report->honor_penguji_skripsi,
            $report->honor_penguji_proposal,
            $report->honor_penguji_seminar,
            $report->banyak_membimbing1 ?? 0,
            $report->banyak_membimbing2 ?? 0,
            $report->banyak_menguji_skripsi ?? 0,
            $report->banyak_menguji_proposal ?? 0,
            $report->banyak_menguji_seminar ?? 0,
            $report->jumlah_honor_pembimbing,
            $report->jumlah_honor_penguji_skripsi,
            $report->jumlah_honor_penguji_proposal,
            $report->jumlah_honor_penguji_seminar,
            $report->total_honor,
            $report->potong_pajak,
            $report->honor_dibayar,
        ];
    }
}
