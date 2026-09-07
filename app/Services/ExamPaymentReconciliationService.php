<?php

namespace App\Services;

use App\Models\ExamPaymentReport;
use App\Models\ExamRegistration;
use App\Models\Lecture;

/**
 * Bandingkan exam_payment_reports.banyak_* (yang TERSIMPAN) dengan hitungan
 * FRESH dari exam_registrations (yang SEHARUSNYA) untuk satu periode
 * (report_date_id) - dipakai halaman "Verifikasi Laporan Honor" supaya
 * keuangan tidak perlu menghitung manual apakah semua dosen sudah masuk
 * dengan angka yang benar.
 *
 * Method hitung "expected" di sini SENGAJA reuse
 * ExamPaymentReportService::getCountOfExaminer() (bukan menulis ulang
 * query sendiri) - method itu juga memfilter kolom `{peran}_dibayar` yang
 * bisa berubah kapan saja (lewat aksi "Cabut dari laporan" dkk), jadi
 * kalau ditulis ulang manual berisiko diam-diam menyimpang dari logika
 * store() yang sebenarnya dan malah menghasilkan mismatch palsu.
 */
class ExamPaymentReconciliationService
{
    private const ROLE_COLUMNS = ['pembimbing1_id', 'pembimbing2_id', 'penguji1_id', 'penguji2_id', 'penguji3_id'];

    private const EXAM_TYPE_SEMPRO = 1;

    private const EXAM_TYPE_SEMHAS = 2;

    private const EXAM_TYPE_SIDANG = 3;

    public function __construct(private ExamPaymentReportService $reportService) {}

    /**
     * Union dosen yang punya baris exam_payment_reports UNTUK periode ini,
     * dan dosen yang muncul di salah satu dari 5 kolom peran di
     * exam_registrations periode ini (ketuapenguji_id sengaja tidak
     * diikutkan - di luar cakupan, lihat plan/diskusi terkait).
     */
    public function rosterLectureIds(int $reportDateId): array
    {
        $fromPaymentReports = ExamPaymentReport::where('report_date_id', $reportDateId)
            ->pluck('lecture_id');

        $fromRegistrations = ExamRegistration::where('report_date_id', $reportDateId)
            ->where(function ($query) {
                foreach (self::ROLE_COLUMNS as $column) {
                    $query->orWhereNotNull($column);
                }
            })
            ->get(self::ROLE_COLUMNS)
            ->flatMap(fn (ExamRegistration $row) => collect(self::ROLE_COLUMNS)->map(fn ($col) => $row->$col))
            ->filter();

        return $fromPaymentReports->merge($fromRegistrations)->unique()->values()->all();
    }

    /**
     * @return array<string, array{stored: int, expected: int, match: bool}>
     */
    public function compare(Lecture $lecture, int $reportDateId): array
    {
        $stored = ExamPaymentReport::where('report_date_id', $reportDateId)
            ->where('lecture_id', $lecture->id)
            ->first();

        $metrics = [
            'sempro' => [
                'stored' => $stored?->banyak_menguji_proposal ?? 0,
                'expected' => $this->expectedMenguji(self::EXAM_TYPE_SEMPRO, $reportDateId, $lecture->id),
            ],
            'semhas' => [
                'stored' => $stored?->banyak_menguji_seminar ?? 0,
                'expected' => $this->expectedMenguji(self::EXAM_TYPE_SEMHAS, $reportDateId, $lecture->id),
            ],
            'sidang' => [
                'stored' => $stored?->banyak_menguji_skripsi ?? 0,
                'expected' => $this->expectedMenguji(self::EXAM_TYPE_SIDANG, $reportDateId, $lecture->id),
            ],
            'pembimbing1' => [
                'stored' => $stored?->banyak_membimbing1 ?? 0,
                'expected' => $this->reportService->getCountOfExaminer(self::EXAM_TYPE_SIDANG, $reportDateId, 'pembimbing1_dibayar', 'pembimbing1_id', $lecture->id),
            ],
            'pembimbing2' => [
                'stored' => $stored?->banyak_membimbing2 ?? 0,
                'expected' => $this->reportService->getCountOfExaminer(self::EXAM_TYPE_SIDANG, $reportDateId, 'pembimbing2_dibayar', 'pembimbing2_id', $lecture->id),
            ],
        ];

        foreach ($metrics as &$metric) {
            $metric['match'] = $metric['stored'] === $metric['expected'];
        }

        return $metrics;
    }

    public function hasMismatch(array $comparison): bool
    {
        foreach ($comparison as $metric) {
            if (! $metric['match']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Persis variabel $semua di ExamPaymentReportService::store() - jumlah
     * SEMUA peran (bukan cuma satu) untuk dosen ini, exam_type_id, dan
     * periode ini.
     */
    private function expectedMenguji(int $examTypeId, int $reportDateId, int $lectureId): int
    {
        $dibayarColumns = [
            'pembimbing1_id' => 'pembimbing1_dibayar',
            'pembimbing2_id' => 'pembimbing2_dibayar',
            'penguji1_id' => 'penguji1_dibayar',
            'penguji2_id' => 'penguji2_dibayar',
            'penguji3_id' => 'penguji3_dibayar',
        ];

        $total = 0;

        foreach ($dibayarColumns as $roleColumn => $dibayarColumn) {
            $total += $this->reportService->getCountOfExaminer($examTypeId, $reportDateId, $dibayarColumn, $roleColumn, $lectureId);
        }

        return $total;
    }
}
