<?php

namespace Tests\Feature;

use App\Models\ExamPaymentReport;
use App\Models\ReportDate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\SeedsExamFixtures;
use Tests\TestCase;

class ExamPaymentReportsDataTableTest extends TestCase
{
    use RefreshDatabase;
    use SeedsExamFixtures;

    public function test_financial_columns_are_computed_via_accessors_not_a_db_view(): void
    {
        $departement = $this->makeDepartement();
        $lecture = $this->makeLecture($departement, ['nama' => 'Mumu', 'gelar_depan' => 'H.', 'gelar_belakang' => 'Drs.']);
        $reportDate = ReportDate::create(['tanggal' => '2026-07-14']);

        ExamPaymentReport::create([
            'kode_laporan' => '2026-07',
            'lecture_id' => $lecture->id,
            'report_date_id' => $reportDate->id,
            'status' => '1',
            'golongan' => '4',
            'honor_pembimbing' => 100000,
            'banyak_membimbing1' => 2,
            'banyak_membimbing2' => 0,
            'honor_penguji_skripsi' => 50000,
            'banyak_menguji_skripsi' => 1,
            'honor_penguji_proposal' => 0,
            'banyak_menguji_proposal' => 0,
            'honor_penguji_seminar' => 0,
            'banyak_menguji_seminar' => 0,
        ]);

        $keuangan = $this->makeUserWithRole('keuangan');

        $response = $this->actingAs($keuangan)->getJson(
            "/exam/reports/1/{$reportDate->id}?draw=1&start=0&length=10",
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertOk();
        $response->assertJsonPath('recordsTotal', 1);
        $response->assertJsonPath('data.0.dosen', 'H. Mumu, Drs.');
        $response->assertJsonPath('data.0.status_nama', 'ASN');
        $response->assertJsonPath('data.0.golongan_nama', 'IV');
        $response->assertJsonPath('data.0.total_honor', 250000);
        $response->assertJsonPath('data.0.potong_pajak', 37500);
        $response->assertJsonPath('data.0.honor_dibayar', 212500);
    }
}
