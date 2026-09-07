<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\ExamPayment;
use App\Models\ExamRegistration;
use App\Models\ExamPaymentReport;
use App\Models\ReportDate;

/**
 * Konsolidasi _reportStore() yang tadinya terduplikasi persis di
 * ExamPaymentReportController dan ReportDateController (~130 baris
 * masing-masing, isinya sama kecuali cara mendapatkan $report_date_id).
 * Kedua controller tetap punya method _reportStore() publik dengan
 * signature yang sama seperti sebelumnya - keduanya kini cuma
 * mendelegasikan ke sini, supaya semua caller lama tidak perlu berubah.
 */
class ExamPaymentReportService
{
    /**
     * Hitung ulang & simpan baris exam_payment_reports untuk satu
     * pendaftaran ujian, untuk setiap slot pembimbing/penguji yang terisi.
     *
     * PENTING: method ini SENGAJA tidak mengubah kolom `dilaporkan` sama
     * sekali - itu tanggung jawab caller. Versi lama di
     * ExamPaymentReportController selalu force dilaporkan=1 di sini, tapi
     * versi lama di ReportDateController TIDAK (caller-nya, setReportDate(),
     * dipakai juga oleh alur "cabut laporan" yang justru men-set
     * dilaporkan=0 SEBELUM memanggil ini - kalau method ini ikut memaksa
     * jadi 1, alur cabut-laporan rusak). Jadi caller yang genuinely ingin
     * menandai "sudah dilaporkan" (mis. ExamPaymentReportController) harus
     * eksplisit set sendiri sebelum memanggil store().
     */
    public function store(int $examregistrationId, ?int $reportDateId = null): void
    {
        $examregistration = ExamRegistration::find($examregistrationId);
        $report_date_id = $reportDateId ?? $examregistration->report_date_id;
        $exam_type_id = $examregistration->exam_type_id;

        // kode_laporan wajib diisi (kolom NOT NULL, tanpa default) — dulu tidak
        // pernah di-set di sini sama sekali, jadi ExamPaymentReport::updateOrCreate()
        // selalu gagal saat harus INSERT baris baru (bug lama, baru ketahuan
        // sekarang karena baru sekarang path INSERT-nya benar-benar dipakai).
        // Dipakai tanggal periode laporan (bukan tanggal ujian), karena satu
        // baris exam_payment_reports meringkas SEMUA ujian milik satu dosen
        // dalam satu report_date_id — jadi kode_laporan adalah properti periode
        // laporannya, bukan properti ujian per baris.
        $kode_laporan = Carbon::parse(ReportDate::find($report_date_id)->tanggal)->format('Y-m');

        foreach (['pembimbing1', 'pembimbing2', 'penguji1', 'penguji2', 'penguji3'] as $penguji) {
            $urutan_penguji = $penguji.'_id';
            $id_penguji = $examregistration->$urutan_penguji;

            // Slot ini tidak diisi untuk ujian ini (mis. sempro/semhas yang
            // cuma punya 1 pembimbing) — lewati, tidak ada dosen untuk dibayar
            // di slot ini. Tanpa ini, updateOrCreate() di bawah gagal karena
            // lecture_id (foreign key, NOT NULL) tidak boleh null.
            if (empty($id_penguji)) {
                continue;
            }

            $pembimbing1 = $this->getCountOfExaminer($exam_type_id, $report_date_id, 'pembimbing1_dibayar', 'pembimbing1_id', $id_penguji);
            $pembimbing2 = $this->getCountOfExaminer($exam_type_id, $report_date_id, 'pembimbing2_dibayar', 'pembimbing2_id', $id_penguji);
            $penguji1 = $this->getCountOfExaminer($exam_type_id, $report_date_id, 'penguji1_dibayar', 'penguji1_id', $id_penguji);
            $penguji2 = $this->getCountOfExaminer($exam_type_id, $report_date_id, 'penguji2_dibayar', 'penguji2_id', $id_penguji);
            $penguji3 = $this->getCountOfExaminer($exam_type_id, $report_date_id, 'penguji3_dibayar', 'penguji3_id', $id_penguji);

            $semua = $pembimbing1 + $pembimbing2 + $penguji1 + $penguji2 + $penguji3;

            $data_tambahan = [];
            if ($exam_type_id == 3) {
                if ($penguji == 'pembimbing1') {
                    $data_tambahan['banyak_membimbing1'] = $pembimbing1;
                }
                if ($penguji == 'pembimbing2') {
                    $data_tambahan['banyak_membimbing2'] = $pembimbing2;
                }

                $data_tambahan['banyak_menguji_skripsi'] = $semua;
            } elseif ($exam_type_id == 1) {
                $data_tambahan['banyak_menguji_proposal'] = $semua;
            } else {
                $data_tambahan['banyak_menguji_seminar'] = $semua;
            }

            // exam_payments cuma berisi kombinasi jabatan_akademik+pendidikan
            // tertentu (bukan semua kombinasi dari dropdown edit form) — dosen
            // dengan kombinasi yang belum didaftarkan (mis. jabatan/pendidikan
            // belum lengkap diisi) bikin first() null dan crash tanpa guard ini.
            $examPayment = ExamPayment::where('jabatan_akademik', $examregistration->$penguji->jabatan_akademik)
                ->where('pendidikan', $examregistration->$penguji->pendidikan)
                ->first();

            if (! $examPayment) {
                throw new \RuntimeException(
                    'Data honor untuk jabatan akademik "'.$examregistration->$penguji->jabatan_akademik.'" dan pendidikan "'.$examregistration->$penguji->pendidikan.'" (dosen '.$examregistration->$penguji->nama.') belum diatur di data honor ujian.'
                );
            }

            ExamPaymentReport::updateOrCreate([
                'report_date_id' => $report_date_id,
                'lecture_id' => $id_penguji,
            ], array_merge([
                'kode_laporan' => $kode_laporan,
                'status' => $examregistration->$penguji->pns ? 1 : 0,
                'golongan' => substr($examregistration->$penguji->golongan, 0, 1),
                'npwp' => $examregistration->$penguji->npwp,
                'rekening' => $examregistration->$penguji->rekening,
                'jabatan_akademik' => $examregistration->$penguji->jabatan_akademik,
                'pendidikan' => $examregistration->$penguji->pendidikan,
                'honor_pembimbing' => $examPayment->honor,
                'honor_penguji_skripsi' => ExamPayment::find(3)->honor,
                'honor_penguji_proposal' => ExamPayment::find(1)->honor,
                'honor_penguji_seminar' => ExamPayment::find(2)->honor,
            ], $data_tambahan));

            ReportDate::updateOrCreate([
                'id' => $report_date_id,
            ], [
                'dibayar' => ExamPaymentReport::where('report_date_id', $report_date_id)->get()->sum('total_honor'),
                // Satu-satunya titik bersama semua alur tambah/cabut ujian
                // (setReportDate, confirmSidangCascade, confirmLecturerCascade
                // semua berujung ke sini) - dipakai sebagai "terakhir ditarik",
                // sengaja terpisah dari updated_at bawaan (lihat migrasi
                // add_lock_columns_to_report_dates_table).
                'last_pulled_at' => now(),
            ]);
        }
    }

    /**
     * Public (bukan private) supaya bisa dipakai ulang oleh
     * ExamPaymentReconciliationService untuk menghitung ulang nilai
     * "expected" saat verifikasi - murni method baca, tidak ada efek
     * samping, aman dipakai di luar store().
     */
    public function getCountOfExaminer($exam_type_id, $report_date_id, $guide_cek, $guide_order, $guide_id): int
    {
        return ExamRegistration::where('exam_type_id', $exam_type_id)
            ->where('report_date_id', $report_date_id)
            ->where('dilaporkan', 1)
            ->where($guide_cek, 1)
            ->where($guide_order, $guide_id)
            ->count();
    }
}
