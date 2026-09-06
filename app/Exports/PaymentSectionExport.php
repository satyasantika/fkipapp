<?php

namespace App\Exports;

use App\Models\ExamPaymentReport;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Dipakai oleh tombol "Export Excel" di slide-over "List Bayar ASN"/"List
 * Bayar Non-ASN" (ReportDateResource\Tables\PaymentSectionTable) - kolomnya
 * disamakan persis dengan tabelnya (PaymentSectionTable::getColumns()),
 * yang sendirinya sengaja disamakan dengan halaman lama
 * /exam/reports/{pns}/{report_date_id} (ViewExamPaymentReportsDataTable).
 */
class PaymentSectionExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        protected int $reportDateId,
        protected int $pns,
    ) {}

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
            $report->banyak_membimbing1,
            $report->banyak_membimbing2,
            $report->banyak_menguji_skripsi,
            $report->banyak_menguji_proposal,
            $report->banyak_menguji_seminar,
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
