<?php

namespace App\Exports;

use App\Models\ExamRegistration;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

/**
 * Dipakai oleh tombol "Export Mahasiswa" di baris Penarikan Laporan
 * (ReportDateResource) — daftar mahasiswa yang ujiannya sudah masuk
 * periode penarikan itu (report_date_id), kolomnya: npm | nama | ujian |
 * tanggal | penguji 1–3 | pembimbing 1–2. Tombolnya hanya tampil jika
 * periode sudah dikunci, di samping ikon Bayar Non-ASN. Tidak membagi
 * ASN/Non-ASN: itu urusan "Export Excel" honor dosen di slide-over bayar.
 */
class PaymentSectionStudentsExport extends DefaultValueBinder implements FromCollection, WithCustomValueBinder, WithHeadings, WithMapping
{
    /**
     * NPM/NIM harus teks. Tanpa ini PhpSpreadsheet men-deteksi angka
     * (DefaultValueBinder::bindValue() memanggil is_numeric()) — Excel
     * lalu membuang nol di depan.
     */
    private const TEXT_COLUMNS = ['A'];

    public function __construct(
        protected int $reportDateId,
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
        return ExamRegistration::with([
            'student',
            'exam_type',
            'ketuapenguji',
            'penguji1',
            'penguji2',
            'penguji3',
            'pembimbing1',
            'pembimbing2',
        ])
            ->where('report_date_id', $this->reportDateId)
            ->orderBy('tanggal_ujian')
            ->orderBy('id')
            ->get();
    }

    public function headings(): array
    {
        return [
            'npm',
            'nama',
            'ujian',
            'tanggal',
            'penguji 1',
            'penguji 2',
            'penguji 3',
            'pembimbing 1',
            'pembimbing 2',
        ];
    }

    public function map($registration): array
    {
        $penguji = $registration->pengujiBerurutan();

        return [
            $registration->student?->nim ?? '',
            $registration->student?->nama ?? '',
            $registration->ujian ?? '',
            $registration->tanggal_ujian?->format('Y-m-d') ?? '',
            $penguji->get(0)?->nama ?? '',
            $penguji->get(1)?->nama ?? '',
            $penguji->get(2)?->nama ?? '',
            $registration->pembimbing1?->nama ?? '',
            $registration->pembimbing2?->nama ?? '',
        ];
    }
}
