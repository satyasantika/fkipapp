<?php

namespace Tests\Unit;

use App\Models\ExamPaymentReport;
use App\Models\Lecture;
use Tests\TestCase;

/**
 * Accessor-accessor ini mereplikasi persis kolom turunan yang dulu dihitung
 * SQL view `view_exam_payment_reports` sebelum view itu dihapus (lihat
 * migration 2026_07_14_150634_drop_stakeholder_and_examination_views.php).
 * Tidak butuh koneksi DB — relasi `lecture` di-mock lewat setRelation().
 */
class ExamPaymentReportAccessorsTest extends TestCase
{
    private function makeReport(array $attributes, ?Lecture $lecture = null): ExamPaymentReport
    {
        $report = new ExamPaymentReport(array_merge([
            'honor_pembimbing' => 0,
            'banyak_membimbing1' => 0,
            'banyak_membimbing2' => 0,
            'honor_penguji_skripsi' => 0,
            'banyak_menguji_skripsi' => 0,
            'honor_penguji_proposal' => 0,
            'banyak_menguji_proposal' => 0,
            'honor_penguji_seminar' => 0,
            'banyak_menguji_seminar' => 0,
        ], $attributes));

        $report->setRelation('lecture', $lecture ?? new Lecture([
            'nama' => 'Test Lecture',
            'gelar_depan' => 'Dr.',
            'gelar_belakang' => 'M.Pd.',
            'departement_id' => 99,
        ]));

        return $report;
    }

    public function test_jumlah_honor_dihitung_per_komponen(): void
    {
        $report = $this->makeReport([
            'honor_pembimbing' => 100000,
            'banyak_membimbing1' => 2,
            'banyak_membimbing2' => 1,
            'honor_penguji_skripsi' => 50000,
            'banyak_menguji_skripsi' => 2,
            'honor_penguji_proposal' => 40000,
            'banyak_menguji_proposal' => 1,
            'honor_penguji_seminar' => 45000,
            'banyak_menguji_seminar' => 3,
        ]);

        $this->assertSame(300000, $report->jumlah_honor_pembimbing);
        $this->assertSame(100000, $report->jumlah_honor_penguji_skripsi);
        $this->assertSame(40000, $report->jumlah_honor_penguji_proposal);
        $this->assertSame(135000, $report->jumlah_honor_penguji_seminar);
        $this->assertSame(575000, $report->total_honor);
    }

    public function test_golongan_4_dan_asn_kena_pajak_15_persen(): void
    {
        $report = $this->makeReport([
            'golongan' => '4',
            'status' => '1',
            'honor_pembimbing' => 100000,
            'banyak_membimbing1' => 1,
        ]);

        $this->assertSame(0.15, $report->persen_pajak);
        $this->assertSame(15000.0, $report->potong_pajak);
        $this->assertSame(85000.0, $report->honor_dibayar);
    }

    public function test_golongan_lain_kena_pajak_5_persen_default(): void
    {
        $report = $this->makeReport([
            'golongan' => '4',
            'status' => '0',
            'honor_pembimbing' => 100000,
            'banyak_membimbing1' => 1,
        ]);

        $this->assertSame(0.05, $report->persen_pajak);
    }

    /**
     * Bug lama di SQL view: kondisi tarif pajak golongan III pakai "npwp=NULL"
     * (bukan IS NULL), yang di SQL selalu UNKNOWN/false — jadi golongan III
     * TIDAK PERNAH kena tarif 0.06 dan selalu jatuh ke default 0.05. Test ini
     * mengunci perilaku tersebut secara sengaja: kalau suatu saat bug ini
     * benar-benar diperbaiki, test ini akan gagal sebagai penanda perubahan
     * yang disadari, bukan diam-diam berubah.
     */
    public function test_golongan_3_dengan_npwp_null_tetap_kena_pajak_5_persen_bug_lama_dipertahankan(): void
    {
        $report = $this->makeReport([
            'golongan' => '3',
            'status' => '0',
            'npwp' => null,
        ]);

        $this->assertSame(0.05, $report->persen_pajak);
    }

    public function test_status_nama_dan_golongan_nama(): void
    {
        $asn = $this->makeReport(['status' => '1', 'golongan' => '4']);
        $nonAsn = $this->makeReport(['status' => '0', 'golongan' => '3']);
        $belumSet = $this->makeReport(['status' => '0', 'golongan' => null]);

        $this->assertSame('ASN', $asn->status_nama);
        $this->assertSame('IV', $asn->golongan_nama);
        $this->assertSame('NON ASN', $nonAsn->status_nama);
        $this->assertSame('III', $nonAsn->golongan_nama);
        $this->assertSame('BELUM SET', $belumSet->golongan_nama);
    }

    public function test_dosen_menggabungkan_gelar_dan_nama_lengkap(): void
    {
        $lecture = new Lecture([
            'nama' => 'Budi Santoso',
            'gelar_depan' => 'Dr.',
            'gelar_belakang' => 'M.Kom.',
            'departement_id' => 7,
        ]);
        $report = $this->makeReport([], $lecture);

        $this->assertSame('Dr. Budi Santoso, M.Kom.', $report->dosen);
        $this->assertSame(7, $report->departemen_id);
    }

    public function test_dosen_tanpa_gelar_depan(): void
    {
        $lecture = new Lecture([
            'nama' => 'Budi Santoso',
            'gelar_depan' => null,
            'gelar_belakang' => 'M.Kom.',
            'departement_id' => 7,
        ]);
        $report = $this->makeReport([], $lecture);

        $this->assertSame('Budi Santoso, M.Kom.', $report->dosen);
    }
}
