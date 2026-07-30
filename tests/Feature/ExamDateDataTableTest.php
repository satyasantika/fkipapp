<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\SeedsExamFixtures;
use Tests\TestCase;

/**
 * Rute reports.by.departement dulu selalu 500 (view_exam_dates tidak pernah
 * punya migration sama sekali) — lihat context di rencana refactor. Test ini
 * memastikan agregasi baru (GROUP BY tanggal_ujian atas exam_registrations)
 * menghitung sempro/semhas/sidang dengan benar per exam_type_id.
 */
class ExamDateDataTableTest extends TestCase
{
    use RefreshDatabase;
    use SeedsExamFixtures;

    public function test_counts_are_grouped_by_date_and_exam_type(): void
    {
        $departement = $this->makeDepartement();
        $student = $this->makeStudent($departement);
        // exam_type_id 1/2/3 = sempro/semhas/sidang adalah konvensi tetap yang
        // sudah dipakai di seluruh aplikasi (lihat query agregat
        // ViewExamDateDataTable) — auto-increment di DB test yang masih kosong
        // akan menghasilkan urutan id yang sama persis dengan urutan create() ini.
        $sempro = $this->makeExamType('sempro');
        $semhas = $this->makeExamType('semhas');
        $sidang = $this->makeExamType('sidang');

        $this->makeExamRegistration($departement, $student, $sempro, ['tanggal_ujian' => '2026-07-14']);
        $this->makeExamRegistration($departement, $student, $sempro, ['tanggal_ujian' => '2026-07-14']);
        $this->makeExamRegistration($departement, $student, $semhas, ['tanggal_ujian' => '2026-07-14']);
        $this->makeExamRegistration($departement, $student, $sidang, ['tanggal_ujian' => '2026-07-15']);

        $jurusan = $this->makeUserWithRole('jurusan', $departement->id);

        $response = $this->actingAs($jurusan)->getJson(
            '/exam/reports/departement?draw=1&start=0&length=10',
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertOk();
        $response->assertJsonPath('recordsTotal', 2);

        $rows = collect($response->json('data'))->keyBy(fn ($row) => substr($row['DT_RowId'], 0, 10));

        $this->assertSame(3, $rows['2026-07-14']['total']);
        $this->assertSame('2', $rows['2026-07-14']['sempro']);
        $this->assertSame('1', $rows['2026-07-14']['semhas']);
        $this->assertSame('0', $rows['2026-07-14']['sidang']);

        $this->assertSame(1, $rows['2026-07-15']['total']);
        $this->assertSame('1', $rows['2026-07-15']['sidang']);
    }

    /**
     * `tanggal_ujian` di-cast sebagai Carbon oleh ExamRegistration, jadi
     * link "detail" (action column) wajib format eksplisit ke Y-m-d sebelum
     * dipakai di route('reports.by.date', ...) — kalau tidak, Carbon
     * ter-stringify jadi "Y-m-d H:i:s" dan bikin reports.by.date 500
     * (Carbon::createFromFormat('Y-m-d', ...) gagal karena "Trailing data").
     */
    public function test_action_link_uses_clean_ymd_date(): void
    {
        $departement = $this->makeDepartement();
        $student = $this->makeStudent($departement);
        $sempro = $this->makeExamType('sempro');

        $this->makeExamRegistration($departement, $student, $sempro, ['tanggal_ujian' => '2026-07-14']);

        $jurusan = $this->makeUserWithRole('jurusan', $departement->id);

        $response = $this->actingAs($jurusan)->getJson(
            '/exam/reports/departement?draw=1&start=0&length=10',
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertOk();

        $action = $response->json('data.0.action');
        $this->assertStringContainsString('/exam/reports/date/2026-07-14"', $action);
        $this->assertStringNotContainsString('00%3A00%3A00', $action);
        $this->assertStringNotContainsString(' 00:00:00', $action);
    }

    public function test_report_by_date_page_renders_without_error(): void
    {
        $departement = $this->makeDepartement();
        $student = $this->makeStudent($departement);
        $sempro = $this->makeExamType('sempro');

        $this->makeExamRegistration($departement, $student, $sempro, ['tanggal_ujian' => '2026-07-14']);

        $jurusan = $this->makeUserWithRole('jurusan', $departement->id);

        $response = $this->actingAs($jurusan)->get('/exam/reports/date/2026-07-14');

        $response->assertOk();
    }

    public function test_default_sort_is_tanggal_ujian_descending(): void
    {
        $departement = $this->makeDepartement();
        $jurusan = $this->makeUserWithRole('jurusan', $departement->id);

        $response = $this->actingAs($jurusan)->get('/exam/reports/departement');

        $response->assertOk();
        $response->assertSee('"order":[[1,"desc"]]', false);
    }
}
